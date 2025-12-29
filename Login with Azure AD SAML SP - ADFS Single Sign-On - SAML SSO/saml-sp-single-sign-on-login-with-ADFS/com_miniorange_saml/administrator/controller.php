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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Controller\BaseController;
/**
 * Class Miniorange_samlController
 *
 * @since  1.6
 */
class Miniorange_samlController extends AdminController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   mixed    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return   JController This object to support chaining.
	 *
	 * @since    1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
	$input = method_exists(Factory::getApplication(), 'getInput')
    ? Factory::getApplication()->getInput()
    : Factory::getApplication()->input;

	$view = $input->getCmd('view', 'myaccounts');
	$input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}
}
