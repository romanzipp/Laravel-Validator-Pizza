<?php

namespace romanzipp\ValidatorPizza\Tests;

use romanzipp\ValidatorPizza\Checker;

class CheckerTest extends TestCase
{
    /** @test **/
    public function the_allowedDomain_function_returns_true_for_a_valid_domain()
    {
        $checker = (new Checker())->allowedDomain('validator.pizza');

        $this->assertTrue($checker);
    }

    /** @test **/
    public function the_allowedDomain_function_returns_false_for_an_disposable_domain()
    {
        $checker = (new Checker())->allowedDomain('mailinator.com');

        $this->assertFalse($checker);
    }

    /** @test **/
    public function the_allowedDomain_function_returns_false_for_an_invalid_domain()
    {
        $checker = (new Checker())->allowedDomain('t.t');

        $this->assertFalse($checker);
    }
}
