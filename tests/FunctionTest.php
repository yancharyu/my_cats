<?php

require_once(dirname(__FILE__) . '/../function.php');

use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{

    public function testValidHalfTrue()
    {
        validHalf('123', 'number');
        $results = getErrMsg('number');
        $this->assertNull($results);
    }
    public function testValidHalfFalse()
    {
        validHalf('１２３', 'number');
        $results = getErrMsg('number');
        $this->assertEquals(ERR_MSG08, $results);
    }
    public function testValidHalfFalse2()
    {
        validHalf('一二三', 'number');
        $results = getErrMsg('number');
        $this->assertEquals(ERR_MSG08, $results);
    }
}
