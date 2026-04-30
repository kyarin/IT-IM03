# Project Updates & Enhancements

This document outlines the recent modifications made to the Paimon's Kitchen (IM03Case) application, specifically focusing on address validation, geospatial functionality, and user interface improvements.

## 1. Geospatial Branch Setup (`setup_db.php`)
To fully utilize MongoDB's `$near` geospatial operator, the database seeding script ensures the single official branch is configured. This allows the system to test delivery ranges relative to the main kitchen.
*   **Mondstadt HQ** (Cabanatuan, Nueva Ecija)

## 2. Dynamic Philippine Address Dropdowns (`User_address.php`)
The user delivery address form was completely overhauled. Static text inputs were replaced with dynamic, cascading dropdown menus.
*   Integrated the **Philippine Standard Geographic Code (PSGC) API** (`https://psgc.gitlab.io/api/`) to fetch real-time, accurate location data.
*   **Cascading Logic**: Selecting a Region unlocks the Province dropdown; selecting a Province unlocks the City dropdown; selecting a City unlocks the Barangay dropdown. The lower-level dropdowns are strictly populated based on their parent's geographic code to prevent invalid addresses.

## 3. Geospatial Range & Province Restriction (`User_address.php`)
Because the backend order processor (`place_order.php`) enforces a maximum delivery distance of 50 kilometers (`$maxDistance: 50000`) from the branch using MongoDB's `$near` query, the frontend was adapted to reflect this limit for the Mondstadt HQ (Cabanatuan):
*   **Province Lock**: The Province dropdown was strictly filtered to only allow **Nueva Ecija**.
*   **50km City Exclusion**: The API fetching logic was intercepted to remove specific municipalities that exceed the 50-kilometer radius from Cabanatuan City. Excluded municipalities include: *Carranglan, Pantabangan, Cuyapo, Nampicuan, and Talugtug*. All other municipalities inside the 50km range populate normally.

## 4. UI Polish: Menu Filter Reset (`menu.php`)
The text-based "Clear" filter link on the menu page was upgraded into a responsive icon container.
*   Replaced the plain text hyperlink with an SVG "reset" (arrow-clockwise) icon.
*   Added styled hover states (turning red on hover) to visually communicate the "clear/reset" destructive action.
*   Ensured alignment and padding match the adjacent "Apply Filters" button for a cleaner, unified aesthetic.
