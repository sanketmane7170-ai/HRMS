<?php

namespace Modules\AgenticAI\Traits;

/**
 * SSL Certificate Resolution Trait
 * 
 * Resolves CA certificate bundle path for SSL verification
 * across different server environments (Docker, Linux, Windows).
 * 
 * Author: Sanket v2.0
 */
trait ResolvesSSLCertificate
{
    //Sanket v2.0 - resolve CA certificate bundle path for SSL verification
    protected function getCertPath(): ?string
    {
        $paths = [
            '/etc/ssl/certs/ca-certificates.crt',          // Debian/Ubuntu/Docker
            '/etc/pki/tls/certs/ca-bundle.crt',            // CentOS/RHEL
            '/etc/ssl/ca-bundle.pem',                       // OpenSUSE
            ini_get('curl.cainfo') ?: null,                 // php.ini curl setting
            ini_get('openssl.cafile') ?: null,              // php.ini openssl setting
        ];

        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    //Sanket v2.0 - get Guzzle/Laravel HTTP options with SSL cert path
    protected function getSSLOptions(): array
    {
        $certPath = $this->getCertPath();
        return $certPath ? ['verify' => $certPath] : [];
    }
}
