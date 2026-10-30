<?php
// Copy this file to config.php and fill in real values for your environment.
// config.php is gitignored so real credentials never get committed.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'markme_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');


// Base URL of the app as served by XAMPP, no trailing slash.

define(
    'BASE_URL',
    rtrim(
        getenv('BASE_URL') ?: 'http://localhost/markme',
        '/'
    )
);


// Upload constraints
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024); // 2MB
// Smallest artwork that could actually be printed on a bookmark. Anything
// below this is a valid image file but not a usable shape or design.
define('MIN_UPLOAD_PIXELS', 120);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_IMAGE_MIME', ['image/jpeg', 'image/png', 'image/webp']);

// Antivirus scanning for uploads.
//
// Leave AV_SCANNER blank and no scan happens - MarkMe will say so rather than
// implying files were checked. Point it at a ClamAV binary (clamscan.exe or
// clamdscan.exe) to turn real scanning on; clamdscan is much faster because it
// talks to a resident daemon.
//
// AV_REQUIRED decides what happens when no scanner is configured:
//   true  - refuse every upload (fail closed; correct for a public server)
//   false - accept the upload and record that it was not scanned
define('AV_REQUIRED', true);

// ---------------------------------------------------------------------
// PayMongo (https://docs.paymongo.com)
//
// The secret key is exactly that: it authenticates every charge, so it lives
// here in config.php, which is gitignored, and never reaches the browser.
// Keys beginning sk_test_ put the integration in test mode automatically -
// nothing else has to be switched - so live payments cannot happen by
// accident.
//
// Where to get them:
//   1. Create a free account at https://dashboard.paymongo.com
//   2. Developers -> API keys, and copy the TEST keys (pk_test_... / sk_test_...)
//   3. Developers -> Webhooks, add an endpoint pointing at
//      <your BASE_URL>/customer/paymongo_webhook.php, subscribe to
//      checkout_session.payment.paid, and copy the signing secret it returns.
//
// Two ways to supply them, checked in this order:
//
//   1. Environment variables - PAYMONGO_PUBLIC_KEY, PAYMONGO_SECRET_KEY and
//      PAYMONGO_WEBHOOK_SECRET. Nothing is then written into any file in the
//      project, which is the safest option: a key that was never in a file
//      cannot be committed, copied into a screenshot or left in a backup.
//      Under XAMPP, add to httpd.conf (or a vhost) and restart Apache:
//
//          SetEnv PAYMONGO_PUBLIC_KEY   pk_test_xxxxxxxx
//          SetEnv PAYMONGO_SECRET_KEY   sk_test_xxxxxxxx
//          SetEnv PAYMONGO_WEBHOOK_SECRET whsk_xxxxxxxx
//
//   2. Otherwise, the literal below. config.php is gitignored, so this is
//      safe for local development - but the value does end up in a file.
//
// Leave both unset and MarkMe keeps offering Cash on Delivery and Bank
// Transfer only - the card option simply does not appear.
define('PAYMONGO_PUBLIC_KEY', getenv('PAYMONGO_PUBLIC_KEY') ?: '');
define('PAYMONGO_SECRET_KEY', getenv('PAYMONGO_SECRET_KEY') ?: '');
define('PAYMONGO_WEBHOOK_SECRET', getenv('PAYMONGO_WEBHOOK_SECRET') ?: '');

// Personalized bookmark text. Enforced server-side in add_to_cart.php and
// design_save.php; the studio's maxlength mirrors it for a good experience.
define('CUSTOM_TEXT_MAX', 20);

// ---------------------------------------------------------------------
// Security integrations
//
// These are the only two features that need credentials from outside the
// project. Both fail closed: with the values left blank, MarkMe does not
// render a CAPTCHA widget and does not claim an activation email was sent.
// Nothing here is a secret that belongs in version control - config.php is
// gitignored, and config.example.php carries empty placeholders.
// ---------------------------------------------------------------------

// Google reCAPTCHA v2 ("I'm not a robot"). Get keys at
// https://www.google.com/recaptcha/admin  -> register localhost.

define(
    'RECAPTCHA_SITE_KEY',
    getenv('RECAPTCHA_SITE_KEY') ?: ''
);

define(
    'RECAPTCHA_SECRET_KEY',
    getenv('RECAPTCHA_SECRET_KEY') ?: ''
);

define(
    'FORCE_HTTPS',
    filter_var(
        getenv('FORCE_HTTPS') ?: 'false',
        FILTER_VALIDATE_BOOLEAN
    )
);


// SMTP, for account-activation email. For Gmail use an App Password, not
// the account password: host smtp.gmail.com, port 587, secure 'tls'.
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');   // 'tls' | 'ssl' | '' for none
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', '');        // defaults to SMTP_USER when blank


// Mailjet settings
define('MAILJET_API_KEY', getenv('MAILJET_API_KEY') ?: '');
define('MAILJET_SECRET_KEY', getenv('MAILJET_SECRET_KEY') ?: '');
define('MAILJET_FROM_EMAIL', getenv('MAILJET_FROM_EMAIL') ?: '');


// Set to true once the site is served over HTTPS. It turns on the Secure
// flag for the session cookie; leaving it true on plain HTTP would stop
// the cookie being sent at all, which is why it is not simply hard-coded.

// ---------------------------------------------------------------------
// Application timezone
//
// PHP and MySQL must agree, or every comparison between a stored timestamp
// and PHP's clock is wrong by the difference between them. XAMPP ships PHP
// on whatever the php.ini says (often not local time) while MySQL uses the
// machine's timezone, which is how a lock set "now" could look six hours in
// the future and a lockout could never expire.
//
// Set this to the timezone the database server runs in.
// ---------------------------------------------------------------------
define('APP_TIMEZONE', 'Asia/Manila');

