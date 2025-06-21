<?php

namespace Tests\Unit;

use IFRS\Models\CostCenter;
use IFRS\Tests\TestCase;

class CostCenterTest extends TestCase
{
    /**
     * Test Creating CostCenter
     *
     * @return void
     */
    public function testCreateCostCenter()
    {
        $costCenter = new CostCenter([
            'code' => 'CC001',
            'name' => 'Administration',
            'description' => 'Administrative cost center',
            'active' => true,
        ]);
        $costCenter->save();

        $this->assertEquals($costCenter->code, 'CC001');
        $this->assertEquals($costCenter->name, 'Administration');
        $this->assertEquals($costCenter->description, 'Administrative cost center');
        $this->assertTrue($costCenter->active);
    }

    /**
     * CostCenter Model relationships test.
     *
     * @return void
     */
    public function testCostCenterRelationships()
    {
        $costCenter = new CostCenter([
            'code' => 'CC002',
            'name' => 'Sales Department',
            'description' => 'Sales department cost center',
            'active' => true,
        ]);
        $costCenter->save();

        $this->assertEquals($costCenter->toString(), 'Sales Department');
        $this->assertEquals($costCenter->toString(true), 'CostCenter: Sales Department');

        // Test accounts relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $costCenter->accounts());
    }

    /**
     * Test CostCenter name capitalization
     *
     * @return void
     */
    public function testCostCenterNameCapitalization()
    {
        $costCenter = new CostCenter([
            'code' => 'CC003',
            'name' => 'human resources',
            'active' => true,
        ]);
        $costCenter->save();

        $this->assertEquals($costCenter->name, 'Human resources');
    }
}
