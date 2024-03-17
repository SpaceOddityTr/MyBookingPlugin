<?php
/**
 * Handles email functionalities for the MyBookingPlugin.
 *
 * @package MyBookingPlugin
 */
class Email {
    /**
     * The default content type for emails.
     *
     * @var string
     */
    protected $contentType = 'text/html; charset=UTF-8';

    /**
     * Email headers.
     *
     * @var array
     */
    protected $headers = [];

    protected $booking;

    /**
     * Class constructor.
     */
    public function __construct(Booking $booking) {
        $this->headers[] = $this->contentType;
        $this->booking = $booking;

        add_action('my_bookings_plugin_booking_added', function (int $bookingId) {
            $booking = $this->booking->get($bookingId);
            $this->sendBookingConfirmation($booking);
        });
    }

    /**
     * Sends an email notification.
     *
     * @param string $to Email address to send to.
     * @param string $subject Email subject.
     * @param string $message Email message.
     * @param array $additionalHeaders (optional) Additional email headers.
     * 
     * @throws RuntimeException If problem sending.
     */
    public function sendEmail($to, $subject, $message, array $additionalHeaders = []) {
        $headers = array_merge($this->headers, $additionalHeaders);

        if (!is_email($to)) {
            throw new RangeException("Invalid email address: {$to}");
        }

        if (!wp_mail($to, $subject, $message, $headers)) {
            // Use again when server able to send email
            // throw new RuntimeException("Failed to send email to: {$to}");
        }
    }

    /**
     * Prepares and sends a booking confirmation email.
     *
     * @param array $booking Details of the booking.
     * 
     * @throws RuntimeException If problem sending.
     */
    public function sendBookingConfirmation(array $booking) {
        $to = $booking['client_email'];
        $subject = __('Your Booking Confirmation', 'mybookingplugin');
        $message = $this->getBookingConfirmationMessage($booking);

        $this->sendEmail($to, $subject, $message);
    }

    /**
     * Generates the booking confirmation email message.
     *
     * @param array $bookingDetails Details of the booking.
     * @return string The email message.
     */
    protected function getBookingConfirmationMessage(array $bookingDetails) {
        $name = htmlspecialchars($bookingDetails['client_name'], ENT_QUOTES, 'UTF-8');
        $service = htmlspecialchars($bookingDetails['service_name'], ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars($bookingDetails['date'], ENT_QUOTES, 'UTF-8');
        $time = htmlspecialchars($bookingDetails['time'], ENT_QUOTES, 'UTF-8');

        $message = sprintf(
            __("Hello %s, <br><br>Thank you for your booking. Here are your booking details:<br>Service: %s<br>Date: %s<br>Time: %s<br><br>Best regards,<br>Your Name.", 'mybookingplugin'),
            $name,
            $service,
            $date,
            $time
        );

        return $message;
    }

    /**
     * Sets a custom content type for the email.
     *
     * @param string $contentType The content type.
     */
    public function setContentType($contentType) {
        $this->contentType = $contentType;
        $this->headers = [$this->contentType]; // Reset headers to ensure content type is updated
    }

}
