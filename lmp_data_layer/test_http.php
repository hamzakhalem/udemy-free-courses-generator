<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * HTTP API Testing Script for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Configuration
$base_url = 'http://localhost/moodle'; // Change this to your Moodle URL
$api_path = '/local/lmp_data_layer/api.php?path=';
$username = 'admin'; // Change to your admin username
$password = 'admin'; // Change to your admin password

echo "=== LMP Data Layer HTTP API Testing ===\n\n";

// Function to make HTTP requests
function make_request($url, $method = 'GET', $data = null, $cookies = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/moodle_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/moodle_cookies.txt');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $http_code,
        'error' => $error
    ];
}

// Function to login to Moodle
function login_to_moodle($base_url, $username, $password) {
    echo "Step 1: Logging into Moodle...\n";
    
    // Get login page
    $login_url = $base_url . '/login/index.php';
    $result = make_request($login_url);
    
    if ($result['error']) {
        echo "❌ Error accessing login page: " . $result['error'] . "\n";
        return false;
    }
    
    // Extract login token
    preg_match('/name="logintoken" value="([^"]+)"/', $result['response'], $matches);
    $logintoken = isset($matches[1]) ? $matches[1] : '';
    
    // Login
    $login_data = [
        'username' => $username,
        'password' => $password,
        'logintoken' => $logintoken
    ];
    
    $result = make_request($login_url, 'POST', $login_data);
    
    if ($result['http_code'] === 200 && strpos($result['response'], 'dashboard') !== false) {
        echo "✅ Successfully logged in\n";
        return true;
    } else {
        echo "❌ Login failed\n";
        return false;
    }
}

// Function to test API endpoint
function test_api_endpoint($base_url, $api_path, $endpoint, $method = 'GET', $data = null) {
    $url = $base_url . $api_path . $endpoint;
    echo "\nTesting: {$method} {$endpoint}\n";
    echo "URL: {$url}\n";
    
    $result = make_request($url, $method, $data);
    
    if ($result['error']) {
        echo "❌ Error: " . $result['error'] . "\n";
        return false;
    }
    
    echo "HTTP Code: " . $result['http_code'] . "\n";
    
    // Try to decode JSON response
    $json_response = json_decode($result['response'], true);
    if ($json_response) {
        echo "✅ Valid JSON response\n";
        echo "Response: " . json_encode($json_response, JSON_PRETTY_PRINT) . "\n";
        return true;
    } else {
        echo "⚠️  Non-JSON response (might be HTML)\n";
        echo "Response: " . substr($result['response'], 0, 200) . "...\n";
        return false;
    }
}

// Main testing
echo "Base URL: {$base_url}\n";
echo "API Path: {$api_path}\n\n";

// Step 1: Login
if (!login_to_moodle($base_url, $username, $password)) {
    echo "❌ Cannot proceed without authentication\n";
    exit(1);
}

// Step 2: Test API endpoints
echo "\nStep 2: Testing API Endpoints\n";
echo "==============================\n";

$test_endpoints = [
    // Publishing Events
    ['v1/publisher/publishing-events', 'GET'],
    ['v1/publisher/product-control', 'GET'],
    
    // Consuming Events
    ['v1/consumer/consuming-events', 'GET'],
    
    // Integration Flows
    ['v1/integration/flows/active-events', 'GET'],
];

$success_count = 0;
$total_count = count($test_endpoints);

foreach ($test_endpoints as $endpoint) {
    $endpoint_path = $endpoint[0];
    $method = $endpoint[1];
    
    if (test_api_endpoint($base_url, $api_path, $endpoint_path, $method)) {
        $success_count++;
    }
}

// Step 3: Test POST/PATCH endpoints
echo "\nStep 3: Testing POST/PATCH Endpoints\n";
echo "====================================\n";

// Test product control toggle
echo "\nTesting Product Control Toggle...\n";
test_api_endpoint($base_url, $api_path, 'v1/publisher/product-control', 'PATCH', [
    'eventsPublishingEnabled' => true
]);

// Test creating a test event
echo "\nTesting Event Creation...\n";
test_api_endpoint($base_url, $api_path, 'v1/publisher/publishing-events', 'POST', [
    'eventId' => 'test_event_' . time(),
    'eventName' => 'Test Event',
    'description' => 'Test event created via API',
    'eventPublishingEnabled' => true
]);

// Summary
echo "\n=== Test Summary ===\n";
echo "===================\n";
echo "Total Endpoints Tested: {$total_count}\n";
echo "Successful: {$success_count}\n";
echo "Failed: " . ($total_count - $success_count) . "\n";
echo "Success Rate: " . round(($success_count / $total_count) * 100, 2) . "%\n";

if ($success_count === $total_count) {
    echo "\n🎉 All API endpoints are working correctly!\n";
} else {
    echo "\n⚠️  Some API endpoints failed. Check the responses above.\n";
}

echo "\n=== Manual Testing Instructions ===\n";
echo "1. Open your browser and go to: {$base_url}\n";
echo "2. Log in with your admin credentials\n";
echo "3. Navigate to: {$base_url}{$api_path}v1/publisher/publishing-events\n";
echo "4. You should see a JSON response with events\n";
echo "5. Test other endpoints by changing the path parameter\n";

echo "\n=== cURL Examples ===\n";
echo "Get Publishing Events:\n";
echo "curl -X GET '{$base_url}{$api_path}v1/publisher/publishing-events' -b /tmp/moodle_cookies.txt\n\n";

echo "Toggle Product Control:\n";
echo "curl -X PATCH '{$base_url}{$api_path}v1/publisher/product-control' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -b /tmp/moodle_cookies.txt \\\n";
echo "  -d '{\"eventsPublishingEnabled\": true}'\n\n";

echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
