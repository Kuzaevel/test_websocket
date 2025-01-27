<?php

namespace app\commands;

use app\models\Book;
use app\models\Chat;
use app\models\Message;
use app\models\User;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Yii;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\Console;
use yii\web\Request;

class ChatController extends Controller
{
    public $connections = array();

    public function actionRun() {

        $this->stdout("Run it  ", Console::BG_GREEN);

        $worker = new Worker('websocket://test.local:8080');
        $worker->onWebSocketConnect = [$this, 'onConnect'];
        $worker->onClose = [$this, 'onClose'];
        $worker->onMessage = [$this, 'onMessage'];

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
            if(is_null($user)) {
                $this->stdout("No user found");
                $this->onClose($connection);
                return false;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();  //throw $e;
            $this->stdout("Error while getting user :: " . $message . " \n");
        } catch (\Throwable $e) {
            $message = $e->getMessage();  //throw $e;
            $this->stdout("Error while getting user :: " . $message . " \n");
        }
        $this->connections[$connection->id]['connection'] = $connection;
        $this->connections[$connection->id]['token'] = $_GET['token'];
        $this->connections[$connection->id]['user_id'] = $user->id;

        $this->stdout("\nNew connection added by user :: " . $user->username . "\n" );
    }

    public function onClose(TcpConnection $connection) {
        $this->stdout("Connection closed \n");
    }

    public function onMessage(TcpConnection $connection, string $data)
    {
        $payload = json_decode($data, true);
        switch ($payload['method']) {
            case 'sendMessage':
                $response = $this->sendMessage($connection, $payload);
                break;
            default:
                $response = null; // или какое-то другое значение по умолчанию
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
                'method' => 'newMessage',
                'data' => [
                    'message' => $message->toArray(),
                ]
            ]);

        foreach ($this->connections as $conn) {
            // отправляем всем, кроме текущего
            if ($conn['connection']->id === $connection->id) {
                continue;
            }

            $conn['connection']->send($resp);
        }

        return [
            'message' => $message->toArray()
        ];
    }
}
