<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace app\extensions\doctrine\logger;

use \lithium\analysis\Logger;

class SqlLogger extends \lithium\core\Object implements \Doctrine\DBAL\Logging\SQLLogger {
    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array $params The SQL parameters.
     * @param float $executionMS The microtime difference it took to execute this query.
     * @return void
     */
    public function logSQL($sql, array $params = null, $executionMS = null) {
		Logger::write('debug', $sql);
	}
	
	public function startQuery($sql, array $params = null, array $types = null){
		
	}
	
	public function stopQuery(){
		
	}
}

?>