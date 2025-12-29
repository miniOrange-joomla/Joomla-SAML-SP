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

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class Miniorange_samlFrontendHelper
 *
 * @since  1.6
 */
class Miniorange_samlHelpersMiniorange_saml
{
	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  Model name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_miniorange_saml/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_miniorange_saml/models/' . strtolower($name) . '.php';
			$model = BaseDatabaseModel::getInstance($name, 'Miniorange_samlModel');
		}

		return $model;
	}
}
