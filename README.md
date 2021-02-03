# yii2 Websocket

for yii2 web application

## Dependencies


```
"react/zmq": "^0.4.0"
```
The above library needs a (linux zmq.so) library

```
~$ sudo pecl install zmq-beta
```
and add extension to php.ini

```
~$ sudo nano /etc/php/apache2/php.ini
```

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
'socket' => [
    'class' => 'aki\socket\commands\SocketController',
    'pusherClass' => 'aki\socket\eventHandler\PusherHandler' 
],
?>
```

and run migration

```
yii migrate --migrationPath=@app/vendor/aki/yii2-socket/migration
```

Once the extension is installed, simply use it in your code by :

```
php yii socket --port=8083
```

## Usage widget

```php
 <?= Socket::widget([
    'port' => 8083,
    'host' => "localhost",
    'authModel' => app\models\User::class,
    'data' => [
        'user' => [
            'token' => Yii::$app->user->identity->auth_key,//for current login
        ]
    ]
    'onMessage' => "function(e) {
        console.log('recived data : ');
        console.log(e.data);
    }",
]);
?>
```

## Usage push data

add component to config.php
```php
'components' => [
    'socket' => [
        'class' => 'aki\socket\Socket',
        'port' => '8082',
        'host' => "localhost"
    ],
]
```

```php
Yii::$app->socket->request([
    'message' => 'hello world.',
    'user_id' => '107'
]);
```
