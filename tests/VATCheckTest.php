<?php
use PHPUnit\Framework\TestCase;

require('..'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'Module.php');
require('VATCheck.php');

class VATCheckTest extends TestCase{
	private $model = null;

	private function getModelCore(){
		if(!$this->model){
			$this->model = $this->getMockBuilder('\\Model\\Core')->getMock();
		}

		return $this->model;
	}

	public function providerVats(){
		return [
			['IT', '11194631005', true, true],
			['IT', '02706510845', true, false],
			['IT', '11111111111', false, false],
		];
	}

	/**
	 * @dataProvider providerVats
	 */
	function testIfPassiveCheckWorks($country, $vat, $expectedPassive, $expectedActive){
		$v = new Model\VATCheck($this->getModelCore());

		$this->assertEquals($v->checkValidity($vat, $country), $expectedPassive);
	}

	/**
	 * @dataProvider providerVats
	 */
	function testIfActiveCheckWorks($country, $vat, $expectedPassive, $expectedActive){
		$v = new Model\VATCheck($this->getModelCore());

//		$soapClient = $this->getMockBuilder('SoapClient')->getMock();
//		$v->setSoapClient($soapClient);

		$this->assertEquals($v->fullCheck($vat, $country), $expectedActive);
	}
}
