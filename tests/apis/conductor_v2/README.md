To be able to use the php scripts in this folder you need to create a settings.php file in the same folder with the following content:
```
<?php
// phpcs:ignoreFile

/**
 * @file
 * The variables to authenticate on ACM.
 */

global $config;

$config = [
  'org_uuid' => '2888baa2-9ef5-4ab2-bfb6-7b0b195b1b68',
  'url' => 'https://api.eu-west-1.prod.acm.acquia.io/v2/',
  'hmac_key' => 'xxxxx',
  'hmac_secret' => 'xxxx',
];
```

And then replace hmac_key and hmac_secret with proper values.
Hint: looking at Postman variables or asking ACM product team could help.



To create the entire ACM V2 configuration for one brand on one environment:
* Update the variables.php file with appropriate values.
* Run `php createStream.php`
* Report the displayed values into https://docs.google.com/spreadsheets/d/15Mn5Ql7TPZ6AXJFatgoMIlgLEuKYQvnVyLhYw87qEr4/edit#gid=640392434
