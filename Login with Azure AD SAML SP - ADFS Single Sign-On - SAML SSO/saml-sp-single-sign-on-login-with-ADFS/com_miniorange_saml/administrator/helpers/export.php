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


include "BasicEnum.php";

class mo_idp_info extends BasicEnum{
	
    const idp_entity_id = "idp_entity_id";
	const name_id_format="name_id_format";
	const binding ="binding";
	const single_signon_service_url = "single_signon_service_url";
    const certificate = 'certificate';
}

class mo_attribute_mapping extends BasicEnum{
	const name = "name";
}

class mo_role_mapping extends BasicEnum{
	
	const enable_saml_role_mapping = "enable_saml_role_mapping";
	const mapping_value_default ="mapping_value_default";
	
}

class mo_proxy extends BasicEnum{
	
	const proxy_host_name = "proxy_host_name";
	const port_number = "port_number";
	const username ="username";
	const password = "password";	
	
}
 
