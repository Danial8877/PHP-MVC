<?php

use PHPMailer\PHPMailer\PHPMailer;

function email($fromEmail, $fromName, $toEmail, $toName, $title, $altMessage, $message)
{
    $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $_ENV["MAIL_HOST"];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV["MAIL_PORT"];
        $mail->Username = $_ENV["MAIL_USERNAME"];
        $mail->Password = $_ENV["MAIL_PASSWORD"];
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $title;
        $mail->Body = $message;
        $mail->AltBody = $altMessage;
        $mail->send();

}

