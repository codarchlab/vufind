<?php

namespace Zenon\Controller;
use Zend\ServiceManager\ServiceManager;
/**
 * Controller Factory Class
 *
 * @category Zenon
 * @package  Controller
 * @author   Simon Hohl <simon.hohl@dainst.org>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 */
class Factory
{
    /**
     * Construct a generic controller.
     *
     * @param string         $name Name of table to construct (fully qualified
     * class name, or else a class name within the current namespace)
     * @param ServiceManager $sm   Service manager
     *
     * @return object
     */
    public static function getGenericController($name, ServiceManager $sm)
    {
        // Prepend the current namespace unless we receive a FQCN:
        $class = (strpos($name, '\\') === false)
            ? __NAMESPACE__ . '\\' . $name : $name;
        if (!class_exists($class)) {
            throw new \Exception('Cannot construct ' . $class);
        }
        return new $class($sm->getServiceLocator());
    }
    /**
     * Construct a generic controller.
     *
     * @param string $name Method name being called
     * @param array  $args Method arguments
     *
     * @return object
     */
    public static function __callStatic($name, $args)
    {
        // Strip "get" from method name to get name of class; pass first argument
        // on assumption that it should be the ServiceManager object.
        return static::getGenericController(
            substr($name, 3), isset($args[0]) ? $args[0] : null
        );
    }

    /**
     * Construct the CartController.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return CartController
     */
    public static function getCartController(ServiceManager $sm)
    {
        return new CartController(
            $sm->getServiceLocator(),
            new \Zend\Session\Container(
                'cart_followup',
                $sm->getServiceLocator()->get('VuFind\SessionManager')
            )
        );
    }
}