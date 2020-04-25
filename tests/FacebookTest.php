<?php

namespace SohaibIlyas\FacebookPhpSdk\Tests;

use Exception;
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
            'redirect_uri' => 'https://yourdomain.com'
        ]);

        $this->accessToken = 'your-page-access-token';
    }

    /** @test */
    public function it_throws_exception_if_config_array_not_provided()
    {
        $this->expectExceptionMessage('config array not provided to contructer');
        $facebook = new Facebook();
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
    public function it_checks_if_logged_in()
    {
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertTrue($this->facebook->loggedIn());
    }

    /** @test */
    public function it_gets_login_url()
    {
        $this->assertStringContainsString('/dialog/oauth?client_id=', $this->facebook->getLoginUrl());
    }

    /** @test */
    public function it_throws_an_exception_using_invalid_access_token_on_get_request()
    {
        $this->expectExceptionReuse();
        $this->facebook->setAccessToken($this->accessToken);
        $this->facebook->get('/me')->getStatusCode();
    }

    /** @test */
    public function it_throws_an_exception_using_invalid_access_token_on_post_request()
    {
        $this->expectExceptionReuse();
        $this->facebook->setAccessToken($this->accessToken);
        $this->facebook->post('/me/feed', ['message' => uniqid()])->getStatusCode();
    }

    /** @test */
    public function it_throws_an_exception_using_invalid_access_token_on_returning_response_object()
    {
        $this->expectExceptionReuse();
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertIsObject($this->facebook->get('/me')->toObject());
    }

    /** @test */
    public function it_throws_an_exception_using_invalid_access_token_on_returning_response_array()
    {
        $this->expectExceptionReuse();
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertIsArray($this->facebook->get('/me')->toArray());
    }

    /** @test */
    public function it_throws_an_exception_using_invalid_access_token_on_returning_response_json()
    {
        $this->expectExceptionReuse();
        $this->facebook->setAccessToken($this->accessToken);
        $this->assertJson($this->facebook->get('/me')->toJson());
    }

    private function expectExceptionReuse() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid access token');
        $this->expectExceptionCode(400);
    }

    protected function tearDown(): void
    {
        $this->facebook = null;
        $this->accessToken = null;
    }
}
