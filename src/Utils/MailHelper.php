<?php

namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    public static function sendResetLink(string $to, string $resetUrl): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';         // SMTP server Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'kaziensekai@gmail.com'; // GANTI DENGAN EMAIL GMAIL LO
            $mail->Password = 'cvot kwkp yfhv qjht';     // GANTI DENGAN APP PASSWORD (jangan password akun asli)
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('kaziensekai@gmail.com', 'Cerita Mahasiswa');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password CeritaApp';
            $mail->Body    = 'Klik link berikut untuk reset password:<br><a href="' . $resetUrl . '">' . $resetUrl . '</a>';

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
