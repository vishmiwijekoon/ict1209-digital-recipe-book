# Diary of Taste - Digital Recipe Book
**ICT 1209 - Web Technologies (Mini Project)**
**Department of ICT | Rajarata University of Sri Lanka**

A modern, fully responsive digital recipe book web application designed to collect, explore, and share home cooking recipes. Built with HTML5, Custom CSS3, Bootstrap 5, Vanilla JavaScript, PHP, and MySQL (PDO).

---

## Key Features

### 1. User Authentication (`auth/`)
- **Registration (`auth/register.php`)**: Create account with unique username, email, and password. Passwords are securely encrypted using PHP's `password_hash()` with `PASSWORD_BCRYPT`.
- **Login (`auth/login.php`)**: Secure authentication via PDO prepared statements and `password_verify()`. Calls `session_regenerate_id(true)` upon successful login to prevent session fixation.
- **Logout (`auth/logout.php`)**: Completely destroys session and clears session cookies cleanly.

### 2. Contact System (`contact.php`)
- Integrated contact form storing messages directly in the `messages` table via prepared statements.
- Dual validation: Client-side JavaScript validation runs alongside server-side PHP validation.
- Responsive FAQ accordion and contact information card.

### 3. Recipe Catalog & Exploration (`index.php`, `recipes.php`, `recipe-details.php`)
- **Dynamic Homepage (`index.php`)**: Real-time featured recipes queried from MySQL, category shortcuts, search bar, and story section.
- **Recipe Library (`recipes.php`)**: Filter recipes by category chips (*Breakfast, Lunch, Dinner, Desserts, Drinks*) and real-time live search with empty-state handling.
- **Recipe Detail View (`recipe-details.php`)**: Complete cooking guide with dynamic metadata (author, prep/cook times, servings, difficulty), interactive ingredients checklist, step-by-step instructions, related recipes, and printable format.

### 4. Chef Dashboard & Recipe Management (`dashboard.php`)
- **Protected Access**: Enforces login requirement (`require_login()`).
- **Full Recipe CRUD**:
  - **Create**: Add new recipes with custom categories, times, servings, difficulty, ingredients, and instructions.
  - **Read**: View personal published recipes alongside overall system metrics.
  - **Delete**: Safely delete owned recipes with permission checks and confirmation.

---

## Required Folder Structure

```text
ict1209-digital-recipe-book/
├── css/
│   └── style.css            # Custom CSS variables, typography, animations
├── js/
│   └── script.js            # Unified client-side validation & dynamic interactions
├── images/
│   ├── logo.png             # Site brand logo
│   └── favicon.png          # Browser favicon
├── includes/
│   ├── db.php               # PDO database connection & error diagnostics
│   ├── functions.php        # Reusable helper functions & database queries
│   ├── header.php           # Modular navbar & header template
│   └── footer.php           # Modular footer & newsletter template
├── auth/
│   ├── register.php         # User registration form & BCrypt hashing
│   ├── login.php            # User login & session handling
│   └── logout.php           # Clean session termination
├── contact.php              # Contact form & submission processing
├── index.php                # Dynamic homepage with featured recipes
├── dashboard.php            # User dashboard with recipe CRUD operations
├── recipes.php              # Recipe catalog with live search & filters
├── recipe-details.php       # Dynamic recipe detail view & print support
├── test-db.php              # Database connectivity test script
├── database.sql             # Exported SQL database schema & seed data
└── README.md                # Project documentation & setup instructions
```

---

## Getting Started (Setup with XAMPP)

1. **Move Project to XAMPP**:
   Place or symlink the project folder into your XAMPP `htdocs` directory:
   ```text
   C:\xampp\htdocs\ict1209-digital-recipe-book\
   ```

2. **Start Services**:
   Open **XAMPP Control Panel** and start **Apache** and **MySQL**.

3. **Import the Database**:
   - Open your browser and navigate to `http://localhost/phpmyadmin`.
   - Click the **Import** tab at the top.
   - Choose `database.sql` from the project directory.
   - Click **Go** / **Import** to create the `diary_of_taste` database and populate the initial seed tables.

4. **Test Database Connection**:
   Visit: `http://localhost/ict1209-digital-recipe-book/test-db.php` to verify that tables and records are loaded.

5. **Run the Application**:
   Visit: `http://localhost/ict1209-digital-recipe-book/index.php`

---

## Demo Accounts

All sample users are pre-populated in `database.sql` with default password: `password123`

| Username | Email | Password | Role |
| :--- | :--- | :--- | :--- |
| `admin` | `admin@diaryoftaste.com` | `password123` | Administrator / Chef |
| `vishmi` | `vishmi@diaryoftaste.com` | `password123` | Contributor / Chef |
| `danuka` | `danuka@diaryoftaste.com` | `password123` | Contributor / Chef |

---

## Team Members

- **H.A.D.B. Peiris** (Student ID: `ITT/2024/079`)
- **R.D.W.M.V.A. Wijekoon** (Student ID: `ITT/2024/119`)
