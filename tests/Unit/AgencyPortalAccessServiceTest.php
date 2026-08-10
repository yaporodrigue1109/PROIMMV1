<?php

namespace Tests\Unit;

use App\Services\Mobile\AgencyPortalAccessService;
use PHPUnit\Framework\TestCase;

class AgencyPortalAccessServiceTest extends TestCase
{
    private AgencyPortalAccessService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AgencyPortalAccessService;
    }

    public function test_it_supports_the_current_array_of_module_ids(): void
    {
        $this->assertTrue($this->service->optionsContainPortal([1, 5, 6], 5));
        $this->assertTrue($this->service->optionsContainPortal([1, 5, 6], 6));
        $this->assertFalse($this->service->optionsContainPortal([1, 2, 3], 6));
    }

    public function test_it_supports_detailed_module_objects(): void
    {
        $options = [
            ['id' => 5, 'label' => 'Portail propriétaire', 'actif' => true],
            ['id' => 6, 'label' => 'Portail locataire', 'actif' => false],
        ];

        $this->assertTrue($this->service->optionsContainPortal($options, 5));
        $this->assertFalse($this->service->optionsContainPortal($options, 6));
    }
}
