<?php

namespace sagalaya\tests\cases\extensions\util;

use lithium\test\Unit;
use sagalaya\extensions\util\DomBuilder;

class DomBuilderTest extends Unit {
	
	public function testDomBuilder() {
		
		// Checking constructor
		$dom = new DomBuilder('div', array('id' => 'test'));
		$this->assertEqual("<div id=\"test\"></div>", $dom->render());
		
		// Checking addChild as text
		$dom->addChild("span", array('class' => 'text'));
		$this->assertEqual("<div id=\"test\"><span class=\"text\"></span></div>", $dom->render());
		
		// Checking get
		$this->assertEqual("<span class=\"text\"></span>", $dom->get("span")->render());
		
		// Checking addChild as object
		$dom = new DomBuilder('div', array('id' => 'test'));
		$dom->addChild(new DomBuilder('span', array('class' => 'text')));
		$this->assertEqual("<div id=\"test\"><span class=\"text\"></span></div>", $dom->render());
	}		
}
