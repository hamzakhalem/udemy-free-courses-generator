# LMP Data Layer Plugin

A simple Moodle local plugin that listens to grade events and stores them in an outbox table for SIS integration.

## Features

- **Event Observers**: Listens to Moodle grade events
- **Outbox Storage**: Stores events in database for middle layer consumption
- **SIS Integration**: Ready for SIS (Student Information System) integration

## Supported Events

### 1. Individual Grade Submission
- **Event**: `\core\event\user_graded`
- **Trigger**: When teacher grades a student individually
- **Handler**: `local_lmp_data_layer_observer::user_graded()`

### 2. Quiz Grade Submission
- **Event**: `\mod_quiz\event\attempt_submitted`
- **Trigger**: When student submits quiz attempt
- **Handler**: `local_lmp_data_layer_observer::quiz_attempt_submitted()`

## Installation

1. Copy the plugin to `moodle/plugins/local/lmp_data_layer/`
2. Run database upgrade: `php admin/cli/upgrade.php`

## Requirements

- Moodle 4.5+
- PHP 8.0+

## Usage

The plugin automatically:
1. Listens for grade events in Moodle
2. Processes event data
3. Stores events in `local_lmp_outbox` table
4. Your middle layer reads from outbox and sends to SIS

## Database Tables

### Outbox Table (`local_lmp_outbox`)
Stores events for SIS integration:
- `id` - UUID of the event
- `eventid` - Event identifier (e.g., `lmp_grade_submitted`)
- `eventname` - Human readable event name
- `eventdata` - JSON payload of the CloudEvent
- `status` - Event status (pending, published, failed)
- `tenantid` - Tenant ID for multi-tenancy
- `eventpublishingenabled` - Whether event publishing is enabled
- `createdby` - User who created the event
- `createdat` - Timestamp when event was created
- `updatedby` - User who last updated the event
- `updatedat` - Timestamp when event was last updated
- `retrycount` - Number of retry attempts
- `errormessage` - Error message if event failed
- `scheduleconfig` - Schedule configuration JSON

### Inbox Table (`local_lmp_inbox`)
Stores events consumed from CMP:
- `id` - UUID of the event
- `eventid` - Event identifier (e.g., `cmp_student_created`)
- `eventname` - Human readable event name
- `eventdata` - JSON payload of the CloudEvent
- `status` - Event status (received, processing, processed, failed)
- `tenantid` - Tenant ID for multi-tenancy
- `eventconsumingenabled` - Whether event consuming is enabled
- `fieldmappings` - Field mapping configuration JSON
- `scheduleconfig` - Schedule configuration JSON
- All other fields same as outbox table

### Audit Log Table (`local_lmp_audit_log`)
Stores audit trail for API and system changes:
- `id` - Primary key
- `component` - Component that made the change
- `action` - Action performed
- `eventid` - Event ID if applicable
- `userid` - User who made the change
- `timecreated` - Timestamp when change was made
- `details` - JSON details of the change

## API Endpoints

The plugin provides a comprehensive REST API for managing events and integration flows:

### Publishing Events APIs
- `GET /v1/publisher/publishing-events` - Get list of publishing events
- `PATCH /v1/publisher/publishing-events/{id}` - Toggle publishing event status
- `GET /v1/publisher/publishing-events/{id}/logs` - Get publisher event logs
- `GET /v1/publisher/publishing-events/{id}/logs/{logId}` - Get detailed log record

### Product Control APIs
- `PATCH /v1/publisher/product-control` - Master toggle for product-level publishing
- `GET /v1/publisher/product-control` - Get product control status

### Consuming Events APIs
- `GET /v1/consumer/consuming-events` - Get consumed events with status
- `PATCH /v1/consumer/consuming-events/{id}` - Toggle consuming event status
- `PUT /v1/consumer/consuming-events/{id}/field-mappings` - Update field mappings
- `GET /v1/consumer/consuming-events/{id}/logs` - Get consumer event logs
- `GET /v1/consumer/consuming-events/{id}/logs/{logId}` - Get detailed log record

### Integration Flows APIs
- `PATCH /v1/integration/flows/{id}/schedule` - Update event schedule
- `GET /v1/integration/flows/active-events` - Get active consumer events
- `GET /v1/integration/flows/{id}/status` - Get integration flow status

For detailed API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

## Capabilities

The plugin defines the following capabilities:
- `local/lmp_data_layer:view_publishing_events` - View publishing events
- `local/lmp_data_layer:manage_publishing_events` - Manage publishing events
- `local/lmp_data_layer:view_product_control` - View product control
- `local/lmp_data_layer:manage_product_control` - Manage product control
- `local/lmp_data_layer:view_consuming_events` - View consuming events
- `local/lmp_data_layer:manage_consuming_events` - Manage consuming events
- `local/lmp_data_layer:view_integration_flows` - View integration flows
- `local/lmp_data_layer:manage_integration_flows` - Manage integration flows
- `local/lmp_data_layer:access_api` - Access API endpoints
