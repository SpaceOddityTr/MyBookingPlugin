<?php
/**
 * Represents a booking within the plugin, encapsulating operations such as
 * creation, retrieval, updating, and deletion of booking records in the database.
 * 
 * @package MyBookingPlugin
 */
class Booking
{
    // Name of the database table used to store bookings.
    protected $tableName = 'bookings';

    // List of fields in the 'bookings' table, used to validate and filter data.
    protected $fieldNames = [
        'id',
        'date',
        'time',
        'service_name',
        'client_name',
        'client_email',
    ];

    // Specifies the allowed service names to ensure data integrity.
    protected $validServiceNames = [
        'essential_oils',
        'psychosomatics',
    ];

    /**
     * Creates a new booking record in the database.
     * 
     * @param array $data Data for the new booking.
     * @throws RuntimeException If the database operation fails.
     */
    public function create(array $data): void
    {
        global $wpdb; // Global WordPress database access variable.

        $table_name = $this->getTableName(); // Retrieve the full table name.
        $data = $this->getFieldValues($data); // Filter and retrieve valid field values.

        // Validate the 'service_name' field if present.
        if (array_key_exists('service_name', $data)) {
            $this->validateServiceName($data['service_name']);
        }

        // Attempt to insert the new booking into the database.
        $result = $wpdb->insert($table_name, $data);

        // If insertion fails, throw an exception.
        if ($result === false) {
            throw new RuntimeException('Failed to create booking');
        }
    }

    /**
     * Updates an existing booking record.
     * 
     * @param int $bookingId The ID of the booking to update.
     * @param array $data The new data for the booking.
     * @throws RuntimeException If the update operation fails.
     */
    public function update(int $bookingId, array $data): void
    {
        global $wpdb;

        $data = $this->getFieldValues($data); // Filter the data to include only valid fields.

        // ID field cannot be updated, so it's removed if present.
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }

        // Validate 'service_name' if present.
        if (array_key_exists('service_name', $data)) {
            $this->validateServiceName($data['service_name']);
        }

        // Attempt to update the booking in the database.
        $result = $wpdb->update($this->getTableName(), $data, ['ID' => $bookingId]);

        // If the update fails, throw an exception.
        if ($result === false) {
            throw new RuntimeException(sprintf('Failed to update booking #%1$s', $bookingId));
        }
    }

    /**
     * Deletes a booking record from the database.
     * 
     * @param int $bookingId The ID of the booking to delete.
     * @throws RuntimeException If the deletion fails.
     */
    public function delete(int $bookingId): void
    {
        global $wpdb;
        $table_name = $this->getTableName();
        
        // Attempt to delete the booking from the database.
        $result = $wpdb->delete($table_name, ['ID' => $bookingId]);

        // If the deletion fails, throw an exception.
        if ($result === false) {
            throw new RuntimeException(sprintf('Could not delete booking #%1$s', $bookingId));
        }
    }

    /**
     * Retrieves a single booking record by its ID.
     * 
     * @param int $bookingId The ID of the booking to retrieve.
     * @return array The booking record.
     * @throws OutOfRangeException If the booking cannot be found.
     */
    public function get(int $bookingId): array
    {
        global $wpdb;
        $table_name = $this->getTableName();
        $query = <<<EOF
        SELECT * FROM `{$table_name}`
        WHERE `id` = {$bookingId}
        EOF;

        // Execute the query and return the result.
        $result = $wpdb->get_row($query, ARRAY_A);

        // If the booking cannot be found, throw an exception.
        if ($result === false) {
            throw new OutOfRangeException(sprintf('Could not retrieve booking #%1$s', $bookingId));
        }

        return $result;
    }

    /**
     * Retrieves all booking records, optionally filtered by a date range.
     * 
     * @param string|null $startDate Optional start date to filter bookings.
     * @param string|null $endDate Optional end date to filter bookings.
     * @return array List of bookings.
     */
    public function getAll(?string $startDate = null, ?string $endDate = null): array
    {
        global $wpdb;
        $table_name = $this->getTableName();
    
        $query = <<<EOF
        SELECT *
        FROM `{$table_name}`
        EOF;
        $dateWhereClause = $this->getDateWhereClause($startDate, $endDate);
        $query .= strlen($dateWhereClause)
            ? " AND ({$dateWhereClause})"
            : $dateWhereClause;

        // Execute the query and return the results.
        return  $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Fetches all available booking slots within an optional date range.
     * "Available" is defined as slots that have not been booked (i.e., no service, client name, or email assigned).
     * 
     * @param ?string $startDate The start date of the range to fetch available slots for.
     * @param ?string $endDate The end date of the range to fetch available slots for.
     * @return array An array of available booking slots, each represented as an associative array.
     */
    public function getAvailable(?string $startDate = null, ?string $endDate = null): array
    {
        global $wpdb;
        $table_name = $this->getTableName(); // Retrieve the prefixed table name for bookings.
        
        // SQL query to select all slots where service name, client name, and email are null (unbooked).
        $query = <<<EOF
        SELECT *
        FROM `{$table_name}`
        WHERE `service_name` IS NULL
            AND `client_name` IS NULL
            AND `client_email` IS NULL
        EOF;
        // Dynamically append date range clause if provided.
        $dateWhereClause = $this->getDateWhereClause($startDate, $endDate);
        $query .= strlen($dateWhereClause)
            ? " AND ({$dateWhereClause})"
            : $dateWhereClause;

        // Execute the query and return results as an associative array.
        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Constructs the table name with the appropriate WordPress table prefix.
     * 
     * @return string The full table name with prefix.
     */
    protected function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . $this->tableName; // Concatenate WP prefix with the base table name.
    }

    /**
     * Filters and returns only the data fields that are relevant to the Booking model.
     * 
     * @param array $data The original data array to filter.
     * @return array An associative array of filtered data containing only the fields relevant to Booking.
     */
    protected function getFieldValues(array $data): array
    {
        $values = [];
        // Loop through each field name defined in the model and retain only those present in the input $data.
        foreach ($this->fieldNames as $fieldName) {
            if (array_key_exists($fieldName, $data)) {
                $values[$fieldName] = $data[$fieldName];
            }
        }

        return $values; // Return the filtered data.
    }

    /**
     * Validates that the provided service name is allowed.
     * 
     * @param string $serviceName The service name to validate.
     * @throws RangeException If the service name is not in the list of valid service names.
     */
    protected function validateServiceName(string $serviceName): void
    {
        // Check if the service name is not in the array of valid service names.
        if (!in_array($serviceName, $this->validServiceNames)) {
            throw new RangeException(sprintf('Service name "%1$s" is not allowed', $serviceName));
        }
    }

    /**
     * Generates a SQL WHERE clause for filtering bookings by date range.
     * 
     * @param ?string $startDate The start date of the range.
     * @param ?string $endDate The end date of the range.
     * @return string The SQL WHERE clause for the date range filter.
     */
    protected function getDateWhereClause(?string $startDate = null, ?string $endDate = null): string
    {
        $conditions = [];

        // Add conditions for the start and end dates if they are provided.
        if ($startDate !== null) {
            $conditions[] = "`date` >= '{$startDate}'";
        }

        if ($endDate !== null) {
            $conditions[] = "`date` <= '{$endDate}'";
        }

        return implode(' AND ', $conditions); // Combine conditions with AND for SQL query.
    }
}
