<?php
require 'db.php';

// Set headers to allow cross-origin requests and specify content type
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Retrieve the action from the query string
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle the request for business hours
    if ($action === 'get_business_hours') {
        if (isset($_GET['business_id'])) {
            $business_id = (int)$_GET['business_id'];

            // SQL query to fetch business hours ordered by the days of the week
            $sql = "SELECT * FROM business_hours WHERE business_id = ? 
                    ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$business_id]);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Prepare the business hours array
                $business_hours = [];
                foreach ($result as $row) {
                    $business_hours[] = [
                        'day_of_week' => $row['day_of_week'],
                        'open_time' => $row['open_time'],
                        'close_time' => $row['close_time'],
                        'is_closed' => $row['is_closed']
                    ];
                }

                // Return the business hours in JSON format
                echo json_encode([
                    'success' => true,
                    'business_hours' => $business_hours
                ]);
            } catch (Exception $e) {
                // Handle database errors
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
            }
        } else {
            // If business ID is not provided
            echo json_encode([
                'success' => false,
                'message' => 'Business ID is required'
            ]);
        }
        exit;
    }

    // Handle the request to fetch business details
    if ($action === 'get_business_details') {
        $businessId = $_GET['business_id'] ?? null;

        if (!$businessId) {
            echo json_encode(['success' => false, 'message' => 'Missing business ID']);
            exit;
        }

        $query = "SELECT * FROM businesses WHERE id = ?";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$businessId]);
            $business = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($business) {
                echo json_encode(['success' => true, 'business' => $business]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Business not found']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database query failed: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Default action: Fetch all businesses
    try {
        $stmt = $pdo->prepare("SELECT * FROM businesses");
        $stmt->execute();
        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'businesses' => $businesses]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'update_business_hours':
            $businessId = $_POST['business_id'] ?? null;
            $dayOfWeek = $_POST['day_of_week'] ?? null;
            $openTime = $_POST['open_time'] ?? null;
            $closeTime = $_POST['close_time'] ?? null;
            $isClosed = $_POST['is_closed'] ?? null;

            if (!$businessId || !$dayOfWeek || $openTime === null || $closeTime === null || $isClosed === null) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("UPDATE business_hours 
                                      SET open_time = ?, close_time = ?, is_closed = ? 
                                      WHERE business_id = ? AND day_of_week = ?");
                $stmt->execute([$openTime, $closeTime, $isClosed, $businessId, $dayOfWeek]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        case 'add_business':
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $businessHours = json_decode($_POST['business_hours'], true);

            if (empty($name) || empty($address) || empty($phone)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'All fields are required'
                ]);
                exit;
            }

            try {
                // Insert the new business
                $stmt = $pdo->prepare("INSERT INTO businesses (name, address, phone) VALUES (?, ?, ?)");
                $stmt->execute([$name, $address, $phone]);
                $businessId = $pdo->lastInsertId();

                // Insert business hours if provided
                if (!empty($businessHours)) {
                    foreach ($businessHours as $day) {
                        $stmt = $pdo->prepare("INSERT INTO business_hours (business_id, day_of_week, open_time, close_time, is_closed) 
                                               VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $businessId,
                            $day['day_of_week'],
                            $day['open_time'],
                            $day['close_time'],
                            $day['is_closed']
                        ]);
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Business added successfully']);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
            break;

            case 'get_business_details':
                $businessId = $_POST['business_id'] ?? null;
                $name = $_POST['name'] ?? '';
                $address = $_POST['address'] ?? '';
                $phone = $_POST['phone'] ?? '';
    
                if (empty($name) || empty($address) || empty($phone)) {
                    echo json_encode(['success' => false, 'message' => 'All fields are required']);
                    exit();
                }
    
                try {
                    if ($businessId) {
                        $stmt = $pdo->prepare("UPDATE businesses SET name = ?, address = ?, phone = ? WHERE id = ?");
                        $stmt->execute([$name, $address, $phone, $businessId]);
                        echo json_encode(['success' => true, 'message' => 'Business updated successfully']);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO businesses (name, address, phone) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $address, $phone]);
                        echo json_encode(['success' => true, 'message' => 'Business added successfully']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;
    

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
}
