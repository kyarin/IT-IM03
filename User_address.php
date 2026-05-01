<?php
session_start();
require 'vendor/autoload.php';

// Guard: must be logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: User_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Check if user already has an address
try {
    $conn = new MongoDB\Client("mongodb://localhost:27017");
    $db = $conn->paimon_db;
    $addressCollection = $db->user_address;

    $existingAddress = $addressCollection->findOne(["user_id" => $user_id]);

    if ($existingAddress && !isset($_GET['add_new']) && !isset($_GET['success'])) {
        // Address exists → proceed to ordering
        header("Location: menu.php"); 
        exit;
    }
} catch (Exception $e) {
    error_log("Address check error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Address</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: url('assets/genshin_bg.png') no-repeat center center fixed;
            background-size: cover;
            color: #1e293b;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 24px;
        }
        .address-container {
            background-color: rgba(253, 251, 247, 0.96);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5), 0 8px 10px -6px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 480px;
            border: 2px solid #d4af37;
        }
        .welcome-badge {
            background-color: #f8f4e3;
            border: 1px solid #d4af37;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #78580a;
            margin-bottom: 20px;
            text-align: center;
        }
        .welcome-badge span {
            font-weight: 700;
        }
        h2 {
            margin-bottom: 8px;
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
            margin-top: 8px;
        }
        .form-group {
            margin-bottom: 14px;
        }
        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            background-color: #ffffff;
            color: #1e293b;
        }
        input[type="text"]::placeholder {
            color: #94a3b8;
        }
        input[type="text"]:focus,
        select:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23475569' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            cursor: pointer;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 13px;
            background-color: #1e293b;
            color: #d4af37;
            border: 2px solid #d4af37;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s, box-shadow 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3);
        }
        button[type="submit"]:hover {
            background-color: #334155;
            box-shadow: 0 6px 8px -1px rgba(0,0,0,0.4);
        }
        button[type="submit"]:active {
            transform: scale(0.98);
        }
        .error-message {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 11px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #fca5a5;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="address-container">
        <div class="welcome-badge">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: -3px; margin-right: 4px; color: #d4af37;">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
            </svg>
            Welcome, <span><?php echo htmlspecialchars($user_name); ?></span>! Please set up your delivery address.
        </div>

        <h2>Delivery Address</h2>
        <p class="subtitle">We need your address before you can place an order.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php
                    if ($_GET['error'] == 'missing') echo 'Please fill in all required fields.';
                    elseif ($_GET['error'] == 'save') echo 'Failed to save address. Please try again.';
                    else echo 'An error occurred. Please try again.';
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="margin-bottom: 15px; color: #16a34a;">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 style="border-bottom: none; color: #16a34a; margin-bottom: 15px;">Address Saved!</h2>
                <p style="color: #475569; margin-bottom: 30px; font-size: 15px; line-height: 1.5;">Your delivery address has been successfully saved. Would you like to add another address or proceed to the menu to order?</p>
                
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <a href="menu.php" style="display: flex; align-items: center; justify-content: center; gap: 8px; background-color: #1e293b; color: #d4af37; border: 2px solid #d4af37; padding: 14px; border-radius: 8px; text-decoration: none; font-size: 16px; font-weight: bold; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3);">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Proceed to Menu
                    </a>
                    <a href="User_address.php?add_new=1" style="display: flex; align-items: center; justify-content: center; gap: 8px; background-color: #f8fafc; color: #475569; border: 2px solid #cbd5e1; padding: 14px; border-radius: 8px; text-decoration: none; font-size: 15px; font-weight: bold; transition: all 0.2s;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Another Address
                    </a>
                </div>
            </div>
        <?php else: ?>

        <form action="save_address.php" method="POST" id="addressForm">
            <!-- Hidden: user_id passed from session (not user-editable) -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <!-- Coordinates -->
            <div class="form-row">
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="text" id="latitude" name="latitude" placeholder="e.g. 15.486" required>
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="text" id="longitude" name="longitude" placeholder="e.g. 120.973" required>
                </div>
            </div>

            <div class="form-group">
                <button type="button" id="btnLocation" style="background-color: #d4af37; color: #1e293b; border: none; padding: 10px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Get Current Coordinates (Required)
                </button>
                <div id="locationStatus" style="font-size: 12px; color: #64748b; margin-top: 6px; text-align: center;">Coordinates not set</div>
            </div>

            <div class="form-group">
                <label for="region">Region</label>
                <select id="region" name="region" required>
                    <option value="" disabled selected>Select Region...</option>
                    <option value="Region III" data-code="030000000">Region III (Central Luzon)</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="province">Province</label>
                    <select id="province" name="province" required disabled>
                        <option value="" disabled selected>Select Province...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="city">City / Municipality</label>
                    <select id="city" name="city" required disabled>
                        <option value="" disabled selected>Select City / Municipality...</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="brgy">Barangay</label>
                    <select id="brgy" name="brgy" required disabled>
                        <option value="" disabled selected>Select Barangay...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="street">Street</label>
                    <input type="text" id="street" name="street" placeholder="e.g. Rizal St." required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="house_no">House / Block No.</label>
                    <input type="text" id="house_no" name="house_no" placeholder="e.g. Blk 1 Lot 2" required>
                </div>
                <div class="form-group">
                    <label for="label">Address Label</label>
                    <input type="text" id="label" name="label" placeholder="e.g. Home, Dorm, Office" required>
                </div>
            </div>

            <button type="submit">SAVE ADDRESS & CONTINUE</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('btnLocation').addEventListener('click', function() {
            const statusDiv = document.getElementById('locationStatus');
            
            if (navigator.geolocation) {
                statusDiv.textContent = "Fetching coordinates...";
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        
                        statusDiv.innerHTML = `Coordinates captured: ${lat.toFixed(5)}, ${lng.toFixed(5)} <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-2px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                        statusDiv.style.color = "#66bb6a";
                    },
                    function(error) {
                        statusDiv.textContent = "Error capturing location. Please enable location services.";
                        statusDiv.style.color = "#dc2626";
                    }
                );
            } else {
                statusDiv.textContent = "Geolocation is not supported by this browser.";
                statusDiv.style.color = "#dc2626";
            }
        });

        // Optional form validation to ensure coordinates are fetched
        document.getElementById('addressForm').addEventListener('submit', function(e) {
            if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
                e.preventDefault();
                alert("Please get your current coordinates before saving.");
            }
        });

        // Dynamic address dropdowns using PSGC API
        const regionSelect = document.getElementById('region');
        const provinceSelect = document.getElementById('province');
        const citySelect = document.getElementById('city');
        const brgySelect = document.getElementById('brgy');

        regionSelect.addEventListener('change', async function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.dataset.code) return;
            const code = selectedOption.dataset.code;
            
            provinceSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
            provinceSelect.disabled = true;
            citySelect.innerHTML = '<option value="" disabled selected>Select City / Municipality...</option>';
            citySelect.disabled = true;
            brgySelect.innerHTML = '<option value="" disabled selected>Select Barangay...</option>';
            brgySelect.disabled = true;

            try {
                const response = await fetch(`https://psgc.gitlab.io/api/regions/${code}/provinces/`);
                const provinces = await response.json();
                
                provinces.sort((a, b) => a.name.localeCompare(b.name));
                
                provinceSelect.innerHTML = '<option value="" disabled selected>Select Province...</option>';
                provinces.forEach(prov => {
                    // ONLY allow Nueva Ecija
                    if (prov.name.toLowerCase() === 'nueva ecija') {
                        const option = document.createElement('option');
                        option.value = prov.name;
                        option.dataset.code = prov.code;
                        option.textContent = prov.name;
                        provinceSelect.appendChild(option);
                    }
                });
                provinceSelect.disabled = false;
            } catch (error) {
                console.error('Error fetching provinces:', error);
                provinceSelect.innerHTML = '<option value="" disabled selected>Error loading provinces</option>';
            }
        });

        provinceSelect.addEventListener('change', async function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.dataset.code) return;
            const code = selectedOption.dataset.code;
            
            citySelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
            citySelect.disabled = true;
            brgySelect.innerHTML = '<option value="" disabled selected>Select Barangay...</option>';
            brgySelect.disabled = true;

            try {
                const response = await fetch(`https://psgc.gitlab.io/api/provinces/${code}/cities-municipalities/`);
                const cities = await response.json();
                
                cities.sort((a, b) => a.name.localeCompare(b.name));
                
                // Cities roughly > 50km from Cabanatuan (HQ) to exclude
                const excludedCities = ['carranglan', 'pantabangan', 'cuyapo', 'nampicuan', 'talugtug'];
                
                citySelect.innerHTML = '<option value="" disabled selected>Select City / Municipality...</option>';
                cities.forEach(city => {
                    if (!excludedCities.includes(city.name.toLowerCase())) {
                        const option = document.createElement('option');
                        option.value = city.name;
                        option.dataset.code = city.code;
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    }
                });
                citySelect.disabled = false;
            } catch (error) {
                console.error('Error fetching cities:', error);
                citySelect.innerHTML = '<option value="" disabled selected>Error loading cities</option>';
            }
        });

        citySelect.addEventListener('change', async function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.dataset.code) return;
            const code = selectedOption.dataset.code;
            
            brgySelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
            brgySelect.disabled = true;

            try {
                const response = await fetch(`https://psgc.gitlab.io/api/cities-municipalities/${code}/barangays/`);
                const barangays = await response.json();
                
                barangays.sort((a, b) => a.name.localeCompare(b.name));
                
                brgySelect.innerHTML = '<option value="" disabled selected>Select Barangay...</option>';
                barangays.forEach(brgy => {
                    const option = document.createElement('option');
                    option.value = brgy.name;
                    option.dataset.code = brgy.code;
                    option.textContent = brgy.name;
                    brgySelect.appendChild(option);
                });
                brgySelect.disabled = false;
            } catch (error) {
                console.error('Error fetching barangays:', error);
                brgySelect.innerHTML = '<option value="" disabled selected>Error loading barangays</option>';
            }
        });
    </script>
</body>
</html>
