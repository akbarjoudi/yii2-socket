<?php

namespace aki\socket;

use Yii;
use yii\base\Component;
use yii\base\Widget;
use yii\helpers\Json;

/**
 * @author akbar joudi <akbar.joody@gmail.com>
 */
class Message extends Widget
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
     * @var String event onMessage for connection socket
     */
    public $onMessage = "function(e) {
        console.log(e.data);
    }";

    public $onOpen = "function(e) {
        //login
        var authObj = {'method': 'auth', 'user':{'token':'{{token}}'}};
        authObj = JSON.stringify(authObj)
        conn.send(authObj);

        //send request user method 
        var obj = {{json}};
        if(Object.keys(obj).length>0){
            var json = JSON.stringify(obj);
            conn.send(json);
        }
        console.log('connection estblished');
    }";

    public $onClose = "function (e){
        console.log('connection closed');
    }";

    public function run()
    {
        return $this->registerJs();
    }

    private function registerJs()
    {
        $token = '';
        if (!Yii::$app->user->isGuest) {
            $token = Yii::$app->user->identity->accessToken;
        }

        //send data for user
        if (count($this->data) > 0) {
            $json = Json::encode($this->data);
            $this->onOpen = str_replace('{{json}}', $json, $this->onOpen);
        }
        else{
            $this->onOpen = str_replace('{{json}}', '{}', $this->onOpen);
        }

        $this->onOpen = str_replace('{{token}}', $token, $this->onOpen);
        // die(var_dump($this->onOpen));

        $js = "
        var conn = new WebSocket('ws://{$this->host}:{$this->port}');
        conn.onclose = $this->onClose;
        ";

        $js .= "
        conn.onopen = $this->onOpen;
        conn.onmessage = $this->onMessage;
        ";
        Yii::$app->view->registerJs($js, \yii\web\View::POS_END);
    }
}
