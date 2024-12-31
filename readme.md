

# Business Profile Management System

## Description
The Business Profile Management System is a PHP-based application designed to help businesses manage their profiles, business hours, and associated details. It features a REST API for interaction with the database and provides functionality for storing business data, including operating hours and contact information.

## Features
- Manage business profiles, including name, address, and contact information.
- Define business operating hours for each day of the week.
- CRUD operations for users, businesses, and business hours.
- RESTful API for easy interaction with the database.

## Technologies Used
- **PHP 8**: Backend logic and REST API.
- **MySQL**: Database for storing business-related data.
- **XAMPP**: Development environment stack for PHP, MySQL, and Apache server support.

## Database Structure
### Database Name: `business_profile`

### Tables

#### 1. `users`
| Column    | Type         | Attributes         | Description              |
|-----------|--------------|--------------------|--------------------------|
| `id`      | INT(11)      | PRIMARY, AUTO_INCREMENT | Unique identifier for the user. |
| `username`| VARCHAR(255) | NOT NULL           | User's login username.   |
| `password`| VARCHAR(255) | NOT NULL           | User's login password.   |
| `is_admin`| TINYINT(1)   |                    | Indicates if the user is an admin. |

#### SQL Script:
```sql
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1),
  PRIMARY KEY (`id`)
);
```

#### 2. `businesses`
| Column             | Type         | Attributes         | Description                          |
|--------------------|--------------|--------------------|--------------------------------------|
| `id`               | INT(11)      | PRIMARY, AUTO_INCREMENT | Unique identifier for the business. |
| `name`             | VARCHAR(255) | NOT NULL           | Business name.                       |
| `address`          | VARCHAR(255) | NOT NULL           | Business address.                    |
| `phone`            | VARCHAR(15)  | NOT NULL           | Business phone number.               |
| `header_image_url` | VARCHAR(255) | NULLABLE           | URL for the header image of the business. |
| `created_at`       | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | Timestamp when the record was created. |

#### SQL Script:
```sql
CREATE TABLE `businesses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `header_image_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

#### 3. `business_hours`
| Column        | Type         | Attributes         | Description                                   |
|---------------|--------------|--------------------|-----------------------------------------------|
| `id`          | INT(11)      | PRIMARY, AUTO_INCREMENT | Unique identifier for the record.          |
| `business_id` | INT(11)      | FOREIGN KEY        | Links to the `id` in the `businesses` table. |
| `day_of_week` | ENUM         | NOT NULL           | Day of the week (e.g., 'Sunday', 'Monday').  |
| `open_time`   | TIME         | NULLABLE           | Business opening time.                       |
| `close_time`  | TIME         | NULLABLE           | Business closing time.                       |
| `is_closed`   | TINYINT(1)   |                    | Indicates if the business is closed on this day. |

#### SQL Script:
```sql
CREATE TABLE `business_hours` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `business_id` INT(11) NOT NULL,
  `day_of_week` ENUM('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
  `open_time` TIME DEFAULT NULL,
  `close_time` TIME DEFAULT NULL,
  `is_closed` TINYINT(1),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`)
);
```

## Setup Instructions
1. Install [XAMPP](https://www.apachefriends.org/index.html) and set up your local environment.
2.Access phpMyAdmin (usually at http://localhost/phpmyadmin).
3. Create a new MySQL database named `business_profile`.
4. Execute the provided SQL scripts to create the tables (`users`, `businesses`, `business_hours`).
5. Configure the database connection in `db.php`.
6. Run the application on your XAMPP server.
 
## API Endpoints
### 1. Fetch All Businesses
**Endpoint:** `/api.php?action=get_businesses`  
**Method:** `GET`  
**Response:**
```json
{
  "success": true,
  "businesses": [
    {
      "id": 1,
      "name": "Example Business",
      "address": "123 Main St",
      "phone": "123-456-7890"
    }
  ]
}
```

### 2. Fetch Business Details
**Endpoint:** `/api.php?action=get_business_details&business_id=1`  
**Method:** `GET`  
**Response:**
```json
{
  "success": true,
  "business": {
    "id": 1,
    "name": "Example Business",
    "address": "123 Main St",
    "phone": "123-456-7890"
  }
}
```

### 3. Fetch Business Hours
**Endpoint:** `/api.php?action=get_business_hours&business_id=1`  
**Method:** `GET`  
**Response:**
```json
{
  "success": true,
  "business_hours": [
    {
      "day_of_week": "Monday",
      "open_time": "09:00",
      "close_time": "17:00",
      "is_closed": 0
    }
  ]
}
```