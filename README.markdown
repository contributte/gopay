# Simple Gopay Helper

For Nette Framework 2.0

## Installation

Just copy `/Gopay` directory to your `/libs` directory.

Then register it as service (eg. in your `bootstrap.php`):

	$container->addService('gopay', function ($container) {
		return new \VojtechDobes\Gopay\Helper(array(
			'id'        => '***',
			'secretKey' => '***',
			'imagePath' => '%wwwDir%/images',
			'testMode'  => FALSE,
		));
	});

## Use

### Before the payment

First you create form with appropriate payment buttons.
Every payment channel has its own button. You can easily
add buttons to your form with `bindForm` method:

	$gopay->bindForm($form, array(
		callback($this, 'submittedForm'),
	);

Provided callback will be called after valid click on some
of the payment buttons. You can get chosen channel from button:

	public function submittedForm(\Nette\Forms\Controls\SubmitButton $button)
	{
		$channel = $button->getChannel();
	}

If you need to manually render the form, get list of used
channels to easily iterate over the buttons:

	$this->template->channels = $gopay->getChannels();

	{foreach $channels as $channel}
		{$form['gopayChannel' . $channel]->control}
	{/foreach}

#### Custom payment channels

You can add your own custom channels:

	$gopay->addChannel('name', 'My channel', 'my-channel.png');

You can also deny or then allow again predefined channels:

	$gopay->denyChannel(\VojtechDobes\Gopay\Helper::CARD_VISA);
	$gopay->allowChannel(\VojtechDobes\Gopay\Helper::BANK);

### Payment

Executing the payment can be done in following steps. First, you create
the Payment:

	$payment = $gopay->createPayment(array(
		'sum'      => $sum,      // paid ammount
		'variable' => $variable, // variable symbol
		'specific' => $specific, // specific symbol
		'product'  => $product,  // name of bought product
		'customer' => array(     // for payment via credit card
			'firstName'   => $name,
			'lastName'    => NULL, // all params are voluntary
			'street'      => NULL, // if not provided, Helper will
			'city'        => NULL, // provide empty string
			'postalCode'  => $postal,
			'countryCode' => 'CZE',
			'email'       => $email,
			'phoneNumber' => NULL,
		),
	));

Secondly, you setup URLs for successful or failed response from
Gopay Payment Gate.

	$gopay->success = $this->link('//success');
	$gopay->failure = $this->link('//failure');

And then you pay :) (like this):

	$response = $gopay->pay($payment, \VojtechDobes\Gopay\Helper::CARD_VISA);

You have to provide the payment channel as second argument.
Received response will take the user to Payment Gate.

	$this->sendResponse($response);

But in moment of `pay` two things may go wrong:

1. Parameters provided to Gopay service or Payment aren't okay
2. Something is wrong with official Gopay Web Service (WS)

First error should never happen, because it implies something wrong
in your code. The second reason can happen anytime, therefore
generates `GopayException`. So the code should look like this:

	try {
		$gopay->pay($payment, Helper::CARD_VISA);
	} catch (GopayException $e) {
		echo 'Payment service is unfortunately offline now. Please try again later.';
	}

### After the payment

Shall be continued ...
