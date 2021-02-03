<?php

namespace aki\socket\widgets;

use Yii;
use yii\base\Widget;

/**
 * @author akbar joudi <akbar.joody@gmail.com>
 */
class PusherSocket extends Widget
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
     * @var String
     * autenticate model
     */
    public $authModel;

    /**
     * @var String event onMessage for connection socket
     */
    public $onMessage = "function(e) {
        console.log(e.data);
    }";

    public $onOpen = "function(e) {
        //login
        var authObj = {'method': 'auth', 'user':{'token':'{{token}}'}, 'authModel':'(authModel)'};
        authObj = JSON.stringify(authObj)
        conn.send(authObj);
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

        $this->onOpen = str_replace('{{token}}', $token, $this->onOpen);

        $this->authModel = str_replace(trim("\ "), ".", $this->authModel);
        // die($this->authModel);
        $this->onOpen = str_replace('(authModel)', $this->authModel, $this->onOpen);

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
