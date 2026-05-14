# Recruitment System

## Technology Stack

- PHP
- MySQL
- Native JavaScript
- HTML
- CSS
- Node.js mail service
- Nodemailer

## Required Project Structure

```text
recruitment-system/
|-- config/
|   `-- db.php
|-- auth/
|   |-- signup.php
|   |-- signin.php
|   `-- logout.php
|-- candidate/
|   |-- dashboard.php
|   |-- profile.php
|   |-- save_profile.php
|   |-- jobs.php
|   `-- apply.php
|-- recruiter/
|   |-- dashboard.php
|   |-- create_job.php
|   |-- save_job.php
|   |-- applications.php
|   |-- accept.php
|   `-- reject.php
|-- uploads/
|   `-- cvs/
|-- assets/
|   |-- css/
|   |   `-- style.css
|   `-- js/
|       `-- main.js
|-- mail-service/
|   |-- package.json
|   |-- server.js
|   `-- .env.example
|-- database/
|   |-- schema.sql
|   `-- README.md
|-- index.php
|-- signup.php
|-- signin.php
`-- README.md
```

## Contributors and Task Split

Person 1: Yasmine Zaatour - project setup, database, authentication, sessions, and logout.

Person 2: Dhia Ayed - candidate dashboard, profile, CV upload, job browsing, and applications.

Person 3: Ghaith Bouabda - recruiter dashboard, job creation, application review, accept/reject status updates.

Person 4: Safa Khedhawria - UI polish, native JavaScript improvements, Nodemailer acceptance emails, testing, demo, and bug fixing.

## Task Progress

### Person 1: Yasmine Zaatour - Setup + Authentication

- [x] Create project folder
- [x] Create GitHub repository
- [x] Create database in phpMyAdmin
- [x] Create all database tables
- [x] Create database connection file
- [x] Create sign up page
- [x] Create sign in page
- [x] Add password hashing
- [x] Add role-based redirection
- [x] Add logout

Files:
- [x] config/db.php
- [x] signup.php
- [x] signin.php
- [x] auth/signup.php
- [x] auth/signin.php
- [x] auth/logout.php
- [x] candidate/dashboard.php
- [x] recruiter/dashboard.php
- [x] database/schema.sql
- [x] database/README.md

### Person 2: Dhia Ayed - Candidate Module

- [x] Create candidate dashboard
- [x] Create profile form
- [x] Save candidate profile
- [x] Upload PDF CV
- [x] Display all job offers
- [x] Add apply button
- [x] Save application in database
- [x] Protect candidate pages with sessions
- [x] Prevent duplicate applications
- [x] Validate CV type and size

Files:
- [x] candidate/dashboard.php
- [x] candidate/profile.php
- [x] candidate/save_profile.php
- [x] candidate/jobs.php
- [x] candidate/apply.php

### Person 3: Ghaith Bouabda - Recruiter Module

- [x] Create recruiter dashboard
- [x] Create job offer form
- [x] Save job offer
- [x] Display recruiter's job offers
- [x] Display candidates who applied to each job
- [x] Add Accept button
- [x] Add Reject button
- [x] Update application status

Files:
- [x] recruiter/dashboard.php
- [x] recruiter/create_job.php
- [x] recruiter/save_job.php
- [x] recruiter/applications.php
- [x] recruiter/accept.php
- [x] recruiter/reject.php

### Person 4: Safa Khedhawria - UI + Email + Testing

- [x] Create CSS design
- [x] Improve UI with native JavaScript
- [x] Configure Nodemailer mail service
- [x] Create mail-service/server.js
- [x] Create mail-service/package.json
- [x] Create mail-service/.env.example
- [x] Send email when candidate is accepted
- [x] Add Google Meet link in acceptance email
- [x] Test all project flows
- [x] Fix bugs with the team
- [x] Prepare demo scenario

Files:
- [x] assets/css/style.css
- [x] assets/js/main.js
- [x] recruiter/accept.php
- [x] mail-service/package.json
- [x] mail-service/server.js
- [x] mail-service/.env.example

## Person 2 Verification Notes

Person 2 candidate module was inspected on the current codebase.

Completed:
- candidate/dashboard.php protects candidate access and shows profile status.
- candidate/profile.php fetches user data, displays profile fields, pre-fills existing data, and links to the current CV.
- candidate/save_profile.php validates profile fields, validates PDF CV uploads, stores CVs in uploads/cvs/, inserts new profiles, and updates existing profiles.
- candidate/jobs.php displays jobs, checks profile completion, disables apply buttons when needed, and shows application status.
- candidate/apply.php accepts POST only, verifies profile and job existence, saves pending applications, and prevents duplicates.
- Person 2 SQL access uses PDO prepared statements.
- Dynamic candidate HTML output is escaped with htmlspecialchars().
- Duplicate applications are protected in PHP and by UNIQUE(candidate_id, job_id) in database.sql.

Missing or needs review:
- None.

## Notes for Contributors

- Person 1 should complete authentication and database setup.
- Person 3 should complete recruiter job and application management.
- Person 4 should complete UI polish, Nodemailer email notification, testing, and demo scenario.
- Person 4 owns the mail-service folder.
- Person 2 files should not be overwritten unless coordinated with Dhia Ayed.
- Do not use Laravel, Symfony, React, Vue, Bootstrap, Tailwind, jQuery, PHPMailer, or PHP mail().
- PHP code should continue using PDO, prepared statements, sessions, and htmlspecialchars().

## Local Setup With MAMP

1. Put this project folder inside MAMP's `htdocs` folder.
2. Start Apache and MySQL from MAMP.
3. Open phpMyAdmin from the MAMP start page.
4. Go to the SQL tab.
5. Copy and run the SQL from `database/schema.sql`.
6. Open the project in the browser:

```text
http://localhost:8888/intelligent-recrutement-platform/
```

If your folder is renamed to `Recruitment-System`, use:

```text
http://localhost:8888/Recruitment-System/
```

## Authentication Test Flow

1. Open the homepage.
2. Click Create Account.
3. Create a candidate account.
4. Sign in with that candidate account.
5. Confirm it redirects to `candidate/dashboard.php`.
6. Click Logout.
7. Create a recruiter account.
8. Sign in with that recruiter account.
9. Confirm it redirects to `recruiter/dashboard.php`.
10. Click Logout.
11. Try opening `candidate/dashboard.php` or `recruiter/dashboard.php` while logged out.
12. Confirm the app redirects back to `signin.php`.

## Nodemailer Mail Service

The mail service is located in:

```bash
mail-service/
```

To install dependencies:

```bash
cd mail-service
npm install
```

To configure environment variables:

```bash
cp .env.example .env
```

Then edit `.env` with real SMTP credentials.

To start the mail service:

```bash
npm start
```

Endpoint:

```text
POST http://localhost:3000/send-acceptance-email
```

Expected JSON body:

```json
{
  "candidateEmail": "candidate@example.com",
  "candidateName": "Candidate Name",
  "jobTitle": "Frontend Developer",
  "meetLink": "https://meet.google.com/xxx-xxxx-xxx"
}
