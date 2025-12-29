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
use Joomla\CMS\MVC\Controller\BaseController;

// Include dependancies
JLoader::registerPrefix('Miniorange_saml', JPATH_COMPONENT);
JLoader::register('Miniorange_samlController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
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
