# Homelab deployment

The Compose stack runs Apache/PHP, a database queue worker and the scheduler. It publishes HTTP only on the supplied NetBird and Tailscale interface addresses. The source image contains neither environment secrets nor SQLite data.

Keep the production environment, SQLite database, uploads and backups outside release directories. Preserve `APP_KEY` when migrating: the stored Resend key depends on it. Use SQLite's backup operation instead of copying an active database file.

The Compose environment file supplies `LARABLOGS_VERSION`, `LARABLOGS_ENV_FILE`, `LARABLOGS_DATA_DIR`, `LARABLOGS_STORAGE_DIR`, the three VPN addresses and optionally `LARABLOGS_PORT` (default 80). The application environment uses `APP_ENV=production`, `APP_DEBUG=false`, `DB_CONNECTION=sqlite`, `DB_DATABASE=/data/database.sqlite`, `SESSION_DRIVER=database`, `CACHE_STORE=database` and `QUEUE_CONNECTION=database`.

Build with `docker compose --env-file /private/compose.env build app`. Before starting services, run `docker compose --env-file /private/compose.env run --rm app php artisan migrate --force` and bootstrap the private account using `account:bootstrap` with private email and temporary-password files. The first login requires changing the password. Start with `docker compose --env-file /private/compose.env up -d`.

Use host directories owned by UID/GID 1000 with private permissions; the containers run as that user. Preserve a consistent database snapshot before migrations or upgrades. For a rollback, keep the previous image/release and the matching pre-migration snapshot. Reusing an old snapshot discards subsequent changes, so restore one only deliberately.

The app's `/up` health endpoint is public. Blogs, reader content and email settings require the private account. Test browser flows with `composer run dusk`; they use their own temporary database and processes.
