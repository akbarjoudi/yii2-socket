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
php yii socket --port=8082
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

```php
Yii::$app->socket->request([
    'message' => 'hello world.',
    'user_id' => '107'
]);
```
