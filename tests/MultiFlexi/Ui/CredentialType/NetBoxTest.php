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
    }

    public function testExtendsCredentialFormHelperPrototype(): void
    {
        if (!class_exists(\MultiFlexi\Ui\CredentialFormHelperPrototype::class)) {
            $this->markTestSkipped('multiflexi-web UI helpers not installed');
        }

        $reflection = new \ReflectionClass(NetBox::class);
        $this->assertTrue($reflection->isSubclassOf(\MultiFlexi\Ui\CredentialFormHelperPrototype::class));
    }

    public function testHasFinalizeMethod(): void
    {
        $this->assertTrue(method_exists(NetBox::class, 'finalize'));
    }
}
