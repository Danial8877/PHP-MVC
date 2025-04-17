<?php

use Kavenegar\KavenegarApi;
use Kavenegar\Exceptions\ApiException;
use Kavenegar\Exceptions\HttpException;
use Melipayamak\MelipayamakApi;


function smsKavenegar($receptor, $message)
{
    $sender = $_ENV["SMS_KAVENEGAR_SENDER"];
    try {
        $api = new KavenegarApi($_ENV["SMS_KAVENEGAR_API"]);
        $result = $api->Send($sender, $receptor, $message);
        return [
            'success' => true,
            'message_id' => $result[0]->messageid,
            'status' => $result[0]->status
        ];
    } catch (ApiException $e) {
        return [
            'success' => false,
            'error' => 'API Error: ' . $e->errorMessage()
        ];
    } catch (HttpException $e) {
        return [
            'success' => false,
            'error' => 'HTTP Error: ' . $e->errorMessage()
        ];
    }
}

function smsMelipayamak($receptor, $message)
{
    $username = $_ENV['SMS_MELIPAYAMAK_USERNAME']; // شماره موبایل ثبت‌نامی
    $password = $_ENV['SMS_MELIPAYAMAK_PASSWORD']; // رمز عبور پنل
    $sender = $_ENV['SMS_MELIPAYAMAK_SENDER']; // شماره فرستنده (مثل 5000xxx)

    try {
        $api = new MelipayamakApi($username, $password);
        $sms = $api->sms();

        $result = $sms->send($receptor, $sender, $message);

        return [
            'success' => true,
            'message_id' => $result,
            'status' => 'sent'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'General Error: ' . $e->getMessage()
        ];
    }
}
