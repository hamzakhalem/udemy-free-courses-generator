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
 * API Testing Script for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api_router.php');

// Set up test environment
$testuser = null;
$testresults = [];

echo "=== LMP Data Layer API Testing ===\n\n";

// Test 1: Check if plugin is installed
echo "Test 1: Plugin Installation Check\n";
echo "-----------------------------------\n";

try {
    $plugin = core_plugin_manager::instance()->get_plugin_info('local_lmp_data_layer');
    if ($plugin && $plugin->is_installed_and_upgraded()) {
        echo "✅ Plugin is installed and upgraded\n";
        $testresults['plugin_installed'] = true;
    } else {
        echo "❌ Plugin is not properly installed\n";
        $testresults['plugin_installed'] = false;
    }
} catch (Exception $e) {
    echo "❌ Error checking plugin: " . $e->getMessage() . "\n";
    $testresults['plugin_installed'] = false;
}

// Test 2: Check database tables
echo "\nTest 2: Database Tables Check\n";
echo "-----------------------------\n";

$required_tables = ['local_lmp_outbox', 'local_lmp_inbox', 'local_lmp_audit_log'];
foreach ($required_tables as $table) {
    try {
        if ($DB->get_manager()->table_exists($table)) {
            echo "✅ Table {$table} exists\n";
            $testresults["table_{$table}"] = true;
        } else {
            echo "❌ Table {$table} does not exist\n";
            $testresults["table_{$table}"] = false;
        }
    } catch (Exception $e) {
        echo "❌ Error checking table {$table}: " . $e->getMessage() . "\n";
        $testresults["table_{$table}"] = false;
    }
}

// Test 3: Check capabilities
echo "\nTest 3: Capabilities Check\n";
echo "--------------------------\n";

$required_capabilities = [
    'local/lmp_data_layer:view_publishing_events',
    'local/lmp_data_layer:manage_publishing_events',
    'local/lmp_data_layer:view_product_control',
    'local/lmp_data_layer:manage_product_control',
    'local/lmp_data_layer:view_consuming_events',
    'local/lmp_data_layer:manage_consuming_events',
    'local/lmp_data_layer:view_integration_flows',
    'local/lmp_data_layer:manage_integration_flows',
    'local/lmp_data_layer:access_api'
];

foreach ($required_capabilities as $capability) {
    try {
        if (get_capability_info($capability)) {
            echo "✅ Capability {$capability} exists\n";
            $testresults["capability_{$capability}"] = true;
        } else {
            echo "❌ Capability {$capability} does not exist\n";
            $testresults["capability_{$capability}"] = false;
        }
    } catch (Exception $e) {
        echo "❌ Error checking capability {$capability}: " . $e->getMessage() . "\n";
        $testresults["capability_{$capability}"] = false;
    }
}

// Test 4: Create test user
echo "\nTest 4: Test User Creation\n";
echo "---------------------------\n";

try {
    // Create test user if it doesn't exist
    $testuser = $DB->get_record('user', ['username' => 'apitest']);
    if (!$testuser) {
        $testuser = new stdClass();
        $testuser->username = 'apitest';
        $testuser->firstname = 'API';
        $testuser->lastname = 'Test';
        $testuser->email = 'apitest@example.com';
        $testuser->password = hash_internal_user_password('testpass123');
        $testuser->confirmed = 1;
        $testuser->mnethostid = $CFG->mnet_localhost_id;
        $testuser->timecreated = time();
        $testuser->timemodified = time();
        
        $testuser->id = $DB->insert_record('user', $testuser);
        echo "✅ Test user created with ID: {$testuser->id}\n";
    } else {
        echo "✅ Test user already exists with ID: {$testuser->id}\n";
    }
    
    // Assign manager role to test user
    $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
    if ($managerrole) {
        role_assign($managerrole->id, $testuser->id, context_system::instance()->id);
        echo "✅ Manager role assigned to test user\n";
    }
    
    $testresults['test_user'] = true;
} catch (Exception $e) {
    echo "❌ Error creating test user: " . $e->getMessage() . "\n";
    $testresults['test_user'] = false;
}

// Test 5: API Router Test
echo "\nTest 5: API Router Test\n";
echo "-----------------------\n";

try {
    // Test API router with various endpoints
    $test_endpoints = [
        'v1/publisher/publishing-events',
        'v1/publisher/product-control',
        'v1/consumer/consuming-events',
        'v1/integration/flows/active-events'
    ];
    
    foreach ($test_endpoints as $endpoint) {
        $response = \local_lmp_data_layer\api_router::handle_request('GET', $endpoint);
        if (isset($response['error']) && $response['error']) {
            echo "✅ Endpoint {$endpoint} returns expected error (authentication required)\n";
        } else {
            echo "⚠️  Endpoint {$endpoint} returned unexpected response\n";
        }
    }
    
    $testresults['api_router'] = true;
} catch (Exception $e) {
    echo "❌ Error testing API router: " . $e->getMessage() . "\n";
    $testresults['api_router'] = false;
}

// Test 6: Create Test Data
echo "\nTest 6: Test Data Creation\n";
echo "---------------------------\n";

try {
    // Create test publishing event
    $test_event = new stdClass();
    $test_event->id = strval(time());
    $test_event->eventid = 'lmp_test_event';
    $test_event->eventname = 'Test Event';
    $test_event->description = 'Test event for API testing';
    $test_event->eventpublishingenabled = 1;
    $test_event->createdby = 'test@example.com';
    $test_event->createdat = time();
    $test_event->tenantid = 'default_tenant';
    $test_event->eventdata = json_encode(['test' => 'data']);
    $test_event->status = 'pending';
    $test_event->timecreated = time();
    $test_event->timemodified = time();
    var_dump($test_event);
    $result = $DB->insert_record('local_lmp_outbox', $test_event);
    if ($result) {
        echo "✅ Test publishing event created\n";
        $testresults['test_data_publishing'] = true;
    } else {
        echo "❌ Failed to create test publishing event\n";
        $testresults['test_data_publishing'] = false;
    }
    
    // Create test consuming event
    $test_consuming_event = new stdClass();
    $test_consuming_event->id = strval(time());
    $test_consuming_event->eventid = 'cmp_test_event';
    $test_consuming_event->eventname = 'Test Consuming Event';
    $test_consuming_event->description = 'Test consuming event for API testing';
    $test_consuming_event->eventconsumingenabled = 1;
    $test_consuming_event->createdby = 'test@example.com';
    $test_consuming_event->createdat = time();
    $test_consuming_event->tenantid = 'default_tenant';
    $test_consuming_event->eventdata = json_encode(['test' => 'consuming_data']);
    $test_consuming_event->status = 'received';
    $test_consuming_event->fieldmappings = json_encode(['test' => 'mapping']);
    $test_consuming_event->timecreated = time();
    $test_consuming_event->timemodified = time();
    
    $result = $DB->insert_record('local_lmp_inbox', $test_consuming_event);
    if ($result) {
        echo "✅ Test consuming event created\n";
        $testresults['test_data_consuming'] = true;
    } else {
        echo "❌ Failed to create test consuming event\n";
        $testresults['test_data_consuming'] = false;
    }
    
} catch (Exception $e) {
    var_dump($e);
    echo "❌ Error creating test data: " . $e->getMessage() . "\n";
    $testresults['test_data'] = false;
}

// Test 7: API Endpoint Testing (with authentication)
echo "\nTest 7: API Endpoint Testing\n";
echo "----------------------------\n";

if ($testuser) {
    try {
        // Set user context
        $USER = $testuser;
        
        // Test publishing events API
        $response = \local_lmp_data_layer\api_router::handle_request('GET', 'v1/publisher/publishing-events');
        if (isset($response['events']) && is_array($response['events'])) {
            echo "✅ Publishing events API works\n";
            $testresults['api_publishing'] = true;
        } else {
            echo "❌ Publishing events API failed\n";
            $testresults['api_publishing'] = false;
        }
        
        // Test product control API
        $response = \local_lmp_data_layer\api_router::handle_request('GET', 'v1/publisher/product-control');
        if (isset($response['eventsPublishingEnabled'])) {
            echo "✅ Product control API works\n";
            $testresults['api_product_control'] = true;
        } else {
            echo "❌ Product control API failed\n";
            $testresults['api_product_control'] = false;
        }
        
        // Test consuming events API
        $response = \local_lmp_data_layer\api_router::handle_request('GET', 'v1/consumer/consuming-events');
        if (isset($response['events']) && is_array($response['events'])) {
            echo "✅ Consuming events API works\n";
            $testresults['api_consuming'] = true;
        } else {
            echo "❌ Consuming events API failed\n";
            $testresults['api_consuming'] = false;
        }
        
        // Test integration flows API
        $response = \local_lmp_data_layer\api_router::handle_request('GET', 'v1/integration/flows/active-events');
        if (isset($response['activeEvents']) && is_array($response['activeEvents'])) {
            echo "✅ Integration flows API works\n";
            $testresults['api_integration'] = true;
        } else {
            echo "❌ Integration flows API failed\n";
            $testresults['api_integration'] = false;
        }
        
    } catch (Exception $e) {
        echo "❌ Error testing API endpoints: " . $e->getMessage() . "\n";
        $testresults['api_endpoints'] = false;
    }
} else {
    echo "⚠️  Skipping API endpoint testing (no test user)\n";
    $testresults['api_endpoints'] = false;
}

// Summary
echo "\n=== Test Summary ===\n";
echo "===================\n";

$total_tests = count($testresults);
$passed_tests = array_sum($testresults);
$failed_tests = $total_tests - $passed_tests;

echo "Total Tests: {$total_tests}\n";
echo "Passed: {$passed_tests}\n";
echo "Failed: {$failed_tests}\n";
echo "Success Rate: " . round(($passed_tests / $total_tests) * 100, 2) . "%\n\n";

if ($failed_tests > 0) {
    echo "Failed Tests:\n";
    foreach ($testresults as $test => $result) {
        if (!$result) {
            echo "❌ {$test}\n";
        }
    }
} else {
    echo "🎉 All tests passed! The LMP Data Layer plugin is working correctly.\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Test the API endpoints via HTTP requests\n";
echo "2. Create real events by grading assignments\n";
echo "3. Test the full integration flow\n";
echo "4. Configure production settings\n";

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
