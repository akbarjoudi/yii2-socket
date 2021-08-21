<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace aki\socket\commands;

use yii\console\Controller;
use \React\EventLoop\Factory;
use \React\ZMQ\Context;
use \React\Socket\Server;
use \Ratchet\Server\IoServer;
use \Ratchet\Http\HttpServer;
use \Ratchet\WebSocket\WsServer;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Akbar Joudi <akbar.joody@gmail.com>
 */
class SocketController extends Controller
{
    /**
     * port
     */
    public $port=8083;

    public $pusher = [
        'class' => 'aki\socket\eventHandler\PusherHandler',
        'host' => "127.0.0.1",
        'port' => "8082"
    ];
    // public $pusherClass = 'aki\socket\eventHandler\PusherHandler';
    // public $pusherHost = "127.0.0.1";
    // public $pusherPort = "8082";

    /**
     * options
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'port'
        ]);
    }

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $loop   = Factory::create();
        $pusher = new $this->pusher['class'];
        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://'.$this->pusher['host'].':'.$this->pusher['port']); // Binding to 127.0.0.1 means the only client that can connect is itself
        $pull->on('message', array($pusher, 'onSend'));

        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new Server('0.0.0.0:'.$this->port, $loop); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new IoServer(
            new HttpServer(
                new WsServer(
                    $pusher
                )
            ),
            $webSock
        );
        echo "running server port {$this->port} waiting ... \n";
        $loop->run();
        
    }
}
