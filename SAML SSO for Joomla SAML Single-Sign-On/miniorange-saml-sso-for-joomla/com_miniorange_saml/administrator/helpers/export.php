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

class Mo_Idp_Info extends BasicEnum
{
	const IDP_ENTITY_ID = "idp_entity_id";
	const NAME_ID_FORMAT = "name_id_format";
	const BINDING = "binding";
	const SINGLE_SIGNON_SERVICE_URL = "single_signon_service_url";
	const CERTIFICATE = 'certificate';
}

class Mo_Attribute_Mapping extends BasicEnum
{
	const NAME = "name";
}

class Mo_Role_Mapping extends BasicEnum
{
	const ENABLE_SAML_ROLE_MAPPING = "enable_saml_role_mapping";
	const MAPPING_VALUE_DEFAULT = "mapping_value_default";
}

class Mo_Proxy extends BasicEnum
{
	const PROXY_HOST_NAME = "proxy_host_name";
	const PORT_NUMBER = "port_number";
	const USERNAME = "username";
}
