# TasteBook

TasteBook is a responsive recipe discovery and sharing website. Visitors can browse, search, and filter recipes. Registered users can publish recipes, manage their own recipes, save favorites, and update their profile.

## Features

- Home page with featured recipes and recipe categories.
- Recipe catalog with search, category/difficulty/time filters, and sorting.
- Recipe detail pages with ingredient checklist, serving scaler, print, and share actions.
- Account registration, login, logout, and protected user pages.
- Recipe creation, editing, and deletion for the recipe owner.
- Database-backed favorites for signed-in users.
- Profile and password updates.
- Contact form with server-side validation and database storage.
- Responsive layouts for desktop, tablet, and mobile.
- JavaScript interactions including live search/filtering, form validation, ingredient checklists, and serving scaling.

## Technologies

- HTML5, CSS3, Bootstrap 5, and vanilla JavaScript.
- PHP 8 or later.
- MySQL or MariaDB.
- phpMyAdmin for database administration.
- Apache through WampServer (WAMP).

## Requirements

- Windows with WampServer installed.
- PHP 8+ with the `mysqli` and `fileinfo` extensions enabled.
- MySQL or MariaDB running through WAMP.
- A modern web browser.
- Internet access for Bootstrap, Bootstrap Icons, Google Fonts, and the externally hosted sample recipe photos.

## Install and run with WAMP

### 1. Copy TasteBook into WAMP's web directory

Copy or clone the project so that the main `index.php` file is located at:

```text
C:\wamp64\www\TasteBook\index.php
```

If the project is in a different folder, put that folder inside `C:\wamp64\www\` and use its folder name in the site URL.

### 2. Start WAMP services

1. Launch WampServer.
2. Wait for the WAMP tray icon to turn green, indicating that Apache and MySQL are running.
3. If the icon does not turn green, resolve the WAMP service/port issue before continuing.

### 3. Import the database with phpMyAdmin

1. Open `http://localhost/phpmyadmin/` in your browser.
2. Sign in to phpMyAdmin if prompted. A default local WAMP installation commonly uses username `root` with a blank password; use the credentials configured on your machine.
3. Select **Import** from the top menu.
4. Choose the project's `database.sql` file.
5. Leave the format set to **SQL**, then select **Import** or **Go** at the bottom of the page.
6. After the import completes, confirm that the `tastebook` database appears in the left navigation and contains the `users`, `recipes`, `favorites`, and `messages` tables.

The SQL file creates and selects the `tastebook` database itself, so you do not have to create it manually first. **Warning:** importing this script again drops and recreates TasteBook's tables, replacing their current data. Back up any data you need before re-importing.

### 4. Check the database connection settings

The connection is centralized in [`includes/db.php`](./includes/db.php). The defaults for a typical local WAMP installation are:

| Setting | Default |
| --- | --- |
| Host | `localhost` |
| Database | `tastebook` |
| Username | `root` |
| Password | Empty |
| Port | `3306` (the application also tries `3308` and `3307` if no port override is set) |

If your MySQL credentials or port differ, set these environment variables for the Apache/PHP process and restart WAMP:

- `TASTEBOOK_DB_HOST`
- `TASTEBOOK_DB_NAME`
- `TASTEBOOK_DB_USER`
- `TASTEBOOK_DB_PASSWORD`
- `TASTEBOOK_DB_PORT`

For example, if MySQL uses a password, set `TASTEBOOK_DB_PASSWORD` to that password. Do not put production database credentials in publicly accessible files or commit them to the repository.

### 5. Open the website

Visit:

```text
http://localhost/TasteBook/
```

You can also open individual PHP pages, such as `http://localhost/TasteBook/recipes.php`. The page markup is written in PHP files so pages can load database content; separate `.html` page files are not required.

## Sample account

The imported database includes a sample account:

- **Email:** `gordon@tastebook.com`
- **Password:** `password123`

You can also register a new account through the website. Sample passwords are for local evaluation only; change or remove the sample account before using the application publicly.

## Database overview

The schema and sample records are defined in [`database.sql`](./database.sql).

- `users` stores account details and password hashes.
- `recipes` stores recipe content and belongs to a user.
- `favorites` links users and recipes, with one unique favorite per user/recipe pair.
- `messages` stores contact form submissions.

Foreign keys maintain the relationships between users, recipes, and favorites. Deleting a user or recipe cascades to its related records.

## Project structure

```text
TasteBook/
├── index.php
├── recipes.php
├── recipe.php
├── about.php
├── contact.php
├── dashboard.php
├── add_recipe.php
├── edit_recipe.php
├── my_recipes.php
├── favorites.php
├── profile.php
├── favorite_action.php
├── database.sql
├── README.md
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── css/
│   └── style.css
├── images/
├── includes/
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
└── js/
    └── script.js
```

## Security

- Passwords are stored using PHP's `password_hash()` and checked with `password_verify()`.
- Database operations use prepared statements where user input is involved.
- Protected pages check the signed-in session, and recipe updates/deletions verify recipe ownership.
- Forms validate input on the server; JavaScript validation is an additional usability aid.
- State-changing forms use CSRF protection, and output is escaped before it is rendered.
- Uploaded recipe images are checked for allowed image types and size.

## Troubleshooting

- **Database connection error:** Check that WAMP's MySQL service is running, that the `tastebook` database exists, and that the connection settings in `includes/db.php` match your local MySQL account.
- **phpMyAdmin cannot be opened:** Make sure WAMP is running and Apache/MySQL services are available, then try `http://localhost/phpmyadmin/`.
- **The site shows a 404 error:** Confirm that the project folder is inside `C:\wamp64\www\` and the URL's folder name matches it.
- **Recipe images are not visible:** The sample recipe photos are hosted externally and need internet access. Images uploaded through the site are stored locally.
- **Recipe image upload fails:** Confirm PHP's upload limits allow the selected file and that the project's `images/` folder is writable by Apache.

## Possible future improvements

- Add recipe ratings, comments, dietary tags, and pagination.
- Add password-reset emails and account verification.
- Add recipe moderation and production image storage.
