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
        echo "close connection";
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
            $userToken = $data['user']['token'];
        } else $userToken = '';

        if (isset($data['authModel'])) {
            $authModel = str_replace('.', trim("\ "), $data['authModel']);
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


        if (null === $userModel = $authModel::findIdentityByAccessToken($userToken)) {
            echo 'errCode 1' . PHP_EOL;

            $conn = $this->clients[$rid];
            $conn->send(Json::encode(['result' => false, 'message' => 'user not login']));
            return;
        }

        echo "#{$data['user']['token']} with {$userToken} user {$userModel->id} aka {$userModel->username} authorized as client #{$rid}" . PHP_EOL;

        $conn = $this->clients[$rid];


        $socketModel = SocketResource::find()->where(['user_id' => $userModel->id])->one();
        if (!empty($socketModel)) {

            //بستن ارتباط سوکت قبلی
            unset($this->clients[$socketModel->resource_id]);
            $socketModel->resource_id = $rid;
            if (!$socketModel->save()) {
                return $conn->send(Json::encode([
                    'result' => false,
                    'message' => 'User not saved.',
                ]));
            }
        } else {
            //
            $socketModel = new SocketResource();
            $socketModel->user_id = $userModel->id;
            $socketModel->resource_id = $rid;
            if (!$socketModel->save()) {
                return $conn->send(Json::encode([
                    'result' => false,
                    'message' => 'User not saved.',
                ]));
            }
        }

        $conn->send(Json::encode([
            'result' => true,
            'message' => 'You have logged in successfully',
            'data' => [
                'user' => [
                    'username' => $userModel->username
                ],
            ]
        ]));
    }

    public function onSend($data)
    {
        $data = json_decode($data, true);
        if (!isset($data['user_id']) || !isset($data['message'])) {
            return false;
        }
        $socketModel = SocketResource::find()->where(['user_id' => $data['user_id']])->one();
        if (empty($socketModel)) {
            return;
        }
        $client = $this->clients[$socketModel->resource_id];
        $user_id = $data['user_id'];
        unset($data['user_id']);
        $client->send(json_encode($data));

        echo "Message Delivered To UserID: " . $user_id . " (" . date('Y-m-d H:i:s') . ");\n";
        return true;
    }
}
