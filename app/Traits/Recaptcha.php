<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

trait Recaptcha
{

    public function validateRecaptcha($code)
    {
        $isValid = false;
        if (!$code) {
            $this->throwInvalidException(__trans('please_verify_you_are_not_bot_by_checking_recaptcha'));
        }
        if (isRecaptchaEnabled() && $code) {
            $response = Http::get(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' => getSetting('google_recaptcha_site_secret'),
                    'response' => $code,
                ]
            );
            if ($response->getStatusCode() == 200) {
                $output = json_decode($response->body());
                $isValid = $output->success;
                if (getSetting('google_recaptcha_version') == 'v3') {
                    $isValid  = ($output->score >= 0.6);
                }
            }
        }

        if (!$isValid) {
            $message = "System is not able to verify the human score. Please try again later";
            $this->throwInvalidException($message);
        }

        return $isValid;
    }


    protected function throwInvalidException($message)
    {
        errorMessage($message);
        throw ValidationException::withMessages([
            'recaptcha' => $message,
        ])->status(429);
    }
}
