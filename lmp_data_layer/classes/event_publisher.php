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
 * Event Publisher for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lmp_data_layer;

defined('MOODLE_INTERNAL') || die();

/**
 * Event Publisher
 * 
 * This class publishes events to the outbox table and optionally to external systems.
 * It follows a clean, maintainable structure with proper error handling.
 */
class event_publisher {
    
    /**
     * Publish grade submitted event
     * 
     * @param array $eventData Event data
     * @return void
     */
    public static function publish_grade_submitted($eventData) {
        self::publish_event($eventData, 'lmp_grade_submitted', 'Grade Submitted', 
            'Individual grade submission from LMP to CMP');
    }
    
    /**
     * Publish quiz grade submitted event
     * 
     * @param array $eventData Event data
     * @return void
     */
    public static function publish_quiz_grade_submitted($eventData) {
        self::publish_event($eventData, 'lmp_quiz_grade_submitted', 'Quiz Grade Submitted', 
            'Quiz attempt submission from LMP to CMP');
    }
    
    /**
     * Publish event to outbox table and Kafka
     *
     * @param array $eventData Event data
     * @param string $eventId Event identifier
     * @param string $eventName Event name
     * @param string $description Event description
     * @return void
     */
    private static function publish_event($eventData, $eventId, $eventName, $description) {
        global $DB, $USER;

        try {
            // Start database transaction for data integrity
            $transaction = $DB->start_delegated_transaction();

            // Create CloudEvent structure
            $cloudEvent = self::create_cloud_event($eventId, $eventData);

            // Prepare outbox record
            $outboxRecord = new \stdClass();
            $outboxRecord->id = $cloudEvent['id'];
            $outboxRecord->eventid = $eventId;
            $outboxRecord->eventname = $eventName;
            $outboxRecord->description = $description;
            $outboxRecord->eventpublishingenabled = true;
            $outboxRecord->createdby = $USER->email ?? 'system';
            $outboxRecord->createdat = time();
            $outboxRecord->tenantid = 'default_tenant';
            $outboxRecord->eventdata = json_encode($cloudEvent);
            $outboxRecord->status = 'pending';
            $outboxRecord->timecreated = time();
            $outboxRecord->timemodified = time();

            // Store in outbox table
            $result = $DB->insert_record('local_lmp_outbox', $outboxRecord);

            if ($result) {
                // Commit transaction
                $transaction->allow_commit();

                debugging("LMP Data Layer - {$eventId}: Event published to outbox", DEBUG_DEVELOPER);

                // Try to publish to Kafka (non-blocking)
                self::publish_to_kafka($eventData, $eventId);

            } else {
                // Rollback transaction if insert failed
                $transaction->rollback();
                debugging("LMP Data Layer - {$eventId}: Failed to publish event to outbox", DEBUG_DEVELOPER);
            }

        } catch (\Exception $e) {
            // Rollback transaction on error
            if (isset($transaction)) {
                $transaction->rollback();
            }
            debugging("LMP Data Layer - {$eventId}: ERROR - Failed to publish event: " . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Publish event to Kafka
     *
     * @param array $eventData Event data
     * @param string $eventId Event identifier
     * @return void
     */
    private static function publish_to_kafka($eventData, $eventId) {
        try {
            // Load Kafka publisher
            require_once(__DIR__ . '/kafka_publisher.php');
            
            // Determine event type from event ID
            $eventType = str_replace('lmp_', '', $eventId);
            
            // Publish to Kafka
            $success = kafka_publisher::publish_event($eventData, $eventType);
            
            if ($success) {
                debugging("LMP Data Layer - {$eventId}: Event published to Kafka", DEBUG_DEVELOPER);
            } else {
                debugging("LMP Data Layer - {$eventId}: Failed to publish event to Kafka", DEBUG_DEVELOPER);
            }

        } catch (\Exception $e) {
            debugging("LMP Data Layer - {$eventId}: Kafka publishing error: " . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
    
    /**
     * Create CloudEvent structure
     * 
     * @param string $eventType Event type
     * @param array $data Event data
     * @return array CloudEvent structure
     */
    private static function create_cloud_event($eventType, $data) {
        return [
            'specversion' => '1.0',
            'type' => $eventType,
            'source' => '/LMP/Moodle',
            'id' => self::generate_uuid(),
            'time' => date('c'),
            'datacontenttype' => 'application/json',
            'data' => $data
        ];
    }
    
    /**
     * Generate UUID
     * 
     * @return string
     */
    private static function generate_uuid() {
        return sprintf('event_%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
