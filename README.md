# Facebook PHP SDK

Easy to use PHP SDK to interact with Facebook API.

## Installation

Use the composer to install.

```bash
composer require sohaibilyas/facebook-php-sdk
```

## Login with Facebook

```php
<?php

require './vendor/autoload.php';

use SohaibIlyas\FacebookPhpSdk\Facebook;

$facebook = new Facebook([
    'app_id' => 'app-id-here',
    'app_secret' => 'app-secret-here',
    'redirect_url' => 'https://sohaibilyas.com'
]);

$facebook->setResponseType('object');

$facebook->handleRedirect(function($facebookUser) {
    // save access token in database for later use
    echo $facebookUser->access_token;
});

// get login with facebook url
echo $facebook->getLoginUrl(['email']);
```

## Using saved access token

```php
<?php

require './vendor/autoload.php';

use SohaibIlyas\FacebookPhpSdk\Facebook;

$facebook = new Facebook([
    'app_id' => 'app-id-here',
    'app_secret' => 'app-secret-here',
    'redirect_url' => 'https://sohaibilyas.com'
]);

$facebook->setResponseType('json');

// set facebook access token
$facebook->setAccessToken('facebook-user-access-token');

echo $facebook->get('/me?fields=id,first_name,last_name,name');
```

## License
[MIT](https://choosealicense.com/licenses/mit/)
