<?php

namespace app\commands;

use app\models\Message;
use app\models\User;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Yii;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\Console;

class ChatController extends Controller
{
    public $connections = array();

    public function actionRun() {
        $this->stdout("Run it\n", Console::BG_GREEN);
        $worker = new Worker(Yii::$app->params['websocketAddress']);
        $worker->onWebSocketConnect = [$this, 'onConnect'];
        $worker->onClose = [$this, 'onClose'];
        $worker->onMessage = [$this, 'onMessage'];
        $worker->onError = [$this, 'onError'];

        Worker::runAll();
    }

    public function onConnect(TcpConnection $connection) {

        $this->stdout("Start connection\n");

        $token = $_GET['token'];

        if(!isset($_GET['token']) || trim($token) == '') {
            $this->onClose($connection);
            return false;
        }

        try {
            $user = User::findIdentityByAccessToken($token);
            if (is_null($user)) {
                throw new Exception("No user found");
            }
            $this->connections[$connection->id]['connection'] = $connection;
            $this->connections[$connection->id]['token'] = $_GET['token'];
            $this->connections[$connection->id]['user_id'] = $user->id;
            $this->stdout("New connection added by user :: " . $user->username . "\n" );
        } catch (Exception $e) {
            $this->onError($connection, "Error while getting user :: " . $e->getMessage());
        }
    }

    public function onError(TcpConnection $connection, $message) {
        $this->stdout("$message\n");
        $connection->close();
   }

    public function onClose(TcpConnection $connection) {
        $this->stdout("Connection closed \n");

        //removing from connections array
        foreach ($this->connections as $key => $value) {
            if ($value['connection']->id == $connection->id) {
                unset($this->connections[$key]);
                break;
            }
        }
    }

    public function onMessage(TcpConnection $connection, string $data)
    {
        $payload = json_decode($data, true);

        switch ($payload['method']) {
            case 'sendMessage':
                $response = $this->sendMessage($connection, $payload);
                break;
            case 'sendMessageToUser':
                //TODO add method
            default:
                $response = ['message' => 'method not specified'];
                break;
        }

        $connection->send(json_encode(
            [
                'method' => $payload['method'],
                'data' => $response,
            ])
        );
    }

    private function sendMessage(TcpConnection $connection, $payload) {
        $message = new Message();
        $user = User::findOne(['id' => $this->connections[$connection->id]['user_id']]);
        $message->username = $user->username ;
        $message->text = $payload['data']['text'];
        $message->create_time = time();
        $message->save();

        $this->stdout("new message from user :: " . $user->username . " \n");

        $resp = json_encode(
            [
                'method' => 'sendMessage',
                'data' => [
                    'message' => $message->toArray(),
                ]
            ]);

        foreach ($this->connections as $conn) {
            // отправляем всем, кроме текущего
            if ($conn['connection']->id == $connection->id) {
                continue;
            }
            $conn['connection']->send($resp);
        }

        return ['message' => 'Message sent to all online user'];
    }
}
