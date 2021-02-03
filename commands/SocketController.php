<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace aki\socket\commands;

use yii\console\Controller;
use aki\socket\eventHandler\PusherHandler;

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

    public $pusherClass = 'aki\socket\eventHandler\PusherHandler';

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
        $loop   = \React\EventLoop\Factory::create();
        $pusher = new $this->pusherClass;

        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new \React\ZMQ\Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:8082'); // Binding to 127.0.0.1 means the only client that can connect is itself
        $pull->on('message', array($pusher, 'onSend'));

        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new \React\Socket\Server('0.0.0.0:'.$this->port, $loop); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    $pusher
                )
            ),
            $webSock
        );
        echo "running server port {$this->port} waiting ... \n";
        $loop->run();
        
    }
}
