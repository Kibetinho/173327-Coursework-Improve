## Coursework CMS (PHP + PostgreSQL)

Setup steps:

1. Ensure PostgreSQL and PHP PDO PgSQL extension are installed and enabled.
2. Create the database and tables:
   - Open `psql` and run:
     - `CREATE DATABASE coursework_db;`
     - `\c coursework_db`
     - `CREATE EXTENSION IF NOT EXISTS pgcrypto;` -- for crypt/gen_salt in seed
     - Run the SQL in `schema.sql`.
3. Configure connection in `config/constants.php` (host, user, password).
4. Point Apache to `public/` (or update `BASE_URL` in `config/constants.php`).
5. Visit `index.php`. Default Super User: username `super`, password `admin123`.

Notes:
- All pages require login except `public/index.php`.
- Emails use PHP `mail()`; configure SMTP/sendmail for your environment.
- Roles: `Super_User`, `Administrator`, `Author`.


