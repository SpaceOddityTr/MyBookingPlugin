<?php
/**
 * A model that represents a booking.
 * 
 * @package MyBookingPlugin
 */
class Booking
{

    protected $tableName = 'bookings';

    protected $fieldNames = [
        'id',
        'date',
        'time',
        'service_name',
        'client_name',
        'client_email',
    ];

    protected $validServiceNames = [
        'essential_oils',
        'psychosomatics',
    ];

    public function create(array $data): void
    {
        global $wpdb;

        $table_name = $this->getTableName();
        $data = $this->getFieldValues($data);

        // Only specific service names allowed
        if (array_key_exists('service_name', $data)) {
            $this->validateServiceName($data['service_name']);
        }

        $result = $wpdb->insert($table_name, $data);

        if ($result === false) {
            throw new RuntimeException(sprintf('Failed to create booking'));
        }
    }

    /**
     * @param int $bookingId The ID of the booking to update.
     * @param array $data A map of the field names for update, to their values.
     * 
     * @throws RuntimeException If problem updating.
     */
    public function update(int $bookingId, array $data): void
    {
        global $wpdb;
        
        $data = $this->getFieldValues($data);

        // Disallow ID for update
        if (array_key_exists('id', $data)) {
            unset($data['id']);
        }
        
        // Only specific service names allowed
        if (array_key_exists('service_name', $data)) {
            $this->validateServiceName($data['service_name']);
        }

        $result = $wpdb->update(
            $this->getTableName(),
            $data,
            [ 'ID' => $bookingId ] // Where clause
        );

        if ($result === false) {
            throw new RuntimeException(sprintf('Failed to update booking #%1$s', $bookingId));
        }
    }

    public function delete(int $bookingId): void
    {
        global $wpdb;
        $table_name = $this->getTableName();
        
        $result = $wpdb->delete(
            $table_name,
            [ 'ID' => $bookingId ] // Where clause
        );

        if ($result === false) {
            throw new RuntimeException(sprintf('Could not delete bookin #%1$s', $bookingId));
        }
    }

    public function get(int $bookingId): array
    {
        global $wpdb;
        $table_name = $this->getTableName();
        $query = <<<EOF
        SELECT * FROM `{$table_name}`
        WHERE `id` = {$bookingId}
        EOF;

        $result = $wpdb->get_row($query, ARRAY_A);

        if ($result === false) {
            throw new OutOfRangeException(sprintf('Could not retrieve booking #%1$s', $bookingId));
        }

        return $result;
    }

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

        return  $wpdb->get_results($query, ARRAY_A);
    }

    public function getAvailable(?string $startDate = null, ?string $endDate = null): array
    {
        global $wpdb;
        $table_name = $this->getTableName();
    
        $query = <<<EOF
        SELECT *
        FROM `{$table_name}`
        WHERE `service_name` IS NULL
            AND `client_name` IS NULL
            AND `client_email` IS NULL
        EOF;
        $dateWhereClause = $this->getDateWhereClause($startDate, $endDate);
        $query .= strlen($dateWhereClause)
            ? " AND ({$dateWhereClause})"
            : $dateWhereClause;

        return  $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Retrieves the table name used by this model.
     */
    protected function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . $this->tableName;
    }

    /**
     * Retrieves only the model's field values from a data array.
     */
    protected function getFieldValues(array $data): array
    {
        $values = [];
        foreach ($this->fieldNames as $fieldName) {
            if (array_key_exists($fieldName, $data)) {
                $values[$fieldName] = $data[$fieldName];
            }
        }

        return $values;
    }

    protected function validateServiceName(string $serviceName): void
    {
        if (!in_array($serviceName, $this->validServiceNames)) {
            throw new RangeException(sprintf('Service name "%1$s" is not allowed', $serviceName));
        }
    }

    protected function getDateWhereClause(?string $startDate = null, ?string $endDate = null): string
    {
        $conditions = [];

        if ($startDate !== null) {
            $conditions[] = "`date` >= {$startDate}";
        }

        if ($startDate !== null) {
            $conditions[] = "`date` < {$endDate}";
        }

        return implode(' AND ', $conditions);
    }
}
