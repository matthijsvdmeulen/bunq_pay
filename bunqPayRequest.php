<?php
header('Content-Type: application/json');
include_once('./Request.php');
use bunq\doc\showcase\Request;

const BUNQ_API_SERVICE_URL = 'https://api.bunq.com';
const BUNQ_API_VERSION = 'v1';

//Your bunq API-key
$API_KEY = 'Your bunq API-key';

//Your RSA keys used for installation
//Try to avoid having your keys in your project and/or repository.
//Store the keys seperately on your server if possible.
$clientPublicKey = file_get_contents("/var/keys/client.pub");
$clientPrivateKey = file_get_contents("/var/keys/client.key");

$createUuid = uniqid();
$bunqUserId = '';
$bunqMonetaryAccount = '';
$bunqAuthToken = ''; //To do: Automatic get token 

$requestAmount = $_GET['amount'];
$requestDescription = '';

$arrayBody = [];
$postBody = '{"amount_inquired": {"value": "'.$requestAmount.'","currency": "EUR"},"counterparty_alias": {"type": "EMAIL","value": "your_request_email@domain.com"},"description": "'.$requestDescription.'","allow_bunqme": true}';

$monetaryAccountRequest = new Request(BUNQ_API_SERVICE_URL, BUNQ_API_VERSION);
$monetaryAccountRequest->setMethod(Request::METHOD_POST);
$monetaryAccountRequest->setEndpoint('user/'.$bunqUserId.'/monetary-account/'.$bunqMonetaryAccount.'/request-inquiry');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CACHE_CONTROL, 'no-cache');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_USER_AGENT, 'User agent - bunq API v1'); //Change this
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_REQUEST_ID, $createUuid);
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_GEOLOCATION, '0 0 0 0 000');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_LANGUAGE, 'en_US');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_REGION, 'en_US');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_AUTHENTICATION, $bunqAuthToken);
$monetaryAccountRequest->setBody($postBody);
$signature = $monetaryAccountRequest->getSignature($clientPrivateKey);
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_SIGNATURE, $signature);

$monetaryAccountResponse = $monetaryAccountRequest->execute();
$requestId = json_decode($monetaryAccountResponse->{'Response'}[0]->{'Id'}->{'id'});

$postBody = '';
$createUuid = uniqid();

$monetaryAccountRequest = new Request(BUNQ_API_SERVICE_URL, BUNQ_API_VERSION);
$monetaryAccountRequest->setMethod(Request::METHOD_GET);
$monetaryAccountRequest->setEndpoint('user/'.$bunqUserId.'/monetary-account/'.$bunqMonetaryAccount.'/request-inquiry/'.$requestId);
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CACHE_CONTROL, 'no-cache');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_USER_AGENT, 'User agent - bunq API v1'); //Change this
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_REQUEST_ID, $createUuid);
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_GEOLOCATION, '0 0 0 0 000');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_LANGUAGE, 'en_US');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_REGION, 'en_US');
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_AUTHENTICATION, $bunqAuthToken);
$monetaryAccountRequest->setBody($postBody);
$signature = $monetaryAccountRequest->getSignature($clientPrivateKey);
$monetaryAccountRequest->setHeader(Request::HEADER_REQUEST_CUSTOM_SIGNATURE, $signature);

$monetaryAccountResponse = $monetaryAccountRequest->execute();
$bunqmeShareUrl = json_encode((array)$monetaryAccountResponse->{'Response'}[0]->{'RequestInquiry'}->{'bunqme_share_url'});

echo $bunqmeShareUrl;
?>
