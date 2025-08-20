
# Shiori — Lightweight Student Management System

Shiori is a fast, minimal, and modern **student management system** built with vanilla PHP and a tiny MVC-ish structure. It’s designed to run locally (Laragon/XAMPP) or on a basic LAMP stack and focuses on **speed, simplicity, and safety**.


## 🧱 Tech Stack

* **PHP** 8.x (vanilla)
* **MySQL** 5.7+ / 8.x
* **Apache** 2.4 with `mod_rewrite`
* **Frontend:** Bootstrap 5, Bootstrap Icons, SweetAlert2, Chart.js, Flatpickr, FilePond (via CDN)
* **No big framework** — tiny custom router + small helpers


## 📦 Project Structure

```
Shiori/
├─ public/                # Web root (front controller)
│  └─ index.php           # Router entry
├─ app/
│  ├─ Core/
│  │  ├─ Router.php       # Minimal router
│  │  ├─ Controller.php   # Base controller
│  │  ├─ View.php         # Simple view helper
│  │  ├─ DB.php           # PDO singleton
│  │  ├─ CSRF.php         # CSRF token helper
│  │  └─ Auth.php         # Session auth helper
│  ├─ Controllers/
│  │  ├─ AuthController.php
│  │  ├─ DashboardController.php
│  │  ├─ StudentController.php
│  │  └─ ApiController.php     # /api/search, /api/stats, /api/academic-sessions
│  ├─ Models/
│  │  ├─ User.php
│  │  ├─ Student.php
│  │  ├─ Lookup.php            # classes/sections/categories/family_categories
│  │  └─ AcademicSession.php   # academic_sessions lookup
│  ├─ Services/
│  │  ├─ ImageService.php      # photo validation + thumbnail
│  │  └─ Validator.php         # input rules (email/dates/cnic/mobile/etc.)
│  └─ Views/
│     ├─ layouts/main.php      # HTML head, libs, navbar, CSRF meta
│     ├─ auth/login.php
│     ├─ dashboard/index.php
│     └─ students/
│        ├─ list.php
│        ├─ form.php
│        ├─ view.php
│        └─ search_modal.php
├─ uploads/
│  └─ students/           # writeable; stores uploaded photos
├─ config/
│  ├─ app.php             # app name, base URL
│  └─ database.php        # DB credentials (FGSS)
├─ db/
│  ├─ schema.sql          # tables + indexes
│  └─ seed.sql            # lookups + default admin
└─ storage/
   └─ logs/               # (optional) writeable
```

---

## ✅ Requirements

* PHP 8.0+ with extensions: `pdo_mysql`, `fileinfo`, `gd` (for thumbnails), `openssl`
* MySQL 5.7+ / 8.x
* Apache 2.4 with `mod_rewrite` enabled
* Windows (Laragon/XAMPP) or Linux/Mac (standard LAMP)
* `uploads/students/` must be **writeable**

---

## 🚀 Installation (Laragon / XAMPP / LAMP)

1. **Clone the repo**

   ```bash
   git clone https://github.com/infinity-decoder/Shiori.git
   ```

2. **Create database**

   * Create a MySQL database and config in `config/database.php`).


3. **Configure app**

   * `config/database.php`: set `host`, `dbname`, `user`, `password`
   * `config/app.php`: set `base_url` (examples below)

4. **Set web root**

   * Point Apache/Nginx to `public/` (preferred), **or**
   * Access as `http://localhost/Shiori/public`
   * Ensure `.htaccess` works (rewrite to `public/index.php`). Apache must have:

     * `LoadModule rewrite_module modules/mod_rewrite.so`
     * Your vhost or directory needs `AllowOverride All`

5. **Make uploads writeable**

   * `uploads/students/` must be writable by the web server user.

6. **Login**

   * Default admin :
     **username:** `admin`
     **password:** `admin123`
   * Change password after first login.

> **Note (Windows backup button):** The Backup feature calls `mysqldump` via `exec()`. Ensure `mysqldump.exe` is in your PATH and `exec()` is allowed, or disable the button.

---



## 🧭 Upcoming Features

* Installer wizard (WordPress style)
* Turn fields on/off visually
* Add custom fields
* Activity log (who created/edited/deleted)
* Import CSV
* Multi-user roles (admin/staff/read-only)
* Optional thumbnail BLOBs in DB for ultra-fast table previews


