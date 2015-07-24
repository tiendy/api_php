Tiendy API PHP Client Library
=======

Tiendy API PHP Client library provides integration access to the Tiendy Store API.


## Dependencies

PHP version >= 5.2.1 required.

The following PHP extensions are required:

* curl
* hash
* openssl

## Quick Start Example

```php
<?php
require_once 'PATH_TO_TIENDY/lib/Tiendy.php';
require_once 'PATH_TO_TIENDY/lib/Tiendy/Configuration.php';
require_once 'PATH_TO_TIENDY/lib/Tiendy/Exception.php';
require_once('PATH_TO_TIENDY/lib/Tiendy/Exception/ValidationsFailed.php');
require_once('PATH_TO_TIENDY/lib/Tiendy/Exception/Authentication.php');
require_once('PATH_TO_TIENDY/lib/Tiendy/Util.php');
require_once 'PATH_TO_TIENDY/lib/Tiendy/Instance.php';
require_once 'PATH_TO_TIENDY/lib/Tiendy/ResourceCollection.php';
require_once 'PATH_TO_TIENDY/lib/Tiendy/Order.php';
require_once 'PATH_TO_TIENDY/lib/Tiendy/Product.php';
require_once 'PATH_TO_TIENDY/lib/Tiendy/Http.php';
require_once 'PATH_TO_TIENDY/lib/Tiendy/Version.php';

Tiendy_Configuration::client_id('testclient');
Tiendy_Configuration::client_secret('testsecret');
Tiendy_Configuration::shop('prueba'); // from: prueba.mitiendy.com
$result = Tiendy_Product::all();
if ($result) {
    foreach ($result as $product) {
        echo $product['title'] . "\n";
    }
} else {
    print_r("Error processing API Call");
}
```


## License

See the LICENSE file.