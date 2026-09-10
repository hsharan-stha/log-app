# ABIS School Portal (Laravel 12)

School portal for Asian Bridge International School-style operations: principal, HR, finance, teachers, students, guardians, attendance operator, and general staff — plus a **device-locked face attendance kiosk** and **billing**.

## Roles

| Role | After login | Notes |
|------|-------------|--------|
| Admin / Principal | `/admin/dashboard` | Full school admin, office users, revoke kiosks |
| HR | `/admin/academics` | Academics setup, teachers, students, guardians |
| Finance | `/finance` | Fee types, invoices, record payments |
| Attendance | `/attendance/home` | Setup kiosk + face check-in/out |
| Staff / Other | `/office` | Basic office portal + messages |
| Teacher | `/teacher` | Courses, class, notices, face register |
| Student | `/student` | Subjects + view own invoices |
| Guardian | `/guardian` | Child attendance + invoices |

Default password for newly created users: `config('staff.default_password')` (env `STAFF_DEFAULT_PASSWORD`, usually `password`).

## Billing & payments

1. Finance (or admin) creates **fee types** (e.g. tuition).
2. Issues **invoices** to students.
3. Records **payments** (cash, bank transfer, card, other) — status becomes partial/paid.
4. Guardians and students can **view** invoices and payment history (pay at school office; no online card gateway yet).

## Face attendance (school tablet only)

1. Sign in as the **attendance** user → **Setup kiosk** on the tablet once.
2. Only that device + attendance login can open `/attendance`.
3. Students and teachers are matched by face; guardians are notified for students.

## Quick start

```bash
composer install
cp .env.example .env   # if present
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan serve
```

### Demo accounts (after seed)

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password | Principal |
| hr@example.com | password | HR |
| finance@example.com | password | Finance |
| attendance@example.com | password | Attendance |
| staff@example.com | password | Staff |
| other@example.com | password | Other |
| teacher@example.com | password | Teacher |
| student@example.com | password | Student |
| guardian@example.com | password | Guardian |

Classes Nursery–Grade 5 are seeded. UKG is linked to the demo teacher/student/guardian. A sample tuition invoice is seeded for the demo student.

## Requirements

- PHP 8.2+
- Composer
- MySQL (or SQLite for tests)
- Webcam + HTTPS in production for the kiosk
