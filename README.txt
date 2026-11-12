FISG LANDING PAGE + LOGIN/REGISTRATION (PHP + MySQL)
====================================================

FILES
- index.html     Landing page
- style.css      Landing page design
- script.js      Landing page interactions
- login.php      Sign-in form
- register.php   Registration form; saves user data to MySQL
- account.php    Simple protected account page
- logout.php     Logs the user out
- db.php         Database connection settings
- database.sql   Database and users table
- auth.css       Login/registration page styling

RUN USING LARAGON
1. Start Apache and MySQL in Laragon.
2. Put this entire folder in C:\laragon\www\fisg_landing_page
   (folder name may differ, but keep all files together).
3. Open http://localhost/phpmyadmin
4. Select Import and import database.sql.
   Alternatively, open database.sql in phpMyAdmin's SQL tab and run it.
5. Check db.php. Default Laragon MySQL commonly uses user "root" and a blank password.
   Change $dbuser/$dbpass if your local MySQL credentials are different.
6. Open http://localhost/fisg_landing_page/index.html
7. Click "Daftar Sekarang" to register; test with a test email and password.
8. Log out, then test Log Masuk with that same account.

DATA STORAGE
Registration saves full name, email, phone number, password hash, and created timestamp
in the MySQL table `fisg_website.users`. The raw password is not saved; PHP password_hash()
and password_verify() are used. SQL statements use prepared queries.

IMPORTANT
- This is a local development starter, not a production-ready financial/trading platform.
- Never collect real bank card details, banking passwords, OTPs, or trading account secrets here.
- Before public deployment, add HTTPS, CSRF protection, rate limiting, email verification,
  password reset, privacy/consent wording, logging controls, and professional security review.
- Live trading, payments, deposits, withdrawals and market prices are not implemented.


ADMIN DASHBOARD SETUP
=====================
New files: admin_login.php, admin.php, admin_logout.php, admin_setup.sql

1. Import admin_setup.sql in phpMyAdmin after database.sql.
   If role column already exists, skip the ALTER TABLE statement.
2. Register your own account through register.php.
3. In phpMyAdmin > fisg_website > SQL, run this query after replacing the email:
   UPDATE users SET role = 'admin' WHERE email = 'YOUR_REGISTERED_EMAIL';
4. Open http://localhost/fisg_landing_page/admin_login.php
5. Sign in using that registered email and password.

Dashboard includes total users, registrations today, search, pagination and CSV export.
It displays name, email, phone and registration date, but never password hashes.
Do not assign the admin role to ordinary users. This starter project needs additional
security hardening (HTTPS, CSRF protection, rate limiting and audit logging) before public deployment.

ROLE SELECTION ON REGISTRATION (UPDATED)
========================================
The registration page now offers:
- User: account is created with role `user` and redirects to account.php.
- Admin: creates a `pending_admin` request and redirects to admin_pending.php.
  Admin dashboard access is NOT granted until an existing administrator approves it.

IMPORTANT DATABASE UPDATE
-------------------------
Because the current role column may be ENUM('user','admin'), change it to VARCHAR
so it can store `pending_admin`. In phpMyAdmin, select database `fisg_website` > SQL
and run this ONCE:
   ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'user';

If the role column does not exist yet, run this instead:
   ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user' AFTER password_hash;

After an existing admin reviews a request, they can approve it in phpMyAdmin:
   UPDATE users SET role = 'admin' WHERE email = 'admin-applicant@example.com';
Replace the example email with the applicant's registered email. Only approve trusted people.
The applicant can then sign in at admin_login.php and will be redirected to admin.php.

Regular users register and go to account.php. Admin applicants go to admin_pending.php.
This approval step prevents anyone from selecting Admin and immediately accessing private user data.


LOGIN ROLE SELECTOR AND REDIRECT (UPDATED)
==========================================
login.php now lets the visitor select User or Admin before entering their credentials.
- User login: account must have role `user`; successful login redirects to index.html.
- Admin login: account must have role `admin`; successful login redirects to admin.php.
- Selecting a role does not grant that role. The saved database role is checked.
- Admin applicants with role `pending_admin` cannot access admin.php until approved.

Install by replacing login.php in your existing project folder.
Keep admin.php and admin_login.php. Admins can sign in through the new role selector
at login.php or the separate admin_login.php page.

Database should already have a `role` column. If not, see the previous setup instructions.
If an admin account has not yet been approved, an existing authorised administrator
can approve it using phpMyAdmin:
UPDATE users SET role = 'admin' WHERE email = 'the-approved-email@example.com';

DEDICATED ADMIN LOGIN URL (LATEST UPDATE)
=========================================
The public login.php page is now USER-ONLY:
- No User/Admin role selector is shown.
- Successful user login redirects to index.html (homepage).
- Accounts whose database role is admin or pending_admin cannot use this page.

Administrators use a separate dedicated URL:
  http://localhost/fisg_landing_page/admin_login.php

Successful admin login redirects to:
  admin.php

Install:
1. Back up your current project folder.
2. Replace only login.php with this updated login.php.
3. Keep admin_login.php, admin.php, admin_logout.php, db.php and the database unchanged.
4. Test the user URL:
   http://localhost/fisg_landing_page/login.php
5. Test the dedicated admin URL:
   http://localhost/fisg_landing_page/admin_login.php

The admin account must already have role = 'admin' in the users table. A pending_admin
account must be approved by an authorised existing admin before it can sign in.
Do not rely on hiding the admin URL as the only security measure: admin.php and all
admin-only actions must continue checking the admin session/role server-side.
