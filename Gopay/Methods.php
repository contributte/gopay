<?php

/**
 * Gopay Helper with Happy API
 * 
 * 
 * @author Vojtech Dobes
 */

namespace VojtechDobes\Gopay;

use Nette\Forms\Form;
use Nette\Object;

/**
 * Gadget pro výpis posledních fotoalb
 *
 * @author   Vojtěch Dobeš
 *
 * @property-read   $options
 */
final class Methods extends Object
{
	
	/** @var array */
	private $methods = array();
	
	/** @var array */
	private $disallowedMethods = array();
	
	/** @var event */
	public $onBeforeRequest;
	
	public function __construct()
	{
		$this->methods = $this->getDefaultMethods();
	}
	
	public function getMethods()
	{
		return $this->methods;
	}

/* === Payment Methods ====================================================== */

	public function bind(Form $form)
	{
		foreach ($this->methods as $code => $method) {
			if (!isset($method->image)) {
				$submit = $form->addSubmit('method' . $code, $method->title);
			} else {
				$submit = $form->addImage('method' . $code, '/signaly/jp2/document_root/assets/images/gopay/' . $method->image);
			}
			$this->methods[$code]->control = 'method' . $code;
		}
	}
	
	private function getDefaultMethods()
	{
		return array(
			'cards' => (object) array(
				'image' => 'gopay_payment_cards.gif',
				'title' => 'Zaplatit GoPay - platební karty',
			),
			'gopay' => (object) array(
				'image' => 'gopay_payment_gopay.gif',
				'title' => 'Zaplatit GoPay - GoPay peněženka',
			),
			'mojeplatba' => (object) array(
				'image' => 'gopay_payment_mojeplatba.gif',
				'title' => 'Zaplatit GoPay - MojePlatba',
			),
			'mpenize' => (object) array(
				'image' => 'gopay_payment_mpenize.gif',
				'title' => 'Zaplatit GoPay - mPeníze',
			),
			'moneybookers' => (object) array(
				'image' => 'gopay_payment_moneybookers.gif',
				'title' => 'Zaplatit GoPay - MoneyBookers',
			),
			'eplatby' => (object) array(
				'image' => 'gopay_payment_eplatby.gif',
				'title' => 'Zaplatit GoPay - ePlatby',
			),
		);
	}
	
	public function addMethod($name)
	{
		$this->methods[$name] = (object) array();
	}
	
	public function allowMethod($name)
	{
		$this->methods[$name] = $this->disallowedMethods[$name];
		unset($this->disallowedMethods[$name]);
	}
	
	public function disallowMethod($name)
	{
		$this->disallowedMethods[$name] = $this->methods[$name];
		unset($this->methods[$name]);
	}


}

