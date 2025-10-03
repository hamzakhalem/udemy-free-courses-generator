# LMP Data Layer Plugin Testing Guide

## Prerequisites

1. **Moodle 4.5+** installed and running
2. **Plugin installed** and database upgraded
3. **User with appropriate capabilities** (manager role recommended)
4. **Web browser** or **API testing tool** (Postman, curl, etc.)

## Installation Testing

### 1. Install the Plugin
```bash
# Copy plugin to Moodle
cp -r lmp_data_layer /path/to/moodle/local/plugins/

# Run database upgrade
php /path/to/moodle/admin/cli/upgrade.php
```

### 2. Verify Database Tables
Check that the following tables were created:
- `local_lmp_outbox`
- `local_lmp_inbox` 
- `local_lmp_audit_log`

### 3. Check Capabilities
Go to **Site Administration > Users > Permissions > Define roles** and verify the new capabilities are available:
- `local/lmp_data_layer:view_publishing_events`
- `local/lmp_data_layer:manage_publishing_events`
- `local/lmp_data_layer:view_product_control`
- `local/lmp_data_layer:manage_product_control`
- `local/lmp_data_layer:view_consuming_events`
- `local/lmp_data_layer:manage_consuming_events`
- `local/lmp_data_layer:view_integration_flows`
- `local/lmp_data_layer:manage_integration_flows`
- `local/lmp_data_layer:access_api`

## API Testing

### 1. Authentication Setup

First, you need to authenticate with Moodle. You have several options:

#### Option A: Browser Session
1. Log into Moodle as an admin/manager
2. Use the browser's developer tools to get session cookies
3. Use the cookies in your API requests

#### Option B: Create Test User
```bash
# Create a test user with API access
php /path/to/moodle/admin/cli/create_user.php --username=apitest --password=testpass123 --email=apitest@example.com --firstname=API --lastname=Test
```

### 2. Test API Endpoints

#### Base URL
```
https://your-moodle-site.com/local/lmp_data_layer/api.php?path=
```

#### Test 1: Get Publishing Events
```bash
curl -X GET "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie"
```

**Expected Response:**
```json
{
    "eventsPublishingEnabled": true,
    "events": []
}
```

#### Test 2: Toggle Product Control
```bash
curl -X PATCH "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/product-control" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie" \
  -d '{"eventsPublishingEnabled": true}'
```

**Expected Response:**
```json
{
    "eventsPublishingEnabled": true,
    "updatedBy": "admin@example.com",
    "updatedAt": "2025-01-03T10:00:00Z",
    "message": "Product-level event publishing has been enabled"
}
```

#### Test 3: Get Consuming Events
```bash
curl -X GET "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/consumer/consuming-events" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie"
```

#### Test 4: Get Active Events
```bash
curl -X GET "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/integration/flows/active-events" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie"
```

## Event Testing

### 1. Create Test Events

#### Create a Publishing Event
```bash
# This will be created automatically when a grade event occurs
# Or you can create manually via database
```

#### Create a Consuming Event
```bash
# Insert test data into inbox table
INSERT INTO local_lmp_inbox (
    id, eventid, eventname, description, eventconsumingenabled,
    createdby, createdat, tenantid, eventdata, status, timecreated, timemodified
) VALUES (
    'test-event-123', 'cmp_student_created', 'Student Created', 'Test student creation',
    1, 'admin@example.com', UNIX_TIMESTAMP(), 'default_tenant',
    '{"studentId": "12345", "studentName": "John Doe", "email": "john@example.com"}',
    'received', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
);
```

### 2. Test Event Operations

#### Toggle Publishing Event
```bash
curl -X PATCH "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events/test-event-123" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie" \
  -d '{"eventPublishingEnabled": false}'
```

#### Update Field Mappings
```bash
curl -X PUT "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/consumer/consuming-events/test-event-123/field-mappings" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie" \
  -d '{"fieldMappings": {"studentId": "id", "studentName": "fullname", "email": "email"}}'
```

#### Update Schedule
```bash
curl -X PATCH "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/integration/flows/test-event-123/schedule" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie" \
  -d '{"scheduleConfig": {"enabled": true, "frequency": "hourly", "cronExpression": "0 * * * *"}}'
```

## Browser Testing

### 1. Direct Browser Access
Navigate to:
```
https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events
```

You should see a JSON response (if logged in) or a login redirect.

### 2. Test with Browser Developer Tools
1. Open browser developer tools (F12)
2. Go to Network tab
3. Navigate to the API URL
4. Check the request/response details

## Error Testing

### 1. Test Unauthorized Access
```bash
# Without authentication
curl -X GET "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events"
```

**Expected Response:**
```json
{
    "error": true,
    "message": "Authentication required",
    "code": 401
}
```

### 2. Test Invalid Endpoints
```bash
curl -X GET "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=invalid/path" \
  -b "MoodleSession=your_session_cookie"
```

**Expected Response:**
```json
{
    "error": true,
    "message": "Invalid API path",
    "code": 400
}
```

### 3. Test Invalid Data
```bash
curl -X PATCH "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/product-control" \
  -H "Content-Type: application/json" \
  -b "MoodleSession=your_session_cookie" \
  -d '{"invalidField": "invalidValue"}'
```

**Expected Response:**
```json
{
    "error": true,
    "message": "Invalid eventsPublishingEnabled value",
    "code": 400
}
```

## Performance Testing

### 1. Load Testing
Use tools like Apache Bench or JMeter to test API performance:

```bash
# Test with 100 requests, 10 concurrent
ab -n 100 -c 10 "https://your-moodle-site.com/local/lmp_data_layer/api.php?path=v1/publisher/publishing-events"
```

### 2. Database Performance
Monitor database queries during API calls:
```sql
-- Check table sizes
SELECT 
    table_name,
    table_rows,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'moodle' 
AND table_name LIKE 'local_lmp_%';
```

## Integration Testing

### 1. Test with Real Moodle Events
1. Create a course and student
2. Create an assignment
3. Grade the assignment
4. Check if events are created in `local_lmp_outbox`

### 2. Test Kafka Integration
If Kafka is configured:
```bash
# Test Kafka connection
php /path/to/moodle/local/lmp_data_layer/test_kafka.php
```

## Debugging

### 1. Enable Debug Mode
In `config.php`, add:
```php
$CFG->debug = DEBUG_DEVELOPER;
$CFG->debugdisplay = 1;
```

### 2. Check Logs
```bash
# Check Moodle logs
tail -f /path/to/moodle/data/moodledata/moodle.log

# Check Apache/Nginx logs
tail -f /var/log/apache2/error.log
```

### 3. Database Debugging
```sql
-- Check recent events
SELECT * FROM local_lmp_outbox ORDER BY timecreated DESC LIMIT 10;

-- Check audit logs
SELECT * FROM local_lmp_audit_log ORDER BY timecreated DESC LIMIT 10;

-- Check API errors
SELECT * FROM local_lmp_audit_log WHERE action = 'error' ORDER BY timecreated DESC;
```

## Troubleshooting

### Common Issues

1. **Authentication Errors**
   - Ensure user is logged in
   - Check session cookies
   - Verify user has required capabilities

2. **Database Errors**
   - Run database upgrade: `php admin/cli/upgrade.php`
   - Check table permissions
   - Verify database connection

3. **API Not Found**
   - Check file permissions
   - Verify .htaccess rules
   - Check web server configuration

4. **JSON Errors**
   - Validate JSON syntax
   - Check content-type headers
   - Ensure proper encoding

### Debug Commands

```bash
# Check plugin installation
php /path/to/moodle/admin/cli/upgrade.php --list

# Check capabilities
php /path/to/moodle/admin/cli/capabilities.php

# Check database
php /path/to/moodle/admin/cli/check_database_schema.php
```

## Test Data Scripts

See the following files for automated testing:
- `test_api.php` - API endpoint testing
- `test_events.php` - Event creation and processing testing
- `test_integration.php` - Full integration testing

## Success Criteria

✅ **API endpoints respond correctly**
✅ **Authentication and authorization work**
✅ **Database operations succeed**
✅ **Error handling works properly**
✅ **Events are created and processed**
✅ **Logging and audit trail function**
✅ **Performance is acceptable**
✅ **Integration with Moodle works**

## Next Steps

After successful testing:
1. Configure production settings
2. Set up monitoring
3. Create user documentation
4. Plan deployment strategy
