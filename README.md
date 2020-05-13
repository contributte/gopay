# Markette :: Gopay

[![Build Status](https://img.shields.io/travis/contributte/Gopay.svg?style=flat-square)](https://travis-ci.org/contributte/Gopay)
[![Code coverage](https://img.shields.io/coveralls/Markette/Gopay.svg?style=flat-square)](https://coveralls.io/r/Markette/Gopay)

[![Latest stable](https://img.shields.io/packagist/v/markette/gopay.svg?style=flat-square)](https://packagist.org/packages/markette/gopay)
[![Pre prelease](https://img.shields.io/packagist/vpre/markette/gopay.svg?style=flat-square)](https://packagist.org/packages/markette/gopay)
[![Downloads this Month](https://img.shields.io/packagist/dm/markette/gopay.svg?style=flat-square)](https://packagist.org/packages/markette/gopay)

## Diskuze

[![Join the chat at https://gitter.im/Markette/Gopay](https://img.shields.io/gitter/room/Markette/Gopay.svg?style=flat-square)](https://gitter.im/Markette/Gopay?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Dokumentace

- [Verze 3.x](.docs/README.md#verze-3x)
    - [Features](.docs/README.md#features)
    - [v3.0.0](.docs/README.md#v300)
        - [Instalace](.docs/README.md#instalace)
            - [v3.1.0 (PHP >= 5.6)](.docs/README.md#v310-php--56)
            - [v3.0.1 (PHP >= 5.5)](.docs/README.md#v301-php--55)
        - [Použití](.docs/README.md#použití)
            - [Služby](.docs/README.md#služby)
            - [Před platbou](.docs/README.md#před-platbou)
                - [Vlastní platební kanály](.docs/README.md#vlastní-platební-kanály)
            - [Provedení platby](.docs/README.md#provedení-platby)
            - [REDIRECT brána](.docs/README.md#redirect-brána)
            - [INLINE brána](.docs/README.md#inline-brána)
                - [Chyby s platbou](.docs/README.md#chyby-s-platbou)
            - [Po platbě](.docs/README.md#po-platbě)
            - [Opakované platby](.docs/README.md#opakované-platby)
            - [Předautorizované platby](.docs/README.md#předautorizované-platby)
            - [Vlastní implementace](.docs/README.md#vlastní-implementace)
                - [Inheritance](.docs/README.md#inheritance)
                - [Composition](.docs/README.md#composition)
- [Verze 2.x](https://github.com/contributte/gopay/tree/v2.3.x)

## Vývoj

> Tato verze využívá komunikaci přes SOAP. Doporučujeme přejít na modernější [GopayInline](https://github.com/Markette/GopayInline), jenž využívá JSON REST API.

<table>
    <thead>
        <tr>
            <th align="center">Status</th>
            <th align="center">Composer</th>
            <th align="center"><a href="http://www.gopay.com/cs">GoPay API</a></th>
            <th align="center"><a href="http://www.nette.org">Nette</a></th>
            <th align="center">PHP</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="center">dev</td>
            <td align="center">dev-master</td>
            <td align="center">2.5</td>
            <td align="center">nette/forms: ~2.3.0|~2.4.0 <br> nette/application: ~2.3.0|~2.4.0 <br> nette/di: ~2.3.0|~2.4.0</td>
            <td align="center">&gt;=5.6</td>
        </tr>
        <tr>
            <td align="center">stable</td>
            <td align="center">~3.1.2</td>
            <td align="center">2.5</td>
            <td align="center">nette/forms: ~2.3.0|~2.4.0 <br> nette/application: ~2.3.0|~2.4.0 <br> nette/di: ~2.3.0|~2.4.0</td>
            <td align="center">&gt;=5.6</td>
        </tr>
        <tr>
            <td align="center">stable</td>
            <td align="center">~3.0.1</td>
            <td align="center">2.5</td>
            <td align="center">nette/forms: ~2.3.0 <br> nette/application: ~2.3.0 <br> nette/di: ~2.3.0</td>
            <td align="center">&gt;=5.5</td>
        </tr>
        <tr>
            <td align="center">stable</td>
            <td align="center">~2.3.0</td>
            <td align="center">2.5</td>
            <td align="center">nette/utils: ~2.3 <br> nette/forms: ~2.3 <br> nette/application: ~2.3</td>
            <td align="center">&gt;=5.4</td>
        </tr>
        <tr>
            <td align="center">stable</td>
            <td align="center">~2.2.0</td>
            <td align="center">2.5</td>
            <td align="center">nette/utils: ~2.2 <br> nette/forms: ~2.2 <br> nette/application: ~2.2</td>
            <td align="center">&gt;=5.3.2</td>
        </tr>
    </tbody>
</table>

---

Thank you for testing, reporting and contributing.
