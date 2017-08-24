<?php

namespace Ttskch\Pheetsu\Service;

use PHPUnit\Framework\TestCase;

class ColumnNameResolverTest extends TestCase
{
    /**
     * @var ColumnNameResolver
     */
    protected $columnNameResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->columnNameResolver = new ColumnNameResolver();
    }

    public function testName()
    {
        $this->assertEquals('C', $this->columnNameResolver->getName(3));        // 3*26^0
        $this->assertEquals('Z', $this->columnNameResolver->getName(26));       // 26*26^0
        $this->assertEquals('AA', $this->columnNameResolver->getName(27));      // 1*26^1 + 1*26^0
        $this->assertEquals('GCD', $this->columnNameResolver->getName(4814));   // 7*26^2 + 3*26^1 + 4*26^0 = 4814
    }
}
