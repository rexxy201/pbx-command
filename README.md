# PBX Command Dashboard

  Full-stack call center management dashboard for a Nigeria-based ISP.

  **Stack:** PHP 8.4 · Bootstrap 5.3 · Vanilla JS · MySQL / PostgreSQL (dual-driver)

  ## Features
  - Live call monitoring & queue management
  - IVR menus, ring groups, SIP trunks, extensions
  - WhatsApp Cloud API team inbox (assign, reply, close conversations)
  - System Settings (FreePBX/AMI, WhatsApp, SMTP credentials)
  - Call log & agent performance CSV exports
  - cPanel/shared hosting compatible (single PHP server, no Node.js)

  ## Default Login
  Email: `admin@pbx.local` · Password: `Admin123!`

  ## Deployment
  1. Upload `artifacts/pbx-php/` to your cPanel `public_html`
  2. Import `schema.sql` via phpMyAdmin
  3. Set MySQL credentials in `src/config.php`
  4. Configure WhatsApp & AMI credentials in System Settings
  