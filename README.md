<h1 style="font-family: 'poppins'; font-weight: bold; color: Green;">👨‍💻Author: MAHBOOB ALAM</h1>

[![GitHub](https://img.shields.io/badge/GitHub-Profile-red?style=for-the-badge&logo=github)](https://github.com/infinity-decoder) 
[![Kaggle](https://img.shields.io/badge/Kaggle-Profile-orange?style=for-the-badge&logo=kaggle)](https://www.kaggle.com/infinitydecoder) 
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Profile-yellow?style=for-the-badge&logo=linkedin)](https://pk.linkedin.com/in/infinitydecoder)  
[![Facebook](https://img.shields.io/badge/Facebook-Profile-green?style=for-the-badge&logo=facebook)](https://www.facebook.com/infinitydecoder.me) 
[![Coursera](https://img.shields.io/badge/coursera-Profile-blue?style=for-the-badge&logo=coursera)](https://www.coursera.org/learner/infinitydecoder) 
[![Website](https://img.shields.io/badge/website-visit-indigo?style=for-the-badge&logo=wordpress)](https://www.coursera.org/learner/infinitydecoder) 
[![profile](https://img.shields.io/badge/portfolio-visit-violet?style=for-the-badge&logo=About.me)](https://profile.infinitydecoder.com/) 
[![Email](https://img.shields.io/badge/Email-Contact%20Me-black?style=for-the-badge&logo=email)](mailto:contact@infinitydecoder.com)

---

# 📚 Shiori — Student Management System

**Shiori** is a modern, lightweight, and secure Student Information System (SIS) built with vanilla PHP and a clean MVC architecture. It's designed for schools and educational institutions that need a fast, simple, and reliable solution without the overhead of large frameworks.

---

## 🚀 Features

- ✅ **Student Management**: Add, edit, delete, search, and view student records
- ✅ **Photo Management**: Upload student photos with automatic thumbnail generation
- ✅ **CSV Import/Export**: Bulk import students from CSV files
- ✅ **Dynamic Fields**: Create custom fields (text, select, radio, etc.)
- ✅ **Lookup Management**: Manage classes, sections, sessions, categories
- ✅ **User Management**: Multi-role support (Super Admin, Admin, Staff, Viewer)
- ✅ **Activity Logging**: Track all CRUD operations
- ✅ **Password Security**: Mandatory password changes, strength validation
- ✅ **Database Backup**: One-click MySQL database backup
- ✅ **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- ✅ **Installer Wizard**: WordPress-style installer for easy setup

---

## 🧱 Technology Stack

### Backend
- **PHP** 8.0+ (Vanilla - No frameworks)
- **MySQL** 5.7+ / 8.x
- **Apache** 2.4 with `mod_rewrite`
- **PDO** for secure database connections
- **Custom MVC** architecture (Router, Controllers, Models, Views)

### Frontend
- **Bootstrap 5** - Responsive UI framework
- **Bootstrap Icons** - Icon library
- **SweetAlert2** - Beautiful alerts and modals
- **Chart.js** - Dashboard statistics
- **Flatpickr** - Date picker
- **FilePond** - File uploads

### Security
- **CSRF Protection** - Token-based form protection
- **Password Hashing** - bcrypt via PHP's `password_hash()`
- **Session Management** - Secure session handling
- **SQL Injection Prevention** - Prepared statements with PDO
- **Input Validation** - Server-side validation for all inputs

---

## 📋 Requirements

- **PHP** 8.0 or higher with extensions:
  - `pdo_mysql` - Database connectivity
  - `fileinfo` - File type detection
  - `gd` or `imagick` - Image thumbnail generation
  - `openssl` - Secure operations
  - `mbstring` - Multibyte string handling

- **MySQL** 5.7+ or 8.x
- **Apache** 2.4 with `mod_rewrite` enabled
- **Web Server**: Laragon, XAMPP, or standard LAMP/LEMP stack
- **Permissions**: Write access to `public/uploads/` directory

---

## 📦 Installation

### Option 1: Using the Installer (Recommended)

1. **Clone or Download the Repository**
   ```bash
   git clone https://github.com/infinity-decoder/Shiori.git
   cd Shiori
   ```

2. **Configure Your Web Server**
   - **Laragon/XAMPP**: Place the `Shiori` folder in your `www` or `htdocs` directory
   - **Production**: Set your web server document root to the `/public` folder

3. **Set Permissions**
   ```bash
   chmod -R 755 public/uploads/
   chmod -R 755 storage/
   ```

4. **Access the Installer**
   - Navigate to: `http://localhost/Shiori/public` or your configured URL
   - The installer will automatically launch if the database is not configured
   - Follow the 3-step wizard:
     - **Step 1**: Database Configuration
     - **Step 2**: Super Admin Account
     - **Step 3**: Review & Install

5. **Login**
   - Use the credentials you created during installation

### Option 2: Manual Installation

1. **Create Database**
   ```sql
   CREATE DATABASE shiori_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Schema**
   ```bash
   mysql -u root -p shiori_db < database/schema.sql
   ```

3. **Configure Database**
   
   Create `config/database.php`:
   ```php
   <?php
   return [
       'host'    => 'localhost',
       'port'    => 3306,
       'name'    => 'shiori_db',
       'user'    => 'root',
       'pass'    => '',
       'charset' => 'utf8mb4',
   ];
   ```

4. **Configure Application**
   
   Edit `config/app.php`:
   ```php
   <?php
   return [
       'name'     => 'Shiori',
       'base_url' => 'http://localhost/Shiori/public',
       'debug'    => false,
   ];
   ```

5. **Create Super Admin**
   ```sql
   INSERT INTO users (username, password_hash, role, name, email, created_at) 
   VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'Super Admin', 'admin@example.com', NOW());
   -- Password: password
   ```

---

## 🔧 Configuration

### Base URL Configuration

The `base_url` in `config/app.php` must match your server setup:

**Local Development (Laragon/XAMPP):**
```php
'base_url' => 'http://localhost/Shiori/public',
```

**Production (Domain Root):**
```php
'base_url' => 'https://yourdomain.com',
```

**Production (Subdirectory):**
```php
'base_url' => 'https://yourdomain.com/shiori',
```

### Apache Configuration

Ensure `mod_rewrite` is enabled:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Virtual host or directory needs:
```apache
<Directory "/path/to/Shiori/public">
    AllowOverride All
    Require all granted
</Directory>
```

---

## 👤 User Roles & Permissions

| Role | Permissions |
|------|-------------|
| **Super Admin** | Full access - manage users, fields, lookups, students, settings |
| **Admin** | Manage students, lookups, view activity logs |
| **Staff** | Add/edit students, view records |
| **Viewer** | Read-only access to student records |

---

## 📁 Project Structure

```
Shiori/
├── app/
│   ├── Controllers/       # Request handlers
│   ├── Core/             # Router, Auth, CSRF, DB
│   ├── Models/           # Database models
│   ├── Services/         # Business logic
│   ├── Views/            # HTML templates
│   └── Helpers/          # Utility functions
├── config/
│   ├── app.php           # Application settings
│   └── database.php      # Database credentials (auto-generated)
├── database/
│   └── schema.sql        # Database schema
├── public/
│   ├── assets/           # CSS, JS, images, fonts
│   ├── uploads/          # Student photos (writable)
│   └── index.php         # Front controller
└── storage/              # Logs and temporary files
```

---

## 🔒 Security Best Practices

1. **Change Default Credentials**: Always change the default admin password after installation
2. **Enable HTTPS**: Use SSL/TLS in production
3. **File Permissions**: Restrict write access to only necessary directories
4. **Database Security**: Use strong passwords and restrict database user privileges
5. **Disable Debug Mode**: Set `'debug' => false` in `config/app.php` for production
6. **Regular Backups**: Use the built-in backup feature regularly
7. **Keep Updated**: Monitor for security updates

---

## 🐛 Troubleshooting

### 404 Errors / Routing Issues
- Verify `mod_rewrite` is enabled
- Check `.htaccess` exists in `/public`
- Ensure `AllowOverride All` is set in Apache config
- Verify `base_url` matches your actual URL

### File Upload Issues
- Check `public/uploads/` is writable (`chmod 755` or `777`)
- Verify PHP `upload_max_filesize` and `post_max_size` in `php.ini`
- Check `file_uploads = On` in `php.ini`

### Database Connection Failed
- Verify MySQL service is running
- Check credentials in `config/database.php`
- Ensure database exists and user has proper privileges

### Backup Feature Not Working (Windows)
- Ensure `mysqldump.exe` is in your system PATH
- Check that `exec()` function is not disabled in `php.ini`

---

## 📸 Screenshots

> Add screenshots of your application here

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

## 🙏 Acknowledgments

- Bootstrap Team for the amazing UI framework
- Chart.js for beautiful charts
- SweetAlert2 for elegant alerts
- All contributors and testers

---

<div align="center">

**Made with ❤️ by [MAHBOOB ALAM](https://profile.infinitydecoder.com/)**

⭐ Star this repo if you find it helpful!

</div>
