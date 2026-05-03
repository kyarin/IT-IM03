# Paimon's Kitchen - MariaDB / MySQL Counterparts

This guide demonstrates how the MongoDB collections, data, and queries used in the new system would be implemented using a relational database (MariaDB / MySQL).

---

## 1. Creating the Database & Tables

In MySQL, we must explicitly define the schema (columns and data types) before inserting data.

```sql
-- Create the Database
CREATE DATABASE paimon_db;
USE paimon_db;

-- Table for user_reg (Users)
CREATE TABLE user_reg (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    age INT,
    contact VARCHAR(20),
    status VARCHAR(20) DEFAULT 'active',
    role VARCHAR(20) DEFAULT 'user',
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for menu (Food Items)
CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    rarity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    status VARCHAR(20) DEFAULT 'active'
);

-- Table for user_orders (Orders)
CREATE TABLE user_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    grand_total DECIMAL(10, 2) NOT NULL,
    delivery_address TEXT NOT NULL,
    branch VARCHAR(100),
    status VARCHAR(20) DEFAULT 'pending',
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_reg(id)
);
```

*(Note: In a fully normalized SQL structure, you would also have a separate `order_items` table mapped to the `user_orders` table to handle the arrays of items. For simplicity here, we focus on the main queries).*

---

## 2. Inserting Sample Data

Unlike MongoDB's JSON-like objects, SQL uses explicit `INSERT INTO` statements.

```sql
-- Insert an Admin User
INSERT INTO user_reg (name, email, password, age, contact, status, role) 
VALUES ('Grand Master Jean', 'admin@paimon.com', 'hashed_password_here', 25, '09123456789', 'active', 'admin');

-- Insert Menu Items
INSERT INTO menu (name, rarity, price, description, category, status) VALUES 
('Steak', 1, 30.00, 'Revives a character (basic food)', 'Main', 'active'),
('Sweet Madame', 2, 90.00, 'One of the most popular healing foods', 'Main', 'active'),
('Adeptus Temptation', 5, 500.00, 'Extremely powerful buff food', 'Main', 'inactive');

-- Insert an Order
INSERT INTO user_orders (user_id, grand_total, delivery_address, branch, status) 
VALUES (1, 590.00, '123 Favonius HQ, Mondstadt', 'Mondstadt HQ', 'delivered');
```

---

## 3. The 5 Variations of `find()` – SQL Counterparts

Here is how the 5 required MongoDB queries translate into standard SQL queries.

### Variation 1: Empty / Base Find
**Goal:** Retrieve all documents (active items for the menu).
*   **MongoDB:** `$menuCollection->find(['status' => ['$ne' => 'inactive']]);`
*   **MySQL:** 
    ```sql
    SELECT * FROM menu 
    WHERE status != 'inactive';
    ```

### Variation 2: Parameter Find
**Goal:** Retrieve a specific record based on criteria (e.g., fetching a receipt by its ID).
*   **MongoDB:** `$ordersCollection->findOne(['_id' => new ObjectId($order_id)]);`
*   **MySQL:** 
    ```sql
    SELECT * FROM user_orders 
    WHERE id = 1;
    ```

### Variation 3: Projection 1 (Include)
**Goal:** Fetch ONLY specific columns to save memory (e.g., fetching only the fields needed to populate the inventory management table).
*   **MongoDB:** `$menuCollection->find([], ['projection' => ['name' => 1, 'price' => 1, 'category' => 1, 'status' => 1, '_id' => 1]]);`
*   **MySQL:** In SQL, projection simply means specifying the exact columns you want after the `SELECT` statement, instead of using `*`.
    ```sql
    SELECT id, name, price, category, status FROM menu;
    ```

### Variation 4: Projection 0 (Exclude)
**Goal:** Fetch a record but explicitly EXCLUDE a sensitive column (e.g., getting user data but omitting the password).
*   **MongoDB:** `$usersCollection->findOne(['_id' => $user_id], ['projection' => ['password' => 0]]);`
*   **MySQL:** SQL *does not have* a native "exclude" syntax. To achieve Projection 0 in SQL, you must explicitly select every single column *except* the one you want to hide.
    ```sql
    SELECT id, name, email, age, contact, status, role, date_registered 
    FROM user_reg 
    WHERE id = 1;
    ```

### Variation 5: Parameter + Projection 1
**Goal:** Filter records based on criteria AND select only specific columns (e.g., getting the `grand_total` of all 'delivered' orders).
*   **MongoDB:** `$ordersCollection->find(['status' => 'delivered'], ['projection' => ['grand_total' => 1, '_id' => 0]]);`
*   **MySQL:** 
    ```sql
    SELECT grand_total FROM user_orders 
    WHERE status = 'delivered';
    ```

---

## 4. (Bonus) Counterparts for the 5 Operators

*   **`$lt` (Less Than):** 
    *   MongoDB: `['price' => ['$lt' => 150]]`
    *   MySQL: `WHERE price < 150`
*   **`$regex` (Pattern Matching/Search):** 
    *   MongoDB: `['name' => ['$regex' => $search, '$options' => 'i']]`
    *   MySQL: `WHERE name LIKE '%SearchTerm%'`
*   **`$in` (Match values in array):** 
    *   MongoDB: `['category' => ['$in' => ['Main', 'Drink']]]`
    *   MySQL: `WHERE category IN ('Main', 'Drink')`
*   **`$and` (Multiple conditions):** 
    *   MongoDB: `['$and' => [['email' => $email], ['status' => 'active']]]`
    *   MySQL: `WHERE email = 'user@test.com' AND status = 'active'`
*   **`$exists`:** 
    *   MongoDB: `['location' => ['$exists' => true]]`
    *   MySQL: `WHERE location IS NOT NULL` (Assuming location is a standard nullable column).
