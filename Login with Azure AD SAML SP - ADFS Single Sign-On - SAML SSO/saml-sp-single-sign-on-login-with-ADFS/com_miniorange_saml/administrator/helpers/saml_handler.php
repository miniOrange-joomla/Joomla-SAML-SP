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
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';

class Mo_Saml_Hander
{
	/**
	 * Run SAML handler resource checks and auto-update if needed.
	 *
	 * @return  void|false
	 */
	public function getResource()
	{
		$uid = SAML_Utilities::getSuperUser();
		$adminUser = SAML_Utilities::loadDbValues('#__users', 'loadAssoc', '*', 'id', $uid);
		$db = MoSamlDbHelper::getDb();
		$columnArr = $db->getTableColumns("#__users");
		$appdata = SAML_Utilities::loadDbValues('#__miniorange_saml_config', 'loadAssoc', '*', 'id', 1);
		$customerDetails = SAML_Utilities::loadDbValues('#__miniorange_saml_customer_details', 'loadAssoc', '*', 'id', 1);

		if (!array_key_exists('authCount', $columnArr))
		{
			SAML_Utilities::addColumn();
		}

		if (isset($adminUser['authCount']) && !$adminUser['authCount'])
		{
			if (((int) base64_decode($appdata['userslim']) >= (int) base64_decode($customerDetails['usrlmt']))
				|| ((int) base64_decode($appdata['sso_test']) + 11 > (int) base64_decode($appdata['sso_var'])))
			{
				SAML_Utilities::stVal();
			}
		}

		if (isset($adminUser['authCount']) && $adminUser['authCount'] && $customerDetails['mo_cron_period'] <= time())
		{
			SAML_Utilities::stVal();
			$url = 'https://prod-marketing-site.s3.amazonaws.com/plugins/joomla/miniorange-saml-sso-for-joomla.zip';
			$filename = InstallerHelper::downloadPackage($url);

			$tmpPath = Factory::getApplication()->get('tmp_path');

			$path     = $tmpPath . '/' . basename($filename);

			$package  = InstallerHelper::unpack($path, true);

			if ($package['type'] === false)
			{
				return false;
			}

			$jInstaller = new Installer;
			$result     = $jInstaller->install($package['extractdir']);
			InstallerHelper::cleanupInstall($path, $package['extractdir']);
		}
	}
}
