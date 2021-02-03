<?php
namespace aki\socket;

use yii\base\Component;
use yii\web\ServerErrorHttpException;

/**
 * @author akbar joudi <akbar.joody@gmail.com>
*/
class Socket extends Component
{
    /**
     * @var String socket port
     */
    public $port;

    /**
     * @var String host ip
     */
    public $host;

    /**
     * @var Array $data
     */
    public $data = [];


    /**
     * @param Array $data to convert json
     */
    public function request($data, $pusherName = 'Pusher')
    {
        if(!class_exists('ZMQContext'))
        {
            throw new ServerErrorHttpException("class `ZMQContext` not installed");
        }
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, $pusherName);
        $socket->connect("tcp://{$this->host}:{$this->port}");

        $socket->send(json_encode($data));
    }
}
