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
 
use bunq\Context\ApiContext;
use bunq\Util\BunqEnumApiEnvironmentType;
use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\UserPerson;
use bunq\Model\Generated\Endpoint\MonetaryAccount;
use bunq\Model\Generated\Object\Amount;
use bunq\Model\Generated\Object\Pointer;
use bunq\Model\Generated\Endpoint\BunqMeTab;
use bunq\Model\Generated\Endpoint\BunqMeTabEntry;
require_once(__DIR__ . '/vendor/autoload.php');

/**
 * Database functions
 */
require_once(__DIR__ . '/classes/database.php');

/**
 * SQLlite3 database location
 */
$database = new Database('/var/www/database/bunqSession.db');

/** 
 * Constants and settings
 */
const deviceServerDescription = 'bunq_pay v1';
const permitted_ips = [];
const paymentDescription = 'Payment request';
const index_user = 0;
const index_monetaryaccount = 0;
$requestAmount = $_GET['amount'];

/**
 * Only run first time (once) and remove after:
 * $apiKey = 'YOUR_API_KEY';
 * $apiContext = ApiContext::create(BunqEnumApiEnvironmentType::SANDBOX(), $apiKey, deviceServerDescription, permitted_ips);
 * $database->setBunqContext($apiContext->toJson());
 */

$apiContext = ApiContext::fromJson($database->getBunqContext());
$apiContext->ensureSessionActive();
$database->setBunqContext($apiContext->toJson());

$users = User::listing($apiContext)->getValue();
/**
 * If your user is UserPerson replace getUserCompany() with getUserPerson()
 * Also replace bunq\Model\Generated\Endpoint\UserCompany 
 */
$user = $users[index_user]->getUserCompany();
$userId = $user->getId();
$monetaryAccounts = MonetaryAccount::listing($apiContext, $userId)->getValue();
$monetaryAccount = $monetaryAccounts[index_monetaryaccount]->getMonetaryAccountBank();
$monetaryAccountId = $monetaryAccount->getId();

$requestMap = [
	BunqMeTab::FIELD_BUNQME_TAB_ENTRY => [
        BunqMeTabEntry::FIELD_AMOUNT_INQUIRED => new Amount($requestAmount, 'EUR'),
		BunqMeTabEntry::FIELD_DESCRIPTION => paymentDescription
	]
];

$createBunqMeTab = BunqMeTab::create($apiContext, $requestMap, $userId, $monetaryAccountId)->getValue();
$bunqMeRequest = BunqMeTab::get($apiContext, $userId, $monetaryAccountId, $createBunqMeTab)->getValue();

header('Content-Type: application/json');
echo json_encode($bunqMeRequest->getBunqmeTabShareUrl());
?>