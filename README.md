# University Clinic Management System

A web app for running a university clinic: online appointment booking, nurse check-in and vital signs, doctor consultations, medical records, and a head-nurse dashboard with reports and an audit trail.

Built as a single Laravel monolith with Livewire, so there is no separate API or frontend to maintain.

> **Demo project.** The seeded accounts use a public password. Do not deploy it with real patient data until you have changed them and reviewed the security notes below.

## Team

This is a group project built by four people:

- [Aerll Kian Villalon](https://github.com/aerllkianvillalon)
- [Gerald G. Durango](https://github.com/SolielLuna)
- [Friend 2](https://github.com/friend-2-username)
- [Friend 3](https://github.com/friend-3-username)

## Features

| Role | What they can do |
|------|------------------|
| **Patient** | Register, manage profile and medical history, give data-privacy consent, book and cancel appointments, view own medical records, upload documents, read notifications |
| **Nurse** | See today's appointments, check patients in, record vital signs (abnormal readings are flagged), view records from visits they handled |
| **Doctor** | Set weekly availability, see the patients ready for consultation, write diagnosis and treatment, edit own records |
| **Head nurse** | Clinic overview with charts, manage nurse and doctor accounts, view all appointments and records, void or restore records, read the audit log, run date-range reports |

### Design decisions

- **No double-booking.** Slots are generated from each doctor's availability. Booking re-checks the slot inside a database transaction with a row lock.
- **Records are never destroyed.** Incorrect medical records are voided (soft-deleted), and only the head nurse can restore them.
- **Server-side authorization.** Access is enforced with route middleware and Laravel Policies, not just hidden buttons. A nurse only sees records from visits she handled herself.
- **Audit trail.** Changes to appointments, visits, vital signs, records and accounts are logged with who made them and the old and new values. Opening a patient profile or medical record is logged too.
- **Private file storage.** Uploaded documents sit on a private disk and are downloaded through short-lived signed URLs that are also checked against a policy.
- **Data Privacy Act (RA 10173) awareness.** Patients must consent before booking, only clinically needed data is collected, and access is logged.
- **Typed notifications.** `appointment_reminder`, `record_updated` and `system`. A scheduled command sends next-day reminders.

## Tech stack

- Laravel 12+ on PHP 8.4+
- Livewire 3, Blade, Tailwind CSS, Chart.js
- MySQL or MariaDB (SQLite works for local development)
- Laravel Breeze (authentication), Laravel Policies (authorization)
- spatie/laravel-activitylog v5 (audit trail)
- Pest (tests)

## Getting started

Requirements: PHP 8.4+, Composer, Node.js, and a database.

```bash
git clone https://github.com/<your-username>/<repo-name>.git
cd <repo-name>

composer install
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your timezone and database:

```env
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinic
DB_USERNAME=root
DB_PASSWORD=
```

Create an empty database named `clinic`, then:

```bash
php artisan migrate --seed
npm install
npm run dev
```

In a second terminal:

```bash
php artisan serve
```

Open http://127.0.0.1:8000 and log in.

To try SQLite instead, leave `DB_CONNECTION=sqlite` in `.env`. Laravel offers to create the database file when you migrate.

### Demo accounts

All demo accounts use the password `password`.

| Role | Email |
|------|-------|
| Head nurse | `headnurse@clinic.test` |
| Nurse | `nurse@clinic.test`, `nurse2@clinic.test` |
| Doctor | `doctor@clinic.test` |
| Patient | `patient@clinic.test` |

The seeded doctor is available Monday to Friday, 9:00 to 12:00 and 13:00 to 16:00, in 30-minute slots.

### Appointment reminders

Reminders run through Laravel's scheduler. Locally, keep it running with:

```bash
php artisan schedule:work
```

On a server, add the usual cron entry that runs `php artisan schedule:run` every minute.

## A typical flow to try

1. Log in as the **patient**, complete the profile and consent, then book a slot with the doctor.
2. Log in as the **nurse**, open Visits & Vital Signs, check the patient in and record vitals.
3. Log in as the **doctor**, open Consultations, and write the diagnosis and treatment.
4. Log in as the **patient** again and read the new record and notification.
5. Log in as the **head nurse** and look at the dashboard, reports and audit log.

Appointments can only be checked in on the day they are scheduled, so book a slot for today (or change the seeded data) to walk through the whole flow.

## Tests

```bash
php artisan test
```

`tests/Feature/RoleBoundariesTest.php` checks that a patient cannot view another patient's record, that a nurse only sees records from her own visits, that voiding is a soft delete, that patients are blocked from staff pages, and that a booked slot is no longer offered.

## Project structure

```text
app/
  Http/Controllers/DocumentController.php   signed, policy-checked downloads
  Http/Middleware/EnsureRole.php            role and active-account check
  Livewire/{Shared,Patient,Nurse,Doctor,HeadNurse}/
  Models/  Policies/  Console/Commands/
resources/views/
  layouts/clinic.blade.php                  role-based sidebar layout
  components/  livewire/
database/
  migrations/  seeders/
routes/web.php
tests/Feature/RoleBoundariesTest.php
```

## Known limitations

- Live notifications (Laravel Reverb) are not included. Notifications refresh on page load.
- Staff cannot view a patient's uploaded documents from the UI yet, although the download policy already allows it.
- Staff accounts are created by the head nurse with a temporary password. There is no forced password change.
- Patient data is stored unencrypted at rest. For a real deployment, add database or disk encryption and HTTPS.

## Security notes

- Change or remove every seeded account before any real deployment.
- Never commit `.env`, uploaded documents or a database file. The `.gitignore` already excludes them.
- Do not use real patient data in development.

## License

Released under the [MIT License](LICENSE). Copyright (c) 2026 the project team listed above.