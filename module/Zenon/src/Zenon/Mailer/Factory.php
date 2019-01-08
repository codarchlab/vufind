<?php
/**
 * Factory for instantiating Mailer objects.
 * Customized for Zenon in order to be able to set the
 * name and to use ssl.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2009.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Mailer
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace Zenon\Mailer;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;


/**
 * Factory for instantiating Mailer objects
 *
 * @category VuFind2
 * @package  Mailer
 * @author   Sebastian Cuy <sebastian.cuy@uni-koeln.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container Service manager
     * @param string $requestedName Service being created
     * @param null|array $options Extra options (optional)
     *
     * @return object
     *
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when creating a service.
     * @throws ContainerException if any other error occurs
     * @throws \Exception
     */

    public function __invoke(ContainerInterface $container, $requestedName,
                             array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }

        // Load configurations:
        $config = $container->get('VuFind\Config\PluginManager')->get('config');

        // Create service:
        $class = new $requestedName($this->getTransport($config));
        if (!empty($config->Mail->override_from)) {
            $class->setFromAddressOverride($config->Mail->override_from);
        }
        return $class;
    }
}
