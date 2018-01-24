# bunq_pay
Pay with bunq or iDEAL

#### This is only a proof-of-concept/example!
Demo video: https://youtu.be/aLlP9Nx0WYA 

## Installation
- Make a new directory
- cd into new directory
```shell
$ git clone https://github.com/basst85/bunq_pay.git
```
- Run composer install to install all requirements:
```shell
$ composer install
```
- The database directory must be writeable by the web user, also the .db file must be writeable.
- Uncomment this lines in bunqPayRequest.php:
```php
$apiKey = 'YOUR_API_KEY';
$apiContext = ApiContext::create(BunqEnumApiEnvironmentType::SANDBOX(), $apiKey, deviceServerDescription, permitted_ips);
$database->setBunqContext($apiContext->toJson());
```
- Change YOUR_API_KEY, use your bunq API-key (for the Sandbox environment in this example.
- Change the path to the database on line 32 of bunqPayRequest.php, fill with correct path.
- Run bunqPayRequest.php once to get the bunq session and save it to the SQLlite database.
- Remove the uncommented lines from the script.

If your user is UserPerson replace getUserCompany() with getUserPerson(), also replace bunq\Model\Generated\Endpoint\UserCompany 
