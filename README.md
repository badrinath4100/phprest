# Getting started

PHP REST is a simple and light weight framework for rest api development


###### Install PHP REST

1. Download REST PHP
2. Unzip it and copy into your PHP project directory.

ADD ROUTE CONFIGURATION IN www/index.php:

www/index.php
```php
require '../vendor/autoload.php';
$app = new System\Rest();

$app->service("get:/test", [], [], "Application/Demo:test");
$app->run();

```
Application/Demo.php

```php

namespace Application;

use System\Input;
use System\Output;
use System\Database;

class Demo {

    /**
     * test method which
     * @return array
     */
    public function test() {

        $response = "Hello world";

        return Output::response_object(false, 200, response);
    }

}


```


Available Validators
--------------------
* required `Ensures the specified key value exists and is not empty`
* valid_email `Checks for a valid email address`
* max_len,n `Checks key value length, makes sure it's not longer than the specified length. n = length parameter.`
* min_len,n `Checks key value length, makes sure it's not shorter than the specified length. n = length parameter.`
* exact_len,n `Ensures that the key value length precisely matches the specified length. n = length parameter.`
* alpha `Ensure only alpha characters are present in the key value (a-z, A-Z)`
* alpha_numeric `Ensure only alpha-numeric characters are present in the key value (a-z, A-Z, 0-9)`
* alpha_dash `Ensure only alpha-numeric characters + dashes and underscores are present in the key value (a-z, A-Z, 0-9, _-)`
* alpha_space `Ensure only alpha-numeric characters + spaces are present in the key value (a-z, A-Z, 0-9, \s)`
* numeric `Ensure only numeric key values`
* integer `Ensure only integer key values`
* boolean `Checks for PHP accepted boolean values, returns TRUE for "1", "true", "on" and "yes"`
* float `Checks for float values`
* valid_url `Check for valid URL or subdomain`
* url_exists `Check to see if the url exists and is accessible`
* valid_ip `Check for valid generic IP address`
* valid_ipv4 `Check for valid IPv4 address`
* valid_ipv6 `Check for valid IPv6 address`
* valid_cc `Check for a valid credit card number (Uses the MOD10 Checksum Algorithm)`
* valid_name `Check for a valid format human name`
* contains,n `Verify that a value is contained within the pre-defined value set`
* contains_list,n `Verify that a value is contained within the pre-defined value set. The list of valid values must be provided in semicolon-separated list format (like so: value1;value2;value3;..;valuen). If a validation error occurs, the list of valid values is not revelead (this means, the error will just say the input is invalid, but it won't reveal the valid set to the user.`
* doesnt_contain_list,n `Verify that a value is not contained within the pre-defined value set. Semicolon (;) separated, list not outputted. See the rule above for more info.`
* street_address `Checks that the provided string is a likely street address. 1 number, 1 or more space, 1 or more letters`
* iban `Check for a valid IBAN`
* min_numeric `Determine if the provided numeric value is higher or equal to a specific value`
* max_numeric `Determine if the provided numeric value is lower or equal to a specific value`
* date `Determine if the provided input is a valid date (ISO 8601)`
* starts `Ensures the value starts with a certain character / set of character`
* phone_number `Validate phone numbers that match the following examples: 555-555-5555 , 5555425555, 555 555 5555, 1(519) 555-4444, 1 (519) 555-4422, 1-555-555-5555`
* regex `You can pass a custom regex using the following format: 'regex,/your-regex/'`
* valid_json_string `validate string to check if it's a valid json format`

Available Filters
-----------------
Filters can be any PHP function that returns a string. You don't need to create your own if a PHP function exists that does what you want the filter to do.

* sanitize_string `Remove script tags and encode HTML entities, similar to GUMP::xss_clean();`
* urlencode `Encode url entities`
* htmlencode `Encode HTML entities`
* sanitize_email `Remove illegal characters from email addresses`
* sanitize_numbers `Remove any non-numeric characters`
* sanitize_floats `Remove any non-float characters`
* trim `Remove spaces from the beginning and end of strings`
* base64_encode `Base64 encode the input`
* base64_decode `Base64 decode the input`
* sha1 `Encrypt the input with the secure sha1 algorithm`
* md5 `MD5 encode the input`
* noise_words `Remove noise words from string`
* json_encode `Create a json representation of the input`
* json_decode `Decode a json string`
* rmpunctuation `Remove all known punctuation characters from a string`
* basic_tags `Remove all layout orientated HTML tags from text. Leaving only basic tags`
* whole_number `Ensure that the provided numeric value is represented as a whole number`
* ms_word_characters `Converts MS Word special characters [“”‘’–…] to web safe characters`
