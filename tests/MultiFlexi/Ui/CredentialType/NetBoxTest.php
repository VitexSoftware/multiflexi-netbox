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

namespace Test\MultiFlexi\Ui\CredentialType;

use MultiFlexi\Ui\CredentialType\NetBox;
use PHPUnit\Framework\TestCase;

class NetBoxTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(NetBox::class));
        $this->assertTrue(is_subclass_of(NetBox::class, \MultiFlexi\Ui\CredentialFormHelperPrototype::class));
    }
}
