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
use Joomla\CMS\Factory;
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';

/**
 * Utility functions for miniOrange SAML local operations.
 *
 * @since  1.6
 */
class Mo_Saml_Local_Util
{
	/**
	 * Check whether a miniOrange customer is registered.
	 *
	 * @return  integer
	 */
	public static function isCustomerRegistered()
	{
		$result      = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_customer_details');
		$email       = isset($result['email']) ? $result['email'] : '';
		$customerKey = isset($result['customer_key']) ? $result['customer_key'] : '';

		if (! $email || ! $customerKey || ! is_numeric(trim($customerKey)))
		{
			return 0;
		}

		return 1;
	}

	/**
	 * Get the installed component version.
	 *
	 * @return  string
	 */
	public static function getPluginVersion()
	{
		$db = MoSamlDbHelper::getDb();
		$dbQuery = $db->getQuery(true)
			->select('manifest_cache')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_saml'));
		$db->setQuery($dbQuery);
		$manifest = json_decode($db->loadResult());

		return($manifest->version);
	}

	/**
	 * Check whether a value is empty or null.
	 *
	 * @param   mixed  $value  Value to check.
	 *
	 * @return  boolean
	 */
	public static function checkEmptyOrNull($value)
	{
		return !isset($value) || empty($value) ? true : false;
	}

	/**
	 * Check whether cURL extension is installed.
	 *
	 * @return  integer
	 */
	public static function isCurlInstalled()
	{
		 return (in_array('curl', get_loaded_extensions())) ? 1 : 0;
	}

	/**
	 * Get miniOrange hostname.
	 *
	 * @return  string
	 */
	public static function getHostname()
	{
		return "https://login.xecurify.com";
	}

	/**
	 * Load configuration row from a database table.
	 *
	 * @param   string  $table  Table name.
	 *
	 * @return  array|null
	 */
	public function loadDbValues($table)
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName($table));
		$query->where($db->quoteName('id') . " = 1");
		$db->setQuery($query);
		$defaultConfig = $db->loadAssoc();

		return $defaultConfig;
	}

	/**
	 * Insert or update a configuration row.
	 *
	 * @param   string  $databaseName        Table name.
	 * @param   array   $updatefieldsarray   Column values.
	 *
	 * @return  void
	 */
	public static function genericUpdateQuery($databaseName, $updatefieldsarray)
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);

		$query->select('COUNT(*)')
			->from($db->quoteName($databaseName))
			->where($db->quoteName('id') . ' = 1');
		$db->setQuery($query);
		$exists = $db->loadResult();

		if ($exists)
		{
			$fields = [];

			foreach ($updatefieldsarray as $key => $value)
			{
				$fields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
			}

			$query = $db->getQuery(true)
				->update($db->quoteName($databaseName))
				->set($fields)
				->where($db->quoteName('id') . ' = 1');
		}
		else
		{
			// Ensure id = 1 is set
			$updatefieldsarray['id'] = 1;
			$columns = array_keys($updatefieldsarray);
			$values = array_map([$db, 'quote'], array_values($updatefieldsarray));

			$query = $db->getQuery(true)
				->insert($db->quoteName($databaseName))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
		}

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Load values from a database table.
	 *
	 * @param   string       $table     Table name.
	 * @param   string       $loadBy    Load method name.
	 * @param   string       $colName   Column selection.
	 * @param   string       $idName    ID column name.
	 * @param   string|int   $idValue   ID column value.
	 *
	 * @return  mixed
	 */
	public static function loadValuesBy($table, $loadBy, $colName = '*', $idName = 'id', $idValue = 1)
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);

		$query->select($colName);

		$query->from($db->quoteName($table));

		if (is_numeric($idValue))
		{
			$query->where($db->quoteName($idName) . " = $idValue");
		}
		else
		{
			$query->where($db->quoteName($idName) . " = " . $db->quote($idValue));
		}

		$db->setQuery($query);

		if ($loadBy == 'loadAssoc')
		{
			$defaultConfig = $db->loadAssoc();
		}
		elseif ($loadBy == 'loadResult')
		{
			$defaultConfig = $db->loadResult();
		}
		elseif ($loadBy == 'loadColumn')
		{
			$defaultConfig = $db->loadColumn();
		}

		return $defaultConfig;
	}
}
