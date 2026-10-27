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

require_once __DIR__.'/../vendor/autoload.php';

// UI helpers ship with multiflexi-web / multiflexi-web5, not multiflexi-core.
foreach ([
    '/usr/lib/multiflexi/MultiFlexi/Ui/CredentialFormHelperPrototype.php',
    '/usr/lib/multiflexi-web/MultiFlexi/Ui/CredentialFormHelperPrototype.php',
    '/usr/lib/multiflexi-web5/MultiFlexi/Ui/CredentialFormHelperPrototype.php',
] as $uiHelper) {
    if (is_readable($uiHelper)) {
        require_once $uiHelper;
        break;
    }
}
