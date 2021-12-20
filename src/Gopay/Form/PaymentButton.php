<?php declare(strict_types = 1);

namespace Markette\Gopay\Form;

use Nette\Forms\Controls\SubmitButton;

/**
 * Payment button
 *
 * @property-read string $channel
 */
class PaymentButton extends SubmitButton implements IPaymentButton
{

	/** @var string */
	private $channel;

	public function __construct(string $channel, ?string $caption = null)
	{
		parent::__construct($caption);
		$this->channel = $channel;
	}

	public function getChannel(): string
	{
		return $this->channel;
	}

}
