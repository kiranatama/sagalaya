<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Mvc
 */

namespace Zend\Mvc\Controller;

use Zend\Mvc\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigurationInterface;
use Zend\Stdlib\DispatchableInterface;

/**
 * Plugin manager implementation for controllers
 *
 * Registers a number of default plugins, and contains an initializer for
 * injecting plugins with the current controller.
 *
 * @category   Zend
 * @package    Zend_Mvc
 * @subpackage Controller
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * Default set of plugins
     *
     * @var array
     */
    protected $invokableClasses = array(
        'flashmessenger'  => 'Zend\Mvc\Controller\Plugin\FlashMessenger',
        'forward'         => 'Zend\Mvc\Controller\Plugin\Forward',
        'layout'          => 'Zend\Mvc\Controller\Plugin\Layout',
        'params'          => 'Zend\Mvc\Controller\Plugin\Params',
        'postredirectget' => 'Zend\Mvc\Controller\Plugin\PostRedirectGet',
        'redirect'        => 'Zend\Mvc\Controller\Plugin\Redirect',
        'url'             => 'Zend\Mvc\Controller\Plugin\Url',
    );

    /**
     * Default set of plugin aliases
     *
     * @var array
     */
    protected $aliases = array(
        'prg'             => 'postredirectget',
    );

    /**
     * @var DispatchableInterface
     */
    protected $controller;

    /**
     * Constructor
     *
     * After invoking parent constructor, add an initializer to inject the
     * attached controller, if any, to the currently requested plugin.
     *
     * @param  null|ConfigurationInterface $configuration
     * @return void
     */
    public function __construct(ConfigurationInterface $configuration = null)
    {
        parent::__construct($configuration);
        $this->addInitializer(array($this, 'injectController'));
    }

    /**
     * Set controller
     *
     * @param  DispatchableInterface $controller
     * @return PluginManager
     */
    public function setController(DispatchableInterface $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Retrieve controller instance
     *
     * @return null|DispatchableInterface
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Inject a helper instance with the registered controller
     *
     * @param  object $plugin
     * @return void
     */
    public function injectController($plugin)
    {
        if (!is_object($plugin)) {
            return;
        }
        if (!method_exists($plugin, 'setController')) {
            return;
        }

        $controller = $this->getController();
        if (!$controller instanceof DispatchableInterface) {
            return;
        }

        $plugin->setController($controller);
    }

    /**
     * Validate the plugin
     *
     * Any plugin is considered valid in this context.
     *
     * @param  mixed $plugin
     * @return true
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Plugin\PluginInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidPluginException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Plugin\PluginInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
