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
 * 
 * 
 * bunq_pay installationscript. Run this script once.
 */

use bunq\Context\ApiContext;
use bunq\Util\BunqEnumApiEnvironmentType;
require_once(__DIR__ . '/vendor/autoload.php');

/**
 * Database functions
 */
require_once(__DIR__ . '/classes/database.php');

/** 
 * Constants and settings
 */
const apiKey = 'YOUR_API_KEY';
const dbPath = '/var/local/bunq_pay/database/bunqSession.db';
const deviceServerDescription = 'bunq_pay v1';
const permitted_ips = [];
const paymentDescription = 'Payment request';

/**
 * SQLlite3 database location
 */
$database = new Database(dbPath);

/** 
 * Replace SANDBOX() with PRODUCTION() if you want to use the production API.
 */
$apiContext = ApiContext::create(BunqEnumApiEnvironmentType::SANDBOX(), apiKey, deviceServerDescription, permitted_ips);
$database->setBunqContext($apiContext->toJson());
?>
