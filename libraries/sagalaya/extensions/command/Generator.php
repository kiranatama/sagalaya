<?php

namespace sagalaya\extensions\command;

use \sagalaya\extensions\command\generator\Repository;
use \sagalaya\extensions\command\generator\ControllerTest;
use \sagalaya\extensions\command\generator\Controller;
use \sagalaya\extensions\command\generator\ModelTest;
use \sagalaya\extensions\command\generator\Model;

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
		while (($filename = readdir($blueprint)) !== false) {
			if (!is_dir($filename)) {
				echo "\nprocessing : $filename";
				echo "\n------------------------------------------------------------------\n\n";
				$this->process(LITHIUM_APP_PATH . $this->designPath . DIRECTORY_SEPARATOR . $filename);
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
		$controller = new Controller($xml);
		$controllerTest = new ControllerTest($xml);
		$repository = new Repository($xml);

		$this->write(array($model, $modelTest, $controller, $controllerTest, $repository));
	}

	/**
	 * 
	 * @param array Generator $classes
	 */
	public function write($classes) {
		
		foreach ($classes as $class) {
			
			if (file_exists($class->path) && !$this->generateAll) {

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
		print "{$path} has created. \n";
	}

}