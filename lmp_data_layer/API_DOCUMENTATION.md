# LMP Data Layer API Documentation

## Overview

The LMP Data Layer plugin provides a comprehensive API for managing event publishing, consuming, and integration flows in Moodle 4.5. This API follows the Medad Fusion Data Layer API Contract and implements Moodle 4.5 authentication and authorization standards.

## Base URL

```
https://your-moodle-site.com/local/lmp_data_layer/api.php?path=
```

## Authentication

All API endpoints require Moodle authentication. Users must be logged in and have appropriate capabilities:

- **View capabilities**: `local/lmp_data_layer:view_*`
- **Manage capabilities**: `local/lmp_data_layer:manage_*`
- **API access**: `local/lmp_data_layer:access_api`

## API Endpoints

### 1. Publishing Events APIs

#### GET /v1/publisher/publishing-events
Get list of publishing events with details.

**Response:**
```json
{
    "eventsPublishingEnabled": true,
    "events": [
        {
            "id": "event_1111-1111-1111-1111-111111111111",
            "eventId": "lmp_grade_submitted",
            "eventName": "Grade Submitted",
            "description": "Individual grade submission from LMP to CMP",
            "eventPublishingEnabled": true,
            "createdBy": "admin@medad.com",
            "createdAt": "2025-01-01T10:00:00Z",
            "updatedBy": "teacher@medad.com",
            "updatedAt": "2025-01-03T10:00:00Z"
        }
    ]
}
```

#### PATCH /v1/publisher/publishing-events/{id}
Toggle status of a specific publishing event.

**Request Body:**
```json
{
    "eventPublishingEnabled": true
}
```

**Response:**
```json
{
    "id": "event_1111-1111-1111-1111-111111111111",
    "eventId": "lmp_grade_submitted",
    "eventName": "Grade Submitted",
    "description": "Individual grade submission from LMP to CMP",
    "eventPublishingEnabled": true,
    "createdBy": "admin@medad.com",
    "createdAt": "2025-01-01T10:00:00Z",
    "updatedBy": "teacher@medad.com",
    "updatedAt": "2025-01-03T10:00:00Z"
}
```

#### GET /v1/publisher/publishing-events/{id}/logs
Get publisher event log details.

**Response:**
```json
{
    "eventId": "lmp_grade_submitted",
    "eventName": "Grade Submitted",
    "logs": [
        {
            "logId": "event_1111-1111-1111-1111-111111111111_log_1",
            "eventId": "lmp_grade_submitted",
            "status": "published",
            "retryCount": 0,
            "errorMessage": null,
            "timestamp": "2025-01-03T10:00:00Z",
            "details": {
                "eventData": {
                    "teacher": "teacher1",
                    "student": "student1",
                    "grade": 85.5
                },
                "eventMetadata": {},
                "scheduleConfig": {
                    "enabled": true,
                    "frequency": "immediate"
                }
            }
        }
    ]
}
```

#### GET /v1/publisher/publishing-events/{id}/logs/{logId}
Get detailed log record by log ID.

**Response:**
```json
{
    "logId": "event_1111-1111-1111-1111-111111111111_log_1",
    "eventId": "lmp_grade_submitted",
    "eventName": "Grade Submitted",
    "status": "published",
    "retryCount": 0,
    "errorMessage": null,
    "timestamp": "2025-01-03T10:00:00Z",
    "details": {
        "eventData": {
            "teacher": "teacher1",
            "student": "student1",
            "grade": 85.5
        },
        "eventMetadata": {},
        "scheduleConfig": {
            "enabled": true,
            "frequency": "immediate"
        },
        "tenantId": "default_tenant",
        "createdBy": "admin@medad.com",
        "createdAt": "2025-01-01T10:00:00Z",
        "updatedBy": "teacher@medad.com",
        "updatedAt": "2025-01-03T10:00:00Z"
    }
}
```

### 2. Product Control APIs

#### PATCH /v1/publisher/product-control
Master toggle for product-level event publishing.

**Request Body:**
```json
{
    "eventsPublishingEnabled": true
}
```

**Response:**
```json
{
    "eventsPublishingEnabled": true,
    "updatedBy": "admin@medad.com",
    "updatedAt": "2025-01-03T10:00:00Z",
    "message": "Product-level event publishing has been enabled"
}
```

#### GET /v1/publisher/product-control
Get current product control status.

**Response:**
```json
{
    "eventsPublishingEnabled": true,
    "lastUpdated": {
        "updatedBy": "admin@medad.com",
        "updatedAt": "2025-01-03T10:00:00Z",
        "action": "enable"
    },
    "status": "enabled"
}
```

### 3. Consuming Events APIs

#### GET /v1/consumer/consuming-events
Get consumed events from product with status and details.

**Response:**
```json
{
    "eventsConsumingEnabled": true,
    "events": [
        {
            "id": "event_2222-2222-2222-2222-222222222222",
            "eventId": "cmp_student_created",
            "eventName": "Student Created",
            "description": "When a new student is created",
            "eventConsumingEnabled": true,
            "status": "received",
            "createdBy": "admin@medad.com",
            "createdAt": "2025-01-01T10:00:00Z",
            "updatedBy": "admin@medad.com",
            "updatedAt": "2025-01-02T14:30:00Z",
            "fieldMappings": {
                "studentId": "id",
                "studentName": "fullname",
                "email": "email"
            },
            "scheduleConfig": {
                "enabled": true,
                "frequency": "immediate"
            }
        }
    ]
}
```

#### PATCH /v1/consumer/consuming-events/{id}
Toggle consuming event enabled/disabled.

**Request Body:**
```json
{
    "eventConsumingEnabled": true
}
```

**Response:**
```json
{
    "id": "event_2222-2222-2222-2222-222222222222",
    "eventId": "cmp_student_created",
    "eventName": "Student Created",
    "description": "When a new student is created",
    "eventConsumingEnabled": true,
    "status": "received",
    "createdBy": "admin@medad.com",
    "createdAt": "2025-01-01T10:00:00Z",
    "updatedBy": "admin@medad.com",
    "updatedAt": "2025-01-02T14:30:00Z"
}
```

#### PUT /v1/consumer/consuming-events/{id}/field-mappings
Update consumer event field mappings.

**Request Body:**
```json
{
    "fieldMappings": {
        "studentId": "id",
        "studentName": "fullname",
        "email": "email",
        "customField": "custom_value"
    }
}
```

**Response:**
```json
{
    "id": "event_2222-2222-2222-2222-222222222222",
    "eventId": "cmp_student_created",
    "eventName": "Student Created",
    "fieldMappings": {
        "studentId": "id",
        "studentName": "fullname",
        "email": "email",
        "customField": "custom_value"
    },
    "updatedBy": "admin@medad.com",
    "updatedAt": "2025-01-02T14:30:00Z"
}
```

#### GET /v1/consumer/consuming-events/{id}/logs
Get consumer event log details.

**Response:**
```json
{
    "eventId": "cmp_student_created",
    "eventName": "Student Created",
    "logs": [
        {
            "logId": "event_2222-2222-2222-2222-222222222222_log_1",
            "eventId": "cmp_student_created",
            "status": "processed",
            "retryCount": 0,
            "errorMessage": null,
            "timestamp": "2025-01-02T14:30:00Z",
            "details": {
                "eventData": {
                    "studentId": "12345",
                    "studentName": "John Doe",
                    "email": "john.doe@example.com"
                },
                "eventMetadata": {},
                "fieldMappings": {
                    "studentId": "id",
                    "studentName": "fullname",
                    "email": "email"
                },
                "scheduleConfig": {
                    "enabled": true,
                    "frequency": "immediate"
                }
            }
        }
    ]
}
```

#### GET /v1/consumer/consuming-events/{id}/logs/{logId}
Get detailed consumer event log record by log ID.

**Response:**
```json
{
    "logId": "event_2222-2222-2222-2222-222222222222_log_1",
    "eventId": "cmp_student_created",
    "eventName": "Student Created",
    "status": "processed",
    "retryCount": 0,
    "errorMessage": null,
    "timestamp": "2025-01-02T14:30:00Z",
    "details": {
        "eventData": {
            "studentId": "12345",
            "studentName": "John Doe",
            "email": "john.doe@example.com"
        },
        "eventMetadata": {},
        "fieldMappings": {
            "studentId": "id",
            "studentName": "fullname",
            "email": "email"
        },
        "scheduleConfig": {
            "enabled": true,
            "frequency": "immediate"
        },
        "tenantId": "default_tenant",
        "createdBy": "admin@medad.com",
        "createdAt": "2025-01-01T10:00:00Z",
        "updatedBy": "admin@medad.com",
        "updatedAt": "2025-01-02T14:30:00Z"
    }
}
```

### 4. Integration Flows APIs

#### PATCH /v1/integration/flows/{id}/schedule
Update schedule for an event.

**Request Body:**
```json
{
    "scheduleConfig": {
        "enabled": true,
        "frequency": "hourly",
        "cronExpression": "0 * * * *"
    }
}
```

**Response:**
```json
{
    "id": "event_3333-3333-3333-3333-333333333333",
    "eventId": "lmp_grade_submitted",
    "eventName": "Grade Submitted",
    "scheduleConfig": {
        "enabled": true,
        "frequency": "hourly",
        "cronExpression": "0 * * * *"
    },
    "updatedBy": "admin@medad.com",
    "updatedAt": "2025-01-03T10:00:00Z",
    "message": "Schedule configuration updated successfully"
}
```

#### GET /v1/integration/flows/active-events
Get active consumer events only from product.

**Response:**
```json
{
    "activeEvents": [
        {
            "id": "event_4444-4444-4444-4444-444444444444",
            "eventId": "cmp_student_created",
            "eventName": "Student Created",
            "description": "When a new student is created",
            "status": "received",
            "scheduleConfig": {
                "enabled": true,
                "frequency": "immediate"
            },
            "fieldMappings": {
                "studentId": "id",
                "studentName": "fullname",
                "email": "email"
            },
            "createdBy": "admin@medad.com",
            "createdAt": "2025-01-01T10:00:00Z",
            "lastProcessed": "2025-01-02T14:30:00Z"
        }
    ],
    "totalCount": 1,
    "lastUpdated": "2025-01-03T10:00:00Z"
}
```

#### GET /v1/integration/flows/{id}/status
Get integration flow status for an event.

**Response:**
```json
{
    "id": "event_3333-3333-3333-3333-333333333333",
    "eventId": "lmp_grade_submitted",
    "eventName": "Grade Submitted",
    "type": "publishing",
    "status": "published",
    "scheduleConfig": {
        "enabled": true,
        "frequency": "hourly",
        "cronExpression": "0 * * * *"
    },
    "isActive": true,
    "lastProcessed": "2025-01-03T09:00:00Z",
    "nextScheduled": "2025-01-03T10:00:00Z",
    "retryCount": 0,
    "errorMessage": null
}
```

## Field Specifications

### Event Data Fields

#### Publishing Events (Outbox)
- `id`: UUID of the event
- `eventId`: Event identifier (e.g., `lmp_grade_submitted`)
- `eventName`: Human readable event name
- `description`: Event description
- `eventPublishingEnabled`: Boolean indicating if publishing is enabled
- `createdBy`: User email who created the event
- `createdAt`: ISO timestamp when event was created
- `updatedBy`: User email who last updated the event
- `updatedAt`: ISO timestamp when event was last updated
- `tenantId`: Tenant ID for multi-tenancy
- `eventData`: JSON payload of the CloudEvent
- `eventMetadata`: Additional event metadata JSON
- `status`: Event status (pending, published, failed)
- `retryCount`: Number of retry attempts
- `errormessage`: Error message if event failed
- `scheduleConfig`: Schedule configuration JSON

#### Consuming Events (Inbox)
- `id`: UUID of the event
- `eventId`: Event identifier (e.g., `cmp_student_created`)
- `eventName`: Human readable event name
- `description`: Event description
- `eventConsumingEnabled`: Boolean indicating if consuming is enabled
- `status`: Event status (received, processing, processed, failed)
- `fieldMappings`: Field mapping configuration JSON
- All other fields same as publishing events

### Schedule Configuration Fields
- `enabled`: Boolean indicating if scheduling is enabled
- `frequency`: Schedule frequency (immediate, hourly, daily, weekly, custom)
- `cronExpression`: Custom cron expression (required when frequency is 'custom')

### Field Mapping Fields
- Key-value pairs mapping external field names to internal field names
- Example: `{"studentId": "id", "studentName": "fullname", "email": "email"}`

## Error Responses

All endpoints return consistent error responses:

```json
{
    "error": true,
    "message": "Error description",
    "code": 400,
    "details": "Additional error details if available"
}
```

### HTTP Status Codes
- `200`: Success
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `500`: Internal Server Error

## Usage Examples

### cURL Examples

#### Get Publishing Events
```bash
curl -X GET "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie"
```

#### Toggle Publishing Event
```bash
curl -X PATCH "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events/event_1111-1111-1111-1111-111111111111" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie" \
  -d '{"eventPublishingEnabled": false}'
```

#### Update Field Mappings
```bash
curl -X PUT "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/consumer/consuming-events/event_2222-2222-2222-2222-222222222222/field-mappings" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie" \
  -d '{"fieldMappings": {"studentId": "id", "studentName": "fullname", "email": "email"}}'
```

## Notes

1. **Authentication**: All API calls require valid Moodle session authentication
2. **Capabilities**: Users must have appropriate capabilities to access different endpoints
3. **Tenant Support**: The API supports multi-tenancy through the `X-TENANT` header
4. **Error Handling**: All endpoints include comprehensive error handling and logging
5. **Moodle Standards**: The API follows Moodle 4.5 coding standards and security practices
6. **Database**: Uses Moodle's database abstraction layer for all database operations
7. **Logging**: All API operations are logged in the audit log table for tracking and debugging

## Internal Functions

For any unclear API functionality, refer to the internal implementation:

- **Publishing Events**: `local_lmp_data_layer\api\publishing_events_api`
- **Product Control**: `local_lmp_data_layer\api\product_control_api`
- **Consuming Events**: `local_lmp_data_layer\api\consuming_events_api`
- **Integration Flows**: `local_lmp_data_layer\api\integration_flows_api`
- **Base API**: `local_lmp_data_layer\api\base_api`
- **Router**: `local_lmp_data_layer\api_router`
