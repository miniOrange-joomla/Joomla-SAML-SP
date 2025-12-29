<?php
/**
 * @package     Joomla.Component	
 * @subpackage  com_miniorange_saml
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\HTML\Sidebar;
use Joomla\CMS\Language\Text;

/**
 * Miniorange_saml helper.
 *
 * @since  1.6
 */
class Miniorange_samlHelpersMiniorange_saml
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  string
	 *
	 * @return void
	 */
	public static function addSubmenu($vName = '')
	{
	
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    CMSObject
	 *
	 * @since    1.6
	 */
	public static function getActions()
	{
		$user   = Factory::getUser();
		$result = new CMSObject;

		$assetName = 'com_miniorange_saml';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
