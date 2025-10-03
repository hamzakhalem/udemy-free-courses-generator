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
 * Product Control API for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lmp_data_layer\api;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api/base_api.php');

/**
 * Product Control API
 * 
 * Handles product-level control for event publishing
 */
class product_control_api extends base_api {
    
    /**
     * Master toggle for product-level event publishing
     * 
     * @param array $data Request data
     * @return array API response
     */
    public function toggle_product_publishing($data) {
        global $DB, $USER;
        
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:manage_product_control');
            
            // Validate input
            if (!isset($data['eventsPublishingEnabled']) || !is_bool($data['eventsPublishingEnabled'])) {
                return $this->error_response('Invalid eventsPublishingEnabled value', 400);
            }
            
            // Update global publishing setting
            $result = set_config('global_publishing_enabled', $data['eventsPublishingEnabled'] ? 1 : 0, 'local_lmp_data_layer');
            
            if ($result !== false) {
                // Log the change
                $this->log_product_control_change($data['eventsPublishingEnabled'], $USER->id);
                
                return [
                    'eventsPublishingEnabled' => $data['eventsPublishingEnabled'],
                    'updatedBy' => $USER->email,
                    'updatedAt' => date('c'),
                    'message' => $data['eventsPublishingEnabled'] ? 
                        'Product-level event publishing has been enabled' : 
                        'Product-level event publishing has been disabled'
                ];
            } else {
                return $this->error_response('Failed to update product control settings', 500);
            }
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to toggle product publishing', $e);
        }
    }
    
    /**
     * Get current product control status
     * 
     * @return array API response
     */
    public function get_product_control_status() {
        try {
            // Check authentication and permissions
            $this->require_login();
            $this->require_capability('local/lmp_data_layer:view_product_control');
            
            $enabled = get_config('local_lmp_data_layer', 'global_publishing_enabled');
            
            return [
                'eventsPublishingEnabled' => (bool) $enabled,
                'lastUpdated' => $this->get_last_product_control_update(),
                'status' => $enabled ? 'enabled' : 'disabled'
            ];
            
        } catch (\Exception $e) {
            return $this->handle_error('Failed to get product control status', $e);
        }
    }
    
    /**
     * Log product control changes
     * 
     * @param bool $enabled Whether publishing is enabled
     * @param int $userId User ID who made the change
     * @return void
     */
    private function log_product_control_change($enabled, $userId) {
        global $DB;
        
        try {
            $logRecord = new \stdClass();
            $logRecord->action = $enabled ? 'enable' : 'disable';
            $logRecord->component = 'product_control';
            $logRecord->userid = $userId;
            $logRecord->timecreated = time();
            $logRecord->details = json_encode([
                'eventsPublishingEnabled' => $enabled,
                'timestamp' => date('c')
            ]);
            
            // Store in a log table (you may need to create this table)
            $DB->insert_record('local_lmp_audit_log', $logRecord);
            
        } catch (\Exception $e) {
            debugging("LMP Data Layer: Failed to log product control change - " . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
    
    /**
     * Get last product control update info
     * 
     * @return array|null Update information
     */
    private function get_last_product_control_update() {
        global $DB;
        
        try {
            $log = $DB->get_record('local_lmp_audit_log', [
                'component' => 'product_control'
            ], 'timecreated DESC');
            
            if ($log) {
                return [
                    'updatedBy' => $DB->get_field('user', 'email', ['id' => $log->userid]),
                    'updatedAt' => date('c', $log->timecreated),
                    'action' => $log->action
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            debugging("LMP Data Layer: Failed to get last product control update - " . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }
}
