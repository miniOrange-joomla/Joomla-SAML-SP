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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

/**
 * View class for a list of Miniorange_saml.
 *
 * @since  1.6
 */
class Miniorange_samlViewMyaccounts extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		Miniorange_samlHelpersMiniorange_saml::addSubmenu('myaccounts');

        $this->addToolbar();

        parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
	

        if(MoConstants::MO_SAML_SP == "ALL")
        {
            ToolBarHelper::title(Text::_('COM_MINIORANGE_SAML_PLUGIN_TITLE'), 'logo mo_saml_sp_logo');
        }
		elseif(MoConstants::MO_SAML_SP == "ADFS")
        {
            ToolBarHelper::title(Text::_('COM_MINIORANGE_SAML_PLUGIN_TITLE_ADFS'), 'logo mo_saml_sp_logo');
        }
		elseif (MoConstants::MO_SAML_SP == "GOOGLEAPPS")
        {
            ToolBarHelper::title(Text::_('COM_MINIORANGE_SAML_PLUGIN_TITLE_GOOGLE'), 'logo mo_saml_sp_logo');
        }
	}

	/**
	 * Method to order fields 
	 *
	 * @return void 
	 */
	protected function getSortFields()
	{
		return array();
	}
}
