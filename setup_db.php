<?php
require 'vendor/autoload.php';

try {
    $conn = new MongoDB\Client("mongodb://localhost:27017");
    $db = $conn->paimon_db;

    // 1. Reset and Recreate Branches Collection
    $db->branches->drop();
    $branches = $db->branches;
    $branches->createIndex(['location' => '2dsphere']);
    echo "Created 2dsphere index on branches.\n";

    $branchData = [
        [
            'name' => 'Mondstadt HQ', // Cabanatuan
            'location' => ['type' => 'Point', 'coordinates' => [120.973, 15.486]],
            'delivery_radius' => 20000 // 20km
        ],
        [
            'name' => 'Liyue Harbor Branch', // Palayan City
            'location' => ['type' => 'Point', 'coordinates' => [121.085, 15.541]],
            'delivery_radius' => 15000 // 15km
        ],
        [
            'name' => 'Inazuma City Branch', // Gapan City
            'location' => ['type' => 'Point', 'coordinates' => [120.947, 15.313]],
            'delivery_radius' => 15000
        ],
        [
            'name' => 'Sumeru Akademiya Branch', // San Jose City
            'location' => ['type' => 'Point', 'coordinates' => [120.988, 15.795]],
            'delivery_radius' => 20000
        ],
        [
            'name' => 'Fontaine Court Branch', // Science City of Muñoz
            'location' => ['type' => 'Point', 'coordinates' => [120.902, 15.716]],
            'delivery_radius' => 15000
        ],
        [
            'name' => 'Natlan Outpost', // Guimba
            'location' => ['type' => 'Point', 'coordinates' => [120.768, 15.659]],
            'delivery_radius' => 12000
        ],
        [
            'name' => 'Snezhnaya Palace Branch', // Talavera
            'location' => ['type' => 'Point', 'coordinates' => [120.916, 15.583]],
            'delivery_radius' => 12000
        ],
        [
            'name' => 'Dragonspine Outpost', // Aliaga
            'location' => ['type' => 'Point', 'coordinates' => [120.851, 15.502]],
            'delivery_radius' => 10000
        ],
        [
            'name' => 'Watatsumi Island Branch', // Santa Rosa
            'location' => ['type' => 'Point', 'coordinates' => [120.939, 15.424]],
            'delivery_radius' => 10000
        ],
        [
            'name' => 'Port Ormos Branch', // San Leonardo
            'location' => ['type' => 'Point', 'coordinates' => [120.963, 15.364]],
            'delivery_radius' => 10000
        ]
    ];
    $branches->insertMany($branchData);
    echo "Inserted 10 branches across Nueva Ecija with dynamic delivery radii.\n";

    // 2. Seed menu collection if empty
    $menuCollection = $db->menu;
    if ($menuCollection->countDocuments() === 0) {
        $menuItems = [
            ['name' => 'Steak', 'rarity' => 1, 'price' => 30, 'desc' => 'Revives a character (basic food)', 'category' => 'Main'],
            ['name' => 'Mondstadt Grilled Fish', 'rarity' => 1, 'price' => 35, 'desc' => 'Simple early-game healing dish', 'category' => 'Main'],
            ['name' => 'Radish Veggie Soup', 'rarity' => 1, 'price' => 40, 'desc' => 'Gradual healing over time', 'category' => 'Soup'],
            ['name' => 'Mora Meat', 'rarity' => 1, 'price' => 45, 'desc' => 'Cheap revive food', 'category' => 'Main'],
            ['name' => 'Sweet Madame', 'rarity' => 2, 'price' => 90, 'desc' => 'One of the most popular healing foods', 'category' => 'Main'],
            ['name' => 'Fried Radish Balls', 'rarity' => 2, 'price' => 100, 'desc' => 'Boosts ATK for the party', 'category' => 'Snack'],
            ['name' => 'Tea Break Pancake', 'rarity' => 2, 'price' => 80, 'desc' => 'Revive + moderate healing', 'category' => 'Dessert'],
            ['name' => 'Mushroom Pizza', 'rarity' => 3, 'price' => 180, 'desc' => 'Strong healing over time', 'category' => 'Main'],
            ['name' => 'Mondstadt Hash Brown', 'rarity' => 3, 'price' => 170, 'desc' => 'High HP restoration', 'category' => 'Snack'],
            ['name' => "Adeptus' Temptation", 'rarity' => 5, 'price' => 500, 'desc' => 'Extremely powerful buff food', 'category' => 'Main'],
        ];
        $menuCollection->insertMany($menuItems);
        echo "Seeded menu collection.\n";
    }

    // 3. Create Admin Account
    $userCollection = $db->user_reg;
    $adminEmail = 'admin@paimon.com';
    $existingAdmin = $userCollection->findOne(["email" => $adminEmail]);

    if (!$existingAdmin) {
        $userCollection->insertOne([
            "name" => "Grand Master Jean",
            "email" => $adminEmail,
            "password" => password_hash('admin', PASSWORD_DEFAULT),
            "age" => 25,
            "contact" => "09123456789",
            "status" => "active",
            "role" => "admin",
            "date_registered" => new MongoDB\BSON\UTCDateTime()
        ]);
        echo "Created default admin account (admin@paimon.com / admin).\n";
    }

    echo "Setup complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
