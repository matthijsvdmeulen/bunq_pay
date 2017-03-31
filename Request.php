<?php
/**
 * The Request file.
 */
namespace bunq\doc\showcase;

use Exception;
use stdClass;

/**
 * Example Bunq API Request.
 *
 * Do you have any question about this class? Contact us at support@bunq.com and we will do our best to help you!
 */
class Request
{
    /**
     * Request HTTP header constants.
     */
    const HEADER_REQUEST_AUTHORIZATION = 'Authorization'; // Not to be signed! Used in sandbox only.
    const HEADER_REQUEST_CACHE_CONTROL = 'Cache-Control';
    const HEADER_REQUEST_CONTENT_TYPE = 'Content-Type'; // Not to be signed!
    const HEADER_REQUEST_CUSTOM_CLIENT_ENCRYPTION_HMAC = 'X-Bunq-Client-Encryption-Hmac';
    const HEADER_REQUEST_CUSTOM_CLIENT_ENCRYPTION_IV = 'X-Bunq-Client-Encryption-Iv';
    const HEADER_REQUEST_CUSTOM_CLIENT_ENCRYPTION_KEY = 'X-Bunq-Client-Encryption-Key';
    const HEADER_REQUEST_CUSTOM_ATTACHMENT_DESCRIPTION = 'X-Bunq-Attachment-Description';
    const HEADER_REQUEST_CUSTOM_AUTHENTICATION = 'X-Bunq-Client-Authentication';
    const HEADER_REQUEST_CUSTOM_GEOLOCATION = 'X-Bunq-Geolocation';
    const HEADER_REQUEST_CUSTOM_LANGUAGE = 'X-Bunq-Language';
    const HEADER_REQUEST_CUSTOM_REGION = 'X-Bunq-Region';
    const HEADER_REQUEST_CUSTOM_REQUEST_ID = 'X-Bunq-Client-Request-Id';
    const HEADER_REQUEST_CUSTOM_SIGNATURE = 'X-Bunq-Client-Signature';
    const HEADER_REQUEST_USER_AGENT = 'User-Agent';

    /**
     * Bunq header prefix constants.
     */
    const HEADER_BUNQ_PREFIX = 'X-Bunq-';
    const HEADER_BUNQ_PREFIX_LENGTH = 7;
    const HEADER_BUNQ_PREFIX_START = 0;

    /**
     * Call methods
     */
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_LIST = 'LIST';
    /**
     * Separators
     */
    const HEADER_SEPARATOR = ': '; // Mind the space after the :
    const URL_SEPARATOR = '/';

    /**
     * The Signature Algorithm we use is SHA256. (php: http://php.net/manual/en/function.openssl-sign.php#signature_alg)
     */
    const SIGNATURE_ALGORITHM = OPENSSL_ALGO_SHA256;

    /**
     * Curl SSL verification constants.
     */
    const CURL_SSL_VERIFYPEER_ENABLED = true;
    const CURL_SSL_VERIFYPEER_DISABLED = false;
    const CURL_SSL_VERIFYHOST_ENABLED = 2;
    const CURL_SSL_VERIFYHOST_DISABLED = 0;

    /**
     * Encryption mode to use for encrypted requests.
     */
    const BUNQ_ENCRYPTION_MODE = 'aes-256-cbc';
    const BUNQ_HMAC_HASH_ALGORITHM = 'sha1';

    /**
     * This variable is only used for debugging purposes.
     * When set to true the Data to sign, URL, Headers and Body of the request will be printed.
     *
     * @var boolean
     */
    private $showOutput = false;

    /**
     * The serviceUrl is the base URL of the API.
     * Example: https://sandbox.public.api.bunq.com
     *
     * @var string
     */
    private $serviceUrl;

    /**
     * The apiVersion is the version of the API.
     * Example: v1
     *
     * @var string
     */
    private $apiVersion;

    /**
     * The endpoint is the part of the URL after the apiVersion (e.g. the part after 'v1/')
     * Example: user/1/monetary-account/11/payment
     *
     * @var string
     */
    private $endpoint;

    /**
     * The call method. Can be POST, GET, PUT or DELETE.
     *
     * @var string
     */
    private $method;

    /**
     * The body of this request represented as a string.
     *
     * @var string
     */
    private $body;

    /**
     * An array containing al the headers for this request.
     *
     * @var string[]
     */
    private $headers = [];

    /**
     * A string with the response body.
     *
     * @var string
     */
    private $responseBodyString;

    /**
     * A string with the response header string.
     *
     * @var string
     */
    private $responseHeaderString;

    /**
     * A string with the response status code.
     *
     * @var string
     */
    private $responseStatusCode;

    /**
     * Option for CURL SSL Peer Verification.
     *
     * @var bool
     */
    private $curlSslVerifyPeer;

    /**
     * Option for CURL SSL Host Verification.
     *
     * @var int
     */
    private $curlSslVerifyHost;

    /**
     * Construct a new request.
     * You need to set the endpoint, method, headers and body of the request with their setters.
     *
     * @param string $serviceUrl
     * @param string $apiVersion
     * @param bool $curlSslVerify   INSECURE. Allows to disable SSL Peer- and Host- verification for debugging purposes.
     */
    public function __construct($serviceUrl, $apiVersion, $curlSslVerify = true)
    {
        $this->serviceUrl = $serviceUrl;
        $this->apiVersion = $apiVersion;

        if ($curlSslVerify === true) {
            $this->curlSslVerifyPeer = self::CURL_SSL_VERIFYPEER_ENABLED;
            $this->curlSslVerifyHost = self::CURL_SSL_VERIFYHOST_ENABLED;
        } else {
            $this->curlSslVerifyPeer = self::CURL_SSL_VERIFYPEER_DISABLED;
            $this->curlSslVerifyHost = self::CURL_SSL_VERIFYHOST_DISABLED;
        }
    }

    /**
     * Execute the request using curl.
     *
     * @return stdClass|string normally returns an instance of stdClass representing the decoded response. Raw response
     * string is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
     * @throws Exception when the response contains errors.
     */
    public function execute()
    {
        // The full URL of the request is the serviceUrl, apiVersion and endpoint combined with '/' in between.
        $url = $this->getServiceUrl() . self::URL_SEPARATOR . $this->getApiVersion() . self::URL_SEPARATOR .
            $this->getEndpoint();
        $body = $this->getBody();
        $curlHeaders = $this->getCurlHeaders();

       //$this->printRequest($url, $body, $curlHeaders);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->curlSslVerifyPeer);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->curlSslVerifyHost);
        curl_setopt($curl, CURLOPT_HEADER, true);

        // Only post the request body if the method is set to POST, PUT.
        // And set the right Method. (for curl the default method is GET).
        if ($this->getMethod() === self::METHOD_POST) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($this->getMethod() === self::METHOD_PUT) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->getMethod());
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($this->getMethod() === self::METHOD_DELETE) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->getMethod());
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);
        $output = curl_exec($curl);

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        $this->responseStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseHeaderString = substr($output, 0, $headerSize);
        $this->responseBodyString = substr($output, $headerSize);

        curl_close($curl);

        //$this->printStatusCode($this->responseStatusCode);

        $outputDecoded = json_decode($this->responseBodyString);

        if ($outputDecoded) {
            if (isset($outputDecoded->{'Error'})) {
                $message = 'Endpoint: ' . $this->endpoint . PHP_EOL . 'Errors: ' . PHP_EOL;

                foreach ($outputDecoded->{'Error'} as $key => $error) {
                    $message .= ($key + 1) . '. ' . $error->{'error_description'} . PHP_EOL;
                }

                throw new Exception($message);
            } else {
               return $outputDecoded;
            }
        } else {
            return $this->responseBodyString;
        }
    }

    /**
     * Curl needs the headers in a specific format.
     *
     * @return array
     */
    private function getCurlHeaders()
    {
        $curlHeaders = [];

        foreach ($this->headers as $key => $value) {
            $curlHeaders[] = $key . self::HEADER_SEPARATOR . $value;
        }

        return $curlHeaders;
    }

    /**
     * Returns the signature. The signature should be added to the X-Bunq-Client-Signature header.
     *
     * @param string $clientPrivateKey The private key corresponding to the client_public_key you provided in the
     * installation call.
     *
     * @return string The result of the signing after we base64 encoded it. This can be used in the headers of the
     * request.
     */
    public function getSignature($clientPrivateKey)
    {
        // When signing the headers they need to be in alphabetical order.
        ksort($this->headers);

        // The first line of string that needs to be signed is for example: POST /v1/installation
        $toSign = $this->getMethod() . ' ' .
            self::URL_SEPARATOR . $this->getApiVersion() . self::URL_SEPARATOR . $this->getEndpoint();

        foreach ($this->headers as $key => $value) {
            // Not all headers should be signed.
            // The User-Agent and Cash-Control headers need to be signed.
            if ($key === self::HEADER_REQUEST_USER_AGENT || $key === self::HEADER_REQUEST_CACHE_CONTROL) {
                // Example: Cache-Control: no-cache
                $toSign .= PHP_EOL . $key . self::HEADER_SEPARATOR . $value;
            }

            // All headers with the prefix 'X-Bunq-' need to be signed.
            if (substr($key, self::HEADER_BUNQ_PREFIX_START, self::HEADER_BUNQ_PREFIX_LENGTH) ===
                self::HEADER_BUNQ_PREFIX) {
                $toSign .= PHP_EOL . $key . self::HEADER_SEPARATOR . $value;
            }
        }

        // Always add two newlines after the headers.
        $toSign .= PHP_EOL . PHP_EOL;

        // If we have a body in this request: add the body to the string that needs to be signed.
        if (!is_null($this->getBody())) {
            $toSign .= $this->getBody();
        }

        if ($this->showOutput) {
            echo $this->colorStringForTerminal('Data to sign: ') . PHP_EOL . $toSign . PHP_EOL;
        }

        openssl_sign($toSign, $signature, $clientPrivateKey, self::SIGNATURE_ALGORITHM);

        // Don't forget to base64 encode the signature.
        return base64_encode($signature);
    }

    /**
     * @return string The endpoint, the endpoint is the last part of the URL needed to reach the desired call.
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint The endpoint of this request. Example: user/1/monetary-account/11/payment
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return string The body of this request.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the body of the request. We use the json_encode to get the json representation of the array passed.
     *
     * @param string[] $body
     */
    public function setBodyFromArray(array $body)
    {
        $this->body = json_encode($body);
    }

    /**
     * In some cases, such as attachments or encrypted requests we do not want to json_encode the body.
     *
     * @link https://doc.bunq.com/api/1/attachment-public/post
     * @link https://doc.bunq.com/api/1/card/put
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string The status code of the response.
     */
    public function getResponseStatusCode()
    {
        return $this->responseStatusCode;
    }

    /**
     * @return string The body of the response.
     */
    public function getResponseBodyString()
    {
        return $this->responseBodyString;
    }

    /**
     * @return string The header of the response.
     */
    public function getResponseHeaderString()
    {
        return $this->responseHeaderString;
    }

    /**
     * Get a specific header from this request by its key.
     *
     * @param string $key
     *
     * @return string
     */
    public function getHeader($key)
    {
        return $this->headers[$key];
    }

    /**
     * Set a specific header for this request.
     * Example: $request->setHeader(Request::HEADER_REQUEST_CACHE_CONTROL, 'no-cache');
     *
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * @return string The call method. Can be POST, GET, PUT or DELETE.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method The call method. Can be POST, GET, PUT or DELETE.
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string The serviceUrl is the base URL of the API.
     */
    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }

    /**
     * @param string $serviceUrl The serviceUrl is the base URL of the API. Example: https://sandbox.public.api.bunq.com
     */
    public function setServiceUrl($serviceUrl)
    {
        $this->serviceUrl = $serviceUrl;
    }

    /**
     * @return string The apiVersion is the version of this request.
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion The apiVersion is the version of the API. Example: v1
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * This is only used for debugging purposes.
     * When set to true the Data to sign, URL, Headers and Body of the request will be printed.
     * Default = true;
     *
     * @param boolean $showOutput
     */
    public function setShowOutput($showOutput)
    {
        $this->showOutput = $showOutput;
    }

    /**
     * This function is for debugging purposes and not important or related to the actual request.
     *
     * @param $url
     * @param $body
     * @param $curlHeaders
     */
    private function printRequest($url, $body, $curlHeaders)
    {
        if ($this->showOutput) {
            echo $this->colorStringForTerminal('Request URL:') . PHP_EOL . $url . PHP_EOL;
            echo $this->colorStringForTerminal('Request Headers:');

            foreach ($curlHeaders as $key => $value) {
                echo PHP_EOL . $value;
            }

            echo PHP_EOL;
            echo $this->colorStringForTerminal('Request Body: ') . PHP_EOL . $body . PHP_EOL;
        } else {
            echo $this->colorStringForTerminal(
                'showOutput is set to false. The request and signature data will not be printed.'
            ) . PHP_EOL;
        }
    }

    /**
     * This function is for debugging purposes and not important or related to the actual request.
     *
     * @param $statusCode
     */
    private function printStatusCode($statusCode)
    {
        if ($this->showOutput) {
            echo $this->colorStringForTerminal('Response Status Code: ') . $statusCode . PHP_EOL;
        }
    }

    /**
     * Used for debugging.
     *
     * @param string $stringToColor
     *
     * @return string
     */
    private function colorStringForTerminal($stringToColor)
    {
        return chr(27) . '[33m' .  $stringToColor . chr(27) . '[0m';
    }
}
