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
 * @subpackage Analytics
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\GData;

use Zend\Http\Client;

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Analytics extends GData
{
	const AUTH_SERVICE_NAME = 'analytics';
	const ANALYTICS_FEED_URI = 'https://www.google.com/analytics/feeds';
	const ANALYTICS_ACCOUNT_FEED_URI = 'https://www.google.com/analytics/feeds/accounts';

	public static $namespaces = array(
        array('ga', 'http://schemas.google.com/analytics/2009', 1, 0)
    );

    /**
     * Create Gdata object
     *
     * @param Client $client
     * @param string $applicationId The identity of the app in the form of
     *          Company-AppName-Version
     */
    public function __construct(Client $client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Analytics');
        $this->registerPackage('Zend_Gdata_Analytics_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost(array('service' => self::AUTH_SERVICE_NAME));
    }

    /**
     * Retrieve account feed object
     *
     * @return Analytics\AccountFeed
     */
    public function getAccountFeed()
    {
        $uri = self::ANALYTICS_ACCOUNT_FEED_URI . '/default?prettyprint=true';
        return parent::getFeed($uri, 'Zend\GData\Analytics\AccountFeed');
    }

    /**
     * Retrieve data feed object
     * 
     * @param mixed $location
     * @return Analytics\DataFeed
     */
    public function getDataFeed($location = self::ANALYTICS_FEED_URI)
    {
		if ($location instanceof Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend\GData\Analytics\DataFeed');
    }

    /**
     * Returns a new DataQuery object.
     * 
     * @return Analytics\DataQuery
     */
    public function newDataQuery()
    {
    	return new Analytics\DataQuery();
    }
}
