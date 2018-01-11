<?php namespace Model\VATCheck;

use Model\Core\Module;

class VATCheck extends Module {
	/**
	 * @var \SoapClient
	 */
	private $soapClient = null;
	/**
	 * @var string[]
	 */
	private $supportedCountries = [
		'AT',
		'BE',
		'BG',
		'CY',
		'CZ',
		'DE',
		'DK',
		'EE',
		'EL',
		'ES',
		'FI',
		'FR',
		'GB',
		'HR',
		'HU',
		'IE',
		'IT',
		'LT',
		'LU',
		'LV',
		'MT',
		'NL',
		'PL',
		'PT',
		'RO',
		'SE',
		'SI',
		'SK',
	];

	/**
	 * Checks VAT validity (only for Italian VATs)
	 *
	 * @param string $vat
	 * @param string $country
	 * @return bool
	 */
	public function checkValidity($vat, $country = 'IT'){
		$vat = trim($vat);

		switch($country){
			case 'IT':
				return $this->checkItalianValidity($vat);
				break;
			default:
				return true;
				break;
		}
	}

	/**
	 * @param string $vat
	 * @return bool
	 */
	private function checkItalianValidity($vat){
		if(empty($vat))
			return false;

		if(strlen($vat)!==11)
			return false;

		if(preg_match("/^[0-9]+\$/", $vat) != 1)
			return false;

		$s = 0;
		for( $i = 0; $i <= 9; $i += 2 )
			$s += ord($vat[$i]) - ord('0');
		for( $i = 1; $i <= 9; $i += 2 ){
			$c = 2*( ord($vat[$i]) - ord('0') );
			if( $c > 9 )
				$c = $c - 9;
			$s += $c;
		}
		if( ( 10 - $s%10 )%10 != ord($vat[10]) - ord('0') )
			return false;

		return true;
	}

	/**
	 * Performs a full check of the VAT (validity and online existing check)
	 *
	 * @param $vat
	 * @param string $country
	 * @return bool
	 */
	public function fullCheck($vat, $country = 'IT'){
		if($this->checkValidity($vat, $country)){
			if(in_array($country, $this->supportedCountries))
				return $this->checkExisting($vat, $country);
			else
				return true;
		}else{
			return false;
		}
	}

	/**
	 * @param string $vat
	 * @param string $country
	 * @return bool
	 */
	public function checkExisting($vat, $country = 'IT'){
		$response = $this->sendRequest($vat, $country);

		if($response and $response->valid){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @param string $vat
	 * @param string $country
	 * @return \stdClass
	 */
	private function sendRequest($vat, $country){
		$soapClient = $this->getSoapClient();

		return $soapClient->checkVat([
			'countryCode' => $country,
			'vatNumber' => $vat,
		]);
	}

	/**
	 * Retrieve the Soap Client object - creates a new one if necessary
	 *
	 * @return \SoapClient
	 */
	private function getSoapClient(){
		if(!$this->soapClient){
			$this->soapClient = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl', [
				'trace'=>1,
				'exceptions'=>0,
				'cache_wsdl'=>0
			]);
		}

		return $this->soapClient;
	}

	/**
	 * Set the Soap Client object to use
	 *
	 * @param \SoapClient $soapClient
	 */
	public function setSoapClient(\SoapClient $soapClient){
		$this->soapClient = $soapClient;
	}
}
