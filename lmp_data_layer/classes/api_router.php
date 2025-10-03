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
 * API Router for LMP Data Layer
 *
 * @package    local_lmp_data_layer
 * @copyright  2025 Medad LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lmp_data_layer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api/publishing_events_api.php');
require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api/product_control_api.php');
require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api/consuming_events_api.php');
require_once($CFG->dirroot . '/local/lmp_data_layer/classes/api/integration_flows_api.php');

/**
 * API Router
 * 
 * Routes API requests to appropriate handlers
 */
class api_router {
    
    /**
     * Handle API request
     * 
     * @param string $method HTTP method
     * @param string $path API path
     * @param array $data Request data
     * @return array API response
     */
    public static function handle_request($method, $path, $data = []) {
        try {
            // Parse the path
            $pathParts = explode('/', trim($path, '/'));
            
            // Route to appropriate API handler
            switch ($pathParts[0]) {
                case 'v1':
                    return self::handle_v1_request($method, $pathParts, $data);
                default:
                    return [
                        'error' => true,
                        'message' => 'Invalid API version',
                        'code' => 400
                    ];
            }
            
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Internal server error',
                'code' => 500,
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle v1 API requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @return array API response
     */
    private static function handle_v1_request($method, $pathParts, $data) {
        if (count($pathParts) < 2) {
            return [
                'error' => true,
                'message' => 'Invalid API path',
                'code' => 400
            ];
        }
        
        $service = $pathParts[1];
        
        switch ($service) {
            case 'publisher':
                return self::handle_publisher_request($method, $pathParts, $data);
            case 'consumer':
                return self::handle_consumer_request($method, $pathParts, $data);
            case 'integration':
                return self::handle_integration_request($method, $pathParts, $data);
            default:
                return [
                    'error' => true,
                    'message' => 'Invalid service',
                    'code' => 400
                ];
        }
    }
    
    /**
     * Handle publisher requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @return array API response
     */
    private static function handle_publisher_request($method, $pathParts, $data) {
        $publishingApi = new \local_lmp_data_layer\api\publishing_events_api();
        $productControlApi = new \local_lmp_data_layer\api\product_control_api();
        
        if (count($pathParts) < 3) {
            return [
                'error' => true,
                'message' => 'Invalid publisher path',
                'code' => 400
            ];
        }
        
        $endpoint = $pathParts[2];
        
        switch ($endpoint) {
            case 'publishing-events':
                return self::handle_publishing_events($method, $pathParts, $data, $publishingApi);
            case 'product-control':
                return self::handle_product_control($method, $pathParts, $data, $productControlApi);
            default:
                return [
                    'error' => true,
                    'message' => 'Invalid publisher endpoint',
                    'code' => 400
                ];
        }
    }
    
    /**
     * Handle consumer requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @return array API response
     */
    private static function handle_consumer_request($method, $pathParts, $data) {
        $consumingApi = new \local_lmp_data_layer\api\consuming_events_api();
        
        if (count($pathParts) < 3) {
            return [
                'error' => true,
                'message' => 'Invalid consumer path',
                'code' => 400
            ];
        }
        
        $endpoint = $pathParts[2];
        
        switch ($endpoint) {
            case 'consuming-events':
                return self::handle_consuming_events($method, $pathParts, $data, $consumingApi);
            default:
                return [
                    'error' => true,
                    'message' => 'Invalid consumer endpoint',
                    'code' => 400
                ];
        }
    }
    
    /**
     * Handle integration requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @return array API response
     */
    private static function handle_integration_request($method, $pathParts, $data) {
        $integrationApi = new \local_lmp_data_layer\api\integration_flows_api();
        
        if (count($pathParts) < 3) {
            return [
                'error' => true,
                'message' => 'Invalid integration path',
                'code' => 400
            ];
        }
        
        $endpoint = $pathParts[2];
        
        switch ($endpoint) {
            case 'flows':
                return self::handle_integration_flows($method, $pathParts, $data, $integrationApi);
            default:
                return [
                    'error' => true,
                    'message' => 'Invalid integration endpoint',
                    'code' => 400
                ];
        }
    }
    
    /**
     * Handle publishing events requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @param object $api API instance
     * @return array API response
     */
    private static function handle_publishing_events($method, $pathParts, $data, $api) {
        switch ($method) {
            case 'GET':
                if (count($pathParts) === 3) {
                    return $api->get_publishing_events();
                } elseif (count($pathParts) === 5 && $pathParts[4] === 'logs') {
                    return $api->get_publisher_event_logs($pathParts[3]);
                } elseif (count($pathParts) === 6 && $pathParts[4] === 'logs') {
                    return $api->get_publisher_event_log_detail($pathParts[3], $pathParts[5]);
                }
                break;
            case 'PATCH':
                if (count($pathParts) === 4) {
                    return $api->toggle_publishing_event($pathParts[3], $data);
                }
                break;
        }
        
        return [
            'error' => true,
            'message' => 'Invalid publishing events request',
            'code' => 400
        ];
    }
    
    /**
     * Handle product control requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @param object $api API instance
     * @return array API response
     */
    private static function handle_product_control($method, $pathParts, $data, $api) {
        switch ($method) {
            case 'GET':
                return $api->get_product_control_status();
            case 'PATCH':
                return $api->toggle_product_publishing($data);
        }
        
        return [
            'error' => true,
            'message' => 'Invalid product control request',
            'code' => 400
        ];
    }
    
    /**
     * Handle consuming events requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @param object $api API instance
     * @return array API response
     */
    private static function handle_consuming_events($method, $pathParts, $data, $api) {
        switch ($method) {
            case 'GET':
                if (count($pathParts) === 3) {
                    return $api->get_consuming_events();
                } elseif (count($pathParts) === 5 && $pathParts[4] === 'logs') {
                    return $api->get_consumer_event_logs($pathParts[3]);
                } elseif (count($pathParts) === 6 && $pathParts[4] === 'logs') {
                    return $api->get_consumer_event_log_detail($pathParts[3], $pathParts[5]);
                }
                break;
            case 'PATCH':
                if (count($pathParts) === 4) {
                    return $api->toggle_consuming_event($pathParts[3], $data);
                }
                break;
            case 'PUT':
                if (count($pathParts) === 5 && $pathParts[4] === 'field-mappings') {
                    return $api->update_field_mappings($pathParts[3], $data);
                }
                break;
        }
        
        return [
            'error' => true,
            'message' => 'Invalid consuming events request',
            'code' => 400
        ];
    }
    
    /**
     * Handle integration flows requests
     * 
     * @param string $method HTTP method
     * @param array $pathParts Path parts
     * @param array $data Request data
     * @param object $api API instance
     * @return array API response
     */
    private static function handle_integration_flows($method, $pathParts, $data, $api) {
        switch ($method) {
            case 'GET':
                if (count($pathParts) === 4 && $pathParts[3] === 'active-events') {
                    return $api->get_active_consumer_events();
                } elseif (count($pathParts) === 5 && $pathParts[4] === 'status') {
                    return $api->get_integration_flow_status($pathParts[3]);
                }
                break;
            case 'PATCH':
                if (count($pathParts) === 5 && $pathParts[4] === 'schedule') {
                    return $api->update_event_schedule($pathParts[3], $data);
                }
                break;
        }
        
        return [
            'error' => true,
            'message' => 'Invalid integration flows request',
            'code' => 400
        ];
    }
}
