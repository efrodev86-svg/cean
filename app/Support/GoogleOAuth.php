<?php

namespace App\Support;

class GoogleOAuth
{
    public static function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public static function institutionalDomainHint(): string
    {
        return config('services.google.allowed_domain')
            ?: config('cean.institutional_email_domain', 'ensq.edu.mx');
    }
}
