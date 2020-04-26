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
    'redirect_uri' => 'https://sohaibilyas.com'
]);

if ($facebook->loggedIn() || isset($_SESSION['access_token'])) {
    if (isset($_SESSION['access_token'])) {
        $facebook->setAccessToken($_SESSION['access_token']);
    }
    
    $_SESSION['access_token'] = $facebook->getAccessToken();

    $response = $facebook->get('/me?fields=id,name,email')->toJson();

    echo $response;
} else {
    echo $facebook->getLoginUrl(['email']);
}
```

## License
[MIT](https://choosealicense.com/licenses/mit/)
