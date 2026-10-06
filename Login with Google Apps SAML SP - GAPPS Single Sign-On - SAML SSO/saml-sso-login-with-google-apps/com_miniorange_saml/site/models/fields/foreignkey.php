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

defined('JPATH_BASE') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';

/**
 * Supports a value from an external table
 *
 * @since  1.6
 */
class JFormFieldForeignKey extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var      string
	 * @since    1.6
	 */
	protected $type = 'foreignkey';

	/**
	 * Input type for the field.
	 *
	 * @var    string
	 */
	private $inputType;

	/**
	 * Database table name.
	 *
	 * @var    string
	 */
	private $table;

	/**
	 * Key column name.
	 *
	 * @var    string
	 */
	private $keyField;

	/**
	 * Value column name.
	 *
	 * @var    string
	 */
	private $valueField;

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		$this->inputType = $this->getAttribute('input_type');
		$this->table = $this->getAttribute('table');
		$this->keyField = (string) $this->getAttribute('key_field');
		$this->valueField = (string) $this->getAttribute('value_field');

		$html = '';

		$db    = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);

		$query
			->select(
				array(
					$db->quoteName($this->keyField),
					$db->quoteName($this->valueField)
				)
			)
			->from($this->table);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		$inputOptions = 'class="' . $this->getAttribute('class') . '"';

		switch ($this->inputType)
		{
			case 'list':
			default:
				$options = array();

				foreach ($results as $result)
				{
					$options[] = HTMLHelper::_('select.option', $result->{$this->keyField}, $result->{$this->valueField});
				}

				$value = $this->value;

				if (is_string($value))
				{
					$value = array($value);
				}
				elseif (is_object($value))
				{
					$value = get_object_vars($value);
				}

				if ($this->multiple)
				{
					$inputOptions .= 'multiple="multiple"';
				}
				else
				{
					array_unshift($options, HTMLHelper::_('select.option', '', ''));
				}

				$html = HTMLHelper::_('select.genericlist', $options, $this->name, $inputOptions, 'value', 'text', $value, $this->id);
				break;
		}

		return $html;
	}

	/**
	 * Wrapper method for getting attributes from the form element
	 *
	 * @param   string  $attrName  Attribute name
	 * @param   mixed   $default   Optional value to return if attribute not found
	 *
	 * @return mixed The value of the attribute if it exists, null otherwise
	 */
	public function getAttribute($attrName, $default = null)
	{
		if (!empty($this->element[$attrName]))
		{
			return $this->element[$attrName];
		}

		return $default;
	}
}
