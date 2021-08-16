<p align="center">
	<img width="200px" src="https://nulled-scripts.ir/yii2websocket.png">
</p>


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
php composer require aki/yii2-socket:dev-master
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
    'port' => '8083',
    'pusherClass' => 'aki\socket\eventHandler\PusherHandler' ,
    'pusherHost' => '127.0.0.1',
    'pusherPort' => '8082'
],
?>
```

Once the extension is installed, simply use it in your code by :

```
php yii socket --port=8083
```

## Usage widget
set config global in config.php
```php
'container' => [
    'definitions' => [
        'aki\socket\widgets\SocketListener' => [
            'host' => "localhost",
            'port' => '8083'
        ],
        'authModel' => app\models\User::class,
    ],
],
```
```php
 <?= SocketListener::widget([
    'data' => [
        'user' => [
            'token' => Yii::$app->user->identity->auth_key,//for current login
        ]
    ],
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
    'socketPusher' => [
        'class' => 'aki\socket\SocketPusher',
        'port' => '8082',
        'host' => "localhost"
    ],
]
```

and use it:
```php
//user_id = 100
Yii::$app->socketPusher->request(100, [
    'message' => 'hello world.',
]);
```

use custom Pusher class:
```php
Yii::$app->socketPusher->request(100, [
    'message' => 'hello world.',
], 'myPusher');
```
