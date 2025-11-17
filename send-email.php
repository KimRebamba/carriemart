<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'd06f8760800fa0'; // Mailtrap username
    $mail->Password   = 'c6053775ed89ea'; // Mailtrap password
    $mail->Port       = 2525;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // optional for Mailtrap

    //Recipients
    $mail->setFrom('no-reply@carriemart.com', 'Carriemart');
    $mail->addAddress('kim.fernandezmail@gmail.com');

    //Content
    $mail->isHTML(true);
    $mail->Subject = ' tito boy';
    $mail->Body    = '<p>This is a test email from Carriemart.</p>';

    $mail->send();
    echo "Sent!";
} catch (Exception $e) {
    echo "Mail error: {$mail->ErrorInfo}";
}
