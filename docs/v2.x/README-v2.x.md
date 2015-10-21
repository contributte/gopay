# Markette :: Gopay

## v2.1.0 - v2.3.0

## Instalace

Nejjednodušeji stáhněte Gopay přes Composer:

### v2.3.0 (PHP >= 5.4)
```sh
$ composer require markette/gopay:~2.3.0
```

### v2.2.0 (PHP >= 5.3.2)
```sh
$ composer require markette/gopay:~2.2.0
```

Pokud nepoužijete Composer, zkopírujte `/src/Gopay` adresář mezi vaše knihovny - pokud používáte
RobotLoader, není nic víc potřeba.

Samotnou knihovnu lze nejsnáze zaregistrovat jako rozšíření v souboru `config.neon`:

```neon
extensions:
    gopay: Markette\Gopay\Extension
```

Poté můžeme v konfiguračním souboru nastavit parametry:

```neon
gopay:
    gopayId        : ***
    gopaySecretKey : ***
    testMode       : false
```

A přístup v presenteru pak bude díky autowiringu a `@inject` anotaci vypadat:

```php
use Markette\Gopay;

/** @var Gopay\Service @inject */
public $gopay;
```

## Použití

### Před platbou

Před platbou je třeba vytvořit formulář s odpovídajícími platebními tlačítky.
Každý platební kanál je reprezentován jedním tlačítkem. Do formuláře můžete
tlačítka jednoduše přidat metodou `bindPaymentButtons()`:

```php
$gopay->bindPaymentButtons($form, [$this, 'submitForm']);
// nebo vice callbacku
$gopay->bindPaymentButtons($form, [
    [$this, 'preProcessForm'],
    [$this, 'processForm'],
    [$this, 'postProcessForm'],
]);
```

Předaný `callback` bude zavolán po úspěšném odeslání formuláře jedním
z platebních tlačítek (tedy jako po zavolání `->onClick[]` na daném tlačítku).
Zvolený kanál lze získat z tlačítka:

```php
use Markette\Gopay;

public function submittedForm(Gopay\PaymentButton $button)
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
$service->addChannel(Gopay::METHOD_TRANSFER, 'My transfer channel', '/my-channel.png', NULL, NULL, []);
```

Také můžete zakázat či povolit kterýkoliv předdefinovaný (nebo i váš vlastní)
platební kanál:

```php
$gopay->denyChannel($gopay::METHOD_TRANSFER);
$gopay->allowChannel($gopay::METHOD_GOPAY);
```

Tato nastavení můžeme provést i v konfiguračním souboru:

```yaml
gopay:
    channels:
		gopay: 'Gopay - Elektronická peněženka'
		card_gpkb: 'Platba kartou - Komerční banka, a.s. - Global Payments'
```

Pokud chceme umožnit změnit **channel** na straně GoPay:

```yaml
gopay:
    changeChannel: yes
```

### Provedení platby

Platbu lze uskutečnit v následující krocích. Nejprve je třeba si vytvořit
novou instanci platby:

```php
$payment = $gopay->createPayment([
    'sum'         => $sum,      // placená částka
    'variable'    => $variable, // variabilní symbol
    'specific'    => $specific, // specifický symbol
    'productName' => $product,  // název produktu (popis účelu platby)
    'customer' => [
        'firstName'   => $name,
        'lastName'    => NULL,    // všechna parametry jsou volitelné
        'street'      => NULL,    // pokud některý neuvedete,
        'city'        => NULL,    // použije se prázdný řetězec
        'postalCode'  => $postal,
        'countryCode' => 'CZE',
        'email'       => $email,
        'phoneNumber' => NULL,
    ],
]);
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
Samotné placení lze provést dvěma způsoby.

### REDIRECT

```php
$response = $gopay->pay($payment, $gopay::METHOD_TRANSFER, $storeIdCallback);
```

Akce `pay()` vrátí `Response` objekt. Resp. `RedirectResponse`, který vás přesměruje na Gopay bránu.

```php
$this->sendResponse($response);
```

### INLINE brána

```php
$response = $gopay->payInline($payment, $gopay::METHOD_TRANSFER, $storeIdCallback);
```

Akce `payInline()` vám vrátí pole s klíči **url** a **signature**.

```php
[ 
    "url" => "https://gate.gopay.cz/gw/v3/3100000099",
    "signature" => "25ee53a1ec­cc253a8310f5267d2de6b483f58a­f9676d883e26600ce3316ai"
];
```

Platební bránu je možné vytvořit pomocí formuláře, který najdete v [dokumentaci](https://help.gopay.com/cs/tema/integrace-platebni-brany/integrace-nova-platebni-brany/integrace-nove-platebni-brany-pro-stavajici-zakazniky).

```html
<form action="https://gate.gopay.cz/gw/v3/3100000099" method="post" id="gopay-payment-button">
  <input type="hidden" name="signature" value="25ee53a1ec­cc253a8310f5267d2de6b483f58a­f9676d883e26600ce3316ai"/>
  <button name="pay" type="submit">Zaplatit</button>
  <script type="text/javascript" src="https://gate.gopay.cz/gp-gw/js/embed.js"></script>
</form>
```

#### Chyby s platbou

V okamžiku zavolání `pay()` nebo `payInline()` se mohou pokazit dvě věci:

1. Někde jsou poskytnuty špatné parametry
2. Je pokažená oficiální platební brána Gopay

První chyba by nikdy neměla nastat. Znamená totiž nějakou krpu ve vašem kódu.
Druhá se může přihodit kdykoliv, proto generuje mírně odlišnou výjimku, kterou
je třeba zachytit a podle ní informovat zákazníka, že chyba právě není na vaší
straně.

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

### Po platbě

Váš zákazník provede potřebné úkony na Gopay platební bráně, a jakmile je proces
dokončen, je přesměrován zpátky do vaší aplikace, buď na `successUrl`
nebo `failureUrl` adresu. Obě dvě dostanou od Gopay následující sadu parametrů:

- paymentSessionId
- targetGoId
- orderNumber // variabilní číslo
- encryptedSignature

*Plus parametry, které uvedete v successUrl, resp. failureUrl.*

První parametr je totožný s tím, který jsme si v předchozí kapitole uložili do
naší interní modelové reprezentace objednávky. Můžeme jej tedy použít k jejímu
opětovnému načtení.

Všechny tyto údaje + údaje z načtené objednávky pak použijeme ke znovusestavení
objektu platby:

```php
$order = $database->getOrderByPaymentId($paymentSessionId);

$payment = $gopay->restorePayment([
    'sum'          => $order->price,
    'variable'    => $order->varSymbol,
    'specific'    => $order->specSymbol,
    'productName' => $order->product,
], [
    'paymentSessionId'   => $paymentSessionId,
    'targetGoId'         => $targetGoId,
    'orderNumber'        => $orderNumber,
    'encryptedSignature' => $encryptedSignature,
]);
```

Na objektu platby lze zavolat dvě kontrolní metody: `isFraud()` a `isPaid()`.
První nás informuje, jestli je platba pravá, respektive nejedná-li se
o podvrh (interně se zde kontroluje ona čtveřice parametrů předaných z platební
brány).

Druhá `isPaid()` pak vrátí `TRUE`, pokud je platba skutečně zaplacena. Pokud
ano, proces je u konce, můžeme si poznačit, že objednávka je zaplacena a poslat
třeba zákazníkovi email.

V případě neúspěšné platby jsou opět předány všechny čtyři parametry, je tedy
opět možné načíst si informace o související objednávce. Nic však kontrolovat
není třeba, informace o neúspěchu je zcela jasná z povahy daného požadavku.

Příklad použití `gopay` služby si můžete prohlédnout v [ukázkovém presenteru](https://github.com/Markette/Gopay/blob/master/docs/v2.x/examples/GopayPresenter.php).

## Co tahle věc neumí a co s tím

Tahle mini-knihovnička, spíše snippet kódu nepokrývá velkou část Gopay API.
Pokud vám v ní chybí, co potřebujete, laskavě si potřebnou část dopište,
klidně i pošlete jako pull-request. Stejně tak můžete v issues informovat
o aktualizaci oficiálního API (které se zrovna před nedávném rozšířilo).
