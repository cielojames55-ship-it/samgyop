PREMIER THE SAMGYUPSAL - SMART TABLE & INVENTORY SYSTEM
PHP + MySQL + Laragon starter project

INSTALLATION
1. Extract the folder "premier_samgyupsal_system" into:
   C:\laragon\www\
2. Start Laragon and click Start All.
3. Open HeidiSQL and select the database premier_samgyupsal.
4. Run setup.sql to create the staff login table and initial admin account.
5. Open config/database.php and confirm your MySQL username/password.
   Default Laragon settings are usually username=root and blank password.
6. Visit:
   http://localhost/premier_samgyupsal_system/login.php

INITIAL LOGIN
Username: admin
Password: password

Change the initial password before using this system beyond local testing.

FEATURES
- Staff login/logout
- Dashboard summary
- Inventory create, read, update, delete
- Refill request list and status management
- Customer refill form by table number
- Inventory deduction when a request is marked Completed
- Table refill URLs for QR-code preparation

IMPORTANT LIMITATIONS
- The supplied database has four core tables. setup.sql adds the staff table.
- Existing customers.table_number is UNIQUE, so this starter design stores one active
  customer record per table. It updates that customer record when a new request is sent.
- QR URL links are provided; QR image generation is not bundled.
- For a phone to access the local server, use the computer's LAN IP address instead
  of localhost, and configure Windows Firewall/Laragon access.
- This is a local academic starter system. Add CSRF protection, stronger role checks,
  audit logs, backups, and production security before public deployment.
- Refill stock is deducted only when staff changes a request to Completed.
  The deduction is protected against duplicate completion through a database transaction.
