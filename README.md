# yii2 Websocket

for yii2 web application

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist aki/yii2-socket "*"
```

or add

```
"aki/yii2-socket": "*"
```

to the require section of your `composer.json` file.

## Usage

first add to config/console.php

```php
<?php
'controllerMap' => [
    'socket' => 'aki\socket\commands\SocketController',
],
?>
```

and run migration
```
yii migrate --migrationPath=@app/vendor/aki/yii2-socket/migration
```


Once the extension is installed, simply use it in your code by :

```
php yii socket --port=8082
```

## Usage widget

```php
 <?= Socket::widget([
    'host' => 'localhost',
    'port' => '8082',
    'data' => [
        'method' => 'sendMessage',
        'message' => 'hello',
        'to_user_id' => 101,//user id in user table
    ],
    'onMessage' => "function(e) {
        console.log(e.data);
    }",
]);
?>
```
