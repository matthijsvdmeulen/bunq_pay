<?php
/**
 * bunq_pay
 * Pay with bunq or by iDEAL
 *
 * @author	Bastiaan Steinmeier <bastiaan85@gmail.com>
 * @license	http://opensource.org/licenses/mit-license.php MIT License
 *
 * Uses the official bunq PHP SDK
 * https://github.com/bunq/sdk_php
 */
 
if(!isset($_GET['amount'])){
	echo "The 'amount' argument was not set";
	die();
}

if(!isset($_GET['description'])){
	echo "The 'description' argument was not set";
	
}
 
use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Util\BunqEnumApiEnvironmentType;
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\UserPerson;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Object\Amount;
use bunq\Model\Generated\Endpoint\BunqMeTab;
use bunq\Model\Generated\Endpoint\BunqMeTabEntry;
require_once(__DIR__ . '/vendor/autoload.php');

/**
 * Database functions
 */
require_once(__DIR__ . '/classes/database.php');

/** 
 * Constants and settings
 */
$paymentDescription = $_GET['description'];
 const dbPath = __DIR__ . '/database/bunqSession.db';
const index_user = 0;
const index_monetaryaccount = 0; //Bank account to use

$requestAmount = $_GET['amount'];


/**
 * SQLlite3 database location
 */
$database = new Database(dbPath);

$apiContext = ApiContext::fromJson($database->getBunqContext());
$apiContext->ensureSessionActive();
$database->setBunqContext($apiContext->toJson());
BunqContext::loadApiContext($apiContext);

/**
 * If your user is UserPerson replace getUserCompany() with getUserPerson()
 * Also replace bunq\Model\Generated\Endpoint\UserCompany 
 */
$user = BunqContext::getUserContext()->getUserPerson();
$userId = $user->getId();

/**
 * Get monetary account of the active user
 */
$monetaryAccounts = MonetaryAccountBank::listing([])->getValue();
$monetaryAccountId = $monetaryAccounts[index_monetaryaccount]->getId();

/**
 * Create bunqMeTab (open request) and get share URL
 */
$bunqMeTabEntry = new BunqMeTabEntry($paymentDescription, new Amount($requestAmount, 'EUR'));
$createBunqMeTab = BunqMeTab::create($bunqMeTabEntry, $monetaryAccountId)->getValue();
$bunqMeRequest = BunqMeTab::get($createBunqMeTab, $monetaryAccountId)->getValue();

header('Content-Type: application/json');
echo json_encode($bunqMeRequest->getBunqmeTabShareUrl());
?>
