# Railway Deployment Guide

This project is configured for deployment on [Railway](https://railway.app/).

## Prerequisites
1. A [Railway](https://railway.app/) account.
2. The project code pushed to a GitHub repository.

## Deployment Steps

1. **New Project**: In Railway, create a "New Project" and select "Deploy from GitHub repo".
2. **Select Repository**: Choose this repository.
3. **Variables**: Before the final deployment (or after detailed configuration), add the following environment variables in the "Variables" tab:
    - `APP_KEY`: (Generate one locally with `php artisan key:generate --show` or use the one from your .env)
    - `APP_URL`: `https://<your-railway-url>.up.railway.app`
    - `APP_ENV`: `production`
    - `APP_DEBUG`: `false` (or `true` for debugging issues)
    - `DB_CONNECTION`: `mysql`
    
    *Database Variables*:
    - If you add a MySQL service in Railway, the connection details are usually exposed as `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`.
    - You should map these to Laravel's variables in the Railway "Variables" tab:
        - `DB_HOST` = `${MYSQLHOST}`
        - `DB_PORT` = `${MYSQLPORT}`
        - `DB_USERNAME` = `${MYSQLUSER}`
        - `DB_PASSWORD` = `${MYSQLPASSWORD}`
        - `DB_DATABASE` = `${MYSQLDATABASE}`

4. **Build & Deploy**: Railway will automatically detect the `Dockerfile` and build the image.
5. **Domain**: Go to "Settings" -> "Networking" and generate a domain (e.g., `xxx.up.railway.app`) to access your app.

## Database Setup
1. In your Railway project, click "New" -> "Database" -> "MySQL".
2. Connect it to your Laravel service as described in the "Variables" section above.

## Migrations
- The `docker/run.sh` script has a commented-out section for migrations.
- **Recommended**: Use the Railway CLI or "Command" pallet to run migrations manually after deployment:
  ```bash
  railway run php artisan migrate --force
  ```
- **Alternative**: Uncomment the migration line in `docker/run.sh` to run migrations automatically on every container startup. This is convenient for simple apps but can risk race conditions if you scale to multiple replicas.
