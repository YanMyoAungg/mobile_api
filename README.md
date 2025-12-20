# Fitness Tracker Mobile API

A lightweight PHP/MySQL backend API for a Fitness Tracker Android application. This API handles user authentication, profile management, and activity tracking.

## Features

* **User Authentication**: Register, Login (via Email), and Logout.
* **Profile Management**: Manage personal details (Height, Weight, Date of Birth, Phone, Gender, etc.).
* **Activity Tracking**: Record and retrieve fitness activities (Run, Walk, Swim, Cycle, Jump Rope).
* **Data Filtering**: Filter activities by date range.
* **Standardized Responses**: All endpoints return a consistent JSON format.

## Prerequisites

* **PHP** (7.4 or higher)
* **MySQL** (5.7 or higher)
* **Composer** (Dependency Manager)

## Setup Instructions

### 1. Install Dependencies

Run the following command in the project root to install required PHP libraries:

```bash
composer install
```

### 2. Database Configuration

The API connects to a MySQL database named `fitness` by default. You can configure the connection details by copying the template file:

```bash
cp .env.example .env
```

Then, open `.env` and fill in your actual database credentials (Host, User, Password).

### 3. Initialize Database

Run the following command to create the database and required tables (`users`, `activities`):

```bash
composer run db:create
```

To reset the database (WARNING: Deletes all data):

```bash
composer run db:drop && composer run db:create
```

### 4. Start the Server

Start the built-in PHP development server:

```bash
php -S 0.0.0.0:8000
```

The API will be accessible at `http://localhost:8000`.

---

## API Documentation

All API responses follow this standard JSON format:

```json
// Success
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}

// Error
{
    "success": false,
    "message": "Error description",
    "error": "Detailed error message"
}
```

### 1. Authentication

* **Register**
  * **Endpoint**: `POST /api/register.php`
  * **Body**: `username`, `email`, `password`, `phone`, `address`

* **Login**
  * **Endpoint**: `POST /api/login.php`
  * **Body**: `email`, `password`
  * **Response**: Returns user data if successful.

* **Logout**
  * **Endpoint**: `POST /api/logout.php`

### 2. User Profile

* **Get Profile**
  * **Endpoint**: `GET /api/profile.php`
  * **Params**: `user_id` (e.g., `?user_id=1`)

* **Update Profile**
  * **Endpoint**: `POST /api/profile.php`
  * **Body**: `user_id`, `phone`, `height` (cm), `current_weight` (kg), `date_of_birth` (YYYY-MM-DD), `gender` ('male', 'female', 'other')

### 3. Activities

* **List Activities**
  * **Endpoint**: `GET /api/activities.php`
  * **Params**:
    * `user_id` (Required)
    * `start_date` (Optional, YYYY-MM-DD)
    * `end_date` (Optional, YYYY-MM-DD)
    * `stats` (Optional, set `true` to get summary totals instead of list)

* **Record Activity**
  * **Endpoint**: `POST /api/activities.php`
  * **Body**:
    * `user_id`
    * `activity_type` (running, walking, cycling, swimming, jumping_rope)
    * `duration` (minutes)
    * `calories_burned`
    * `activity_date` (Optional, YYYY-MM-DD HH:MM:SS)

* **Update Activity**
  * **Endpoint**: `PUT /api/activities.php`
  * **Body**: `activity_id`, `user_id`, `activity_type`, `duration`, `calories_burned`

* **Delete Activity**
  * **Endpoint**: `DELETE /api/activities.php`
  * **Body**: `activity_id`, `user_id`
