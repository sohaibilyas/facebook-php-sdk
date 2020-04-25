<?php

namespace SohaibIlyas\FacebookPhpSdk;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Facebook
{
    const BASE_URL = 'https://graph.facebook.com';

    private $config = null;
    private $accessToken = null;
    private $client = null;
    private $state = null;
    private $response = null;
    private $graphVersion = 'v6.0';

    public function __construct(array $config = null)
    {

        if (!$config) {
            throw new Exception('config array not provided to contructer');
        }

        if (!isset($config['app_id']) || $config['app_id'] == '') {
            throw new Exception('app_id not set in config array');
        }

        if (!isset($config['app_secret']) || $config['app_secret'] == '') {
            throw new Exception('app_secret not set in config array');
        }

        if (!isset($config['redirect_uri']) || $config['redirect_uri'] == '') {
            throw new Exception('redirect_uri not set in config array');
        }

        if (isset($config['graph_version']) && $config['graph_version'] != '') {
            $this->graphVersion = $config['graph_version'];
        }

        $this->config = $config;
        $this->client = new Client(['base_uri' => self::BASE_URL . '/' . $this->graphVersion]);
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function loggedIn()
    {
        if (isset($this->accessToken) && $this->accessToken != '') {
            return true;
        }

        if (isset($_GET['code'], $_GET['state'])) {
            if ($_SESSION['state'] != $_GET['state']) {
                throw new Exception('state token did not match');
            }
            
            try {
                $this->accessToken = json_decode($this->client->get('/oauth/access_token?client_id=' . $this->config['app_id'] . '&client_secret=' . $this->config['app_secret'] . '&redirect_uri=' . $this->config['redirect_uri'] . '&code=' . $_GET['code'])->getBody())->access_token;
            } catch (ClientException $e) {
                echo $e->getMessage();
            }

            return true;
        }

        return false;
    }

    public function getLoginUrl(array $permissions = ['default'])
    {
        if (count($permissions) <= 0) {
            throw new Exception('did not select any permissions');
        }

        $permissions = implode(',', $permissions);
        $this->state = bin2hex(random_bytes(20));
        $_SESSION['state'] = $this->state;
        return 'https://www.facebook.com/' . $this->graphVersion . '/dialog/oauth?client_id=' . $this->config['app_id'] . '&redirect_uri=' . $this->config['redirect_uri'] . '&scope=' . $permissions . '&state=' . $this->state;
    }

    public function post(string $path, array $params, string $accessToken = null)
    {
        $path = $path[0] == '/' ? $path : '/' . $path;

        $this->accessToken = $accessToken ? $accessToken : $this->accessToken;

        $separator = '?';
        if (strpos($path, '?') != false) {
            $separator = '&';
        }

        try {
            $this->response = $this->client->post($path . $separator . 'access_token=' . $this->accessToken, [
                'json' => $params
            ]);
            return $this;
        } catch (ClientException $e) {
            throw new Exception('Invalid access token', 400);
        }
    }

    public function get(string $path, string $accessToken = null)
    {
        $path = $path[0] == '/' ? $path : '/' . $path;

        $this->accessToken = $accessToken ? $accessToken : $this->accessToken;

        $separator = '?';
        if (strpos($path, '?') != false) {
            $separator = '&';
        }

        try {
            $this->response = $this->client->get($path . $separator . 'access_token=' . $this->accessToken);
            return $this;
        } catch (ClientException $e) {
            throw new Exception('Invalid access token', 400);
        }
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function toObject()
    {
        return json_decode($this->response->getBody()->getContents());
    }

    public function toArray()
    {
        return json_decode($this->response->getBody()->getContents(), true);
    }

    public function toJson()
    {
        return $this->response->getBody()->getContents();
    }
}
