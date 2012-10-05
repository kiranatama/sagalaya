<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Mvc
 */

namespace Zend\Mvc\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\Di\DiAbstractServiceFactory;
use Zend\ServiceManager\Di\DiServiceInitializer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * @category   Zend
 * @package    Zend_Mvc
 * @subpackage Service
 */
class ControllerLoaderFactory implements FactoryInterface
{
    /**
     * Create the controller loader service
     *
     * Creates and returns a scoped service manager. The only controllers
     * this manager will allow are those defined in the application
     * configuration's "controllers" array. If a controller is matched, the
     * scoped manager will attempt to load the controller, pulling it from
     * a DI service if a matching service is not found. Finally, it will
     * attempt to inject the controller plugin manager if the controller
     * implements a setPluginManager() method.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ServiceManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (!$serviceLocator instanceof ServiceManager) {
            return $serviceLocator;
        }

        $controllerLoader = $serviceLocator->createScopedServiceManager();

        $configuration    = $serviceLocator->get('Config');
        if (isset($configuration['di']) && $serviceLocator->has('Di')) {
            $di = $serviceLocator->get('Di');
            $controllerLoader->addAbstractFactory(
                new DiAbstractServiceFactory($di, DiAbstractServiceFactory::USE_SL_BEFORE_DI)
            );
            $controllerLoader->addInitializer(
                new DiServiceInitializer($di, $serviceLocator)
            );
        }

        $controllerLoader->addInitializer(function ($instance) use ($serviceLocator) {
            if ($instance instanceof ServiceLocatorAwareInterface) {
                $instance->setServiceLocator($serviceLocator->get('Zend\ServiceManager\ServiceLocatorInterface'));
            }

            if ($instance instanceof EventManagerAwareInterface) {
                $instance->setEventManager($serviceLocator->get('EventManager'));
            }

            if (method_exists($instance, 'setPluginManager')) {
                $instance->setPluginManager($serviceLocator->get('ControllerPluginBroker'));
            }
        });

        return $controllerLoader;
    }
}
