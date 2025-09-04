# WordPress Spin-up & Restore in Docker (Windows / PowerShell)

This kit assumes a `docker-compose.yml` that mounts `./_data` to `/var/www/html` and defines:
- `db` → MariaDB
- `wordpress` → `wordpress:php8.x-apache`
- `wpcli` → `wordpress:cli-php8.x`

## Requirements
- Windows 10/11 with **Docker Desktop** (WSL2).
- Files required in the same folder as `docker-compose.yml`:
  - `wordpress.sql.gz` → MySQL/MariaDB dump.
  - (optional) `uploads.tar.gz` containing `wp-content/uploads`.
  - `.env` (or `.env.sample`) with at least:
    ```
    MYSQL_DATABASE=wordpress
    MYSQL_USER=wp_user
    MYSQL_PASSWORD=bsccns
    MYSQL_ROOT_PASSWORD=put_a_strong_password_here
    ```

## One-shot usage
1. Copy .env file from NexCloud Service on the application root.
2. Copy `restore.ps1` next to `docker-compose.yml` and `wordpress.sql.gz`.
3. Open PowerShell in that folder and run:
   ```powershell
   .\restore.ps1 -DumpFile "wordpress.sql.gz" -SiteURL "http://localhost:8080" -Clean
   ```
   - `-Clean` (optional) removes containers **and volumes** to start from scratch.

3. Open `http://localhost:8080` in your browser (preferably in a private window).

## Restore media (uploads)
- If you have `uploads.tar.gz`:
  ```powershell
  tar -xzf .\uploads.tar.gz -C .\_data
  docker compose exec -T wordpress bash -lc 'chown -R www-data:www-data wp-content/uploads && find wp-content/uploads -type d -exec chmod 755 {} \; && find wp-content/uploads -type f -exec chmod 644 {} \;'
  ```
- If you don't see images after importing, update old URLs to `localhost`:
  ```powershell
  docker compose run --rm wpcli search-replace "https://YOUR-DOMAIN" "http://localhost:8080" --all-tables --precise --allow-root
  docker compose run --rm wpcli search-replace "http://YOUR-DOMAIN"  "http://localhost:8080" --all-tables --precise --allow-root
  ```

## If you get a white page
The script already disables plugins, activates a default theme, and enables logs.
- View logs:
  ```powershell
  docker compose logs --tail=120 wordpress
  docker compose exec -T wordpress bash -lc 'tail -n 100 wp-content/debug.log || echo "no debug.log"'
  ```

## Notes
- If `8080` or `3306` are in use, change the `ports:` in `docker-compose.yml` (e.g., `8088:80`) and use `-SiteURL "http://localhost:8088"`.
- If Compose shows `version is obsolete`, remove the `version: "3.9"` line from `docker-compose.yml`.
- To stop:
  ```powershell
  docker compose down
  docker compose down -v
  ```
