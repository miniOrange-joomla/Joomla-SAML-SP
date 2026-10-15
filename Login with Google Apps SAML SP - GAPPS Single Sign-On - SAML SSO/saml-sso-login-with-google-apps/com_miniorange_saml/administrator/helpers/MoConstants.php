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

defined('_JEXEC') or die('Restricted access');

class MoConstants
{
	//const MO_SAML_SP = 'ALL';

	// Const MO_SAML_SP = 'ADFS';
	const MO_SAML_SP = 'GOOGLEAPPS';

	/**
	 * Default miniOrange cloud customer key. Override with MO_SAML_DEFAULT_CUSTOMER_KEY.
	 *
	 * @return  string
	 */
	public static function getDefaultCustomerKey()
	{
		$envKey = getenv('MO_SAML_DEFAULT_CUSTOMER_KEY');

		if ($envKey !== false && $envKey !== '')
		{
			return $envKey;
		}

		require_once __DIR__ . DIRECTORY_SEPARATOR . 'DbHelper.php';

		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true)
			->select($db->quoteName('customer_key'))
			->from($db->quoteName('#__miniorange_saml_customer_details'))
			->where($db->quoteName('id') . ' = 1');
		$db->setQuery($query);
		$customerKey = $db->loadResult();

		return self::decodeStoredCredential($customerKey);
	}

	/**
	 * Default miniOrange cloud API key. Override with MO_SAML_DEFAULT_API_KEY.
	 *
	 * @return  string
	 */
	public static function getDefaultApiKey()
	{
		$envKey = getenv('MO_SAML_DEFAULT_API_KEY');

		if ($envKey !== false && $envKey !== '')
		{
			return $envKey;
		}

		require_once __DIR__ . DIRECTORY_SEPARATOR . 'DbHelper.php';

		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true)
			->select($db->quoteName('api_key'))
			->from($db->quoteName('#__miniorange_saml_customer_details'))
			->where($db->quoteName('id') . ' = 1');
		$db->setQuery($query);
		$apiKey = $db->loadResult();

		return self::decodeStoredCredential($apiKey);
	}

	/**
	 * Decode a base64-encoded credential stored in the database.
	 *
	 * @param   string  $value  Stored credential value.
	 *
	 * @return  string
	 */
	private static function decodeStoredCredential($value)
	{
		if (empty($value))
		{
			return '';
		}

		$decoded = base64_decode($value, true);

		return ($decoded !== false) ? $decoded : $value;
	}
}
