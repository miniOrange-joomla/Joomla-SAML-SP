<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_miniorange_saml
 *
 * @author      miniOrange Security Software Pvt. Ltd. <info@xecurify.com>
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// No direct access
defined('_JEXEC') or die;

// Jimport('joomla.application.component.controllerform');
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
jimport('joomla.filesystem.file');
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components'
	. DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components'
	. DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-utility.php';

/**
 * Myaccount controller class.
 *
 * @since  1.6
 */
class Miniorange_SamlControllerMyaccount extends FormController
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		$config['view_list'] = 'myaccounts';
		parent::__construct($config);
	}

	/**
	 * Request trial plan from miniOrange.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function requestForTrialPlan()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		if ((!isset($post['email'])) || (!isset($post['plan'])) || (!isset($post['description'])))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=request_demo', Text::_('COM_MINIORANGE_SAML_MSG_A'), 'error');

			return;
		}

		$email = $post['email'];
		$plan = $post['plan'];
		$description = trim($post['description']);
		$demo = 'Trial';

		if (empty($email) || empty($plan) || empty($description))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=request_demo', Text::_('COM_MINIORANGE_SAML_MSG_A'), 'error');

			return;
		}

		if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=request_demo', Text::_('COM_MINIORANGE_SAML_MSG_21'), 'error');

			return;
		}

		$customer = new Mo_Saml_Local_Customer;
		$rawResponse = $customer->requestForTrial($email, $plan, $demo, $description);
		$response = json_decode(is_string($rawResponse) ? $rawResponse : '', true);
		$isErrorStatus = is_array($response) && isset($response['status'])
			&& strtoupper((string) $response['status']) === 'ERROR';

		if (json_last_error() !== JSON_ERROR_NONE || !is_array($response) || $isErrorStatus)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=request_demo', Text::_('COM_MINIORANGE_SAML_NOT_SUBMITTED_QUERY'), 'error');

			return;
		}

		$msg = ($demo == 'Demo') ? Text::sprintf('COM_MINIORANGE_SAML_MSG_B', $email) : Text::sprintf('COM_MINIORANGE_SAML_MSG_C', $email);
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=request_demo', $msg);

		return;

	}

	/**
	 * Save successful customer registration details.
	 *
	 * @param   array   $customerKey  Customer key data.
	 * @param   string  $message      Status message.
	 * @param   string  $email        Customer email.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function saveSuccessCustomer($customerKey, $message, $email = '')
	{
		if (empty($email))
		{
			$email = isset($customerKey['email']) ? $customerKey['email'] : '';
		}

		$apiKey = isset($customerKey['apiKey']) ? $customerKey['apiKey'] : '';
		$token = isset($customerKey['token']) ? $customerKey['token'] : '';
		$phone = isset($customerKey['phone']) ? $customerKey['phone'] : '';
		$customerId = isset($customerKey['id']) ? $customerKey['id'] : '';
		$this->saveSuccessCustomerConfig($email, $customerId, $apiKey, $token, $phone, $message);

	}

	/**
	 * Delete the current IDP configuration.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function deleteCurrentIdp()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp');

			return;
		}

		if (isset($post['idp_id']) && $post['idp_id'] > 0)
		{
			$db = MoSamlDbHelper::getDb();

			$query = $db->getQuery(true);

			// Delete all custom keys for user 1001.
			$conditions = array(
				$db->quoteName('id') . ' = ' . $db->quote($post['idp_id'])
			);

			$query->delete($db->quoteName('#__miniorange_saml_config'));
			$query->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			$query = $db->getQuery(true);

			// Delete all custom keys for user 1001.
			$conditions = array(
				$db->quoteName('idp_id') . ' = ' . $db->quote($post['idp_id'])
			);

			$query->delete($db->quoteName('#__miniorange_saml_role_mapping'));
			$query->where($conditions);
			$db->setQuery($query);

			$result = $db->execute();

			if ($result)
			{
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', Text::_('COM_MINIORANGE_SAML_IDP_CONFIG_DELETED'));

				return;
			}
			else
			{
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', Text::_('COM_MINIORANGE_SAML_NO_IDP_FOUND'));

				return;
			}
		}
		else
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', Text::_('COM_MINIORANGE_SAML_IDP_CONFIG_DELETION_FAILED'));

			return;
		}
	}

	/**
	 * Update SP issuer or base URL.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function updateSPIssuerOrBaseUrl()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		if ((!isset($post['sp_entity_id'])) || (!isset($post['sp_base_url'])))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=description');

			return;
		}

		$databaseName = '#__miniorange_saml_customer_details';
		$updatefieldsarray = array(
			'sp_entity_id' => trim($post['sp_entity_id']),
			'sp_base_url' => trim($post['sp_base_url']),
		);

		$result = new Mo_Saml_Local_Util;
		$result->genericUpdateQuery($databaseName, $updatefieldsarray);
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=description', Text::_('COM_MINIORANGE_SAML_MSG_G'));
	}

	/**
	 * Update organization details.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function updateOrganizationDetails()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		$databaseName = '#__miniorange_saml_customer_details';
		$updatefieldsarray = array(
			'organization_name' => trim($post['organization_name']),
			'organization_display_name' => trim($post['organization_display_name']),
			'organization_url' => trim($post['organization_url']),
			'tech_per_name' => trim($post['tech_per_name']),
			'tech_email_add' => trim($post['tech_email_add']),
			'support_per_name' => trim($post['support_per_name']),
			'support_email_add' => trim($post['support_email_add']),
		);

		$result = new Mo_Saml_Local_Util;
		$result->genericUpdateQuery($databaseName, $updatefieldsarray);
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=description', Text::_('COM_MINIORANGE_SAML_MSG_H'));

	}

	/**
	 * Save successful customer configuration to the database.
	 *
	 * @param   string  $email  Customer email.
	 * @param   string  $id     Customer ID.
	 * @param   string  $apiKey API key.
	 * @param   string  $token  Customer token.
	 * @param   string  $phone  Admin phone.
	 * @param   string  $msg    Redirect message.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function saveSuccessCustomerConfig($email, $id, $apiKey, $token, $phone, $msg)
	{
		$databaseName = '#__miniorange_saml_customer_details';
		$updatefieldsarray = array(
			'email' => $email,
			'customer_key' => $id,
			'api_key' => $apiKey,
			'customer_token' => $token,
			'admin_phone' => $phone,
			'login_status' => 0,
			'password' => '',
		);

		$result = new Mo_Saml_Local_Util;
		$result->genericUpdateQuery($databaseName, $updatefieldsarray);
		$erMsg = Text::_('COM_MINIORANGE_SAML_MSG_E');
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=licensing', $erMsg);
	}




	/**
	 * Handle metadata upload from file or URL.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function handleUploadMetadata()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		$idpName = isset($post['idp_name']) ? $post['idp_name'] : "idp1";
		require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'MetadataReader.php';

		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$file = Factory::getApplication()->getInput()->files->getArray();
		}
		else
		{
			$file = Factory::getApplication()->input->files->getArray();
		}

		if (isset($file['metadata_file']) || isset($post['metadata_url']))
		{
			if (!empty($file['metadata_file']['tmp_name']))
			{
				$file = $this->readValidatedUploadFile($file['metadata_file']['tmp_name']);

				if ($file === false)
				{
					$cause = Text::_('COM_MINIORANGE_SAML_MSG_INVALID_METADATA');
					SAML_Utilities::keepRecords('Metadata Fetched', $cause);
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp&id=1', $cause, 'error');

					return;
				}
			}
			else
			{
				$file = $this->fetchMetadataFromUrl(isset($post['metadata_url']) ? $post['metadata_url'] : '');

				if ($file === false || empty($file))
				{
					$cause = empty($post['metadata_url'])
						? Text::_('COM_MINIORANGE_SAML_MSG_J')
						: Text::_('COM_MINIORANGE_SAML_MSG_INVALID_METADATA');
					SAML_Utilities::keepRecords('Metadata Fetched', $cause);
					$this->setRedirect(
						'index.php?option=com_miniorange_saml&tab=idp' . (empty($post['metadata_url']) ? '' : '&id=1'),
						$cause,
						empty($post['metadata_url']) ? 'message' : 'error'
					);

					return;
				}
			}

			$this->uploadMetadata($file, $idpName);
		}
		else
		{
			$cause = Text::_('COM_MINIORANGE_SAML_MSG_J');
			SAML_Utilities::keepRecords('Metadata Fetched', $cause);
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp');

			return;
		}
	}

	/**
	 * Parse and save uploaded IDP metadata.
	 *
	 * @param   string  $file     Metadata XML content.
	 * @param   string  $idpName  IDP name.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function uploadMetadata($file, $idpName)
	{
		if (empty($file))
		{
			$cause = Text::_('COM_MINIORANGE_SAML_MSG_NO_METADATA_CONTENT');
			SAML_Utilities::keepRecords('Metadata Fetched', $cause);
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $cause, 'error');

			return;
		}

		$document = new DOMDocument;
		$document->loadXML($file);
		restore_error_handler();
		$firstChild = $document->firstChild;

		if (!empty($firstChild))
		{
			$metadata = new IDPMetadataReader($document);
			$identityProviders = $metadata->getIdentityProviders();

			if (empty($identityProviders))
			{
				$cause = Text::_('COM_MINIORANGE_SAML_MSG_K');
				SAML_Utilities::keepRecords('Metadata Fetched', $cause);
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $cause, 'error');

				return;
			}

			foreach ($identityProviders as $key => $idp)
			{
				$samlLoginUrl = $idp->getLoginURL('HTTP-Redirect');
				$samlIssuer = $idp->getEntityID();
				$samlX509Certificate = $idp->getSigningCertificate();
				$databaseName = '#__miniorange_saml_config';
				$updatefieldsarray = array(
					'idp_name' => $idpName,
					'idp_entity_id' => isset($samlIssuer) ? $samlIssuer : 0,
					'single_signon_service_url' => isset($samlLoginUrl) ? $samlLoginUrl : 0,
					'name_id_format' => "urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress",
					'binding' => 'HttpRedirect',
					'certificate' => isset($samlX509Certificate) ? $samlX509Certificate[0] : 0,
				);

				$result = new Mo_Saml_Local_Util;
				$result->genericUpdateQuery($databaseName, $updatefieldsarray);
				break;
			}

			$cause = Text::_('COM_MINIORANGE_SAML_MSG_TEST');
			SAML_Utilities::keepRecords('Metadata Fetched', $cause);
			$isAdmintoolsSystemEnabled = PluginHelper::isEnabled('system', 'admintools');
			$customer = new Mo_Saml_Local_Util;
			$configuration = $customer->loadDbValues('#__miniorange_saml_config');

			if ($isAdmintoolsSystemEnabled && $configuration['close_admintool_popup'] != 1)
			{
				SAML_Utilities::showAdminToolsMsg();

				return;
			}

			$test = 'onclick="showTestWindow()';
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', Text::sprintf('COM_MINIORANGE_SAML_MSG_TEST', $test));

			return;
		}
		else
		{
			$cause = Text::_('COM_MINIORANGE_SAML_MSG_K');
			SAML_Utilities::keepRecords('Metadata Fetched', $cause);
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $cause, 'error');

			return;
		}
	}

	/**
	 * Export plugin configuration as JSON.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function importexport()
	{
		$currentUserInfo = Factory::getUser();
		$lastVisistTime = '';
		$currentTime = time();
		$d = DateTime::createFromFormat(
			'Y-m-d H:i:s',
			$currentUserInfo->lastvisitDate,
			new DateTimeZone('GMT')
		);

		if ($d != false)
		{
			$lastVisistTime = $d->getTimestamp();
		}

		if ($lastVisistTime == $currentTime)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=overview');

			return;
		}

		$customer = new Mo_Saml_Local_Util;
		$customerResult = $customer->loadDbValues('#__miniorange_saml_config');

		if (empty($customerResult['idp_entity_id']) || empty($customerResult['single_signon_service_url']))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=com_miniorange_saml&tab=idp', Text::_('COM_MINIORANGE_SAML_MSG_M'), 'error');

			return;
		}

		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components'
			. DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'export.php';

		$tabClassName = array(
			'idp_info' => 'Mo_Idp_Info',
			'role_mapping' => 'Mo_Role_Mapping',
			'proxy' => 'Mo_Proxy',
		);
		$configurationArray = array();

		foreach ($tabClassName as $key => $value)
		{
			$configurationArray[$key] = $this->moGetConfigurationArray($value);
		}

		if ($configurationArray)
		{
			header("Content-Disposition: attachment; filename=miniorange-SP-config.json");
			echo (json_encode($configurationArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			exit;
		}

		if (isset($post) && $post['test_configuration'] == 'true')
		{
			$this->setRedirect('index.php??morequest=acs', Text::_('COM_MINIORANGE_SAML_MSG_N'));

			return;
		}

		$this->setRedirect('index.php?option=com_miniorange_saml&tab=import_export', Text::_('COM_MINIORANGE_SAML_MSG_N'));

		return;
	}

	/**
	 * Build configuration array for export.
	 *
	 * @param   string  $className  Export helper class name.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function moGetConfigurationArray($className)
	{
		if ($className == 'Mo_Idp_Info' || $className == 'Mo_Attribute_Mapping')
		{
			$customerResult = SAML_Utilities::getValuesFromTable('#__miniorange_saml_config');
		}

		if ($className == 'Mo_Role_Mapping')
		{
			$customerResult = SAML_Utilities::getValuesFromTable('#__miniorange_saml_role_mapping');
		}

		if ($className == 'Mo_Proxy')
		{
			$customerResult = SAML_Utilities::getValuesFromTable('#__miniorange_saml_proxy_setup');
		}

		$classObject = call_user_func($className . '::getConstants');
		$moArray = array();

		foreach ($classObject as $key => $value)
		{
			if ($moOptionExists = $customerResult[$value])
			{
				$moArray[$key] = $moOptionExists;
			}
		}

		return $moArray;
	}

	/**
	 * Save administrator email address.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function saveAdminMail()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('email') . ' = ' . $db->quote($post['admin_email']),

		);

		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);

		$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=com_miniorange_saml&tab=idp', Text::_('COM_MINIORANGE_SAML_MSG_O'));

		return;
	}
	/**
	 * Save proxy configuration.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function proxyConfig()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=overview');

			return;
		}

		$databaseName = '#__miniorange_saml_proxy_setup';
		$updatefieldsarray = array(
			'proxy_host_name' => isset($post['mo_proxy_host']) ? $post['mo_proxy_host'] : 0,
			'port_number' => isset($post['mo_proxy_port']) ? $post['mo_proxy_port'] : 0,
			'username' => isset($post['mo_proxy_username']) ? $post['mo_proxy_username'] : 0,
			'password' => isset($post['mo_proxy_password']) ? $post['mo_proxy_password'] : 0
		);

		$proxySetup = new Mo_Saml_Local_Util;
		$proxySetup->genericUpdateQuery($databaseName, $updatefieldsarray);

		// Save saml configuration
		$message = Text::_('COM_MINIORANGE_SAML_MSG_P');
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=overview', $message);
	}

	/**
	 * Reset proxy configuration.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function proxyConfigReset()
	{
		$databaseName = '#__miniorange_saml_proxy_setup';
		$updatefieldsarray = array('proxy_host_name' => '', 'port_number' => '', 'username' => '', 'password' => '');
		$proxySetup = new Mo_Saml_Local_Util;
		$proxySetup->genericUpdateQuery($databaseName, $updatefieldsarray);
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=overview', Text::_('COM_MINIORANGE_SAML_MSG_Q'));
	}

	/**
	 * Save SAML IDP and attribute configuration.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function saveConfig()
	{
		?>
		<script>
			function showTest() {
				var testconfigurl = window.location.href;
				testconfigurl = testconfigurl.substr(0, testconfigurl.indexOf('administrator')) + '?morequest=sso&q=test_config';
				var myWindow = window.open(testconfigurl, 'TEST SAML IDP', 'scrollbars=1 width=800, height=600');
			}
		</script>
		<?php

		$customer = new Mo_Saml_Local_Util;
		$customerResult = $customer->loadDbValues('#__miniorange_saml_config');

		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp');

			return;
		}

		if (isset($post['reset_config']))
		{
			$databaseName = '#__miniorange_saml_config';
			$updatefieldsarray = array(
				'idp_name' => '',
				'idp_entity_id' => '',
				'single_signon_service_url' => '',
				'name_id_format' => '',
				'certificate' => '',
			);
			$configReset = new Mo_Saml_Local_Util;
			$configReset->genericUpdateQuery($databaseName, $updatefieldsarray);
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp&id=1', Text::_('COM_MINIORANGE_SAML_MANUAL_CONFIG_CLEARED'));

			return;
		}

		$updatedSettings = isset($post['option1']) ? $post['option1'] : '';

		if ($updatedSettings == "mo_saml_save_config")
		{
			$upload = isset($post['cert']) ? $post['cert'] : '';

			if ($upload == "upload_cert")
			{
				if ($_FILES["myFile"]["name"] != "")
				{
					$certFileName = $_FILES["myFile"]["name"];
					$cerVar = explode(".", $certFileName);

					if ($cerVar[1] == 'cer' || $cerVar[1] == 'cert' || $cerVar[1] == 'crt')
					{
						$fileCert = $this->readValidatedUploadFile($_FILES['myFile']['tmp_name']);

						if ($fileCert === false)
						{
							$message = Text::_('COM_MINIORANGE_SAML_MSG_S');
							SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
							$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $message, 'error');

							return;
						}

						$certificate = SAML_Utilities::sanitizeCertificate($fileCert);

						if (!@openssl_x509_read($certificate))
						{
							$message = Text::_('COM_MINIORANGE_SAML_MSG_R');
							SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
							$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $message, 'error');

							return;
						}
					}
					else
					{
						$message = Text::_('COM_MINIORANGE_SAML_MSG_S');
						SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
						$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $message, 'error');

						return;
					}
				}
				else
				{
					$message = Text::_('COM_MINIORANGE_SAML_MSG_T');
					SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $message, 'error');

					return;
				}
			}
			else
			{
				if (isset($post['certificate']) && (!empty($post['certificate'])))
				{
					$certificate = SAML_Utilities::sanitizeCertificate($post['certificate']);

					if (!@openssl_x509_read($certificate))
					{
						$message = Text::_('COM_MINIORANGE_SAML_MSG_R');
						SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
						$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $message, 'error');

						return;
					}
				}
				else
				{
					$certificate = $post['certificate'];
				}
			}

			// Simple validation: if SAML login is enabled, check required fields
			$loginLinkCheck = isset($post['login_link_check']) ? $post['login_link_check'] : '0';

			if ($loginLinkCheck == '1')
			{
				if (empty($post['dynamic_link']))
				{
					$message = Text::_('COM_MINIORANGE_SAML_MSG_ENTER_LOGIN_BUTTON_NAME');
					SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp&id=1#', $message, 'error');

					return;
				}
			}

			$databaseName = '#__miniorange_saml_config';
			$idpName = isset($post['idp_name']) ? $post['idp_name'] : 'idp1';
			$idpEntityId = isset($post['idp_entity_id']) ? $post['idp_entity_id'] : '';
			$idpEntityId = SAML_Utilities::sanitizeUrl($idpEntityId);
			$updatefieldsarray = array(
				'idp_name' => $idpName,
				'idp_entity_id' => $idpEntityId,
				'single_signon_service_url' => isset($post['single_signon_service_url']) ? $post['single_signon_service_url'] : '',
				'name_id_format' => isset($post['name_id_format']) ? $post['name_id_format'] : '',
				'login_link_check' => isset($post['login_link_check']) ? $post['login_link_check'] : '0',
				'dynamic_link' => isset($post['dynamic_link']) ? $post['dynamic_link'] : "Login with IDP",
				'binding' => 'HttpRedirect',
				'certificate' => isset($certificate) ? $certificate : '',
			);

			$result = new Mo_Saml_Local_Util;
			$result->genericUpdateQuery($databaseName, $updatefieldsarray);
			$isAdmintoolsSystemEnabled = PluginHelper::isEnabled('system', 'admintools');

			if ($isAdmintoolsSystemEnabled && $customerResult['close_admintool_popup'] != 1)
			{
				SAML_Utilities::showAdminToolsMsg();

				return;
			}

			// Save saml configuration
			$message = Text::_('COM_MINIORANGE_SAML_MSG_TEST');
			SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
			$status = 'success';
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', $message);
		}
		elseif ($updatedSettings == "mo_saml_save_attribute")
		{
			$databaseName = '#__miniorange_saml_config';
			$updatefieldsarray = array(
				'enable_email' => isset($post['enable_email']) ? $post['enable_email'] : 0,
				'username' => isset($post['username']) ? $post['username'] : 0,
				'email' => isset($post['email']) ? $post['email'] : 0,
				'name' => isset($post['name']) ? $post['name'] : 0,
				'grp' => isset($post['grp']) ? $post['grp'] : 0,
			);

			$result = new Mo_Saml_Local_Util;
			$result->genericUpdateQuery($databaseName, $updatefieldsarray);

			// Save saml configuration
			$message = Text::_('COM_MINIORANGE_SAML_MSG_P');
			SAML_Utilities::keepRecords('Manual IDP Configuration', $message);
			$status = 'success';
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=attribute_mapping', $message);
		}
	}

	/**
	 * Save SAML role mapping configuration.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function saveRolemapping()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=group_mapping');

			return;
		}

		$maxAllowedMappings = 100;
		$addedMappingsCount = 0;
		$roleMappingKeyValue = array();

		for ($i = 1; $i <= $maxAllowedMappings; $i++)
		{
			if (isset($post['mapping_key_' . $i]))
			{
				if ($post['mapping_key_' . $i] == "")
				{
					continue;
				}

				$roleMappingKeyValue[($post['mapping_key_' . $i])] = $post['mapping_value_' . $i];
				$addedMappingsCount++;
			}
			else
			{
				break;
			}
		}

		$databaseName = '#__miniorange_saml_role_mapping';
		$updatefieldsarray = array(
			'mapping_value_default' => $post['mapping_value_default'],
			'mapping_memberof_attribute' => isset($post['mapping_memberof_attribute']) ? $post['mapping_memberof_attribute'] : '',
			'enable_saml_role_mapping' => isset($post['enable_role_mapping']) ? $post['enable_role_mapping'] : 0,
			'role_mapping_key_value' => json_encode($roleMappingKeyValue),
			'role_mapping_count' => $addedMappingsCount,
		);

		$result = new Mo_Saml_Local_Util;
		$result->genericUpdateQuery($databaseName, $updatefieldsarray);

		$result = new Mo_Saml_Local_Util;
		$result = $result->loadDbValues('#__miniorange_saml_role_mapping');

		$enableRoleMapping = $result['enable_saml_role_mapping'];
		$statusMessage = Text::_('COM_MINIORANGE_SAML_MSG_P');
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=group_mapping', '' . $statusMessage);
	}


	/**
	 * Cancel account form and clear customer details.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function cancelform()
	{
		$databaseName = '#__miniorange_saml_customer_details';
		$updatefieldsarray = array(
			'email' => '',
			'login_status' => 0,
			'sms_count' => 0,
		);

		$proxySetup = new Mo_Saml_Local_Util;
		$proxySetup->genericUpdateQuery($databaseName, $updatefieldsarray);
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=overview');
	}


	/**
	 * Submit contact-us query to miniOrange.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function contactUs()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab');

			return;
		}

		$phone = '';

		if (!empty($post['query_phone']))
		{
			$queryPhoneCode = isset($post['query_phone_code']) ? trim($post['query_phone_code']) : '';
			$queryPhoneNumber = preg_replace('/\D/', '', $post['query_phone']);
			$phone = $queryPhoneCode . $queryPhoneNumber;

			if ($phone !== '' && !preg_match("/^\+?[0-9]{6,15}$/", $phone))
			{
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab', Text::_('COM_MINIORANGE_SAML_MSG_15'), 'error');

				return;
			}
		}

		if (Mo_Saml_Local_Util::checkEmptyOrNull($post['query_email']))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab', Text::_('COM_MINIORANGE_SAML_MSG_16'), 'error');

			return;
		}
		elseif (Mo_Saml_Local_Util::checkEmptyOrNull(trim($post['mo_saml_query_support'] || trim($post['mo_saml_query_support']))))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab', Text::_('COM_MINIORANGE_SAML_MSG_20'), 'error');

			return;
		}
		else
		{
			$query = $post['mo_saml_query_support'];
			$email = $post['query_email'];
			$clientTimezone = isset($post['client_timezone']) ? $post['client_timezone'] : '';
			$clientTimezoneOffset = isset($post['client_timezone_offset']) ? $post['client_timezone_offset'] : '';
			$contactUs = new Mo_Saml_Local_Customer;
			$submited = $contactUs->submitContactUs($email, $phone, $query, $clientTimezone, $clientTimezoneOffset);

			if ($submited == 'Query submitted.')
			{
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab', Text::_('COM_MINIORANGE_SAML_MSG_18'));

				return;
			}
			elseif ($submited == 'Invalid email.')
			{
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab', Text::_('COM_MINIORANGE_SAML_MSG_21'), 'error');

				return;
			}
			else
			{
				$this->setRedirect(
					'index.php?option=com_miniorange_saml&tab=support_tab',
					Text::_('COM_MINIORANGE_SAML_NOT_SUBMITTED_QUERY'),
					'error'
				);

				return;
			}
		}
	}


	/**
	 * Check whether a value is empty or null.
	 *
	 * @param   mixed  $value  Value to check.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function checkEmptyOrNull($value)
	{
		if (!isset($value) || empty($value))
		{
			return true;
		}

		return false;
	}


	/**
	 * Close admin tools popup after test configuration.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function closePopup()
	{
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', Text::_('COM_MINIORANGE_SAML_MSG_TEST'));
	}

	/**
	 * Persist do-not-show-again preference for admin tools popup.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function doNotShowAgain()
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		$value = isset($post['do_not_show_again']) ? $post['do_not_show_again'] : 0;
		$databaseName = '#__miniorange_saml_config';
		$updatefieldsarray = array('close_admintool_popup' => $value);
		$saveConfig = new Mo_Saml_Local_Util;
		$saveConfig->genericUpdateQuery($databaseName, $updatefieldsarray);
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp', Text::_('COM_MINIORANGE_SAML_MSG_TEST'));
	}

	/**
	 * Schedule a setup call with miniOrange support.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function meetingSetup()
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getInput'))
		{
			$input = $app->getInput();
		}
		else
		{
			// Joomla 3
			$input = $app->input;
		}

		$post = $input->post->getArray();

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=overview');

			return;
		}

		$queryEmail = $post['mo_saml_setup_call_email'];
		$query = $post['mo_saml_setup_call_issue'];
		$description = $post['mo_saml_setup_call_desc'];
		$callDate = $post['mo_saml_setup_call_date'];
		$timeZone = $post['mo_saml_setup_call_timezone'];

		if (Mo_Saml_Local_Util::checkEmptyOrNull($timeZone)
			|| Mo_Saml_Local_Util::checkEmptyOrNull($callDate)
			|| Mo_Saml_Local_Util::checkEmptyOrNull($queryEmail)
			|| Mo_Saml_Local_Util::checkEmptyOrNull($query))
		{
			$this->setRedirect(
				'index.php?option=com_miniorange_saml&tab=support_tab',
				Text::_('COM_MINIORANGE_SAML_ENTER_ALL_FIELDS_TO_SETUP_A_CALL'),
				'error'
			);

			return;
		}
		elseif (!filter_var(trim($queryEmail), FILTER_VALIDATE_EMAIL))
		{
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab', Text::_('COM_MINIORANGE_SAML_MSG_21'), 'error');

			return;
		}
		else
		{
			$contactUs = new Mo_Saml_Local_Customer;
			$rawResponse = $contactUs->requestForSetupCall($queryEmail, $query, $description, $callDate, $timeZone);
			$submited = json_decode(is_string($rawResponse) ? $rawResponse : '', true);
			$isErrorStatus = is_array($submited) && isset($submited['status'])
				&& strtoupper((string) $submited['status']) === 'ERROR';

			if (json_last_error() !== JSON_ERROR_NONE || !is_array($submited) || $isErrorStatus)
			{
				$this->setRedirect(
					'index.php?option=com_miniorange_saml&tab=support_tab',
					Text::_('COM_MINIORANGE_SAML_NOT_SUBMITTED_QUERY'),
					'error'
				);

				return;
			}
			else
			{
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=support_tab', Text::_('COM_MINIORANGE_SAML_MSG_18'));

				return;
			}
		}
	}

	/**
	 * Read an uploaded file after validating tmp_name with is_uploaded_file() and realpath().
	 *
	 * @param   string  $tmpName  Upload tmp_name from $_FILES.
	 *
	 * @return  string|false  File contents or false when validation fails.
	 *
	 * @since   1.6
	 */
	private function readValidatedUploadFile($tmpName)
	{
		if (empty($tmpName) || !is_uploaded_file($tmpName))
		{
			return false;
		}

		$resolvedPath = realpath($tmpName);

		if ($resolvedPath === false || !is_readable($resolvedPath))
		{
			return false;
		}

		$uploadTempDir = realpath(sys_get_temp_dir());

		if ($uploadTempDir !== false)
		{
			$normalizedTempDir = strtolower(str_replace('\\', '/', $uploadTempDir));
			$normalizedPath = strtolower(str_replace('\\', '/', $resolvedPath));

			if (strpos($normalizedPath, $normalizedTempDir) !== 0)
			{
				return false;
			}
		}

		return file_get_contents($resolvedPath);
	}

	/**
	 * Fetch SAML metadata from an HTTP(S) URL only (blocks file:// and other schemes).
	 *
	 * @param   string  $metadataUrl  Metadata URL from request input.
	 *
	 * @return  string|false  Metadata XML content or false when validation fails.
	 *
	 * @since   1.6
	 */
	private function fetchMetadataFromUrl($metadataUrl)
	{
		$url = filter_var(trim($metadataUrl), FILTER_VALIDATE_URL);

		if ($url === false)
		{
			return false;
		}

		$parsedUrl = parse_url($url);

		if (!isset($parsedUrl['scheme'])
			|| !in_array(strtolower($parsedUrl['scheme']), array('http', 'https'), true))
		{
			return false;
		}

		$arrContextOptions = array(
			'http' => array(
				'follow_location' => 0,
				'max_redirects' => 0,
			),
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
			),
		);

		return @file_get_contents($url, false, stream_context_create($arrContextOptions));
	}

}
