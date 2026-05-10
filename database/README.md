# Database Setup

This project uses MySQL with MAMP and phpMyAdmin.

## Database name

```text
recruitment_system
```

## MAMP credentials

```text
host: localhost
user: root
password: root
```

## Create the database with phpMyAdmin

1. Start MAMP.
2. Open phpMyAdmin from the MAMP start page.
3. Click the SQL tab.
4. Open `database/schema.sql`.
5. Copy the full SQL script into phpMyAdmin.
6. Click Go.

The script creates these tables:

- `users`
- `candidate_profiles`
- `job_profiles`
- `applications`

The `users.password` column stores hashed passwords created with PHP `password_hash()`.
