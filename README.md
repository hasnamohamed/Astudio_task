# Job Board API Documentation

## API Endpoints

### GET /api/jobs

Returns an list of jobs with advanced filtering capabilities.

**Query Parameters:**
- `filter`: Filter expression using the syntax described below
- `per_page`: Number of items per page (default: 15)

## Filter Syntax

The filter parameter supports complex filtering with the following syntax:

### Basic Field Filtering
- `field=value`: Equality
- `field!=value`: Inequality
- `field>value`: Greater than
- `field<value`: Less than
- `field>=value`: Greater than or equal
- `field<=value`: Less than or equal
- `field LIKE value`: Contains (string search)
- `field IN (value1,value2)`: In array

### Relationship Filtering
- `languages=PHP`: Jobs requiring PHP
- `languages HAS_ANY (PHP,JavaScript)`: Jobs requiring PHP OR JavaScript
- `locations IS_ANY (New York,Remote)`: Jobs in New York OR Remote
- `categories EXISTS`: Jobs with any category

### EAV Attribute Filtering
- `attribute:years_experience>=3`: Jobs with years_experience attribute ≥ 3

### Logical Operators
- `AND`: All conditions must be true
- `OR`: At least one condition must be true
- `()`: Grouping conditions

### Examples

1. Full-time jobs requiring PHP or JavaScript:
   `/api/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3&per_page=10`

### Setup
1. git clone [https://github.com/hasnamohamed/Astudio_task]
2. composer install
3. Configure .env
4. php artisan migrate --seed
5. php artisan serve
