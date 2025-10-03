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
 * Setup Script for LMP Data Layer Plugin
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require admin access
require_admin();

$PAGE->set_url('/local/lmp_data_layer/setup.php');
$PAGE->set_title('LMP Data Layer Setup');
$PAGE->set_heading('LMP Data Layer Setup');

echo $OUTPUT->header();

echo $OUTPUT->heading('LMP Data Layer Plugin Setup');

echo "<div class='alert alert-info'>";
echo "<h4>Setup Instructions</h4>";
echo "<p>This script will help you set up the LMP Data Layer plugin.</p>";
echo "</div>";

// Check if plugin is installed
echo "<h3>1. Plugin Installation Check</h3>";

$plugin = core_plugin_manager::instance()->get_plugin_info('local_lmp_data_layer');
if ($plugin && $plugin->is_installed_and_upgraded()) {
    echo "<div class='alert alert-success'>✅ Plugin is installed and upgraded</div>";
} else {
    echo "<div class='alert alert-danger'>❌ Plugin is not properly installed</div>";
    echo "<p>Please run: <code>php admin/cli/upgrade.php</code></p>";
}

// Check database tables
echo "<h3>2. Database Tables Check</h3>";

$required_tables = ['local_lmp_outbox', 'local_lmp_inbox', 'local_lmp_audit_log'];
foreach ($required_tables as $table) {
    if ($DB->get_manager()->table_exists($table)) {
        echo "<div class='alert alert-success'>✅ Table {$table} exists</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Table {$table} does not exist</div>";
    }
}

// Check capabilities
echo "<h3>3. Capabilities Check</h3>";

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
    if (get_capability_info($capability)) {
        echo "<div class='alert alert-success'>✅ Capability {$capability} exists</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Capability {$capability} does not exist</div>";
    }
}

// Test API endpoints
echo "<h3>4. API Endpoints Test</h3>";

$api_endpoints = [
    'v1/publisher/publishing-events',
    'v1/publisher/product-control',
    'v1/consumer/consuming-events',
    'v1/integration/flows/active-events'
];

echo "<div class='alert alert-info'>";
echo "<h4>API Endpoints Available:</h4>";
echo "<ul>";
foreach ($api_endpoints as $endpoint) {
    echo "<li><code>{$endpoint}</code></li>";
}
echo "</ul>";
echo "</div>";

// Test API access
echo "<h3>5. API Access Test</h3>";

try {
    $response = \local_lmp_data_layer\api_router::handle_request('GET', 'v1/publisher/publishing-events');
    if (isset($response['events'])) {
        echo "<div class='alert alert-success'>✅ API is working correctly</div>";
        echo "<p>Found " . count($response['events']) . " publishing events</p>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ API returned unexpected response</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ API test failed: " . $e->getMessage() . "</div>";
}

// Configuration
echo "<h3>6. Configuration</h3>";

echo "<div class='alert alert-info'>";
echo "<h4>Global Settings:</h4>";
echo "<p>You can configure the plugin in <strong>Site Administration > Plugins > Local plugins > LMP Data Layer</strong></p>";
echo "</div>";

// Test data creation
echo "<h3>7. Test Data Creation</h3>";

if (isset($_POST['create_test_data'])) {
    try {
        // Create test event
        $test_event = new stdClass();
        $test_event->id = 'test-event-' . time();
        $test_event->eventid = 'lmp_test_event';
        $test_event->eventname = 'Test Event';
        $test_event->description = 'Test event created via setup';
        $test_event->eventpublishingenabled = 1;
        $test_event->createdby = $USER->email;
        $test_event->createdat = time();
        $test_event->tenantid = 'default_tenant';
        $test_event->eventdata = json_encode(['test' => 'data']);
        $test_event->status = 'pending';
        $test_event->timecreated = time();
        $test_event->timemodified = time();
        
        $result = $DB->insert_record('local_lmp_outbox', $test_event);
        if ($result) {
            echo "<div class='alert alert-success'>✅ Test event created successfully</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Failed to create test event</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Error creating test data: " . $e->getMessage() . "</div>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='create_test_data' class='btn btn-primary'>Create Test Data</button>";
echo "</form>";

// API Testing
echo "<h3>8. API Testing</h3>";

echo "<div class='alert alert-info'>";
echo "<h4>Test the API endpoints:</h4>";
echo "<ul>";
echo "<li><a href='api.php?path=v1/publisher/publishing-events' target='_blank'>Get Publishing Events</a></li>";
echo "<li><a href='api.php?path=v1/publisher/product-control' target='_blank'>Get Product Control</a></li>";
echo "<li><a href='api.php?path=v1/consumer/consuming-events' target='_blank'>Get Consuming Events</a></li>";
echo "<li><a href='api.php?path=v1/integration/flows/active-events' target='_blank'>Get Active Events</a></li>";
echo "</ul>";
echo "</div>";

// Documentation
echo "<h3>9. Documentation</h3>";

echo "<div class='alert alert-info'>";
echo "<h4>Available Documentation:</h4>";
echo "<ul>";
echo "<li><a href='README.md' target='_blank'>README.md</a> - Plugin overview and features</li>";
echo "<li><a href='API_DOCUMENTATION.md' target='_blank'>API_DOCUMENTATION.md</a> - Complete API documentation</li>";
echo "<li><a href='TESTING_GUIDE.md' target='_blank'>TESTING_GUIDE.md</a> - Testing instructions</li>";
echo "</ul>";
echo "</div>";

// Next steps
echo "<h3>10. Next Steps</h3>";

echo "<div class='alert alert-success'>";
echo "<h4>What to do next:</h4>";
echo "<ol>";
echo "<li>Test the API endpoints using the links above</li>";
echo "<li>Create real events by grading assignments</li>";
echo "<li>Test the full integration flow</li>";
echo "<li>Configure production settings</li>";
echo "<li>Set up monitoring and logging</li>";
echo "</ol>";
echo "</div>";

// Run tests
echo "<h3>11. Run Tests</h3>";

echo "<div class='alert alert-info'>";
echo "<h4>Automated Testing:</h4>";
echo "<p>You can run the automated tests:</p>";
echo "<ul>";
echo "<li><a href='test_api.php' target='_blank'>test_api.php</a> - Run API tests</li>";
echo "<li><a href='test_events.php' target='_blank'>test_events.php</a> - Run event tests</li>";
echo "<li><a href='test_http.php' target='_blank'>test_http.php</a> - Run HTTP tests</li>";
echo "</ul>";
echo "</div>";

echo $OUTPUT->footer();
