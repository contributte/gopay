# Simple Gopay Helper

Pro Nette Framework 2.0

## Instalace

Nejprve zkopírujte `/Gopay` adresář mezi vaše knihovny - pokud používáte
RobotLoader, není nic víc potřeba.

Samotná knihovna se registruje jako služba, například v `bootstrap.php`:

	$container->addService('gopay', function ($container) {
		return new \VojtechDobes\Gopay\Helper(array(
			'id'        => '***',
			'secretKey' => '***',
			'imagePath' => '%wwwDir%/images',
			'testMode'  => FALSE,
		));
	});

Nebo v `NEON` konfiguraci:

	services:
		gopay:
			class: VojtechDobes\Gopay\Helper
			arguments:
				- [id=***, secretKey=***, imagePath=%wwwDir%/images, testMode=FALSE]

A přístup v presenteru pak bude vypadat:

	$gopay = $this->context->gopay;

## Použití

### Před platbou

Nejprvě je třeba vytvořit formulář s odpovídajícími platebními tlačítky.
Každý platební kanál je reprezentován jedním tlačítkem. Do formuláře můžete
tlačítka jednoduše přidat metodou `bindForm()`:

	$gopay->bindForm($form, array(
		callback($this, 'submittedForm'),
	);

Předaný `callback` bude zavolán po úspěšném odeslání formuláře jedním
z platebních tlačítek (tedy jako po zavolání `->onValid[]` na daném tlačítku).
Zvolený kanál lze získat z tlačítka:

	public function submittedForm(\Nette\Forms\Controls\SubmitButton $button)
	{
		$channel = $button->getChannel();
	}

Pokud chcete formulář renderovat manuálně (např. s využitím formulářových
maker), je nejlepší si do šablony předat seznam použitých kanálů a iterovat
nad ním:

	$this->template->channels = $gopay->getChannels();

	{foreach $channels as $channel}
		{input "gopayChannel.$channel"}
	{/foreach}

#### Vlastní platební kanály

Můžete si zaregistrovat vlastí platební kanály pro jednotnou práci:

	$gopay->addChannel('name', 'My channel', 'my-channel.png');

Také můžete zakázat či povolit kterýkoliv předdefinovaný (nebo i váš vlastní)
platební kanál:

	$gopay->denyChannel($gopay::CARD_VISA);
	$gopay->allowChannel($gopay::BANK);

### Provedení platby

Platbu lze uskutečnit v následující krocích. Nejprve je třeba si vytvořit
novou instanci platby:

	$payment = $gopay->createPayment(array(
		'sum'      => $sum,      // placená částka
		'variable' => $variable, // variabilní symbol
		'specific' => $specific, // specifický symbol
		'product'  => $product,  // název produktu (popis účelu platby)
		'customer' => array(     // při platbě kartou lze poskytnou tyto údaje
			'firstName'   => $name,
			'lastName'    => NULL, // všechna parametry jsou volitelné
			'street'      => NULL, // pokud některý neuvedete,
			'city'        => NULL, // použije se prázdný řetězec
			'postalCode'  => $postal,
			'countryCode' => 'CZE',
			'email'       => $email,
			'phoneNumber' => NULL,
		),
	));

Zadruhé nastavit adresy, na které Gopay platební brána přesměruje při úspěchu či
naopak selhání platby.

	$gopay->success = $this->link('//success');
	$gopay->failure = $this->link('//failure');

A nakonec s platbou zaplatíte :) (takto):

	$response = $gopay->pay($payment, $gopay::CARD_VISA);

Druhý parametr je platební kanál, kterým má být platba uskutečněna. Akce vrátí
`Response` objekt, který aplikaci přesměruje na platební bránu Gopay.

	$this->sendResponse($response);

V okamžiku zavolání `pay()` se mohou pokazit dvě věci:

1. Někde jsou poskytnuty špatné parametry
2. Je pokažená oficiální platební brána Gopay

První chyba by nikdy neměl nastat. Znamená totiž nějakou chybu ve vašem kódu.
Druhá se může přihodit kdykoliv, proto generuje mírně odlišnou vyjímku, kterou
je třeba zachytit a podle ní informovat zákazníka, že chyba právě není na vaší
straně.

	try {
		$gopay->pay($payment, $gopay::CARD_VISA);
	} catch (GopayException $e) {
		echo 'Platební služba Gopay bohužel momentálně nefunguje. Zkuste to
		prosím za chvíli.';
	}

### Po platbě

Shall be continued ...
