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

namespace Test\MultiFlexi\CredentialProtoType;

use MultiFlexi\CredentialProtoType\NetBox;
use PHPUnit\Framework\TestCase;

class NetBoxTest extends TestCase
{
    private NetBox $proto;

    protected function setUp(): void
    {
        $this->proto = new NetBox();
    }

    public function testName(): void
    {
        $this->assertNotEmpty($this->proto->name());
    }

    public function testDescription(): void
    {
        $this->assertNotEmpty($this->proto->description());
    }

    public function testUuid(): void
    {
        $this->assertSame('49767570-3daa-45b9-9e64-94070f9455fc', $this->proto->uuid());
    }

    public function testLogo(): void
    {
        $this->assertSame('NetBox.svg', $this->proto->logo());
    }

    public function testFieldsProvided(): void
    {
        $fields = $this->proto->fieldsProvided();
        $this->assertNotNull($fields->getFieldByCode('NETBOXURL'));
        $this->assertNotNull($fields->getFieldByCode('NETBOXTOKEN'));
    }

    public function testCheckAvailabilityMisconfiguredWithoutValues(): void
    {
        $result = $this->proto->checkAvailability();
        $this->assertSame(\MultiFlexi\CredentialState::Misconfigured, $result->state);
    }
}
