<?php
namespace app\behaviors;

use yii\base\Behavior;
use yii\helpers\Json;
use yii\log\Logger;
use yii\web\Controller;

class LogBehavior extends Behavior
{
    private $_url = 'http://activity-contest/api';
    private $_login = 'api';
    private $_password = '';

    public function afterAction()
    {
        $requestId = uniqid('', true);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => Json::encode([
                'jsonrpc' => '2.0',
                'id' => $requestId,
                'method' => 'log-activity',
                'params' => [\Yii::$app->request->getUrl(), date('Y-m-d')]
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_USERPWD => $this->_login . ':' . $this->_password
        ]);

        $res = @Json::decode(curl_exec($ch));

        if (empty($res['result']) && !empty($res['error'])) {
            \Yii::getLogger()->log($res['error']['message'], Logger::LEVEL_ERROR, 'log-behavior');
        }
    }

    public function events()
    {
        return [
            Controller::EVENT_AFTER_ACTION => 'afterAction'
        ];
    }

    public function getLogin()
    {
        return $this->_login;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setLogin($value)
    {
        $this->_login = $value;
    }

    public function setPassword($value)
    {
        $this->_password = $value;
    }

    public function setUrl($value)
    {
        $this->_url = $value;
    }
}
