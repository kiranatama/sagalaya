<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\GData;

use Zend\Http,
    Zend\Http\Client;

/**
 * Wrapper around Zend\Http\Client to facilitate Google's "Account Authentication
 * Proxy for Web-Based Applications".
 *
 * @see http://code.google.com/apis/accounts/AuthForWebApps.html
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class AuthSub
{

    const AUTHSUB_REQUEST_URI      = 'https://www.google.com/accounts/AuthSubRequest';

    const AUTHSUB_SESSION_TOKEN_URI = 'https://www.google.com/accounts/AuthSubSessionToken';

    const AUTHSUB_REVOKE_TOKEN_URI  = 'https://www.google.com/accounts/AuthSubRevokeToken';

    const AUTHSUB_TOKEN_INFO_URI    = 'https://www.google.com/accounts/AuthSubTokenInfo';

     /**
      * Creates a URI to request a single-use AuthSub token.
      *
      * @param string $next (required) URL identifying the service to be
      *                     accessed.
      *  The resulting token will enable access to the specified service only.
      *  Some services may limit scope further, such as read-only access.
      * @param string $scope (required) URL identifying the service to be
      *                      accessed.  The resulting token will enable
      *                      access to the specified service only.
      *                      Some services may limit scope further, such
      *                      as read-only access.
      * @param int $secure (optional) Boolean flag indicating whether the
      *                    authentication transaction should issue a secure
      *                    token (1) or a non-secure token (0). Secure tokens
      *                    are available to registered applications only.
      * @param int $session (optional) Boolean flag indicating whether
      *                     the one-time-use  token may be exchanged for
      *                     a session token (1) or not (0).
      * @param string $request_uri (optional) URI to which to direct the
      *                            authentication request.
      */
     public static function getAuthSubTokenUri($next, $scope, $secure=0, $session=0,
                                               $request_uri = self::AUTHSUB_REQUEST_URI)
     {
         $querystring = '?next=' . urlencode($next)
             . '&scope=' . urldecode($scope)
             . '&secure=' . urlencode($secure)
             . '&session=' . urlencode($session);
         return $request_uri . $querystring;
     }


    /**
     * Upgrades a single use token to a session token
     *
     * @param string $token The single use token which is to be upgraded
     * @param \Zend\Http\Client $client (optional) HTTP client to use to
     *                                 make the request
     * @param string $request_uri (optional) URI to which to direct
     *                            the session token upgrade
     * @return string The upgraded token value
     * @throws \Zend\GData\App\AuthException
     * @throws \Zend\GData\App\HttpException
     */
    public static function getAuthSubSessionToken(
            $token, $client = null,
            $request_uri = self::AUTHSUB_SESSION_TOKEN_URI)
    {
        $client = self::getHttpClient($token, $client);

        if ($client instanceof HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
        } else {
            $client->setUri($request_uri);
        }
        $client->setMethod(Http\Request::METHOD_GET);

        try {
            $response = $client->send();
        } catch (Client\Exception\ExceptionInterface $e) {
            throw new App\HttpException($e->getMessage(), $e);
        }

        // Parse Google's response
        if ($response->isSuccess()) {
            $goog_resp = array();
            foreach (explode("\n", $response->getBody()) as $l) {
                $l = rtrim($l);
                if ($l) {
                    list($key, $val) = explode('=', rtrim($l), 2);
                    $goog_resp[$key] = $val;
                }
            }
            return $goog_resp['Token'];
        } else {
            throw new App\AuthException(
                    'Token upgrade failed. Reason: ' . $response->getBody());
        }
    }

    /**
     * Revoke a token
     *
     * @param string $token The token to revoke
     * @param \Zend\Http\Client $client (optional) HTTP client to use to make the request
     * @param string $request_uri (optional) URI to which to direct the revokation request
     * @return boolean Whether the revokation was successful
     * @throws \Zend\GData\App\HttpException
     */
    public static function AuthSubRevokeToken($token, $client = null,
                                              $request_uri = self::AUTHSUB_REVOKE_TOKEN_URI)
    {
        $client = self::getHttpClient($token, $client);

        if ($client instanceof HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
            $client->resetParameters();
        } else {
            $client->setUri($request_uri);
        }
        $client->setMethod(Http\Request::METHOD_GET);

        ob_start();
        try {
            $response = $client->send();
        } catch (Client\Exception\ExceptionInterface $e) {
            ob_end_clean();
            throw new App\HttpException($e->getMessage(), $e);
        }
        ob_end_clean();
        // Parse Google's response
        if ($response->isSuccess()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * get token information
     *
     * @param string $token The token to retrieve information about
     * @param \Zend\Http\Client $client (optional) HTTP client to use to
     *                                 make the request
     * @param string $request_uri (optional) URI to which to direct
     *                            the information request
     */
    public static function getAuthSubTokenInfo(
            $token, $client = null, $request_uri = self::AUTHSUB_TOKEN_INFO_URI)
    {
        $client = self::getHttpClient($token, $client);

        if ($client instanceof HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
        } else {
            $client->setUri($request_uri);
        }
        $client->setMethod(Http\Request::METHOD_GET);

        ob_start();
        try {
            $response = $client->send();
        } catch (Client\Exception\ExceptionInterface $e) {
            ob_end_clean();
            throw new App\HttpException($e->getMessage(), $e);
        }
        ob_end_clean();
        return $response->getBody();
    }

    /**
     * Retrieve a HTTP client object with AuthSub credentials attached
     * as the Authorization header
     *
     * @param string $token The token to retrieve information about
     * @param HttpClient $client (optional) HTTP client to use to make the request
     * @return HttpClient
     */
    public static function getHttpClient($token, HttpClient $client = null)
    {
        if ($client == null) {
            $client = new HttpClient();
        } elseif (!$client instanceof Client) {
            throw new App\HttpException('Client is not an instance of Zend_Http_Client.');
        }
        $useragent = 'Zend_Framework_Gdata/' . \Zend\Version::VERSION;
        $client->setOptions(array(
                'strictredirects' => true,
                'useragent' => $useragent
            )
        );
        $client->setAuthSubToken($token);
        return $client;
    }

}
