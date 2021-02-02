<?php

namespace aki\socket\components;

use app\models\SocketResource;
use app\models\User;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use yii\helpers\Json;

class AppSocket implements MessageComponentInterface
{

    /**
     * list of clients
     */
    protected $clients;



    /**
     * @param $conn Ratchet\MessageComponentInterface
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $rid = $this->getResourceId($conn);
        $this->clients[$rid] = $conn;
        echo "Connection is established: " . $rid, "\n";
    }

    /**
     * @param $from Ratchet\MessageComponentInterface
     * @param $msg message sended
     */
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
        }
        else if(method_exists($this, $method) && $method == 'sendMessageRequest')
        {
            call_user_func_array([$this, $method], [$from, $data['message'], $data['to_user_id']]);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $socketModel = SocketResource::find()->where(['source_id' => $conn->resource_id]);
        if(!empty($socketModel)){
            $socketModel->remove();
        }
        unset($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
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

        $user = User::class;

        if (null === $userModel = $user::findIdentityByAccessToken($userToken)) {
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
            if(!$socketModel->save()){
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
            if(!$socketModel->save()){
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

    private function sendMessageRequest($conn, $message, $uid)
    {   
        $socketModel = SocketResource::find()->where(['user_id' => $uid])->one();

        //چک کردن اینکه آیا کاربری که برای آن پیام ارسال می شود وجود دارد
        if(empty($socketModel))
        {
            return $conn->send(Json::encode([
                'result' => false,
                'message' => 'socket not found.',
            ]));
        }
        if(array_key_exists($socketModel->resource_id, $this->clients))
        {
            $to_conn = $this->clients[$socketModel->resource_id];
            return $to_conn->send(Json::encode([
                'result' => true,
                'message' => $message,
            ]));
        } 
        else{
            return $conn->send(Json::encode([
                'result' => true,
                'message' => "user not found.",
            ]));
        }
    }



    /**
     * Get connection resource id
     *
     * @access private
     * @param ConnectionInterface $conn
     * @return string
     */
    private function getResourceId(ConnectionInterface $conn)
    {
        return $conn->resourceId;
    }
}
