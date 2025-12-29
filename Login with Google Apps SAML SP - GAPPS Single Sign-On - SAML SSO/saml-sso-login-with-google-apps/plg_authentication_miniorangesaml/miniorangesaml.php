<?php
/**
 * @package     Joomla.Plugin	
 * @subpackage  plg_authentication_miniorangesaml
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die; 

 use Joomla\CMS\Plugin\CMSPlugin; 
 use Joomla\CMS\Factory;
 
 if (defined('_JEXEC')) {

	/**
	 * This block of code is not used.
	 */
	class plgauthenticationminiorangesaml extends CMSPlugin 
	{
		/**
		 * This method should handle any authentication and report back to the subject
		 * 
		 *
		 * @access    public
		 * @param     array     $credentials    Array holding the user credentials ('username' and 'password')
		 * @param     array     $options        Array of extra options
		 * @param     object    $response       Authentication response object
		 * @return    boolean
		 */
		function onUserAuthenticate( $credentials, $options, &$response )
		{
			if (method_exists(Factory::getApplication(), 'getInput')) {
    			$cookie = Factory::getApplication()->getInput()->cookie->get('mosamlauthadmin');
			} else {
    			$cookie = Factory::getApplication()->input->cookie->get('mosamlauthadmin');
			}
			if (isset($cookie['mosamlauthadmin']) && $cookie['mosamlauthadmin'] != '-1'){
				$db = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select(array('api_key','customer_token'));
				$query->from($db->quoteName('#__miniorange_saml_customer_details'));
				$query->where($db->quoteName('id')." = 1");
				$db->setQuery($query);
				$customerResult = $db->loadAssoc();
			}
		}
	}
 }
