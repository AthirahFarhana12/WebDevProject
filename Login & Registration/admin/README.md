# Railway Lost and Found System

A web-based system for managing lost and found items in railway stations, featuring role-based access control and two-factor authentication.


## Email Configuration

### Important Security Notice
⚠️ **CRITICAL**: Secure your email configuration to prevent unauthorized access.

### Email Setup Requirements
- Gmail account
- App Password (recommended)
- PHPMailer library installed

### Configuring Email (PHPMailer)

#### 1. Generate App Password
1. Go to your Google Account
2. Navigate to Security > 2-Step Verification
3. Scroll down and select "App passwords"
4. Create a new app name
5. Generate a 16-character app password

#### 2. Update Email Configuration
Modify `config/email_config.php`:
```php
$mailer->Username = 'YOUR_EMAIL@gmail.com';  // Your Gmail address
$mailer->Password = 'YOUR_APP_PASSWORD';     // Generated app password
$mailer->setFrom('SENDER_EMAIL@gmail.com', 'Railway Lost and Found');
```

#### 3. Recommended Security Practices
- Never commit credentials to version control
- Use environment variables or .env files
- Rotate app passwords periodically
- Limit email account permissions

## Database Setup

### Import Database
1. Create a new database named 'web'
2. Import the `web.sql` file through phpMyAdmin
3. The SQL file includes:
   - Tables structure
   - Initial admin user
   - Required indexes and foreign keys
   - Automated cleanup events

### Database Tables

#### Users Table
```sql
CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL UNIQUE,
  first_name varchar(50) NOT NULL,
  last_name varchar(50) NOT NULL,
  email varchar(100) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  role enum('admin','staff','customer') NOT NULL,
  created_at timestamp DEFAULT current_timestamp(),
  updated_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id)
);
```

#### Verification Attempts Table
```sql
CREATE TABLE verification_attempts (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  verification_code varchar(6) NOT NULL,
  expiry datetime NOT NULL,
  is_used tinyint(1) DEFAULT 0,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### Password Resets Table
```sql
CREATE TABLE password_resets (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  token varchar(100) NOT NULL,
  expiry datetime NOT NULL,
  is_used tinyint(1) DEFAULT 0,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Admin Configuration

### Default Admin Credentials
```
Username: admin
First Name: System
Last Name: Administrator
Email: your_new_email@example.com
Password: Admin@123
Role: admin
```

### Changing Admin Email

#### Using phpMyAdmin
1. Navigate to phpMyAdmin
2. Select 'web' database
3. Open 'users' table
4. Find user with role = 'admin'
5. Click 'Edit'
6. Update email field
7. Click 'Go' to save

#### Using SQL Command
```sql
UPDATE users 
SET email = 'new_admin_email@example.com' 
WHERE username = 'admin' AND role = 'admin';
```

### Creating Additional Admin Users
```sql
INSERT INTO users (
    username, 
    first_name,
    last_name,
    email, 
    password, 
    role
) VALUES (
    'new_admin',
    'Admin',
    'User',
    'admin_email@example.com',
    -- Password must be hashed using bcrypt
    '$2y$10$...',  
    'admin'
);
```

---
*Last Updated: January 4, 2025*
