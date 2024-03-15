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
    private $contentType = 'text/html; charset=UTF-8';

    /**
     * Email headers.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->headers[] = $this->contentType;
    }

    /**
     * Sends an email notification.
     *
     * @param string $to Email address to send to.
     * @param string $subject Email subject.
     * @param string $message Email message.
     * @param array $additionalHeaders (optional) Additional email headers.
     * @return bool Whether the email was sent successfully.
     */
    public function sendEmail($to, $subject, $message, array $additionalHeaders = []) {
        $headers = array_merge($this->headers, $additionalHeaders);

        if (!is_email($to)) {
            error_log("Invalid email address: {$to}");
            return false;
        }

        $sent = wp_mail($to, $subject, $message, $headers);

        if (!$sent) {
            error_log("Failed to send email to: {$to}");
        }

        return $sent;
    }

    /**
     * Prepares and sends a booking confirmation email.
     *
     * @param array $bookingDetails Details of the booking.
     * @return bool Whether the email was sent successfully.
     */
    public function sendBookingConfirmation(array $bookingDetails) {
        $to = $bookingDetails['email'];
        $subject = __('Your Booking Confirmation', 'mybookingplugin');
        $message = $this->getBookingConfirmationMessage($bookingDetails);

        return $this->sendEmail($to, $subject, $message);
    }

    /**
     * Generates the booking confirmation email message.
     *
     * @param array $bookingDetails Details of the booking.
     * @return string The email message.
     */
    protected function getBookingConfirmationMessage(array $bookingDetails) {
        $name = htmlspecialchars($bookingDetails['name'], ENT_QUOTES, 'UTF-8');
        $service = htmlspecialchars($bookingDetails['service'], ENT_QUOTES, 'UTF-8');
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
