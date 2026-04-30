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

    if ($existingAddress) {
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
        <div class="welcome-badge">👋 Welcome, <span><?php echo htmlspecialchars($user_name); ?></span>! Please set up your delivery address.</div>

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

        <form action="save_address.php" method="POST" id="addressForm">
            <!-- Hidden: user_id passed from session (not user-editable) -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <!-- Hidden: Coordinates -->
            <input type="hidden" id="latitude" name="latitude" required>
            <input type="hidden" id="longitude" name="longitude" required>

            <div class="form-group">
                <button type="button" id="btnLocation" style="background-color: #d4af37; color: #1e293b; border: none; padding: 10px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px;">
                    📍 Get Current Coordinates (Required)
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
                    <select id="label" name="label" required>
                        <option value="" disabled selected>Select label...</option>
                        <option value="home">🏠 Home</option>
                        <option value="work">💼 Work</option>
                    </select>
                </div>
            </div>

            <button type="submit">SAVE ADDRESS & CONTINUE</button>
        </form>
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
                        
                        statusDiv.textContent = `Coordinates captured: ${lat.toFixed(5)}, ${lng.toFixed(5)} ✅`;
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
