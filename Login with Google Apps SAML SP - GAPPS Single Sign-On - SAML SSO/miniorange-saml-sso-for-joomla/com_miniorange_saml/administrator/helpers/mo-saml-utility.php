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

/**
This class contains all the utility functions

**/
class Mo_saml_Local_Util{

    public static function is_customer_registered()
    {
        $result      = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_customer_details');
        $email       = isset($result['email']) ? $result['email'] : '';
        $customerKey = isset($result['customer_key']) ? $result['customer_key'] : '';
        if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
            return 0;
        }
        return 1;
    }


    public static function GetPluginVersion()
    {
        $db = Factory::getDbo();
        $dbQuery = $db->getQuery(true)
        ->select('manifest_cache')
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_saml'));
        $db->setQuery($dbQuery);
        $manifest = json_decode($db->loadResult());
        return($manifest->version);
    }
 
    
    public static function check_empty_or_null( $value ) {
        return !isset($value) || empty($value) ? true : false;
    }
    
    public static function is_curl_installed() {
         return (in_array  ('curl', get_loaded_extensions())) ?  1 : 0;
    }

    public static function getHostname(){
        return "https://login.xecurify.com";
    }

    public function _load_db_values($table){
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $default_config = $db->loadAssoc();
        return $default_config;
    }

    public static function generic_update_query($database_name, $updatefieldsarray)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        // Check if an entry with id = 1 exists
        $query->select('COUNT(*)')
              ->from($db->quoteName($database_name))
              ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);
        $exists = $db->loadResult();
    
        if ($exists) {
            // Update existing record
            $fields = [];
            foreach ($updatefieldsarray as $key => $value) {
                $fields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
            }
            $query = $db->getQuery(true)
                        ->update($db->quoteName($database_name))
                        ->set($fields)
                        ->where($db->quoteName('id') . ' = 1');
        } else {
            // Insert new record with id = 1
            $updatefieldsarray['id'] = 1; // Ensure id = 1 is set
            $columns = array_keys($updatefieldsarray);
            $values = array_map([$db, 'quote'], array_values($updatefieldsarray));
    
            $query = $db->getQuery(true)
                        ->insert($db->quoteName($database_name))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));
        }
    
        $db->setQuery($query);
        $db->execute();
    }
    

    public static function loadDBValues($table, $load_by, $col_name = '*', $id_name = 'id', $id_value = 1){
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($col_name);

        $query->from($db->quoteName($table));
        if(is_numeric($id_value)){
            $query->where($db->quoteName($id_name)." = $id_value");

        }else{
            $query->where($db->quoteName($id_name) . " = " . $db->quote($id_value));
        }
        $db->setQuery($query);

        if($load_by == 'loadAssoc'){
            $default_config = $db->loadAssoc();
        }
        elseif ($load_by == 'loadResult'){
            $default_config = $db->loadResult();
        }
        elseif($load_by == 'loadColumn'){
            $default_config = $db->loadColumn();
        }
        return $default_config;
    }
}
