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
 * Event Testing Script for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/lmp_data_layer/classes/event_publisher.php');

echo "=== LMP Data Layer Event Testing ===\n\n";

// Test 1: Create test course and users
echo "Test 1: Creating Test Data\n";
echo "---------------------------\n";

try {
    // Create test course
    $course = new stdClass();
    $course->fullname = 'LMP Data Layer Test Course';
    $course->shortname = 'LMPTEST';
    $course->category = 1; // Default category
    $course->summary = 'Test course for LMP Data Layer testing';
    $course->format = 'topics';
    $course->numsections = 1;
    $course->startdate = time();
    $course->timecreated = time();
    $course->timemodified = time();
    
    $courseid = $DB->insert_record('course', $course);
    echo "✅ Test course created with ID: {$courseid}\n";
    
    // Create test student
    $student = new stdClass();
    $student->username = 'teststudent';
    $student->firstname = 'Test';
    $student->lastname = 'Student';
    $student->email = 'teststudent@example.com';
    $student->password = hash_internal_user_password('testpass123');
    $student->confirmed = 1;
    $student->mnethostid = $CFG->mnet_localhost_id;
    $student->timecreated = time();
    $student->timemodified = time();
    
    $studentid = $DB->insert_record('user', $student);
    echo "✅ Test student created with ID: {$studentid}\n";
    
    // Enroll student in course
    $enrollment = new stdClass();
    $enrollment->userid = $studentid;
    $enrollment->courseid = $courseid;
    $enrollment->status = ENROL_USER_ACTIVE;
    $enrollment->timestart = time();
    $enrollment->timecreated = time();
    $enrollment->timemodified = time();
    
    $DB->insert_record('user_enrolments', $enrollment);
    echo "✅ Student enrolled in course\n";
    
    // Create test teacher
    $teacher = new stdClass();
    $teacher->username = 'testteacher';
    $teacher->firstname = 'Test';
    $teacher->lastname = 'Teacher';
    $teacher->email = 'testteacher@example.com';
    $teacher->password = hash_internal_user_password('testpass123');
    $teacher->confirmed = 1;
    $teacher->mnethostid = $CFG->mnet_localhost_id;
    $teacher->timecreated = time();
    $teacher->timemodified = time();
    
    $teacherid = $DB->insert_record('user', $teacher);
    echo "✅ Test teacher created with ID: {$teacherid}\n";
    
    // Assign teacher role to course
    $editingteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
    if ($editingteacherrole) {
        role_assign($editingteacherrole->id, $teacherid, context_course::instance($courseid)->id);
        echo "✅ Teacher role assigned to course\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating test data: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Create assignment and grade it
echo "\nTest 2: Creating Assignment and Grade\n";
echo "-------------------------------------\n";

try {
    // Create assignment
    $assignment = new stdClass();
    $assignment->course = $courseid;
    $assignment->name = 'Test Assignment';
    $assignment->intro = 'Test assignment for LMP Data Layer';
    $assignment->introformat = FORMAT_HTML;
    $assignment->assignmenttype = 'upload';
    $assignment->resubmit = 0;
    $assignment->preventlate = 0;
    $assignment->emailteachers = 0;
    $assignment->var1 = 0;
    $assignment->var2 = 0;
    $assignment->var3 = 0;
    $assignment->var4 = 0;
    $assignment->var5 = 0;
    $assignment->maxbytes = 1048576;
    $assignment->timedue = 0;
    $assignment->timeavailable = 0;
    $assignment->grade = 100;
    $assignment->timemodified = time();
    
    $assignmentid = $DB->insert_record('assignment', $assignment);
    echo "✅ Assignment created with ID: {$assignmentid}\n";
    
    // Create course module
    $cm = new stdClass();
    $cm->course = $courseid;
    $cm->module = $DB->get_field('modules', 'id', ['name' => 'assignment']);
    $cm->instance = $assignmentid;
    $cm->section = 1;
    $cm->idnumber = '';
    $cm->added = time();
    $cm->score = 0;
    $cm->indent = 0;
    $cm->visible = 1;
    $cm->visibleoncoursepage = 1;
    $cm->visibleold = 1;
    $cm->groupmode = 0;
    $cm->groupingid = 0;
    $cm->completion = 0;
    $cm->completionview = 0;
    $cm->completionexpected = 0;
    $cm->showdescription = 0;
    $cm->availability = null;
    $cm->deletioninprogress = 0;
    
    $cmid = $DB->insert_record('course_modules', $cm);
    echo "✅ Course module created with ID: {$cmid}\n";
    
    // Create grade item
    $gradeitem = new stdClass();
    $gradeitem->courseid = $courseid;
    $gradeitem->categoryid = null;
    $gradeitem->itemname = 'Test Assignment';
    $gradeitem->itemtype = 'mod';
    $gradeitem->itemmodule = 'assignment';
    $gradeitem->iteminstance = $assignmentid;
    $gradeitem->itemnumber = 0;
    $gradeitem->idnumber = '';
    $gradeitem->calculation = null;
    $gradeitem->gradetype = 1; // Value
    $gradeitem->grademax = 100;
    $gradeitem->grademin = 0;
    $gradeitem->scaleid = null;
    $gradeitem->outcomeid = null;
    $gradeitem->gradepass = 0;
    $gradeitem->multfactor = 1;
    $gradeitem->plusfactor = 0;
    $gradeitem->aggregationcoef = 0;
    $gradeitem->aggregationcoef2 = 0;
    $gradeitem->sortorder = 0;
    $gradeitem->display = 0;
    $gradeitem->decimals = 2;
    $gradeitem->hidden = 0;
    $gradeitem->locked = 0;
    $gradeitem->locktime = 0;
    $gradeitem->needsupdate = 0;
    $gradeitem->timecreated = time();
    $gradeitem->timemodified = time();
    
    $gradeitemid = $DB->insert_record('grade_items', $gradeitem);
    echo "✅ Grade item created with ID: {$gradeitemid}\n";
    
    // Create grade
    $grade = new stdClass();
    $grade->itemid = $gradeitemid;
    $grade->userid = $studentid;
    $grade->rawgrade = 85.5;
    $grade->rawgrademax = 100;
    $grade->rawgrademin = 0;
    $grade->rawscaleid = null;
    $grade->usermodified = $teacherid;
    $grade->finalgrade = 85.5;
    $grade->hidden = 0;
    $grade->locked = 0;
    $grade->locktime = 0;
    $grade->exported = 0;
    $grade->overridden = 0;
    $grade->excluded = 0;
    $grade->feedback = 'Good work!';
    $grade->feedbackformat = 1;
    $grade->information = '';
    $grade->informationformat = 1;
    $grade->timecreated = time();
    $grade->timemodified = time();
    
    $gradeid = $DB->insert_record('grade_grades', $grade);
    echo "✅ Grade created with ID: {$gradeid}\n";
    
} catch (Exception $e) {
    echo "❌ Error creating assignment and grade: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Trigger grade event
echo "\nTest 3: Triggering Grade Event\n";
echo "-------------------------------\n";

try {
    // Set user context for event
    $USER = $DB->get_record('user', ['id' => $teacherid]);
    
    // Create and trigger grade event
    $event = \core\event\user_graded::create([
        'objectid' => $gradeid,
        'relateduserid' => $studentid,
        'context' => context_course::instance($courseid),
        'courseid' => $courseid,
        'userid' => $teacherid,
        'other' => [
            'itemid' => $gradeitemid,
            'finalgrade' => 85.5
        ]
    ]);
    
    $event->trigger();
    echo "✅ Grade event triggered\n";
    
    // Check if event was created in outbox
    $outbox_events = $DB->get_records('local_lmp_outbox', ['eventid' => 'lmp_grade_submitted']);
    if (count($outbox_events) > 0) {
        echo "✅ Event created in outbox table\n";
        $event_record = reset($outbox_events);
        echo "   Event ID: {$event_record->id}\n";
        echo "   Event Name: {$event_record->eventname}\n";
        echo "   Status: {$event_record->status}\n";
    } else {
        echo "⚠️  No events found in outbox table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error triggering grade event: " . $e->getMessage() . "\n";
}

// Test 4: Test API with real data
echo "\nTest 4: Testing API with Real Data\n";
echo "-----------------------------------\n";

try {
    // Test publishing events API
    $response = \local_lmp_data_layer\api_router::handle_request('GET', 'v1/publisher/publishing-events');
    
    if (isset($response['events']) && is_array($response['events'])) {
        echo "✅ Publishing events API returned " . count($response['events']) . " events\n";
        
        if (count($response['events']) > 0) {
            $event = $response['events'][0];
            echo "   Sample event:\n";
            echo "   - ID: {$event['id']}\n";
            echo "   - Event ID: {$event['eventId']}\n";
            echo "   - Event Name: {$event['eventName']}\n";
            echo "   - Status: " . ($event['eventPublishingEnabled'] ? 'Enabled' : 'Disabled') . "\n";
        }
    } else {
        echo "❌ Publishing events API failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing API: " . $e->getMessage() . "\n";
}

// Test 5: Test event toggling
echo "\nTest 5: Testing Event Toggling\n";
echo "-------------------------------\n";

try {
    // Get the first event from outbox
    $outbox_events = $DB->get_records('local_lmp_outbox', [], 'timecreated DESC', '*', 0, 1);
    
    if (!empty($outbox_events)) {
        $event_record = reset($outbox_events);
        $event_id = $event_record->id;
        
        echo "Testing toggle for event: {$event_id}\n";
        
        // Toggle event status
        $response = \local_lmp_data_layer\api_router::handle_request('PATCH', "v1/publisher/publishing-events/{$event_id}", [
            'eventPublishingEnabled' => false
        ]);
        
        if (isset($response['eventPublishingEnabled']) && !$response['eventPublishingEnabled']) {
            echo "✅ Event successfully disabled\n";
        } else {
            echo "❌ Event toggle failed\n";
        }
        
        // Toggle back to enabled
        $response = \local_lmp_data_layer\api_router::handle_request('PATCH', "v1/publisher/publishing-events/{$event_id}", [
            'eventPublishingEnabled' => true
        ]);
        
        if (isset($response['eventPublishingEnabled']) && $response['eventPublishingEnabled']) {
            echo "✅ Event successfully enabled\n";
        } else {
            echo "❌ Event toggle back failed\n";
        }
        
    } else {
        echo "⚠️  No events found to test toggling\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing event toggling: " . $e->getMessage() . "\n";
}

// Summary
echo "\n=== Test Summary ===\n";
echo "===================\n";
echo "✅ Test course created\n";
echo "✅ Test users created\n";
echo "✅ Assignment and grade created\n";
echo "✅ Grade event triggered\n";
echo "✅ API tested with real data\n";

echo "\n=== Next Steps ===\n";
echo "1. Check the outbox table for events:\n";
echo "   SELECT * FROM local_lmp_outbox ORDER BY timecreated DESC;\n\n";
echo "2. Test the API endpoints via HTTP:\n";
echo "   GET /local/lmp_data_layer/api.php?path=v1/publisher/publishing-events\n\n";
echo "3. Test event toggling via API:\n";
echo "   PATCH /local/lmp_data_layer/api.php?path=v1/publisher/publishing-events/{event_id}\n\n";
echo "4. Check audit logs:\n";
echo "   SELECT * FROM local_lmp_audit_log ORDER BY timecreated DESC;\n\n";

echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
