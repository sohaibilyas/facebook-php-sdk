<?php

namespace SohaibIlyas\FacebookPhpSdk\Tests;

use PHPUnit\Framework\TestCase;
use SohaibIlyas\FacebookPhpSdk\Facebook;

class FacebookTest extends TestCase
{
    private $facebook;
    private $accessToken;

    protected function setUp(): void
    {
        $this->facebook = new Facebook([
            'app_id' => '123456789',
            'app_secret' => 'abcdefgh123456789',
            'redirect_url' => 'https://yourdomain.com',
        ]);

        $this->accessToken = 'your-page-access-token';
    }

    /** @test */
    public function it_returns_config_array()
    {
        $this->assertIsArray($this->facebook->getConfig());
    }

    /** @test */
    public function it_saves_access_token()
    {
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertNotEmpty($this->facebook->getAccessToken());
    }

    /** @test */
    public function it_returns_access_token()
    {
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertNotEmpty($this->facebook->getAccessToken());
    }

    /** @test */
    public function it_gets_login_url()
    {
        $this->assertStringContainsString('/dialog/oauth?client_id=', $this->facebook->getLoginUrl());
    }

    /** @test */
    public function it_throws_an_error_using_invalid_access_token_on_get_request()
    {
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertStringContainsString('Invalid OAuth access token', $this->facebook->get('/me'));
    }

    /** @test */
    public function it_throws_an_error_using_invalid_access_token_on_post_request()
    {
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertStringContainsString('Invalid OAuth access token', $this->facebook->post('/me/feed', ['message' => uniqid()]));
    }

    /** @test */
    public function it_return_response_as_json()
    {
        $this->facebook->setResponseType('json');
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertJson($this->facebook->get('/me'));
    }

    protected function tearDown(): void
    {
        $this->facebook = null;
        $this->accessToken = null;
    }
}
