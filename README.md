# [EN] SNIPERBOT
#### Automatic trading system for the crypto currency

## Installation:

To run the code, you need a server with a configured php and with extension of **curl_php**

If your server is installed on the local machine and has the address:
```
http://127.0.0.1/
```
then extract the contents to the root folder:
```
products/sniperbot/binance.html
products/sniperbot/bitfinex.html
products/sniperbot/dovewallet.html
products/sniperbot/huobi.html
products/sniperbot/index.html
products/sniperbot/poloniex.html
js/aex.js 
js/cookies.html 
js/xhr.js
api/binance_api.php
api/bitfinex_api.php
api/dovewallet_api.php
api/huobi_api.php
api/poloniex_api.php
```
For launch bot type in adress bar `http://127.0.0.1/products/sniperbot`

The bot trades at the best prices in depth.

The bot sends buy orders at price lower than `maxBuyPrice`.

The bot sends sell orders at price higher than `minSellPrice`.

The bot is good for a long buy or sell at same price.


# [RU] SNIPERBOT

## Установка:

Для работы кода, вам необходим сервер с настроенным php и в частности обязательным расширением **curl_php**

Если ваш сервер установлен на локальной машине и имеет адрес:
```
http://127.0.0.1/
```
то распакуйте содержимое в корневую папку:
```
products/sniperbot/binance.html
products/sniperbot/bitfinex.html
products/sniperbot/dovewallet.html
products/sniperbot/huobi.html
products/sniperbot/index.html
products/sniperbot/poloniex.html
js/aex.js 
js/cookies.html 
js/xhr.js
api/binance_api.php
api/bitfinex_api.php
api/dovewallet_api.php
api/huobi_api.php
api/poloniex_api.php
```
Для запуска бота набирайте в адресной строке `http://127.0.0.1/products/sniperbot`

Бот предназначен для торговли по лучшим ценам в стакане.

Бот берет цены из стакана и выставляет ордера по лучшей цене и не хуже чем `maxBuyPrice` и `minSellPrice`.

Бот подходит для тех, кто хочет закупиться криптой по лучшей цене в стакане, а не просто выставив ордер.

Также бот подойдет, для тех, кто хочет потроллить ботов-соперников, постоянно подгоняя цену крипты в ту или другую сторону.
