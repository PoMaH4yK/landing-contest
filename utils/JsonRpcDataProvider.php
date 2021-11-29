<?php
namespace app\utils;

use yii\base\Exception;
use yii\data\BaseDataProvider;
use yii\helpers\Json;

class JsonRpcDataProvider extends BaseDataProvider
{
    private $_url;
    private $_login;
    private $_password;
    private $_responseData;
    private $_key = 'id';

    public function getResponseData()
    {
        if (!$this->_responseData) {
            $this->_request();
        }

        return $this->_responseData;
    }

    public function prepareKeys($models)
    {
        return array_map(function ($model) {
            return $model[$this->_key];
        }, $models);
    }

    public function prepareModels()
    {
        return $this->getResponseData()['items'];
    }

    public function prepareTotalCount()
    {
        if ($this->pagination) {
            $this->pagination->totalCount = $this->getResponseData()['total'];
        }

        return $this->getResponseData()['total'];
    }

    public function setKey($value)
    {
        $this->_key = $value;
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

    private function _request()
    {
        $params = [
            \Yii::$app->request->get('page', 1),
            $this->getPagination()->getLimit(),
        ];

        if ($this->getSort()->orders) {
            $params[] = $this->getSort()->orders;
        }

        $ch = curl_init($this->_url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_URL => $this->_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => Json::encode([
                'jsonrpc' => '2.0',
                'id' => uniqid(''),
                'method' => 'get-activities',
                'params' => $params
            ])
        ]);

        if ($this->_login && $this->_password) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->_login . ':' . $this->_password);
        }

        $res = @Json::decode(curl_exec($ch));

        if (!empty($res['error'])) {
            throw new Exception($res['error']['message']);
        }

        $this->_responseData = $res['result'];
    }
}
