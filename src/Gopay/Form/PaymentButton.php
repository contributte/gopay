<?php declare(strict_types = 1);

namespace Markette\Gopay\Form;

use Nette\Forms\Controls\SubmitButton;

/**
 * Payment button
 *
 * @property-read $channel
 */
class PaymentButton extends SubmitButton implements IPaymentButton
{

	/** @var string */
	private $channel;

	/**
	 * @param string $channel
	 * @param string $caption
	 */
	public function __construct($channel, $caption = null)
	{
		parent::__construct($caption);
		$this->channel = $channel;
	}

	/**
	 * @return string
	 */
	public function getChannel()
	{
		return $this->channel;
	}

}
