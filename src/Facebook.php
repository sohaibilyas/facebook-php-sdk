<?php

namespace SohaibIlyas\FacebookPhpSdk;

use Exception;
use GuzzleHttp\Client;

class Facebook
{
    const BASE_URL = 'https://graph.facebook.com';

    private $config;
    private $accessToken;
    private $client;
    private $state;
    private $response;
    private $graphVersion = 'v6.0';

    public function __construct(array $config = null)
    {
        if (! $config) {
            throw new Exception('config array not provided to contructer');
        }

        if (! isset($config['app_id']) || $config['app_id'] == '') {
            throw new Exception('app_id not set in config array');
        }

        if (! isset($config['app_secret']) || $config['app_secret'] == '') {
            throw new Exception('app_secret not set in config array');
        }

        if (! isset($config['redirect_uri']) || $config['redirect_uri'] == '') {
            throw new Exception('redirect_uri not set in config array');
        }

        if (isset($config['graph_version']) && $config['graph_version'] != '') {
            $this->graphVersion = $config['graph_version'];
        }

        $this->config = $config;
        $this->client = new Client(['base_uri' => self::BASE_URL.'/'.$this->graphVersion]);
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
        if (! empty($this->accessToken)) {
            return true;
        }

        if (isset($_GET['code'], $_GET['state'])) {
            if ($_SESSION['state'] != $_GET['state']) {
                throw new Exception('state token did not match');
            }

            $this->accessToken = json_decode($this->client->get('/oauth/access_token?client_id='.$this->config['app_id'].'&client_secret='.$this->config['app_secret'].'&redirect_uri='.$this->config['redirect_uri'].'&code='.$_GET['code'])->getBody())->access_token;

            return true;
        }

        return false;
    }

    public function getLoginUrl(array $permissions = ['default'])
    {
        $permissions = implode(',', $permissions);
        $this->state = bin2hex(random_bytes(20));
        $_SESSION['state'] = $this->state;

        return 'https://www.facebook.com/'.$this->graphVersion.'/dialog/oauth?client_id='.$this->config['app_id'].'&redirect_uri='.$this->config['redirect_uri'].'&scope='.$permissions.'&state='.$this->state;
    }

    public function post(string $path, array $params, string $accessToken = null)
    {
        $path = $path[0] == '/' ? $path : '/'.$path;

        $this->accessToken = $accessToken ? $accessToken : $this->accessToken;

        $separator = '?';
        if (strpos($path, '?') != false) {
            $separator = '&';
        }

        $this->response = $this->client->post($path.$separator.'access_token='.$this->accessToken, [
            'json' => $params,
        ]);

        return $this;
    }

    public function get(string $path, string $accessToken = null)
    {
        $path = $path[0] == '/' ? $path : '/'.$path;

        $this->accessToken = $accessToken ? $accessToken : $this->accessToken;

        $separator = '?';
        if (strpos($path, '?') != false) {
            $separator = '&';
        }

        $this->response = $this->client->get($path.$separator.'access_token='.$this->accessToken);

        return $this;
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function toObject()
    {
        return json_decode($this->response->getBody()->getContents());
    }

    public function toArray(): array
    {
        return json_decode($this->response->getBody()->getContents(), true);
    }

    public function toJson(): string
    {
        return $this->response->getBody()->getContents();
    }
}
