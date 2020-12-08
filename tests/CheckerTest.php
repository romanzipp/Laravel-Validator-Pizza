<?php

namespace romanzipp\ValidatorPizza\Tests;

use romanzipp\ValidatorPizza\Checker;

class CheckerTest extends TestCase
{
    /** @test **/
    public function theAllowedDomainFunctionReturnsTrueForAValidDomain()
    {
        $checker = (new Checker())->allowedDomain('validator.pizza');

        $this->assertTrue($checker);
    }

    /** @test **/
    public function theAllowedDomainFunctionReturnsFalseForAnDisposableDomain()
    {
        $checker = (new Checker())->allowedDomain('mailinator.com');

        $this->assertFalse($checker);
    }

    /** @test **/
    public function theAllowedDomainFunctionReturnsFalseForAnInvalidDomain()
    {
        $checker = (new Checker())->allowedDomain('t.t');

        $this->assertFalse($checker);
    }
}
