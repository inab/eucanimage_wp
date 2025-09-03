param(
  [string]$DumpFile = "wordpress.sql.gz",
  [string]$SiteURL  = "http://localhost:8080",
  [switch]$Clean = $false,           # si lo pasas, borra contenedores y VOLUMENES (DB incluida)
  [string]$DbService = "db",
  [string]$WpService = "wordpress"
)

$ErrorActionPreference = "Stop"

function Load-Env {
  if (-not (Test-Path ".env") -and (Test-Path ".env.sample")) { Copy-Item ".env.sample" ".env" }
  if (-not (Test-Path ".env")) { throw ".env no existe. Crea uno (puedes copiar .env.sample) y pon un MYSQL_ROOT_PASSWORD." }
  Get-Content .env | ForEach-Object {
    if ($_ -match '^\s*#' -or $_ -match '^\s*$') { return }
    if ($_ -match '^\s*([^=]+)=(.*)$') {
      $k = $Matches[1].Trim(); $v = $Matches[2]
      if ($v.StartsWith('"') -and $v.EndsWith('"')) { $v = $v.Substring(1, $v.Length-2) }
      if ($v.StartsWith("'") -and $v.EndsWith("'")) { $v = $v.Substring(1, $v.Length-2) }
      [System.Environment]::SetEnvironmentVariable($k,$v,"Process")
    }
  }
  if (-not $env:MYSQL_DATABASE) { $env:MYSQL_DATABASE = "wordpress" }
}

function Ensure-ContentDir {
  # Tu compose monta ./_data → /var/www/html. Solo avisamos si está vacío.
  if (-not (Test-Path ".\_data")) { New-Item -ItemType Directory -Path ".\_data" | Out-Null }
  if (-not (Test-Path ".\_data\wp-admin")) {
    Write-Host "Aviso: .\_data no contiene wp-admin/wp-content. La imagen copiará WP limpio al primer arranque." -ForegroundColor Yellow
  }
}

function Compose-Down {
  if ($Clean) {
    Write-Host "Bajando stack y borrando VOLUMENES (DB incluida)..." -ForegroundColor Yellow
    docker compose down -v
  } else {
    Write-Host "Bajando stack (conservando volúmenes)..." -ForegroundColor Cyan
    docker compose down
  }
}

function Up-DB-And-Wait {
  Write-Host "Levantando DB..." -ForegroundColor Cyan
  docker compose up -d $DbService | Out-Null
  Write-Host "Esperando a MySQL..." -ForegroundColor Cyan
  for ($i=0; $i -lt 60; $i++) {
    docker compose exec -T $DbService sh -lc 'mysqladmin ping -h 127.0.0.1 -p"$MYSQL_ROOT_PASSWORD" --silent >/dev/null 2>&1'
    if ($LASTEXITCODE -eq 0) { return }
    Start-Sleep -Seconds 2
  }
  throw "MySQL no responde. Revisa: docker compose logs $DbService"
}

function Import-Dump {
  param([string]$File)
  if (-not (Test-Path $File)) { throw "No encuentro el dump: $File" }
  $cid = docker compose ps -q $DbService
  if (-not $cid) { throw "No encuentro el contenedor de $DbService" }

  Write-Host "Copiando dump al contenedor..." -ForegroundColor Cyan
  if ($File.ToLower().EndsWith(".gz")) {
    docker cp $File "$($cid):/dump.sql.gz" | Out-Null
    # Filtra líneas "mysqldump:" (cuando el dump trae avisos) usando sed y, si no está, grep
    $cmd = 'gzip -dc /dump.sql.gz | sed "/^mysqldump:/d" 2>/dev/null | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}" || gzip -dc /dump.sql.gz | grep -v "^mysqldump:" | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}"'
  } else {
    docker cp $File "$($cid):/dump.sql" | Out-Null
    $cmd = 'sed "/^mysqldump:/d" /dump.sql 2>/dev/null | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}" || grep -v "^mysqldump:" /dump.sql | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}"'
  }
  Write-Host "Importando en BD $($env:MYSQL_DATABASE)..." -ForegroundColor Cyan
  docker compose exec -T $DbService sh -lc $cmd
  if ($LASTEXITCODE -ne 0) { throw "Importación fallida" }
}

function Up-WP {
  Write-Host "Levantando WordPress..." -ForegroundColor Cyan
  docker compose up -d $WpService | Out-Null
}

function Post-Fix {
  Write-Host "Ajustando siteurl/home -> $SiteURL" -ForegroundColor Cyan
  docker compose run --rm wpcli option update siteurl "$SiteURL" --allow-root | Out-Null
  docker compose run --rm wpcli option update home    "$SiteURL" --allow-root | Out-Null

  Write-Host "Desactivando todos los plugins..." -ForegroundColor Cyan
  docker compose run --rm wpcli plugin deactivate --all --allow-root | Out-Null

  Write-Host "Activando tema twentytwentyfour..." -ForegroundColor Cyan
  docker compose run --rm wpcli theme activate twentytwentyfour --allow-root
  if ($LASTEXITCODE -ne 0) {
    docker compose run --rm wpcli theme install twentytwentyfour --activate --allow-root
  }

  Write-Host "Flush de permalinks..." -ForegroundColor Cyan
  docker compose run --rm wpcli rewrite flush --hard --allow-root | Out-Null

  Write-Host "Habilitando WP_DEBUG (log en wp-content/debug.log)..." -ForegroundColor Cyan
  docker compose run --rm wpcli config set WP_DEBUG true --raw --type=constant --allow-root | Out-Null
  docker compose run --rm wpcli config set WP_DEBUG_LOG true --raw --type=constant --allow-root | Out-Null
  docker compose run --rm wpcli config set WP_DEBUG_DISPLAY false --raw --type=constant --allow-root | Out-Null
}

# -------- MAIN --------
try {
  Load-Env
  Ensure-ContentDir
  Compose-Down
  Up-DB-And-Wait
  Import-Dump -File $DumpFile
  Up-WP
  Post-Fix
  Write-Host "`n✅ Todo listo. Abre: $SiteURL" -ForegroundColor Green
  Write-Host "👉 Logs recientes de wordpress:" -ForegroundColor Cyan
  docker compose logs --tail=60 $WpService
}
catch {
  Write-Error $_.Exception.Message
  exit 1
}
