# Paimon's Kitchen - MongoDB Upgrade

This document outlines the setup instructions, system flow, and the specific locations of the required MongoDB operations implemented in the application.

## 🚀 Getting Started (Setup Instructions)

If you have just pulled this code, you MUST run the initialization script to set up the database, create required geospatial indexes, and seed the default data.

1. **Install Dependencies:** Open your terminal in the project directory and run:
   ```bash
   composer install
   ```
2. **Initialize the Database:** Run the setup script via PHP CLI to prepare MongoDB:
   ```bash
   php setup_db.php
   ```
   *This script will automatically:*
   - *Create the `2dsphere` index required for the delivery location calculation.*
   - *Seed the `menu` collection with the default food items.*
   - *Create the default Admin account (`admin@paimon.com` / `admin`).*
3. **Start Local Server:** Ensure your Apache (XAMPP) and MongoDB servers are running, then navigate to `index.php` in your browser.

---

## 🧭 System Flow

1. **Landing Page (`index.php`)**: Users enter the site here and can choose to Log In or Register.
2. **Registration (`register.php` / `User_reg.php`)**: Users create an account. They are assigned a `status: 'active'` by default.
3. **Login (`login.php` / `User_login.php`)**: Users authenticate. 
   - **Admins** (`role: 'admin'`) are redirected to the **Admin Dashboard** (`admin.php`). *(Admin account: admin@paimon.com / admin)*
   - **Customers** are redirected to the **Delivery Address Form** (`User_address.php`).
4. **Location Capture (`User_address.php` & `save_address.php`)**: Using HTML5 Geolocation, the user's coordinates are captured via browser and saved to MongoDB as a GeoJSON `Point`.
5. **Menu (`menu.php`)**: Users browse active food items. They can search, filter by budget, and filter by categories using the UI at the top.
6. **Checkout (`place_order.php`)**: The system validates that the user has a location, uses `$near` to find the closest delivery branch, calculates the total, and saves the order to the database.
7. **Receipt (`order_success.php`)**: The user's finalized receipt is pulled from the database and displayed on screen.

---

## 💾 MongoDB Requirements Implementation

### The 5 Variations of `find()` (Unit 5)
1. **Empty/Base Find (Retrieve all documents)**
   - **Location:** `menu.php` (Line ~45)
   - **Purpose:** Fetches all active menu items dynamically.
2. **Parameter Find (Retrieve based on criteria)**
   - **Location:** `order_success.php` (Line ~20)
   - **Purpose:** Fetches the specific order details using the `_id` to display the final receipt.
3. **Projection 1 (Include specific attributes only)**
   - **Location:** `admin.php` (Line ~53)
   - **Purpose:** Fetches *only* the specific fields needed (`name`, `price`, `category`, `status`, `_id`) of food items to efficiently populate the "Manage Inventory" table without wasting memory.
4. **Projection 0 (Exclude specific attributes)**
   - **Location:** `menu.php` (Line ~22)
   - **Purpose:** Fetches the logged-in user's data for the header greeting, explicitly excluding the sensitive `password` field from the result.
5. **Parameter + Projection 1 (Filter and specify attributes)**
   - **Location:** `admin.php` (Line ~48)
   - **Purpose:** Finds orders marked as "delivered" and projects *only* the `grand_total` to efficiently calculate the total revenue metric.

---

### Integration of 5 Operators (Unit 8)
1. **`$lt` (Less Than)**
   - **Location:** `menu.php` (Line ~37)
   - **Purpose:** Filters the menu to display "Budget Meals" (prices `< 150`).
2. **`$regex` (Pattern Matching)**
   - **Location:** `menu.php` (Line ~32)
   - **Purpose:** Powers the search bar, allowing for case-insensitive searching.
3. **`$in` (Match values in an array)**
   - **Location:** `menu.php` (Line ~42)
   - **Purpose:** Filters the menu based on the selected category checkboxes (e.g., showing both "Main" and "Drink").
4. **`$and` (Satisfy all expressions)**
   - **Location:** `login.php` (Line ~25)
   - **Purpose:** Authenticates the user by ensuring the email matches AND their account `status` is 'active'.
5. **`$exists` (Check if a field is present)**
   - **Location:** `place_order.php` (Line ~55)
   - **Purpose:** Validates that the user has a GeoJSON `location` point saved before allowing them to check out.

---

### Geospatial Handling (Unit 7)
- **HTML5 Geolocation:** Found in the frontend logic of `User_address.php`.
- **GeoJSON Saving:** Handled in `save_address.php`.
- **`$near` Operator:** Implemented in `place_order.php` (Line ~80) to calculate the nearest `branches` collection location to the user's coordinates.
