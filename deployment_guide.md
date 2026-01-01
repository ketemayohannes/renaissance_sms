# Beginner's Guide: Deploying to InfinityFree

This guide will help you move your Laravel application from your local machine to **InfinityFree** (`rischool.rf.gd`). Since you haven't deployed before, follow these steps exactly.

## Phase 1: Local Preparation

Before uploading anything, we need to prepare the "Production" version of your app.

### 1. Build Assets
Run this in your terminal to compile and minify your CSS and JavaScript:
```powershell
npm run build
```

### 2. Prepare Composer
Shared hosting is slow, so we optimize the PHP files:
```powershell
composer install --optimize-autoloader --no-dev
```

### 3. Handle the "Public" Folder
InfinityFree uses a folder named `htdocs` as the root. However, Laravel expects the root to be one level above `public`.
**The easiest fix for beginners:**
1.  Open your `public` folder.
2.  Select everything inside it.
3.  Copy it into a new folder named `htdocs_upload` (we will use this later).
4.  Copy all other Laravel files (app, config, routes, etc.) into a folder named `laravel_upload`.

---

## Phase 2: InfinityFree Setup

1.  **Log in** to your InfinityFree Control Panel (VistaPanel).
2.  **Create a Database**:
    - Go to "MySQL Databases".
    - Create a new database (e.g., `epiz_xxx_renaissance`).
    - Note down the **DB Host**, **DB Name**, **DB User**, and **DB Password**.
3.  **Find FTP Details**:
    - Go to "FTP Accounts" or "Account Details".
    - You need: **FTP Host**, **Username**, and **Password**.

---

## Phase 3: Uploading Files

You will need an FTP client like **FileZilla** (Free).

1.  Connect to your InfinityFree account using the FTP details.
2.  On the server side, you will see a folder named `htdocs`. **Delete everything inside it.**
3.  Upload the contents of your `htdocs_upload` (the stuff from your local `public` folder) directly into the server's `htdocs` folder.
4.  Create a folder named `laravel` **outside** of `htdocs` (at the same level as `htdocs`).
5.  Upload everything else (the contents of `laravel_upload`) into that new `laravel` folder.

---

## Phase 4: Connecting the Dots

Because we split the folders, we need to tell PHP where they are.

1.  In the `htdocs` folder on the server, find `index.php`.
2.  Edit `index.php` and change these two lines (around lines 34 and 47):
```php
// Old
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// New (updated paths)
require __DIR__.'/../laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel/bootstrap/app.php';
```

---

## Phase 5: Environment Config (.env)

1.  In your `laravel` folder on the server, create a file named `.env`.
2.  Copy the contents of your local `.env` into it, but **update these values**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://rischool.rf.gd

DB_CONNECTION=mysql
DB_HOST=sqlxxx.infinityfree.com (Get this from control panel)
DB_PORT=3306
DB_DATABASE=epiz_xxx_database
DB_USERNAME=epiz_xxx_user
DB_PASSWORD=your_password

SESSION_DRIVER=file
CACHE_STORE=file
```

---

## Phase 6: Migrating the Database

Since InfinityFree has no terminal (SSH), you can't run `php artisan migrate`.
**The work-around:**
1.  On your local machine, switch your `.env` to a local MySQL temporarily and run migrations.
2.  Export the database as a `.sql` file using a tool like TablePlus or PHPMyAdmin.
3.  Go to InfinityFree Control Panel -> **phpMyAdmin**.
4.  Select your database and click **Import**. Upload your `.sql` file.

---

## Common Issues & Tips

> [!WARNING]
> **Storage Link**: Laravel uses a "symlink" for photos. InfinityFree often blocks this. 
> To fix, you may need to change your `config/filesystems.php` to use the `public` path directly or use a script to generate the link.

> [!TIP]
> **Permissions**: Ensure the `laravel/storage` and `laravel/bootstrap/cache` folders are "Writable" (Permission 775 or 777 in FileZilla).
