<?php

namespace amo;

use Database;
use Exception;

class AccessToken
{

    private string $token;
    private array $config;

    public function __construct()
    {
        $this->config = require_once "config.php";

        $db = new Database\Database();
        $sql = "SELECT access_token FROM access_tokens LIMIT 1";
        $tokenData = $db->query($sql);
        if($tokenData)
            $this->token = $tokenData[0]['access_token'];
    }

    public function createToken(): void
    {
        if(isset($this->token))
            return;

        $subdomain = 'vbudai297'; //Поддомен нужного аккаунта
        $link = "https://$subdomain.amocrm.ru/oauth2/access_token"; //Формируем URL для запроса

        /** Соберем данные для запроса */
        $data = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => 'authorization_code',
            'code' => $this->config['code'],
            'redirect_uri' => $this->config['redirect_uri'],
        ];

        $response = $this->getApiToken($data);

        $access_token = $response['access_token']; //Access токен
        $refresh_token = $response['refresh_token']; //Refresh токен
        $token_type = $response['token_type']; //Тип токена
        $expires_in = $response['expires_in']; //Через сколько действие токена истекает

        // Сохрание токена в бд
        $sql = "INSERT INTO access_tokens (access_token, refresh_token, token_type, expires_in) VALUES ('$access_token', '$refresh_token', '$token_type', '$expires_in')";
        $db = new Database\Database();
        $db->query($sql);

        $this->token = $access_token;
    }

    public function refreshToken(): void
    {
        if(!isset($this->token))
            return ;

        /** Соберем данные для запроса */
        $data = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => 'refresh_token',
            'code' => $this->config['code'],
            'redirect_uri' => $this->config['redirect_uri'],
        ];

        $response = $this->getApiToken($data);

        $access_token = $response['access_token']; //Access токен
        $refresh_token = $response['refresh_token']; //Refresh токен
        $token_type = $response['token_type']; //Тип токена
        $expires_in = $response['expires_in']; //Через сколько действие токена истекает

        // Сохрание токена в бд
        $sql = "UPDATE SET access_tokens access_token='$access_token', refresh_token='$refresh_token', token_type='$token_type', expires_in='$expires_in' WHERE access_token='$this->token'";
        $db = new Database\Database();
        $db->query($sql);

        $this->token = $access_token;
    }

    public function getToken() : string
    {
        return $this->token;
    }

    private function getApiToken(array $data)
    {
        $subdomain = 'vbudai297'; //Поддомен нужного аккаунта
        $link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];


        try {
            /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
            if ($code < 200 || $code > 204) {
                throw new Exception($errors[$code] ?? 'Undefined error', $code);
            }
        }
        catch(\Exception $e) {
            echo '<pre>';
            var_dump($out);
            echo '</pre>';

            die('<br>Ошибка: ' . $e->getMessage() . PHP_EOL . '<br>Код ошибки: ' . $e->getCode());
        }

        return json_decode($out, true);
    }

}