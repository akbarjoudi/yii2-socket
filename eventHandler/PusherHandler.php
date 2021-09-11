<?php

namespace aki\socket\eventHandler;

use aki\socket\models\SocketResource;
use app\models\User;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use yii\helpers\Json;

class PusherHandler implements MessageComponentInterface
{
    public $clients = [];

    public $clientIds = [];
    

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients[$conn->resourceId] = $conn;
        echo "\n Connection is established: " . $conn->resourceId, "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf(
            'Connection %d sending message "%s" to %d other connection%s' . "\n",
            $from->resourceId,
            $msg,
            $numRecv,
            $numRecv == 1 ? '' : 's'
        );


        //json decode
        $data = Json::decode($msg, true);

        //generate method
        $method = $data['method'] . 'Request';

        //call method
        if (method_exists($this, $method) && $method == 'authRequest') {
            call_user_func_array([$this, $method], [$from->resourceId, $data]);
        } else if (method_exists($this, $method) && $method == 'sendMessageRequest') {
            call_user_func_array([$this, $method], [$from, $data['message'], $data['to_user_id']]);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "close connection client id : {$conn->resourceId}";
        $conn->close();
        unset($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }

    /**
     * Process auth request. Find user chat(if not exists - create it)
     * and send message to all other clients
     *
     * @access private
     * @param $rid
     * @param $data
     * @return void
     */
    private function authRequest($rid, array $data)
    {
        
        if (isset($data['user']) && isset($data['user']['token'])) {
            $tokenModel = UserToken::getUserTokenModel($data['user']['token']);
            if(empty($tokenModel)){
                $this->clients[$rid]->send(Json::encode([
                    'result' => false,
                    'message' => 'token model not found',
                ]));
            }
        } else {
            $this->clients[$rid]->send(Json::encode([
                'result' => false,
                'message' => 'token not found',
            ]));
        }
        
        if (isset($data['authModel'])) {
            $authModel = str_replace('.', trim("\ "), $data['authModel']);
            if(!class_exists($authModel)){
                $this->clients[$rid]->send(Json::encode([
                    'result' => false,
                    'message' => 'Authenticate Model not found',
                ]));
                return ;
            }
        }
        else{
            if(class_exists(\app\models\User::class)){
                $authModel = \app\models\User::class;
            }
            else{
                $this->clients[$rid]->send(Json::encode([
                    'result' => false,
                    'message' => 'Authenticate Model not found',
                ]));
                return ;
            }
        }
        $conn = $this->clients[$rid];
        
        $userModel = User::findOne($tokenModel->user_id);
        if (null === $userModel) {
            echo 'errCode 1' . PHP_EOL;

            $conn = $this->clients[$rid];
            $conn->send(Json::encode(['result' => false, 'message' => "user not login"]));
            return;
        }
        
        echo "#user {$userModel->id} width username {$userModel->profile->username} authorized as client #{$rid}" . PHP_EOL;


        $conn = $this->clients[$rid];
        
        $this->clientIds[$userModel->id][] = $rid;


        $conn->send(Json::encode([
            'result' => true,
            'message' => 'You have logged in successfully',
            'data' => [
                'user' => [
                    'username' => $userModel->profile->username
                ],
            ]
        ]));
    }




    public function onSend($data)
    {
        $data = json_decode($data, true);
        if (!isset($data['user_id'])) {
            echo "user id not found";
            return false;
        }


        $rIds = $this->clientIds[$data['user_id']] ?? null;
        if (!is_array($rIds)) {
            return;
        }

        $user_id = $data['user_id'];
        unset($data['user_id']);
        foreach ($rIds as $rid) {
            if(isset($this->clients[$rid])){
                $client = $this->clients[$rid];
                
                $client->send(json_encode($data));
        
                echo "Message Delivered To UserID: " . $user_id . " (" . date('Y-m-d H:i:s') . "); "." type : ". $data['type']."\n";
            }
        }

       
        return true;
    }
}
