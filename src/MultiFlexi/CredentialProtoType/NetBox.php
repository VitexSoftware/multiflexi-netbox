<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\CredentialProtoType;

/**
 * NetBox REST API credential prototype.
 *
 * Supplies NETBOXURL + NETBOXTOKEN to MultiFlexi applications
 * (e.g. isp-tools NetBoxer backend).
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class NetBox extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface, \MultiFlexi\checkableCredentialInterface
{
    public static string $logo = 'NetBox.svg';

    public function __construct()
    {
        parent::__construct();

        $urlField = new \MultiFlexi\ConfigField('NETBOXURL', 'url', _('NetBox URL'), _('NetBox instance base URL'));
        $urlField->setHint('https://netbox.example.com')->setValue('');

        $tokenField = new \MultiFlexi\ConfigField('NETBOXTOKEN', 'password', _('NetBox API Token'), _('NetBox API authentication token'));
        $tokenField->setHint('')->setValue('');

        $this->configFieldsProvided->addField($urlField);
        $this->configFieldsProvided->addField($tokenField);
    }

    #[\Override]
    public function prepareConfigForm(): void
    {
    }

    public function name(): string
    {
        return _('NetBox');
    }

    public function description(): string
    {
        return _('NetBox REST API credentials for IPAM / DCIM integration');
    }

    #[\Override]
    public function uuid(): string
    {
        return '49767570-3daa-45b9-9e64-94070f9455fc';
    }

    #[\Override]
    public function logo(): string
    {
        return self::$logo;
    }

    /**
     * curl error codes that indicate a TLS/certificate problem rather than a
     * transient network failure.
     */
    private const TLS_ERRNOS = [
        \CURLE_SSL_CONNECT_ERROR,   // 35
        51,                         // CURLE_PEER_FAILED_VERIFICATION
        \CURLE_SSL_CERTPROBLEM,     // 58
        \CURLE_SSL_CIPHER,          // 59
        \CURLE_SSL_CACERT,          // 60
        \CURLE_SSL_CACERT_BADFILE,  // 77
        82,                         // CURLE_SSL_CRL_BADFILE
        83,                         // CURLE_SSL_ISSUER_ERROR
    ];

    #[\Override]
    public function checkAvailability(): \MultiFlexi\CredentialCheckResult
    {
        $url = (string) ($this->configFieldsProvided->getFieldByCode('NETBOXURL')?->getValue() ?? '');
        $token = (string) ($this->configFieldsProvided->getFieldByCode('NETBOXTOKEN')?->getValue() ?? '');

        if ($url === '' || $token === '') {
            $missing = array_keys(array_filter([
                'NETBOXURL' => $url === '',
                'NETBOXTOKEN' => $token === '',
            ]));

            return new \MultiFlexi\CredentialCheckResult(
                \MultiFlexi\CredentialState::Misconfigured,
                sprintf(_('Required fields not set: %s'), implode(', ', $missing)),
                time(),
            );
        }

        $endpoint = rtrim($url, '/').'/api/status/';
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            \CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Token '.$token,
            ],
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 5,
            \CURLOPT_RETURNTRANSFER => true,
        ]);
        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);

        if ($errno !== 0) {
            if (\in_array($errno, self::TLS_ERRNOS, true)) {
                return new \MultiFlexi\CredentialCheckResult(
                    \MultiFlexi\CredentialState::Misconfigured,
                    sprintf(_('NetBox server TLS certificate is not trusted: %s'), curl_strerror($errno)),
                    time(),
                );
            }

            return new \MultiFlexi\CredentialCheckResult(
                \MultiFlexi\CredentialState::Unavailable,
                sprintf(_('NetBox server unreachable: %s'), curl_strerror($errno)),
                time(),
                60,
            );
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return new \MultiFlexi\CredentialCheckResult(
                \MultiFlexi\CredentialState::Misconfigured,
                sprintf(_('Authentication failed (HTTP %d) — check NETBOXTOKEN'), $httpCode),
                time(),
            );
        }

        if ($httpCode >= 500) {
            return new \MultiFlexi\CredentialCheckResult(
                \MultiFlexi\CredentialState::Unavailable,
                sprintf(_('NetBox server returned HTTP %d'), $httpCode),
                time(),
                60,
            );
        }

        if ($httpCode !== 200) {
            return new \MultiFlexi\CredentialCheckResult(
                \MultiFlexi\CredentialState::Misconfigured,
                sprintf(_('Unexpected NetBox response HTTP %d'), $httpCode),
                time(),
            );
        }

        $version = '';

        if (\is_string($body) && $body !== '') {
            $decoded = json_decode($body, true);

            if (\is_array($decoded)) {
                $version = (string) ($decoded['netbox-full-version'] ?? $decoded['netbox-version'] ?? '');
            }
        }

        $detail = $version !== '' ? sprintf(_('NetBox %s reachable'), $version) : '';

        return new \MultiFlexi\CredentialCheckResult(\MultiFlexi\CredentialState::Available, $detail, time(), 300);
    }
}
