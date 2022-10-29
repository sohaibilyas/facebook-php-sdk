<?php

namespace SohaibIlyas\FacebookPhpSdk;

use Exception;
use GuzzleHttp\Client;
use stdClass;

class Facebook
{
    const BASE_URL = 'https://graph.facebook.com/';

    private $config;
    private $accessToken;
    private $client;
    private $state;
    private $responseType = 'json';
    private $response;
    private $apiVersion = 'v15.0';

    public function __construct(array $config)
    {
        if (!isset($config['access_token'])) {
            if (! isset($config['app_id']) || $config['app_id'] == '') {
                throw new Exception('app_id not set in config array');
            }

            if (! isset($config['app_secret']) || $config['app_secret'] == '') {
                throw new Exception('app_secret not set in config array');
            }

            if (! isset($config['redirect_url']) || $config['redirect_url'] == '') {
                throw new Exception('redirect_url not set in config array');
            }
        }
        
        if (isset($config['api_version']) && $config['api_version'] != '') {
            $this->apiVersion = $config['api_version'];
        }
        
        if (isset($config['response_type']) && $config['response_type'] != '') {
            $this->responseType = $config['response_type'];
        }

        if (isset($config['access_token']) && $config['access_token'] != '') {
            $this->accessToken = $config['access_token'];
        }

        $this->config = $config;
        $this->client = new Client(['base_uri' => self::BASE_URL]);
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

    public function setResponseType(string $type = 'json')
    {
        $this->responseType = $type;
    }

    public function getUser(string $userId = 'me', array $fields = ['id', 'name'], string $accessToken = null)
    {
        return $this->get($userId.'?fields='.implode(',', $fields), $accessToken);
    }

    // businesses
    public function getBusinesses(string $userId = 'me', array $fields = ['id', 'name'], int $limit = 100, string $accessToken = null)
    {
        return $this->get($userId.'/businesses?fields='.implode(',', $fields).'&limit='.$limit, $accessToken);
    }

    // pages
    public function getPages(string $userId = 'me', array $fields = ['id', 'name'], int $limit = 100, string $accessToken = null)
    {
        return $this->get($userId.'/accounts?fields='.implode(',', $fields).'&limit='.$limit, $accessToken);
    }

    // adaccounts
    public function getAdAccounts(string $userId = 'me', array $fields = ['id', 'name'], int $limit = 100, string $accessToken = null)
    {
        return $this->get($userId.'/adaccounts?fields='.implode(',', $fields).'&limit='.$limit, $accessToken);
    }

    // campaigns
    public function getCampaigns(string $id, array $fields = ['id', 'name', 'status'], int $limit = 100, string $accessToken = null)
    {
        return $this->get($id.'/campaigns?fields='.implode(',', $fields).'&limit='.$limit, $accessToken);
    }

    public function createCampaign(string $id, array $campaignData = ['status' => 'PAUSED'], string $accessToken = null)
    {
        return $this->post($id.'/campaigns', $campaignData, $accessToken);
    }

    // adsets
    public function getAdsets(string $id, array $fields = ['id', 'name', 'status'], int $limit = 100, string $accessToken = null)
    {
        return $this->get($id.'/adsets?fields='.implode(',', $fields).'&limit='.$limit, $accessToken);
    }

    public function createAdset(string $adAccountId, array $adsetData = ['status' => 'PAUSED'], string $accessToken = null)
    {
        return $this->post($adAccountId.'/adsets', $adsetData, $accessToken);
    }

    public function handleRedirect(callable $callable)
    {
        if (isset($_GET['code'], $_GET['state'])) {
            $this->accessToken = json_decode($this->client->get($this->apiVersion.'/oauth/access_token?client_id='.$this->config['app_id'].'&client_secret='.$this->config['app_secret'].'&redirect_uri='.$this->config['redirect_url'].'&code='.$_GET['code'])->getBody()->getContents())->access_token;

            $user = json_decode($this->client->get($this->apiVersion.'/me?fields=id,first_name,last_name,name,picture.height(150).width(150)&access_token='.$this->accessToken)->getBody()->getContents());

            $response = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->name,
                'profile_picture_url' => $user->picture->data->url,
                'access_token' => $this->accessToken,
            ];

            if ($this->responseType == 'json') {
                $response = json_encode($response);
            }
            if ($this->responseType == 'object') {
                $response = json_decode(json_encode($response));
            }

            $callable($response);
        }
    }

    public function getLoginUrl(array $permissions = ['public_profile', 'email'])
    {
        $permissions = implode(',', $permissions);
        $this->state = bin2hex(random_bytes(20));

        return 'https://www.facebook.com/'.$this->apiVersion.'/dialog/oauth?client_id='.$this->config['app_id'].'&redirect_uri='.$this->config['redirect_url'].'&scope='.$permissions.'&state='.$this->state;
    }

    public function get(string $path, string $accessToken = null)
    {
        $path = $path[0] == '/' ? $path : '/'.$path;

        $this->accessToken = $accessToken ? $accessToken : $this->accessToken;

        $separator = '?';
        if (strpos($path, '?') != false) {
            $separator = '&';
        }

        try {
            $this->response = $this->client->get($this->apiVersion.$path.$separator.'access_token='.$this->accessToken);

            return $this->getResponse();
        } catch (\Exception $e) {
            $this->response = $e->getResponse();

            return $this->getResponse();
        }
    }

    public function post(string $path, array $params, string $accessToken = null)
    {
        $path = $path[0] == '/' ? $path : '/'.$path;

        $this->accessToken = $accessToken ? $accessToken : $this->accessToken;

        $separator = '?';
        if (strpos($path, '?') != false) {
            $separator = '&';
        }

        try {
            $this->response = $this->client->post($this->apiVersion.$path.$separator.'access_token='.$this->accessToken, [
                'json' => $params,
            ]);

            return $this->getResponse();
        } catch (\Exception $e) {
            $this->response = $e->getResponse();

            return $this->getResponse();
        }
    }

    public function delete(string $path, string $accessToken = null)
    {
        $path = $path[0] == '/' ? $path : '/'.$path;

        $this->accessToken = $accessToken ? $accessToken : $this->accessToken;

        $separator = '?';
        if (strpos($path, '?') != false) {
            $separator = '&';
        }

        try {
            $this->response = $this->client->delete($this->apiVersion.$path.$separator.'access_token='.$this->accessToken);

            return $this->getResponse();
        } catch (\Exception $e) {
            $this->response = $e->getResponse();

            return $this->getResponse();
        }
    }

    private function getResponse()
    {
        if ($this->responseType == 'json') {
            return $this->toJson();
        }
        if ($this->responseType == 'object') {
            return $this->toObject();
        }
        if ($this->responseType == 'array') {
            return $this->toArray();
        }
    }

    public function toObject(): stdClass
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
