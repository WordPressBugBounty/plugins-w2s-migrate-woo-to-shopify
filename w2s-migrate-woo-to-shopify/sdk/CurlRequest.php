<?php
/**
 * Created by PhpStorm.
 * @author Villatheme
 * Created at 8/17/16 2:50 PM UTC+06:00
 */

namespace PHPShopify;


use PHPShopify\Exception\CurlException;
use PHPShopify\Exception\ResourceRateLimitException;

/*
|--------------------------------------------------------------------------
| CurlRequest
|--------------------------------------------------------------------------
|
| This class handles get, post, put, delete HTTP requests
|
*/
class CurlRequest
{
    /**
     * HTTP Code of the last executed request
     *
     * @var integer
     */
    public static $lastHttpCode;

    /**
     * HTTP response headers of last executed request
     *
     * @var array
     */
    public static $lastHttpResponseHeaders = array();

    /**
     * Total time spent in sleep during multiple requests (in seconds)
     *
     * @var int
     */
    public static $totalRetrySleepTime = 0;

    /**
     * Curl additional configuration
     *
     * @var array
     */
    protected static $config = array();

    /**
     * Initialize the curl resource
     *
     * @param string $url
     * @param array $httpHeaders
     *
     * @return resource
     */
    protected static function init($url, $httpHeaders = array())
    {
        // Create Curl resource
        $ch = curl_init();// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init

        // Set URL
        curl_setopt($ch, CURLOPT_URL, $url);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

        //Return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

        curl_setopt($ch, CURLOPT_HEADER, true);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHPClassic/PHPShopify');// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

        foreach (self::$config as $option => $value) {
            curl_setopt($ch, $option, $value);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
        }

        $headers = array();
        foreach ($httpHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }
        //Set HTTP Headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

        return $ch;

    }

	/**
	 * Implement a GET request and return output
	 *
	 * @param string $url
	 * @param array $httpHeaders
	 *
	 * @return string
	 * @throws CurlException
	 * @throws ResourceRateLimitException
	 */
    public static function get($url, $httpHeaders = array())
    {
        //Initialize the Curl resource
        $ch = self::init($url, $httpHeaders);

        return self::processRequest($ch);
    }

	/**
	 * Implement a POST request and return output
	 *
	 * @param string $url
	 * @param array $data
	 * @param array $httpHeaders
	 *
	 * @return string
	 * @throws CurlException
	 * @throws ResourceRateLimitException
	 */
    public static function post($url, $data, $httpHeaders = array())
    {
        $ch = self::init($url, $httpHeaders);
        //Set the request type
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

        return self::processRequest($ch);
    }

	/**
	 * Implement a PUT request and return output
	 *
	 * @param string $url
	 * @param array $data
	 * @param array $httpHeaders
	 *
	 * @return string
	 * @throws CurlException
	 * @throws ResourceRateLimitException
	 */
    public static function put($url, $data, $httpHeaders = array())
    {
        $ch = self::init($url, $httpHeaders);
        //set the request type
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

        return self::processRequest($ch);
    }

	/**
	 * Implement a DELETE request and return output
	 *
	 * @param string $url
	 * @param array $httpHeaders
	 *
	 * @return string
	 * @throws CurlException
	 * @throws ResourceRateLimitException
	 */
    public static function delete($url, $httpHeaders = array())
    {
        $ch = self::init($url, $httpHeaders);
        //set the request type
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

        return self::processRequest($ch);
    }

    /**
     * Set curl additional configuration
     *
     * @param array $config
     */
    public static function config($config = array())
    {
        self::$config = $config;
    }

	/**
	 * Execute a request, release the resource and return output
	 *
	 * @param resource $ch
	 *
	 * @return string
	 * @throws ResourceRateLimitException
	 *
	 * @throws CurlException if curl request is failed with error
	 */
    protected static function processRequest($ch)
    {
        # Check for 429 leaky bucket error
        while (1) {
            $output   = curl_exec($ch);// phpcs:ignore 	WordPress.WP.AlternativeFunctions.curl_curl_exec
            $response = new CurlResponse($output);

            self::$lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
            if (self::$lastHttpCode != 429) {
                break;
            }

            $apiCallLimit = $response->getHeader('X-Shopify-Shop-Api-Call-Limit');

            if (!empty($apiCallLimit)) {
                $limitHeader = explode('/', $apiCallLimit, 2);
                if (isset($limitHeader[1]) && $limitHeader[0] < $limitHeader[1]) {
                    throw new ResourceRateLimitException($response->getBody());// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                }
            }
            
            $retryAfter = $response->getHeader('Retry-After');

            if ($retryAfter === null) {
                break;
            }

            self::$totalRetrySleepTime += (float)$retryAfter;
            sleep((float)$retryAfter);
        }

        if (curl_errno($ch)) {// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_errno
            throw new Exception\CurlException(curl_errno($ch) . ' : ' . curl_error($ch));// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped, WordPress.WP.AlternativeFunctions.curl_curl_error,WordPress.WP.AlternativeFunctions.curl_curl_errno
        }

        // close curl resource to free up system resources
        curl_close($ch);// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

        self::$lastHttpResponseHeaders = $response->getHeaders();

        return $response->getBody();
    }
}
