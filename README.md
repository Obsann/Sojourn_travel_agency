# Sojourn Travel Agency Website

A comprehensive web-based travel agency management system built with PHP and MySQL. This application allows customers to book travel packages, flights, and hotels, while providing agents and administrators with powerful tools to manage bookings, packages, and users.

## 🚀 Features

### 👤 User Roles & Authentication
- **Multi-role System**: Distinct functionalities for Customers, Travel Agents, and Administrators.
- **Secure Authentication**: User registration and login with password hashing.
- **Session Management**: Role-based access control and secure session handling.

### 🌎 For Customers
- **Explore & Search**: Search for tour packages, flights, and hotels by name or description.
- **Booking System**: Easy booking process for packages and services with simulated payment.
- **Dashboard**: View booking history, booking status (confirmed/cancelled), and manage profile information.
- **Profile Management**: Update contact details and personal information.

### 💼 For Travel Agents
- **Package Management**: Create and delete tour packages.
- **Image Uploads**: Upload package photos directly to the server.
- **Booking Oversight**: View all bookings made for their created packages.
- **Status Tracking**: Monitor booking statuses and customer details.

### 🛡️ For Administrators
- **Dashboard Overview**: View key statistics (Total Users, Bookings, Revenue, Packages).
- **User Management**: View and delete users.
- **Destination Management**: Add new destinations to the system.

### 💻 Technical Features
- **Responsive Design**: Built with Tailwind CSS for a mobile-friendly interface.
- **MVC-like Structure**: Organized code with separate layout files (`header.php`, `footer.php`) and helper functions.
- **Database Integration**: Robust MySQL database schema for data persistence.
- **Flash Messages**: Instant feedback for user actions (success/error notifications).

## 🛠️ Technology Stack

- **Backend**: PHP (Vanilla)
- **Database**: MySQL
- **Frontend**: HTML5, CSS (Tailwind CSS via CDN)
- **Scripting**: JavaScript (Vanilla)
- **Environment**: XAMPP / Apache

## ⚙️ Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/yourusername/sojourn-travel.git
    cd sojourn-travel
    ```

2.  **Set Up the Database**
    - Open your MySQL management tool (e.g., phpMyAdmin).
    - Create a new database named `travel_agency`.
    - Import the provided SQL schema file: `database_schema.sql`.

3.  **Configure Database Connection**
    - Open `config/database.php` (or checking `includes/functions.php`).
    - Update the database credentials to match your local setup:
      ```php
      $host = 'localhost';
      $dbname = 'travel_agency';
      $username = 'root';
      $password = '';
      ```

4.  **Run the Application**
    - Move the project folder to your web server's root directory (e.g., `htdocs` in XAMPP).
    - Start Apache and MySQL user your server control panel.
    - Open your browser and navigate to: `http://localhost/sojourn-travel`

## 📂 Project Structure

```
sojourn-travel/
├── admin/              # Admin dashboard and logic
├── agent/              # Agent dashboard and package management
├── customer/           # Customer dashboard
├── assets/             # Static assets (images, etc.)
├── config/             # Database configuration
├── includes/           # Header, Footer, and Helper functions
├── uploads/            # Uploaded package images
├── index.php           # Landing page
├── search.php          # Search functionality
├── booking.php         # Booking processing
├── login.php           # User authentication
├── register.php        # User registration
└── database_schema.sql # Database structure import file
```

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check [issues page](https://github.com/yourusername/sojourn-travel/issues).

## 📝 License

This project is open-source and available under the [MIT via License](LICENSE).
