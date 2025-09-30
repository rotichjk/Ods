# Origin Driving School Online Management System (ODS)

##  Project Overview
The **Origin Driving School Online Management System (ODS2)** is a database-driven web application designed to manage the operations of a driving school.  
It allows students to enroll in courses, book lessons, and view schedules, while instructors and administrators manage classes, invoices, and payments.  

The system is built using **PHP (custom MVC)** and **MySQL**, without any frameworks.

---

##  Features / Modules
- **User Management**
  - Role-based access (Admin, Staff, Instructor, Student)
  - Secure login/logout
- **Students**
  - Registration and management
  - View enrollments, book lessons
- **Instructors**
  - View assigned lessons and schedules
  - Manage availability
- **Courses & Enrollments**
  - Create/update courses
  - Student enrollments linked to courses
- **Lessons / Scheduling**
  - Students request lessons
  - Instructors/admin approve or decline
  - Prevent double-booking (planned feature)
- **Invoices & Payments**
  - Auto-generate invoices per enrollment
  - Record payments, track status
- **Branches**
  - Manage multiple driving school locations
- **Reminders / Notifications**
  - Schedule reminders via email/SMS (planned)
- **Reports**
  - View student, instructor, and financial reports

---

## System Architecture
- **Backend**: PHP 8+ (custom MVC)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Server Environment**: XAMPP / LAMP stack
- **Security**:
  - CSRF tokens on forms
  - Passwords hashed with bcrypt
  - Role-based access control

---

## ⚙️ Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10+
- Apache 2 (or via XAMPP/WAMP/MAMP)
- Composer (optional, for dependency management)
- phpMyAdmin (optional, for DB import)

---

## 🚀 Installation Guide

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/origin-driving.git
   ```

2. **Move to XAMPP htdocs**
   ```
   C:\xampp\htdocs\origin-driving
   ```

3. **Database Setup**
   - Open phpMyAdmin
   - Create database: `origin_driving`
   - Import `sql/schema.sql`

4. **Configure Database Connection**
   Edit `php/Core/Config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'origin_driving');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Run the Application**
   - Start Apache & MySQL in XAMPP
   - Open browser → `http://localhost/origin-driving/public/`

---
##  Ready-to-Use  Credentials

- **Admin**
  - Email: `admin@kentods.com`
  - Password: `admin123`

- **Instructor**
  - Email: `gershon@gmail.com`
  - Password: `instructor123`

- **Student**
  - Email: `rotich51@icloud.com`, `alupoi@gmail.com`
  - Password: `student123`

 **Note:**  
Any new student created through the system is **automatically assigned the default password `student123`**.  
They should log in with this password and are encouraged to change it after first login in (my account).

##  Database Schema
- Consolidated schema in [`sql/schema.sql`](sql/schema.sql)
- Includes:
  - `users`, `students`, `instructors`, `courses`, `enrollments`
  - `lessons`, `invoices`, `payments`, `branches`, `reminders`

---

##  User Roles

### Admin
- Full access
- Manage students, instructors, courses, branches
- Approve lessons
- Manage invoices and payments

### Staff
- Manage daily operations
- Handle enrollments, invoices, payments

### Instructor
- View assigned lessons & schedules
- Mark lessons as completed / no-show

### Student
- Enroll in courses
- Request lessons
- View invoices and payments

---

##  Usage Guide
1. **Login** with role-based credentials
2. **Students**: enroll → book lessons → view invoices
3. **Instructors**: view “My Schedule” & “Assigned Lessons”
4. **Admin/Staff**: approve lessons, manage courses, handle payments
5. **Reports**: generate summaries of financials and activity

---

##  Security Features
- CSRF protection for all forms
- Session-based authentication
- Password hashing with `password_hash()`
- Role-based access enforcement

---

## Future improvements
- UI/UX improvements (modern design, responsive dashboard)

---
, 
## 📚 Contributors
- **Gershon Mutai(K231317)** 
- **ALexander (K232343)** 
- **Johnson (K432332)** 
- Project guided by course requirements

---

## 📜 License
This project is developed for **for the final assessment for DWIN309 at Kent Institute Australia.** and may be freely adapted or extended.
