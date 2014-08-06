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

Tiendy_Configuration::client_id('testclient');
Tiendy_Configuration::client_secret('testsecret');
Tiendy_Configuration::client_shared('your_public_key');
Tiendy_Configuration::shop('prueba'); // from: prueba.mitiendy.com

$result = Tiendy_Product::all(array('limit' => 50, 'page' => 2));

if ($result->success) {
    print_r("success!: " . $result->products[0]->title);
} else if ($result->errors) {
    print_r("Error processing API Call");
} else {
    print_r("Validation errors: \n");
}

?>
```


## License

See the LICENSE file.