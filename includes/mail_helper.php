<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../config/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Gửi email sử dụng PHPMailer
 * 
 * @param string $to Email người nhận
 * @param string $subject Tiêu đề email
 * @param string $body Nội dung email (HTML)
 * @param string $altBody Nội dung text (không bắt buộc)
 * @param array $attachments Danh sách file đính kèm (không bắt buộc) 
 * @return array ['success' => bool, 'message' => string]
 */
function send_email($to, $subject, $body, $altBody = '', $attachments = [])
{
    try {
        // Khởi tạo PHPMailer
        $mail = new PHPMailer(true);

        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST');  // SMTP server của Gmail
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_USERNAME'); // Email của bạn
        $mail->Password = getenv('MAIL_PASSWORD'); // Mật khẩu ứng dụng
        $mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
        $mail->Port = getenv('MAIL_PORT'); // Cổng SMTP (587 cho TLS, 465 cho SSL)

        // Cấu hình người gửi và người nhận
        $mail->setFrom(getenv('MAIL_FROM_ADDRESS'), 'Electro Shop');
        $mail->addAddress($to);
        $mail->addReplyTo(getenv('MAIL_FROM_REPLY_TO'), 'Electro Shop');

        // Đính kèm file (nếu có)
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
        }

        // Thiết lập nội dung email
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        // Gửi email
        $mail->send();

        return ['success' => true, 'message' => 'Email sent successfully'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}