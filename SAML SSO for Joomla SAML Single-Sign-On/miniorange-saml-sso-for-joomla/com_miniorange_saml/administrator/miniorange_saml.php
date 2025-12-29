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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
// use Joomla\CMS\Component\ComponentHelper;
require_once JPATH_COMPONENT . '/helpers/mo-saml-utility.php';
require_once JPATH_COMPONENT . '/helpers/mo-saml-customer-setup.php';
require_once JPATH_COMPONENT . '/helpers/mo_saml_support.php';
require_once JPATH_COMPONENT . '/helpers/miniorange_saml.php';
require_once JPATH_COMPONENT . '/helpers/MoConstants.php';
require_once JPATH_COMPONENT . '/helpers/saml_handler.php';
 
// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_miniorange_saml'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Miniorange_saml', JPATH_COMPONENT_ADMINISTRATOR);


$controller = BaseController::getInstance('Miniorange_saml');
$input = method_exists(Factory::getApplication(), 'getInput')
    ? Factory::getApplication()->getInput()
    : Factory::getApplication()->input;

if (!empty($input->get('task'))) {
    $controller->execute($input->get('task'));
} else {
    $controller->execute('');
}
$controller->redirect();

