# Restore WordPress in Docker — Linux (bash)

This script spins up the database, imports the SQL dump, and starts WordPress while applying basic fixes
(disable plugins, activate a default theme, set a local URL). It assumes a `docker-compose.yml` that mounts `./_data` at `/var/www/html`
and defines the `db`, `wordpress`, and `wpcli` services.

## Requirements
- Docker and **docker compose** (v2) or `docker-compose` (v1).
- Files in the same folder as `docker-compose.yml`:
  - `.env` (or `.env.sample`) with: `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`.
  - `wordpress.sql.gz` (database dump).
  - *(optional)* `uploads.tar.gz` containing `wp-content/uploads` from the site.

## Usage
```bash
chmod +x restore-linux.sh
./restore-linux.sh -f wordpress.sql.gz -u http://localhost:8080 --clean
```
Parameters:
- `-f, --dump-file` → path to the `.sql` or `.sql.gz` dump (default: `wordpress.sql.gz`).
- `-u, --site-url` → local URL (default: `http://localhost:8080`).
- `--clean` → runs `down -v` and removes volumes (DB included) before starting.
- `--db` / `--wp` → service names in the compose file (defaults: `db` and `wordpress`).

## Restore media (uploads)
If you have `uploads.tar.gz` (created from `/var/www/html`):
```bash
tar -xzf uploads.tar.gz -C ./_data
docker compose exec -T wordpress bash -lc 'chown -R www-data:www-data wp-content/uploads && find wp-content/uploads -type d -exec chmod 755 {} \; && find wp-content/uploads -type f -exec chmod 644 {} \;'
```
If images are missing after import because they point to the old domain:
```bash
docker compose run --rm wpcli search-replace "https://YOUR-DOMAIN" "http://localhost:8080" --all-tables --precise --allow-root
docker compose run --rm wpcli search-replace "http://YOUR-DOMAIN"  "http://localhost:8080" --all-tables --precise --allow-root
```

## Notes
- If a port is in use (8080/3306), adjust `ports:` in the compose file and pass the correct URL with `-u`.
- If your WordPress is old and breaks on PHP 8.2, try `image: wordpress:php8.1-apache` (or, temporarily, `php7.4-apache`).

Ready to go! ✨
