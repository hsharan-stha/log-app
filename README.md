# Face Attendance (Laravel 12)

Simple face check-in/out kiosk for staff (no login) and a Bootstrap admin panel for managing people and logs. Face recognition runs entirely in the browser with [face-api.js](https://github.com/justadudewhohacks/face-api.js) and its model weights served from `public/vendor` (no external CDN); descriptors are compared on the server using Euclidean distance (threshold `0.5`).

## Requirements

- PHP 8.2+
- Composer
- Node.js 20.19+ or 22.12+ (only if you change the default Vite/Breeze assets)
- A modern browser with webcam access (HTTPS in production)

Check-in and check-out **snapshots** from the kiosk are saved under `storage/app/public/attendance/…` and shown in the admin attendance views. Run `php artisan storage:link` so `/storage/...` URLs resolve.

## Quick start

```bash
cd /path/to/logApp
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

**Database:** the default `.env` uses SQLite (`database/database.sqlite`). Create the file if needed:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

**Run the app:**

```bash
php artisan serve
```

- Staff kiosk: `http://127.0.0.1:8000/attendance`
- Admin login: `http://127.0.0.1:8000/login`

### Default admin account (from seeder)

| Field    | Value             |
|----------|-------------------|
| Email    | `admin@example.com` |
| Password | `password`        |

Change this password immediately in production.

## Optional CLI admin

```bash
php artisan face-attendance:create-admin --email=you@example.com --password='your-secure-password' --name='Site Admin'
```

## Front-end assets

Admin and kiosk pages load **Bootstrap 5**, **face-api.js**, and **Figtree** (Breeze layouts) from `public/vendor/…`, so those screens work offline after deploy. The Breeze/Vite stack remains available if you extend the default Tailwind views elsewhere.

Neural **model weights** live under `public/vendor/face-api.js/weights` (same files as the upstream face-api.js repo).

## Attendance flow

1. Admin creates a staff record and registers a face descriptor under **Staff → Face**.
2. Staff open `/attendance`, start the camera, and tap **Scan face**.
3. First successful match each calendar day records **check-in**; the second records **check-out**. Additional scans return “Attendance already completed”.

## Security notes

- Serve the kiosk over **HTTPS** outside `localhost` so browsers allow `getUserMedia`.
- Treat face descriptors as sensitive data (they are stored encrypted-at-rest only if your database layer provides that).
- Rotate the default admin password and restrict who can access `/admin/*`.

## Running tests

```bash
php artisan test
```
