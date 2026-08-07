# Student Attendance Management System
**Shri. T. S. Sawant Jr. College of Science, Kanedi**

A responsive, PHP + MySQL attendance system for XI/XII Commerce (whole-class) and
XI/XII Science (practical batches), with automatic batch suggestion based on the
day of the week, role-based dashboards, reporting, CSV export, and a calendar view.

---

## 1. Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5, vanilla JavaScript (ES6), Chart.js
- **Backend:** PHP 8.x (no framework — plain procedural MVC-style structure, PDO for all DB access)
- **Database:** MySQL 5.7+/8.x
- **Security:** password_hash()/password_verify() (bcrypt), PDO prepared statements everywhere, CSRF tokens on all state-changing forms, session timeout, audit log

## 2. Folder Structure

```
attendance-system/
├── config/database.php        # DB connection + app constants (EDIT THIS FIRST)
├── includes/                  # auth.php, functions.php, header/sidebar/footer.php
├── database/schema.sql        # full schema + sample data (import this into MySQL)
├── install/create_admin.php   # one-time script to set real admin/teacher passwords
├── login.php / logout.php / forgot-password.php
├── index.php                  # redirects to the right dashboard
├── admin/                     # dashboard, students, classes, teachers, reports,
│                               # calendar, backup, audit log, settings
├── teacher/                   # dashboard, attendance (core module), history,
│                               # reports, calendar
├── api/save_attendance.php    # POST endpoint that saves a marked session
├── export/export_csv.php      # CSV export used by Students / Reports pages
├── assets/css/style.css       # Blue/White/Gold professional theme (responsive)
├── assets/js/script.js        # sidebar toggle, live clock, attendance marking JS
└── manifest.json              # PWA manifest
```

This mirrors an MVC separation even without a framework:
**Model** = `config/database.php` + `includes/functions.php` (all SQL lives here),
**View** = the HTML inside each page file,
**Controller** = the top of each page file (handles POST/GET) plus `api/save_attendance.php`.

## 3. Installation Guide

1. **Requirements:** PHP 8.x with PDO MySQL extension, MySQL 5.7+, a web server (Apache/Nginx) or `php -S localhost:8000`.
2. **Copy files** to your web root, e.g. `/var/www/html/attendance-system` or your XAMPP/WAMP `htdocs`.
3. **Create the database:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   This creates the `attendance_system` database, all 11 tables, the college's exact
   class/batch/schedule structure, and a handful of sample students.
4. **Configure the connection** — edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'attendance_system');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('APP_URL', 'http://localhost/attendance-system'); // no trailing slash
   ```
5. **Set your admin & teacher passwords** — open `install/create_admin.php` in your
   browser and choose passwords for the `admin` and `teacher1` sample accounts.
   **Delete `install/create_admin.php` afterward.**
6. **Log in** at `login.php` with the credentials you just set.
7. **Import the full student list** — either add students one by one under
   *Admin → Students → Add Student*, or bulk-load them by adapting the `INSERT INTO students`
   block at the bottom of `database/schema.sql` (columns: admission_no, roll_no,
   student_name, gender, class_id, batch_id, mobile_number, parent_name, status).

### Class/Batch IDs (from the seed data, for bulk import)
| id | Class | Stream | Batches |
|----|-------|--------|---------|
| 1 | XI  | Commerce | whole class (batch_id = NULL) |
| 2 | XII | Commerce | whole class (batch_id = NULL) |
| 3 | XI  | Science  | batch 1=A(Mon), 2=B(Tue), 3=C(Wed) |
| 4 | XII | Science  | batch 4=A(Thu), 5=B(Fri), 6=C(Sat, 35 students) |

## 4. User Manual

### Admin
- **Dashboard:** today's totals, 30-day attendance trend chart, class-wise %, lowest-attendance students.
- **Students:** add/edit/delete, search by name/roll/admission no, filter by class, CSV export.
- **Classes & Batches:** add classes, add/update practical batches (name, capacity, practical day) — this is what drives the automatic batch suggestion.
- **Teachers:** create teacher login accounts, activate/deactivate.
- **Reports:** Daily / Weekly / Monthly / Class-wise / Batch-wise / Student-wise / Subject-wise / Teacher-wise, each with CSV export.
- **Calendar:** month view color-coded Green/Yellow/Red/Gray; unlock any past date so a teacher can correct that day's attendance.
- **Backup:** one-click full SQL database backup download.
- **Audit Log:** last 200 system actions (logins, saves, deletes, settings changes).
- **Settings:** college name, low-attendance threshold, whether teachers may override the auto-suggested batch, auto-lock behavior.

### Teacher
- **Dashboard:** today's suggested practical batch, today's counts, recent sessions, 7-day trend.
- **Take Attendance:**
  1. Pick a **Date** — the system automatically suggests the correct Class + Batch for that day (see schedule below) and displays it in a blue banner.
  2. Pick a **Subject**.
  3. Student list appears with ✅ Present / ❌ Absent / 🟡 Late / 🔵 Leave buttons per student, plus **Mark All Present / Mark All Absent / Reset**.
  4. Click **Save Attendance**.
  - Attendance **cannot be submitted twice** for the same class+batch+subject+date (it becomes an edit instead of a duplicate).
  - A session is **editable only on the day it was taken**; after midnight it becomes read-only until Admin unlocks it from *Admin → Calendar*.
- **Attendance History:** filter by date range/class, see % per session, jump back into an editable session.
- **Monthly Report:** per-student Present/Absent/Late/Leave totals and %, with CSV export and print view.
- **Calendar:** same color-coded month view, read-only.

### Automatic Batch Selection (the college's exact timetable)

| Day | Class | Batch | Students |
|-----|-------|-------|----------|
| Monday | XI Science | A | 25 |
| Tuesday | XI Science | B | 25 |
| Wednesday | XI Science | C | 25 |
| Thursday | XII Science | A | 30 |
| Friday | XII Science | B | 30 |
| Saturday | XII Science | C | 35 |

Commerce classes (XI/XII, 25 each) attend as a whole class and are selected directly
(no batch), independent of this day-based rule. This mapping lives in the `batches`
table (`practical_day` column) — change it any time from *Admin → Classes & Batches*
without touching code.

## 5. Security Notes for Deployment

- Change `DB_USER`/`DB_PASS` to a least-privilege MySQL user (not `root`) in production.
- Serve the site over HTTPS; set `session.cookie_secure` in `php.ini` accordingly.
- Delete `install/create_admin.php` after first use.
- The CSV/backup export routes are all behind `requireLogin()`/`requireRole()` guards.
- All forms include CSRF tokens; all SQL uses PDO prepared statements.

## 6. Known Simplifications / Next Steps

To keep this deliverable focused and reviewable, a few "optional/future" items from
the spec are stubbed for later extension rather than fully built: PDF export (CSV +
browser Print/Save-as-PDF is wired up today), Excel `.xlsx` import (CSV bulk-add via
the SQL seed block works today), QR/barcode, SMS, and email notifications. The
database schema and UI already leave room for all of these — they weren't core to
daily attendance-taking, which was the priority.
