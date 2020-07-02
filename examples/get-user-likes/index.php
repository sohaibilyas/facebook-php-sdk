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

    $response = $facebook->get('/me/likes')->toArray();

    print_r($response);
} else {
    echo "<a href='" . $facebook->getLoginUrl(['email', 'user_likes']) . "'>Login with Facebook</a>";
}
