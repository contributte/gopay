![](https://heatbadger.vercel.app/github/readme/contributte/gopay/?deprecated=1)

<p align=center>
    <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
    <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
    <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
    Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

## Disclaimer

| :warning: | This project is no longer being maintained. Please use [contributte/gopay-inline](https://github.com/contributte/gopay-inline).|
|---|---|

| Composer | [`markette/gopay`](https://packagist.org/packages/markette/gopay) |
|---| --- |
| Version | ![](https://badgen.net/packagist/v/markette/gopay) |
| PHP | ![](https://badgen.net/packagist/php/markette/gopay) |
| License | ![](https://badgen.net/github/license/contributte/gopay) |

## About

This version used communication through SOAP. We recommend using [contributte/gopay-inline](https://github.com/contributte/gopay-inline) which uses JSON REST API.

## Installation

To install the latest version of `markette/gopay` use [Composer](https://getcomposer.org).

```bash
composer require markette/gopay
```

## Configuration

Register extension in DI:

```neon
extensions:
	gopay: Markette\Gopay\DI\Extension

gopay:
	gopayId: ***
	gopaySecretKey: ***
	testMode: false
```

## Usage

### Services

You can choose from three services:

* **PaymentService** (standard payments)
* **RecurrentPaymentService** (recurring payments)
* **PreAuthorizedPaymentService** (pre-authorized payments)

You can use `autowiring` and inject them into `Presenter`:

```php
use Markette\Gopay\Service\PaymentService;
use Markette\Gopay\Service\RecurrentPaymentService;
use Markette\Gopay\Service\PreAuthorizedPaymentService;

/** @var PaymentService @inject */
public $paymentService;

/** @var RecurrentPaymentService @inject */
public $recurrentPaymentService;

/** @var PreAuthorizedPaymentService @inject */
public $preAuthorizedPaymentService;
```

### Before payment

Before payment, you need to create a form with the corresponding payment buttons. Each payment channel is represented by one button. You can add buttons to the form simply via Binder with the `bindPaymentButtons()` method:

```php
$binder->bindPaymentButtons($service, $form, [$this, 'submitForm']);

// or more callbacks

$gopay->bindPaymentButtons($form, [
	[$this, 'preProcessForm'],
	[$this, 'processForm'],
	[$this, 'postProcessForm'],
]);
```

The passed `callback` will be called after the successful submission of the form by one from the payment buttons (ie as after calling `->onClick[]` on the given button). The selected channel can be obtained from the button:

```php
use Markette\Gopay\Form;

public function submittedForm(Form\PaymentButton $button)
{
	$channel = $button->getChannel();
}
```

If you want to render the form manually (eg using form maker), it is best to pass a list of used channels to the template and iterate over it:

```php
$this->template->channels = $service->getChannels();
```

```latte
{foreach $channels as $channel}
    {input $channel->control}
{/foreach}
```

It's a good idea to wrap the `getChannels()` call by catching the `GopayFatalException` exception because for the first time it queries the Gopay server to get the default list.

#### Custom payment channels

You can register your custom payment channels for a single job:

```php
$service->addChannel(Gopay::METHOD_TRANSFER, 'My transfer channel', '/my-channel.png', NULL, NULL, []);
```

You can also disable or enable any predefined (or even your custom) payment channel:

```php
$gopay->denyChannel($gopay::METHOD_TRANSFER);
$gopay->allowChannel($gopay::METHOD_GOPAY);
```

We can also make these settings in the configuration file:

```neon
gopay:
	payments:
		channels:
			gopay: 'Gopay - Elektronická peněženka'
			card_gpkb: 'Platba kartou - Komerční banka, a.s. - Global Payments'
```

If we want to allow to change **channel** on the GoPay page:

```neon
gopay:
	payments:
		changeChannel: yes
```

### Make a payment

Payment can be made in the following steps. First you need to create new payment instance:

```php
$payment = $service->createPayment([
	'sum'				=> $sum,      // placená částka
	'variable'			=> $variable, // variabilní symbol
	'specific'			=> $specific, // specifický symbol
	'productName'		=> $product,  // název produktu (popis účelu platby)
	'customer' => [
		'firstName'		=> $name,
		'lastName'		=> NULL,    // všechna parametry jsou volitelné
		'street'		=> NULL,    // pokud některý neuvedete,
		'city'			=> NULL,    // použije se prázdný řetězec
		'postalCode'	=> $postal,
		'countryCode'	=> 'CZE',
		'email'			=> $email,
		'phoneNumber'	=> NULL,
	],
]);
```

Second, set the addresses to which the Gopay payment gateway redirects the success of the payment or while error in payment proccess:

```php
$service->setSuccessUrl($this->link('//success', ['orderId' => $orderId]));
$service->setFailureUrl($this->link('//failure', ['orderId' => $orderId]));
```

It is useful to make a note of the payment ID (for example if the payment is to be bound to an order etc.). This can be accomplished by passing the callback as the third parameter to the `pay()` method:

```php
$storeIdCallback = function ($paymentId) use ($order) {
	$order->setPaymentId($paymentId);
};
```

The payment itself can be made in two ways:

### Redirect after a payment

```php
$response = $gopay->pay($payment, $gopay::METHOD_TRANSFER, $storeIdCallback);
```

The `pay()` action returns an RedirectResponse, which redirects you to the Gopay gateway:

```php
$this->sendResponse($response);
```

### Inline payment

```php
$response = $gopay->payInline($payment, $gopay::METHOD_TRANSFER, $storeIdCallback);
```

The `payInline()` action returns a field with the **url** and **signature** keys:

```php
[
	"url" => "https://gate.gopay.cz/gw/v3/3100000099",
	"signature" => "25ee53a1ec­cc253a8310f5267d2de6b483f58a­f9676d883e26600ce3316ai"
];
```

The payment gateway can be created using the form, which can be found in [documentation](https://help.gopay.com/cs/tema/integrace-platebni-brany/integrace-nova-platebni-brany/integrace-nove-platebni-brany-pro-stavajici-zakazniky).

```html
<form action="https://gate.gopay.cz/gw/v3/3100000099" method="post" id="gopay-payment-button">
  <input type="hidden" name="signature" value="25ee53a1ec­cc253a8310f5267d2de6b483f58a­f9676d883e26600ce3316ai"/>
  <button name="pay" type="submit">Zaplatit</button>
  <script type="text/javascript" src="https://gate.gopay.cz/gp-gw/js/embed.js"></script>
</form>
```

#### Payment exception

Two things can go wrong when calling `pay()` or `payInline()`:

1. Bad parameters are provided somewhere
2. The official Gopay payment gateway is broken

The first mistake should never occur. It means a mistake in your code.

The second can happen at any time, so it generates a slightly different exception that you need to catch and inform the customer according to it that the error is not on to Your side:

```php
try {
	$gopay->pay($payment, $gopay::TRANSFER, $storeIdCallback);
	// nebo
	$gopay->payInline($payment, $gopay::TRANSFER, $storeIdCallback);
} catch (GopayException $e) {
	echo 'Platební služba Gopay bohužel momentálně nefunguje. Zkuste to
	prosím za chvíli.';
}
```

### After payment

Your customer will perform the necessary actions on the Gopay payment gateway, and once the process is completed, he is redirected back to your application, either to `successUrl` or `failureUrl` address. Both will receive the following set of parameters from Gopay:

- paymentSessionId
- targetGoId
- orderNumber // varying number
- encryptedSignature

*Plus the parameters you specify in successUrl or failureUrl.*

The first parameter is identical to the one that we have saved in the previous chapter to our internal model representation of the order. So we can use it for reload.

We will then use all this data + data from the loaded order for payment object reassembly:

```php
$order = $model->getOrderByPaymentId($paymentSessionId);

$payment = $service->restorePayment([
	'sum'			=> $order->price,
	'variable'		=> $order->varSymbol,
	'specific'		=> $order->specSymbol,
	'productName'	=> $order->product,
], [
	'paymentSessionId'		=> $paymentSessionId,
	'targetGoId'			=> $targetGoId,
	'orderNumber'			=> $orderNumber,
	'encryptedSignature'	=> $encryptedSignature,
]);
```

Two control methods can be called on a payment object: `isFraud()` and `isPaid()`. The first informs us whether the payment is genuine or not for fraud (internally, the four parameters passed from the payment system are checked by payment gate).

The second `isPaid()` then returns TRUE if the payment is actually paid. If yes, the process is over, we can mark that the order is paid and send for example, an email to the customer.

In case of unsuccessful payment, all four parameters are passed again, so it is possible to retrieve information about the related order. However there is no need to check anything, the information about the failure is quite clear from the character of the request.

### Recurring payments

Making a recurring payment is very easy:

```php
$service->payRecurrent(PreAuthorizedPayment $payment, $gopay::METHOD_TRANSFER, function($paymentSessionId) {});
```

We will need `$paymentSessionId` to cancel the recurring payment:

```php
$service->cancelRecurrent($paymentSessionId);
```

### Pre-authorized payments

Making a pre-authorized payment is very simple:

```php
$service->payPreAuthorized(PreAuthorizedPayment $payment, $gopay::METHOD_TRANSFER, function($paymentSessionId) {});
```

We will need `$paymentSessionId` to cancel the pre-authorized payment:

```php
$service->cancelPreAuthorized($paymentSessionId);
```

## Versions

| State  | Version      | Branch   | Nette  | PHP       |
|--------|--------------|----------|--------|-----------|
| dev    | `^3.4.0`     | `master` | `3.0+` | `>=7.1`   |
| stable | `^3.3.0`     | `master` | `3.0+` | `>=7.1`   |
| stable | `^3.2.0`     | `master` | `2.4`  | `>=5.6`   |
| stable | `^3.0.0`     | `master` | `2.3`  | `>=5.5`   |
| stable | `^2.3.0`     | `master` | `2.3`  | `>=5.4`   |
| stable | `^2.2.0`     | `master` | `2.2`  | `>=5.3.2` |

## Development

This package was maintained by these authors.

<a href="https://github.com/f3l1x">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
