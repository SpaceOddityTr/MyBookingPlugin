<?php
/**
 * Handles email functionalities for the MyBookingPlugin.
 *
 * This class is responsible for sending various email notifications related to bookings,
 * such as booking confirmations. It allows for setting custom email content types and headers.
 *
 * @package MyBookingPlugin
 */
class Email {
    /**
     * The default content type for emails, set to HTML for rich text formatting.
     *
     * @var string
     */
    protected $contentType = 'text/html; charset=UTF-8';

    /**
     * Email headers, initialized with the default content type.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Instance of the Booking class for accessing booking details.
     *
     * @var Booking
     */
    protected $booking;

    /**
     * Constructs the Email object and sets up a WordPress action to listen for booking additions.
     * When a booking is added, it automatically sends a booking confirmation email.
     *
     * @param Booking $booking The Booking instance for accessing booking details.
     */
    public function __construct(Booking $booking) 
    {
        $this->headers[] = $this->contentType; // Initialize headers with content type.
        $this->booking = $booking;

        // Adds a WordPress action that triggers when a new booking is added.
        // The callback function sends a booking confirmation email.
        add_action('my_bookings_plugin_booking_added', function (int $bookingId) {
            $booking = $this->booking->get($bookingId); // Retrieves the booking details.
            $this->sendBookingConfirmation($booking);   // Sends the confirmation email.
        });
    }

    /**
     * Sends an email notification with optional additional headers.
     *
     * @param string $to Email address of the recipient.
     * @param string $subject Subject of the email.
     * @param string $message Body of the email.
     * @param array $additionalHeaders Optional additional headers to include in the email.
     * 
     * @throws RangeException If the provided email address is invalid.
     */
    public function sendEmail($to, $subject, $message, array $additionalHeaders = [])
    {
        $headers = array_merge($this->headers, $additionalHeaders); // Merges default headers with additional headers.

        if (!is_email($to)) {
            throw new RangeException("Invalid email address: {$to}"); // Checks for valid email address.
        }

        if (!wp_mail($to, $subject, $message, $headers)) {
            // Currently, the exception throwing is commented out. In a real scenario, you might want to log this or handle it differently.
            // throw new RuntimeException("Failed to send email to: {$to}");
        }
    }

    /**
     * Prepares and sends a booking confirmation email using the booking details.
     *
     * @param array $booking Details of the booking used to construct the confirmation message.
     */
    public function sendBookingConfirmation(array $booking)
    {
        $to = $booking['client_email']; // Recipient's email address.
        $subject = __('Your Booking Confirmation', 'mybookingplugin'); // Subject of the email.
        $message = $this->getBookingConfirmationMessage($booking); // Generates the email message body.

        $this->sendEmail($to, $subject, $message); // Sends the email.
    }

    /**
     * Generates the booking confirmation email message body.
     *
     * @param array $bookingDetails Details of the booking used for creating the message body.
     * @return string The email message body.
     */
    protected function getBookingConfirmationMessage(array $bookingDetails)
    {
        // Sanitizes the booking details to prevent XSS attacks when displaying in the email.
        $name = htmlspecialchars($bookingDetails['client_name'], ENT_QUOTES, 'UTF-8');
        $service = htmlspecialchars($bookingDetails['service_name'], ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars($bookingDetails['date'], ENT_QUOTES, 'UTF-8');
        $time = htmlspecialchars($bookingDetails['time'], ENT_QUOTES, 'UTF-8');

        // Constructs the email message body with booking details.
        $message = sprintf(
            __("Hello %s, <br><br>Thank you for your booking. Here are your booking details:<br>Service: %s<br>Date: %s<br>Time: %s<br><br>Best regards,<br>Your Name.", 'mybookingplugin'),
            $name,
            $service,
            $date,
            $time
        );

        return $message; // Returns the constructed email message.
    }

    /**
     * Updates the content type for outgoing emails.
     *
     * @param string $contentType The new content type to be used for emails.
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType; // Sets the new content type.
        $this->headers = [$this->contentType]; // Updates the headers array to include the new content type only.
    }
}