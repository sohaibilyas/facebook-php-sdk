<?php

session_start();

require './vendor/autoload.php';

use SohaibIlyas\FacebookPhpSdk\Facebook;

$facebook = new Facebook([
    'app_id' => 'your_app_id_here',
    'app_secret' => 'your_app_secret_here',
    'redirect_uri' => 'your_redirect_uri_here',
]);

if ($facebook->loggedIn() || isset($_SESSION['access_token'])) {
    if (isset($_SESSION['access_token'])) {
        $facebook->setAccessToken($_SESSION['access_token']);
    }

    $_SESSION['access_token'] = $facebook->getAccessToken();

    $response = $facebook->get('/me/feed?limit=5');

    $firstPage = $response->toArray();
    $secondPage = $response->nextPage()->toArray();

    // print_r($firstPage);
    print_r($secondPage);
} else {
    echo "<a href='".$facebook->getLoginUrl(['user_posts'])."'>Login with Facebook</a>";
}
