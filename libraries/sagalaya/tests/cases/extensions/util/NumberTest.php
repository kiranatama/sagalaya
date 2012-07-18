<?php

namespace sagalaya\tests\cases\extensions\util;

use sagalaya\extensions\util\Number;

use lithium\test\Unit;

class NumberTest extends Unit {

	public function testAddition() {
		$number = new Number('19292938829239.02');

		$this->assertEqual('19305930759158.03-', $number->add('12991929919.01') . "-");
		$this->assertEqual('19398922689087.04-', $number->add('92991929929.01') . "-");
		$this->assertEqual('19398922689087.94-', $number->add(0.9) . "-");
		$this->assertEqual('19398922689088.87-', $number->add(0.93) . "-");
	}

	public function testSubstraction() {
		$number = new Number('92839288293882928');

		$this->assertEqual('92839258355053539-', $number->sub('29938829389') . "-");
		$this->assertEqual('-35989929927838289-', $number->sub('128829188282891828') . "-");
		$this->assertEqual('-35989929927838288.283-', $number->add(0.717) . "-");
		$this->assertEqual('-35989929927838289-', $number->sub(0.717) . "-");
		$this->assertEqual('0-', $number->sub('-35989929927838289') . "-");
	}

	public function testMultiply() {
		$number = new Number('203928322');

		$this->assertEqual('25272710917757004-', $number->mul('123929382') . "-");
		$this->assertEqual('10.1090843671028016-', $number->mul('0.0000000000000004') . "-");
		$this->assertEqual('11.2191953671028016-', $number->add(1.110111) . "-");
		$this->assertEqual('5.6095976835514008-', $number->mul(0.5) . "-");
	}

	public function testDivision() {
		$number = new Number('19203929388293829');

		$this->assertEqual('1005726394.7415372249-', $number->div('19094586.25') . "-");
		$this->assertEqual('4190526644.7564051037-', $number->div(0.24) . "-");
		$this->assertEqual('21800793.0785011112-', $number->div(192.219) . "-");
	}

	public function testComparison() {
		$number = new Number('129382992839288');
		$small = new Number('1002939');

		$this->assertTrue($number->gt('10299299'));
		$this->assertFalse($number->lt('10029201'));
		$this->assertTrue($number->gt($small));
		$this->assertFalse($number->lt($small));
	}

	public function testBasicScenario() {
		$credit = new Number('-10000300002');
		$debit = new Number('22220000000');
		$sum = $credit->add($debit);

		$this->assertTrue($sum->lt($debit));
		$this->assertTrue($sum->eq($credit));
		$this->assertTrue($sum->sub($debit)->eq($credit->sub($debit)));

		$this->assertFalse($sum->eq($debit));
		$this->assertTrue($sum->eq($credit));

		$sum->add('100290');
		$this->assertTrue($sum->neq($credit));
	}
}

?>