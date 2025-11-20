<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
      
    $mail->isSMTP();
$mail->Host       = MAILTRAP_HOST;
    $mail->SMTPAuth   = true;
$mail->Username   = MAILTRAP_USERNAME;   
$mail->Password   = MAILTRAP_PASSWORD;   
$mail->Port       = MAILTRAP_PORT;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   

      
    $mail->setFrom('no-reply@carriemart.com', 'Carriemart');
    $mail->addAddress('kim.fernandezmail@gmail.com');

      
    $mail->isHTML(true);
    $mail->Subject = ' tito boy';
    $mail->Body    = '<p>This is a test email from Carriemart.</p>';

    $mail->send();
    echo "Sent!";
} catch (Exception $e) {
    echo "Mail error: {$mail->ErrorInfo}";
}
