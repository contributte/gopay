# Markette :: Gopay

- pro Nette Framework 2.1 (master)
- a Gopay API 2.4

## Instalace

Nejjednodušeji stáhněte Gopay přes Composer:
```sh
$ composer require Markette/Gopay
```

Pokud nepoužijete Composer, zkopírujte `/Gopay` adresář mezi vaše knihovny - pokud používáte
RobotLoader, není nic víc potřeba.

Samotnou knihovnu lze nejsnáze zaregistrovat pomocí rozšíření v `bootstrap.php`:

```php
$configurator->onCompile[] = function ($configurator, $compiler) {
	$compiler->addExtension('gopay', new Markette\Gopay\Extension);
};
```

Poté můžeme v konfiguračním souboru nastavit parametry:

```neon
gopay:
	gopayId        : ***
	gopaySecretKey : ***
	testMode       : false
```

A přístup v presenteru pak bude díky autowiringu vypadat:

```php
/** @var Markette\Gopay\Service */
private $gopay;

public function injectGopay(Markette\Gopay\Service $gopay)
{
	$this->gopay = $gopay;
}
```

## Použití

### Před platbou

Před platbou je třeba vytvořit formulář s odpovídajícími platebními tlačítky.
Každý platební kanál je reprezentován jedním tlačítkem. Do formuláře můžete
tlačítka jednoduše přidat metodou `bindPaymentButtons()`:

```php
$gopay->bindPaymentButtons($form, array(
	callback($this, 'submittedForm'),
));
```

Předaný `callback` bude zavolán po úspěšném odeslání formuláře jedním
z platebních tlačítek (tedy jako po zavolání `->onClick[]` na daném tlačítku).
Zvolený kanál lze získat z tlačítka:

```php
public function submittedForm(Markette\Gopay\PaymentButton $button)
{
	$channel = $button->getChannel();
}
```

Pokud chcete formulář renderovat manuálně (např. s využitím formulářových
maker), je nejlepší si do šablony předat seznam použitých kanálů a iterovat
nad ním:

```php
$this->template->channels = $gopay->getChannels();
```

```html
{foreach $channels as $channel}
	{input $channel->control}
{/foreach}
```

Volání `getChannels()` je dobré obalit zachytáváním výjimky `GopayFatalException`,
protože napoprvé se v ní provádí dotaz na Gopay server kvůli získání výchozího
seznamu.

#### Vlastní platební kanály

Můžete si zaregistrovat vlastí platební kanály pro jednotnou práci:

```php
$gopay->addChannel('name', 'My channel', array(
	'image' => '/my-channel.png', // absolutní cestka o brázku
));
```

Také můžete zakázat či povolit kterýkoliv předdefinovaný (nebo i váš vlastní)
platební kanál:

```php
$gopay->denyChannel($gopay::METHOD_TRANSFER);
$gopay->allowChannel($gopay::METHOD_GOPAY);
```

Tato nastavení můžeme provést i v konfiguračním souboru:

```neon
gopay:
	channels:
		transfer: no # deny
		gopay: yes # allow (in default, all Gopay channels are allowed)
		name: # add new one
			title: My channel
			image: /my-channel.png
```

### Provedení platby

Platbu lze uskutečnit v následující krocích. Nejprve je třeba si vytvořit
novou instanci platby:

```php
$payment = $gopay->createPayment(array(
	'sum'         => $sum,      // placená částka
	'variable'    => $variable, // variabilní symbol
	'specific'    => $specific, // specifický symbol
	'productName' => $product,  // název produktu (popis účelu platby)
	'customer' => array(
		'firstName'   => $name,
		'lastName'    => NULL,    // všechna parametry jsou volitelné
		'street'      => NULL,    // pokud některý neuvedete,
		'city'        => NULL,    // použije se prázdný řetězec
		'postalCode'  => $postal,
		'countryCode' => 'CZE',
		'email'       => $email,
		'phoneNumber' => NULL,
	),
));
```

Zadruhé nastavit adresy, na které Gopay platební brána přesměruje při úspěchu či
naopak selhání platby.

```php
$gopay->successUrl = $this->link('//success');
$gopay->failureUrl = $this->link('//failure');
```

Je užitečné si poznačit ID platby (například pokud se má platba vázat
k nějaké objednávce apod.). Toho lze docílit předáním callbacku jako třetího
parametru metodě `pay()`.

```php
$storeIdCallback = function ($paymentId) use ($order) {
	$order->setPaymentId($paymentId);
};
```

A nakonec s platbou zaplatíte :) (takto, druhý parametr je platební kanál,
kterým má být platba uskutečněna):

```php
$response = $gopay->pay($payment, $gopay::METHOD_TRANSFER, $storeIdCallback);
```

Akce `pay()` vrátí `Response` objekt, který aplikaci přesměruje na platební
bránu Gopay.

```php
$this->sendResponse($response);
```

V okamžiku zavolání `pay()` se mohou pokazit dvě věci:

1. Někde jsou poskytnuty špatné parametry
2. Je pokažená oficiální platební brána Gopay

První chyba by nikdy neměla nastat. Znamená totiž nějakou krpu ve vašem kódu.
Druhá se může přihodit kdykoliv, proto generuje mírně odlišnou výjimku, kterou
je třeba zachytit a podle ní informovat zákazníka, že chyba právě není na vaší
straně.

```php
try {
	$gopay->pay($payment, $gopay::TRANSFER);
} catch (GopayException $e) {
	echo 'Platební služba Gopay bohužel momentálně nefunguje. Zkuste to
	prosím za chvíli.';
}
```

### Po platbě

Váš zákazník provede potřebné úkony na Gopay platební bráně, a jakmile je proces
dokončen, je přesměrován zpátky do vaší aplikace, buď na `successUrl`
nebo `failureUrl` adresu. Obě dvě dostanou od Gopay následující sadu parametrů:

- paymentSessionId
- targetGoId
- orderNumber // variabilní číslo
- encryptedSignature

První parametr je totožný s tím, který jsme si v předchozí kapitole uložili do
naší interní modelové reprezentace objednávky. Můžeme jej tedy použít k jejímu
opětovnému načtení.

Všechny tyto údaje + údaje z načtené objednávky pak použijeme ke znovusestavení
objektu platby:

```php
$order = $database->getOrderByPaymentId($paymentSessionId);

$payment = $gopay->restorePayment(array(
	'sum'          => $order->price,
	'variable'    => $order->varSymbol,
	'specific'    => $order->specSymbol,
	'productName' => $order->product,
), array(
	'paymentSessionId'   => $paymentSessionId,
	'targetGoId'         => $targetGoId,
	'orderNumber'        => $orderNumber,
	'encryptedSignature' => $encryptedSignature,
));
```

Na objektu platby lze zavolat dvě kontrolní metody: `isFraud()` a `isPaid()`.
První nás informuje, jestli je platba pravá, respektive jestli se nejedná
o podvrh (interně se zde kontroluje ona čtveřice parametrů předaných z platební
brány.

Druhá `isPaid()` pak vrátí `TRUE`, pokud je platba skutečně zaplacena. Pokud
ano, proces je u konce, můžeme si poznačit, že objednávka je zaplacena a poslat
třeba zákazníkovi email.

V případě neúspěšně platby jsou opět předány všechny čtyři parametry, je tedy
opět možné načíst si informace o související objednávce. Nic však kontrolovat
není třeba, informace o neúspěchu je zcela jasná z povahy daného požadavku.

Příklad použití `gopay` služby si můžete prohlédnout v [ukázkovém presenteru](https://github.com/Markette/Gopay/blob/master/example/GopayPresenter.php).

## Co tahle věc neumí a co s tím

Tahle mini-knihovnička, spíše snippet kódu nepokrývá velkou část Gopay API.
Pokud vám v ní chybí, co potřebujete, laskavě si potřebnou část dopište,
klidně i pošlete jako pull-request. Stejně tak můžete v issues informovat
o aktualizaci oficiálního API (které se zrovna před nedávném rozšířilo).
