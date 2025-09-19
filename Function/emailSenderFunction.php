<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($toUserEmail, $userName, $subject, $body, $env)
{

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USER'];
        $mail->Host = $env['SMTP_HOST'];
        $mail->Password = $env['SMTP_PASS'];
        // $mail->SMTPSecure = 'tls';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $env['SMTP_PORT'];

        $mail->setFrom($env['SMTP_USER'], 'Mamyr Resort and Event Place');
        $mail->addReplyTo($env['SMTP_USER'], 'No Reply');
        $mail->addAddress($toUserEmail, $userName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    } catch (Exception $e) {
        error_log('Email failed: ' . $mail->ErrorInfo);
        return false;
    }
}
