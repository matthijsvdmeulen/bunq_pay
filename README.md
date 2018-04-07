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
#### Make sure that the database directory is absolutely not accessible from the internet!
- Change the path to the database on line 29 of install.php and bunqPayRequest.php, fill with correct path.
- Change YOUR_API_KEY in install.php, use your bunq API-key (for the Sandbox environment in this example).
- Run install.php once to get the bunq session and save it to the SQLlite database.

If your user is UserPerson replace getUserCompany() with getUserPerson(), also replace bunq\Model\Generated\Endpoint\UserCompany in the scripts.
