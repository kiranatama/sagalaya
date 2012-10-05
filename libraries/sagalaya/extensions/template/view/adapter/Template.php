<?php
/**
 * Li3_twig: Two step Twig renderer for Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sagalaya\extensions\template\view\adapter;

use RuntimeException;
use lithium\core\Libraries;
use Twig_TemplateInterface;

/**
 * Template class for rendering Twig templates
 *
 * @see http://twig-project.org
 * @author Raymond Julin <raymond.julin@gmail.com>
 */
abstract class Template extends \Twig_Template {

	/**
	 * Override the getAttribute to handle lazy loaded li3 helpers
	 */
	protected function getAttribute($object, $item, array $arguments = array(),
			$type = Twig_TemplateInterface::ANY_CALL,
			$noStrictCheck = false, $line = -1) {
		$result = parent::getAttribute($object, $item, $arguments, $type, $noStrictCheck, $line);
		if ($result === null) {
			try {
				if (is_a($object, 'sagalaya\extensions\data\Model')) {
					return $object->$item;
				} else {
					$result = (is_object($object) &&
							method_exists($object, 'helper')) ? $object->helper($item) : null;
				}
			}
			catch (\Exception $e) {
				$result = null;
			}
		}
		return $result;
	}
}

?>
