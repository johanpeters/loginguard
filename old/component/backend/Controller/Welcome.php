<?php
/**
 * @package   AkeebaLoginGuard
 * @copyright Copyright (c)2016-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\LoginGuard\Admin\Controller;

use Akeeba\LoginGuard\Admin\Model\Welcome as WelcomeModel;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use JRoute;
use JText;
use RuntimeException;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Controller for the Welcome page in the backend of the site
 *
 * @package     Akeeba\LoginGuard\Admin\Controller
 *
 * @since       2.0.0
 */
class Welcome extends Controller
{
	/**
	 * Welcome constructor.
	 *
	 * Sets up the default task.
	 *
	 * @param   Container  $container
	 * @param   array      $config
	 *
	 * @since   2.0.0
	 */
	public function __construct(Container $container, array $config = array())
	{
		if (!isset($config['default_task']))
		{
			$config['default_task'] = 'welcome';
		}

		parent::__construct($container, $config);
	}

	/**
	 * Triggers before executing any task. We use it to limit this view to Super Users only.
	 *
	 * @param   string  $task
	 *
	 * @since   2.0.0
	 */
	protected function onBeforeExecute(&$task)
	{
		$this->assertSuperUser();

		$this->emptyIPs();
	}

	/**
	 * Displays the welcome screen for the Super User
	 *
	 * @since   2.0.0
	 */
	public function welcome()
	{
		$this->display();
	}

	/**
	 * Handle the update of the GeoIP database
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	public function updategeoip()
	{
		$message = JText::_('COM_LOGINGUARD_MSG_GEOIP_UPDATE_SUCCESS');
		$type = 'info';

		if (!$this->handleGeoIPUpdate(false))
		{
			$message = JText::_('COM_LOGINGUARD_MSG_GEOIP_UPDATE_FAIL');
			$type = 'error';
		}

		$this->setRedirect(JRoute::_('index.php?option=com_loginguard&view=welcome'), $message, $type);
	}

	/**
	 * Handle the upgrade of the GeoIP database to the city-level version
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	public function upgradeageoip()
	{
		$message = JText::_('COM_LOGINGUARD_MSG_GEOIP_UPGRADE_SUCCESS');
		$type = 'info';

		if (!$this->handleGeoIPUpdate(true))
		{
			$message = JText::_('COM_LOGINGUARD_MSG_GEOIP_UPGRADE_FAIL');
			$type = 'error';
		}

		$this->setRedirect(JRoute::_('index.php?option=com_loginguard&view=welcome'), $message, $type);

		return $this;
	}

	/**
	 * Handles the update of the GeoIP database
	 *
	 * @param   bool  $forceCity  Should I force an upgrade to the city-level database?
	 *
	 * @return  bool  True on success
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	protected function handleGeoIPUpdate($forceCity = false)
	{
		$this->csrfProtection();

		/** @var WelcomeModel $model */
		$model = $this->getModel();
		return $model->updateGeoIPDb($forceCity);
	}

	/**
	 * Assert that the user is a Super User
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function assertSuperUser()
	{
		if ($this->container->platform->authorise('core.admin', null))
		{
			return;
		}

		throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 403);
	}

	/**
	 * Empty any already collected IP addresses when the collect_ip component option is disabled.
	 *
	 * @return  void
	 */
	private function emptyIPs()
	{
		$collectIp = $this->container->params->get('collect_ip', true);

		if ($collectIp)
		{
			return;
		}

		$db = $this->container->db;
		$query = $db->getQuery(true)
			->update($db->qn('#__loginguard_tfa'))
			->set($db->qn('ip') . ' = ' . $db->q(''))
			->where($db->qn('ip') . ' != ' . $db->q(''))
		;
		$db->setQuery($query)->execute();
	}
}