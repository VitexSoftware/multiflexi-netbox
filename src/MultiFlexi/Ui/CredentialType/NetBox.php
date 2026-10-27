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

namespace MultiFlexi\Ui\CredentialType;

/**
 * NetBox credential form helper — probes /api/status/ with the configured token.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class NetBox extends \MultiFlexi\Ui\CredentialFormHelperPrototype
{
    public function finalize(): void
    {
        $urlField = $this->credential->getFields()->getFieldByCode('NETBOX_URL');
        $tokenField = $this->credential->getFields()->getFieldByCode('NETBOX_TOKEN');

        $url = $urlField ? (string) $urlField->getValue() : '';
        $token = $tokenField ? (string) $tokenField->getValue() : '';

        if ($url === '') {
            $this->addItem(new \Ease\TWB4\Alert('danger', _('NetBox URL is not set')));
            parent::finalize();

            return;
        }

        if ($token === '') {
            $this->addItem(new \Ease\TWB4\Alert('danger', _('NetBox API token is not set')));
            parent::finalize();

            return;
        }

        $probe = self::probeStatus($url, $token);

        if (!$probe['success']) {
            $this->addItem(new \Ease\TWB4\Alert('danger', $probe['message']));
            parent::finalize();

            return;
        }

        $this->addItem(new \Ease\TWB4\Alert('success', _('NetBox API is reachable and the token is accepted')));

        if (!empty($probe['info'])) {
            $panel = new \Ease\TWB4\Panel(_('NetBox Status'), 'info');
            $list = new \Ease\Html\DlTag(null, ['class' => 'row']);

            foreach ($probe['info'] as $key => $value) {
                $list->addItem(new \Ease\Html\DtTag($key, ['class' => 'col-sm-4']));
                $list->addItem(new \Ease\Html\DdTag((string) $value, ['class' => 'col-sm-8']));
            }

            $panel->addItem($list);
            $this->addItem($panel);
        }

        parent::finalize();
    }

    /**
     * @return array{success: bool, message: string, info: array<string, string>}
     */
    private static function probeStatus(string $url, string $token): array
    {
        $endpoint = rtrim($url, '/').'/api/status/';
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            \CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Token '.$token,
            ],
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 10,
            \CURLOPT_RETURNTRANSFER => true,
        ]);
        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);

        if ($errno !== 0) {
            return [
                'success' => false,
                'message' => sprintf(_('Cannot reach NetBox at %s: %s'), $endpoint, curl_strerror($errno)),
                'info' => [],
            ];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return [
                'success' => false,
                'message' => sprintf(_('Authentication failed (HTTP %d) — check NETBOX_TOKEN'), $httpCode),
                'info' => [],
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => sprintf(_('NetBox returned HTTP %d for %s'), $httpCode, $endpoint),
                'info' => [],
            ];
        }

        $info = [];

        if (\is_string($body) && $body !== '') {
            $decoded = json_decode($body, true);

            if (\is_array($decoded)) {
                foreach ([
                    'netbox-full-version' => _('NetBox Version'),
                    'python-version' => _('Python Version'),
                    'django-version' => _('Django Version'),
                    'hostname' => _('Hostname'),
                ] as $key => $label) {
                    if (!empty($decoded[$key])) {
                        $info[$label] = (string) $decoded[$key];
                    }
                }

                if (!empty($decoded['plugins']) && \is_array($decoded['plugins'])) {
                    $plugins = [];

                    foreach ($decoded['plugins'] as $name => $ver) {
                        $plugins[] = $name.' '.$ver;
                    }

                    if ($plugins !== []) {
                        $info[_('Plugins')] = implode(', ', $plugins);
                    }
                }
            }
        }

        return [
            'success' => true,
            'message' => '',
            'info' => $info,
        ];
    }
}
