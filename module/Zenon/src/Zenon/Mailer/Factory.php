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
use Zend\Mail\Transport\Smtp, Zend\Mail\Transport\SmtpOptions,
    Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for instantiating Mailer objects
 *
 * @category VuFind2
 * @package  Mailer
 * @author   Sebastian Cuy <sebastian.cuy@uni-koeln.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory implements \Zend\ServiceManager\FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $sm Service manager
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        // Load configurations:
        $config = $sm->get('VuFind\Config')->get('config');

        // Create mail transport:
        $settings = array (
            'host' => $config->Mail->host, 'port' => $config->Mail->port, 'name' => $config->Mail->name
        );
        if (isset($config->Mail->username) && isset($config->Mail->password)) {
            $settings['connection_class'] = 'login';
            $settings['connection_config'] = array(
                'username' => $config->Mail->username,
                'password' => $config->Mail->password
            );
        }
        if(isset($config->Mail->ssl)) {
            $settings['connection_config']['ssl'] = $config->Mail->ssl;
        }
        $transport = new Smtp();
        $transport->setOptions(new SmtpOptions($settings));

        // Create service:
        return new \VuFind\Mailer\Mailer($transport);
    }
}
