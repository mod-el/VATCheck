<?php

use PHPUnit\Framework\TestCase;

require('..' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Module.php');
require('VATCheck.php');

class VATCheckTest extends TestCase
{
	private $model = null;

	private function getModelCore()
	{
		if (!$this->model) {
			$this->model = $this->getMockBuilder('\\Model\\Core\\Core')->getMock();
		}

		return $this->model;
	}

	public function providerVats(): array
	{
		return [
			['IT', '11194631005', true, true],
			['IT', '02706510845', true, false],
			['IT', '11111111111', false, false],
			['MX', '4567890', true, true],
		];
	}

	/**
	 * @dataProvider providerVats
	 */
	function testIfPassiveCheckWorks(string $country, string $vat, bool $expectedPassive, bool $expectedActive)
	{
		$v = new Model\VATCheck\VATCheck($this->getModelCore());

		$this->assertEquals($v->checkValidity($vat, $country), $expectedPassive);
	}

	/**
	 * @dataProvider providerVats
	 */
	function testIfActiveCheckWorks(string $country, string $vat, bool $expectedPassive, bool $expectedActive)
	{
		$v = new Model\VATCheck\VATCheck($this->getModelCore());
		$this->assertEquals($v->fullCheck($vat, $country), $expectedActive);
	}
}
