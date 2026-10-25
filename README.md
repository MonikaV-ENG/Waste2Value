# Waste2Value — DBMS Mini Project

A PHP + MySQL website for exchanging reusable waste materials. Designed for XAMPP and a quick local demo.

## Features
- Dashboard with live database statistics
- Browse, search, and filter materials by category or location
- Post a material listing with contact, quantity, condition, urgency, and location
- Submit a request for a listed material
- Accept/reject requests
- Automatically create a match and pickup record when a request is accepted
- Track pickups and mark them completed or cancelled
- 10 relational database tables with primary/foreign keys
- Responsive layout built with HTML and CSS

## Requirements
- XAMPP (Apache + MySQL)
- PHP 8.0+ with PDO MySQL enabled
- A browser

## Run locally on Windows/XAMPP
1. Extract the `Waste2Value` folder into `C:\xampp\htdocs\`. The final path should be `C:\xampp\htdocs\Waste2Value\index.php`.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. If your MySQL root account has a password, open `config.php` and set `DB_PASS` to it. Standard XAMPP often uses a blank root password.
4. Visit `http://localhost/Waste2Value/install.php`.
5. You should see **Database is ready!**. Click **Open dashboard**.
6. Visit `http://localhost/Waste2Value/`.

If your XAMPP uses a different MySQL port, adjust the PDO DSN in `config.php`, for example `mysql:host=localhost;port=3307;dbname=...`.

## Demo flow
1. Dashboard: show sample listings and statistics.
2. Browse Waste: search for `cardboard` or filter a category.
3. Post Waste: create a new material listing.
4. Browse Waste: request an available item using a different email than the listing owner.
5. Requests: click **Accept** for the pending request.
6. Matches: show the automatically created match and pickup.
7. Click **Complete** to update the pickup and item status.
8. About: explain the 10-table relational schema.

## Tables
`users`, `waste_categories`, `locations`, `waste_items`, `requests`, `matches`, `pickups`, `recycling_centers`, `transactions`, `reviews`.

## Deployment notes
- For a public PHP/MySQL host, create a MySQL database and user in the hosting panel, update `config.php` with those credentials, and import/run the schema setup through an allowed method.
- Do not leave the installer publicly accessible after setup. Remove or password-protect `install.php` after the database has been created.
- This is an academic prototype. It intentionally does not include authentication, authorization, email sending, or payment processing. Do not use it with sensitive real user information.

## Troubleshooting
- **Unknown database / dashboard says setup needed:** open `/install.php`.
- **Access denied for root:** update `DB_USER` and `DB_PASS` in `config.php`.
- **MySQL shutdown unexpectedly:** resolve the XAMPP/MySQL service issue first; this app cannot connect until MySQL is running.
- **404 Not Found:** confirm the folder is inside `htdocs` and the URL folder name matches `Waste2Value`.
- **Blank page:** enable PHP error reporting temporarily or check `C:\\xampp\\apache\\logs\\error.log`.
