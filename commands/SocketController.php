<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace aki\socket\commands;

use yii\console\Controller;
use Ratchet\Server\IoServer;
use aki\socket\components\AppSocket;
use app\models\SocketResource;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

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
    public $port;

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
        $result = SocketResource::deleteAll('1=1');
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new appSocket()
                )
            ),
            $this->port
        );
        echo "runing server socket port $this->port";
        $server->run();
        
    }
}
