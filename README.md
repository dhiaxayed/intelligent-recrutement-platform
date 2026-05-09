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

- [ ] Create project folder
- [ ] Create GitHub repository
- [ ] Create database in phpMyAdmin
- [ ] Create all database tables
- [ ] Create database connection file
- [ ] Create sign up page
- [ ] Create sign in page
- [ ] Add password hashing
- [ ] Add role-based redirection
- [ ] Add logout

Files:
- [ ] config/db.php
- [ ] signup.php
- [ ] signin.php
- [ ] auth/signup.php
- [ ] auth/signin.php
- [ ] auth/logout.php

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

- [ ] Create recruiter dashboard
- [ ] Create job offer form
- [ ] Save job offer
- [ ] Display recruiter's job offers
- [ ] Display candidates who applied to each job
- [ ] Add Accept button
- [ ] Add Reject button
- [ ] Update application status

Files:
- [ ] recruiter/dashboard.php
- [ ] recruiter/create_job.php
- [ ] recruiter/save_job.php
- [ ] recruiter/applications.php
- [ ] recruiter/accept.php
- [ ] recruiter/reject.php

### Person 4: Safa Khedhawria - UI + Email + Testing

- [ ] Create CSS design
- [ ] Improve UI with native JavaScript
- [ ] Configure Nodemailer mail service
- [ ] Create mail-service/server.js
- [ ] Create mail-service/package.json
- [ ] Create mail-service/.env.example
- [ ] Send email when candidate is accepted
- [ ] Add Google Meet link in acceptance email
- [ ] Test all project flows
- [ ] Fix bugs with the team
- [ ] Prepare demo scenario

Files:
- [ ] assets/css/style.css
- [ ] assets/js/main.js
- [ ] recruiter/accept.php
- [ ] mail-service/package.json
- [ ] mail-service/server.js
- [ ] mail-service/.env.example

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
