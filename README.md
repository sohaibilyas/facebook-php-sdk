# Facebook PHP SDK

Easy to use PHP SDK to interact with Facebook API.

## Installation

Use the composer to install.

```bash
composer require sohaibilyas/facebook-php-sdk
```

## Usage

```php
<?php
session_start();

require './vendor/autoload.php';

use SohaibIlyas\FacebookPhpSdk\Facebook;

$facebook = new Facebook([
    'app_id' => 'app-id-here',
    'app_secret' => 'app-secret-here',
    'redirect_url' => 'https://sohaibilyas.com'
]);

$facebook->handleRedirect(function($user) {
    // save access token to use it later e.g. session, database
    $_SESSION['access_token'] = $user->access_token;
});

// checking if access token is saved otherwise show login with facebook url
if (isset($_SESSION['access_token'])) {
    // setting default access token for all requests
    $facebook->setAccessToken($_SESSION['access_token']);

    // default response type e.g. object, json, array
    $facebook->setResponseType('json');

    // getting facebook user information
    print_r($facebook->getAdAccounts());
} else {
    echo $facebook->getLoginUrl(['email', 'ads_management', 'business_management', 'ads_read']);exit;
}
```

## License
[MIT](https://choosealicense.com/licenses/mit/)
