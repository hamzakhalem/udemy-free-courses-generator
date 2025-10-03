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
 * Publishing Events API for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lmp_data_layer\api;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api/base_api.php');

/**
 * Publishing Events API
 * 
 * Handles all publishing events related API endpoints
 */
class publishing_events_api extends base_api {
    
    /**
     * Get list of publishing events with details
     * 
     * @return array API response
     */
    public function get_publishing_events() {
        global $DB, $USER;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:view_publishing_events');
            
            // Get all publishing events from outbox table
            $events = $DB->get_records('local_lmp_outbox', [], 'timecreated DESC');
            
            $eventsList = [];
            foreach ($events as $event) {
                $eventsList[] = [
                    'id' => $event->id,
                    'eventId' => $event->eventid,
                    'eventName' => $event->eventname,
                    'description' => $event->description,
                    'eventPublishingEnabled' => (bool) $event->eventpublishingenabled,
                    'createdBy' => $event->createdby,
                    'createdAt' => date('c', $event->createdat),
                    'updatedBy' => $event->updatedby,
                    'updatedAt' => $event->updatedat ? date('c', $event->updatedat) : null
                ];
            }
            
            return [
                'eventsPublishingEnabled' => $this->is_global_publishing_enabled(),
                'events' => $eventsList
            ];
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to get publishing events', $e);
        }
    }
    
    /**
     * Toggle status of a specific publishing event
     * 
     * @param string $eventId Event ID
     * @param array $data Request data
     * @return array API response
     */
    public function toggle_publishing_event($eventId, $data) {
        global $DB, $USER;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:manage_publishing_events');
            
            // Validate input
            if (!isset($data['eventPublishingEnabled']) || !is_bool($data['eventPublishingEnabled'])) {
                return $this->error_response('Invalid eventPublishingEnabled value', 400);
            }
            
            // Get event record
            $event = $DB->get_record('local_lmp_outbox', ['id' => $eventId]);
            if (!$event) {
                return $this->error_response('Event not found', 404);
            }
            
            // Update event status
            $event->eventpublishingenabled = $data['eventPublishingEnabled'] ? 1 : 0;
            $event->updatedby = $USER->email;
            $event->updatedat = time();
            $event->timemodified = time();
            
            $result = $DB->update_record('local_lmp_outbox', $event);
            
            if ($result) {
                return [
                    'id' => $event->id,
                    'eventId' => $event->eventid,
                    'eventName' => $event->eventname,
                    'description' => $event->description,
                    'eventPublishingEnabled' => (bool) $event->eventpublishingenabled,
                    'createdBy' => $event->createdby,
                    'createdAt' => date('c', $event->createdat),
                    'updatedBy' => $event->updatedby,
                    'updatedAt' => date('c', $event->updatedat)
                ];
            } else {
                return $this->error_response('Failed to update event', 500);
            }
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to toggle publishing event', $e);
        }
    }
    
    /**
     * Get publisher event log details
     * 
     * @param string $eventId Event ID
     * @return array API response
     */
    public function get_publisher_event_logs($eventId) {
        global $DB;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:view_publishing_events');
            
            // Get event record
            $event = $DB->get_record('local_lmp_outbox', ['id' => $eventId]);
            if (!$event) {
                return $this->error_response('Event not found', 404);
            }
            
            // Get log details from event record
            $logs = [
                [
                    'logId' => $event->id . '_log_1',
                    'eventId' => $event->eventid,
                    'status' => $event->status,
                    'retryCount' => (int) $event->retrycount,
                    'errorMessage' => $event->errormessage,
                    'timestamp' => date('c', $event->timemodified),
                    'details' => [
                        'eventData' => json_decode($event->eventdata, true),
                        'eventMetadata' => json_decode($event->eventmetadata, true),
                        'scheduleConfig' => json_decode($event->scheduleconfig, true)
                    ]
                ]
            ];
            
            return [
                'eventId' => $event->eventid,
                'eventName' => $event->eventname,
                'logs' => $logs
            ];
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to get publisher event logs', $e);
        }
    }
    
    /**
     * Get detailed log record by log ID
     * 
     * @param string $eventId Event ID
     * @param string $logId Log ID
     * @return array API response
     */
    public function get_publisher_event_log_detail($eventId, $logId) {
        global $DB;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:view_publishing_events');
            
            // Get event record
            $event = $DB->get_record('local_lmp_outbox', ['id' => $eventId]);
            if (!$event) {
                return $this->error_response('Event not found', 404);
            }
            
            // Validate log ID format
            if ($logId !== $event->id . '_log_1') {
                return $this->error_response('Log not found', 404);
            }
            
            return [
                'logId' => $logId,
                'eventId' => $event->eventid,
                'eventName' => $event->eventname,
                'status' => $event->status,
                'retryCount' => (int) $event->retrycount,
                'errorMessage' => $event->errormessage,
                'timestamp' => date('c', $event->timemodified),
                'details' => [
                    'eventData' => json_decode($event->eventdata, true),
                    'eventMetadata' => json_decode($event->eventmetadata, true),
                    'scheduleConfig' => json_decode($event->scheduleconfig, true),
                    'tenantId' => $event->tenantid,
                    'createdBy' => $event->createdby,
                    'createdAt' => date('c', $event->createdat),
                    'updatedBy' => $event->updatedby,
                    'updatedAt' => $event->updatedat ? date('c', $event->updatedat) : null
                ]
            ];
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to get publisher event log detail', $e);
        }
    }
    
    /**
     * Check if global publishing is enabled
     * 
     * @return bool
     */
    private function is_global_publishing_enabled() {
        global $CFG;
        
        // Check if there's a global setting for publishing
        return get_config('local_lmp_data_layer', 'global_publishing_enabled') !== false;
    }
}
