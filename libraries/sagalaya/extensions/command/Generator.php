<?php

namespace sagalaya\extensions\command;

use sagalaya\extensions\command\generator\Repository;
use sagalaya\extensions\command\generator\ControllerTest;
use sagalaya\extensions\command\generator\Controller;
use sagalaya\extensions\command\generator\ModelTest;
use sagalaya\extensions\command\generator\Model;

/**
 * generator command provide automatic generator for CRUD like model, controller, and views
 * this is also generate the Test class for generated object
 * the central focus of generator is model, that will create controller, views, and test
 */
class Generator extends \lithium\console\Command {

	protected $generateAll = false;
	protected $designPath = '/config/design';

	/**
	 *
	 * @param array $args
	 */
	public function run($args = array()) {
		$blueprint = opendir(LITHIUM_APP_PATH . $this->designPath);

		$args = $this->request->argv;
		if (count($args) > 1) {
			foreach ($args as $argv) {
				$filename = LITHIUM_APP_PATH . $this->designPath . DIRECTORY_SEPARATOR . $argv . '.xml';
				if (file_exists($filename)) {
					$this->process($filename);
				}
			}
		} else {
			while (($filename = readdir($blueprint)) !== false) {
				if (!is_dir($filename)) {
					$this->process(LITHIUM_APP_PATH . $this->designPath . DIRECTORY_SEPARATOR . $filename);
				}
			}
		}
	}

	/**
	 *
	 * @param string $filename
	 */
	public function process($filename) {

		$xml = new \SimpleXMLElement(file_get_contents($filename));

		$model = new Model($xml);
		$modelTest = new ModelTest($xml);
		$repository = new Repository($xml);

		$this->write(array($model, $modelTest, $repository));

		if (isset($xml->config->controller) && "{$xml->config->controller}" == "true") {
			$controller = new Controller($xml);
			$controllerTest = new ControllerTest($xml);
			$this->write(array($controller, $controllerTest));
		}
	}

	/**
	 *
	 * @param array Generator $classes
	 */
	public function write($classes) {

		foreach ($classes as $class) {

			$type = substr(get_class($class), strripos(get_class($class), '\\') + 1);

			if (file_exists($class->path) && !$this->generateAll &&
					!in_array($type, array('ModelTest', 'Controller', 'ControllerTest', 'Repository'))) {

				$result = $this->in("File {$class->path} is already exists, overwrite the file?",
				array('choices' => array('Y' => 'Yes', 'N' => 'No', 'A' => 'All')));

				if (strcasecmp($result, "Yes") == 0 || strcasecmp($result, "All") == 0) {
					$this->put_file($class->path,  $class->generate());
					$this->generateAll = (strcasecmp($result, "all") == 0) ? true : false;
				}

			} else {
				$this->put_file($class->path,  $class->generate());
			}

		}

	}

	/**
	 *
	 * @param string $path
	 * @param string $content
	 */
	public function put_file($path, $content) {
		file_put_contents($path, "<?php\n\n{$content}\n?>");
		print "{$path}\n\n";
	}

}