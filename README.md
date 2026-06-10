# SMART NOTE APP

SMART NOTE APP is a complete PHP 8.3 MVC university web programming project for managing personal notes with authentication, categories, tags, image attachments, note pinning, search, filters, recycle bin, profile management, and persistent dark mode.

## Features

- Register, login, logout, and session authentication
- Secure password storage with `password_hash()` and login verification with `password_verify()`
- CSRF protection on all write forms
- PDO prepared statements for database queries
- User-scoped categories, tags, and notes
- Note create, edit, view, soft delete, restore, and permanent delete
- Pinned notes displayed first
- Recycle bin with restore and permanent delete
- Image uploads to `public/uploads/`
- Upload MIME validation for JPG, PNG, and WEBP
- Maximum upload size of 5MB
- Search by title, content, and tags
- Filters by category, tag, priority, and pinned status
- Dashboard totals for notes, categories, tags, important notes, pinned notes, and deleted notes
- Profile display and password change
- Responsive layout for desktop, tablet, and mobile
- Dark mode stored in `localStorage`

## Folder Structure

```text
project/
├── app/
│   ├── controllers/
│   ├── core/
│   ├── models/
│   └── views/
├── config/
│   └── config.php
├── database/
│   └── database.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── images/
│   │   └── js/
│   └── uploads/
├── .htaccess
├── index.php
└── README.md
```

## Architecture

The application follows MVC architecture:

- `index.php` is the front controller and route definition file.
- `app/core/Router.php` resolves GET and POST routes.
- `app/core/Controller.php` renders views and enforces authentication.
- `app/core/Model.php` gives models access to the shared PDO connection.
- Controllers validate input, verify CSRF tokens, and coordinate models and views.
- Models contain database logic only.
- Views contain escaped HTML output using the `e()` helper.

## Database Design

The database is named `smart_note_app` and contains:

- `users`: registered accounts
- `categories`: user-owned note categories
- `tags`: user-owned tags
- `notes`: user-owned notes with priority, image path, pin status, and recycle-bin status
- `note_tags`: many-to-many relationship between notes and tags

Foreign keys are included in `database/database.sql`.

## Laragon Setup

1. Copy or keep this project inside Laragon's web root, for example:
   `C:\laragon\www\smart-note-app`
2. Start Laragon.
3. Start Apache and MySQL.
4. Open Laragon Terminal or phpMyAdmin.
5. Import `database/database.sql`.
6. Visit the project in your browser:
   `http://localhost/smart-note-app`

If your folder remains named `Project1`, visit:

```text
http://localhost/Project1
```

## Database Import

Using phpMyAdmin:

1. Open `http://localhost/phpmyadmin`.
2. Go to Import.
3. Select `database/database.sql`.
4. Run the import.

Using MySQL CLI:

```bash
mysql -u root < database/database.sql
```

## Configuration

Database settings are in `config/config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'smart_note_app');
define('DB_USER', 'root');
define('DB_PASS', '1234');
```

These defaults match a typical Laragon MySQL setup.

## Default Admin Account

```text
Email: trungvo4869@gmail.com
Password: 123456
```

You can change the password from the Profile page after logging in.

## Screenshots

Add screenshots here after running the project:

- Login Page
- Dashboard
- Notes
- Note Form
- Recycle Bin
- Dark Mode

## Notes For Submission

This project does not use Laravel, CodeIgniter, Symfony, Bootstrap, React, Vue, or Angular. It is built with PHP 8.3, MySQL, PDO, HTML5, CSS3, and vanilla JavaScript.

