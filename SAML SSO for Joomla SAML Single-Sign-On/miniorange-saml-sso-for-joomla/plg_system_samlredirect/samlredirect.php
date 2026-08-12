<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_system_samlredirect
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Installer\Installer;
$lang = Factory::getLanguage();
$lang->load('plg_system_samlredirect', JPATH_ADMINISTRATOR);

jimport('miniorangesamlplugin.utility.SAML_Utilities');
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-utility.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'saml_handler.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';
/**
 * miniOrange SAML System plugin
 */
class PlgSystemSamlredirect extends CMSPlugin
{

	public function onAfterRender()
	{
		$app = Factory::getApplication();
		$body = $app->getBody();
		$url = Uri::root();
		$tab = 0;
		$tables = Factory::getDbo()->getTableList();

		foreach ($tables as $table)
		{
			if (strpos($table, "miniorange_saml_config") !== false)
			{
				$tab = $table;
			}
		}

		if ($tab === 0)
		{
			return;
		}

		if ($tab)
		{
			$jCmsVersion = (float) substr(SAML_Utilities::getJoomlaCmsVersion(), 0, 3);
			$linkStyleAttr = '';

			if ($jCmsVersion < 4.0)
			{
				$buttonStyle = 'border: 1px solid rgba(0, 0, 0, 0.2);color: #fff;background-color: #226a8b !important; padding: 4px 12px; border-radius: 3px;';
				$linkStyleAttr = "style='$buttonStyle' ";
			}

			$attribute = new Mo_Saml_Local_Util;
			$linkCheck = $attribute->loadDbValues('#__miniorange_saml_config');
			$linkChecked = isset($linkCheck['login_link_check']) && $linkCheck['login_link_check'] == 1;
			$isEntityID = isset($linkCheck['idp_entity_id']) && !empty($linkCheck['idp_entity_id']);
			$dynamicLink = empty($linkCheck['dynamic_link']) || !isset($linkCheck['dynamic_link']) ? "Login with IDP" : $linkCheck['dynamic_link'];

			if ($linkChecked == 1 && $isEntityID && $app->isClient('site'))
			{
				if (stristr($body, "Submit"))
				{
					if (stristr($body, "user.login"))
					{
						$linkPosition = "Log in</button><br><br><a " . $linkStyleAttr . "class='btn btn-primary w-100' href = " . $url . "?morequest=sso>" . $dynamicLink . " ";
						$body = str_replace('Log in</button>', $linkPosition . '</a>', $body);
						$app->setBody($body);
					}
				}
			}
		}
	}
	public function onAfterInitialise()
	{

		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$get = Factory::getApplication()->getInput()->getArray();
		}
		else
		{
			$get = Factory::getApplication()->input->getArray();
		}

		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		$tab = 0;
		$tables = Factory::getDbo()->getTableList();

		foreach ($tables as $table)
		{
			if ((strpos($table, "miniorange_saml_config") !== false) ||(strpos($table, "miniorange_saml_customer_details") !== false))
			{
				$tab = $table;
			}
		}

		if ($tab === 0)
		{
			return;
		}

		$customerResult = new Mo_Saml_Local_Util;
		$customerResult = $customerResult->loadDbValues('#__miniorange_saml_customer_details');
		$mailSent = isset($customerResult['mail_sent']) ? $customerResult['mail_sent'] : "admin@localhost";

		if ($mailSent == 0)
		{
			$mailSent = 1;
			require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-customer-setup.php';

			$baseUrl = Uri::root();
			$adminUser = SAML_Utilities::getSuperUser();
			$user = User::getInstance($adminUser);
			$adminEmail = $user->email;
			Mo_Saml_Local_Customer::installMailSent($adminEmail, $baseUrl);
			$databaseName = '#__miniorange_saml_customer_details';
			$updatefieldsarray = array(
			'mail_sent' => 1,
			);
			$result = new Mo_Saml_Local_Util;
			$result->genericUpdateQuery($databaseName, $updatefieldsarray);
		}

		if (isset($post['mojsp_feedback']) || isset($post['mojspfree_skip_feedback']))
		{
			if ($tab)
			{
				$radio = isset($post['deactivate_plugin']) ? $post['deactivate_plugin'] : '';
				$data = isset($post['query_feedback']) ? $post['query_feedback'] : '';
				$feedbackEmail = isset($post['feedback_email']) ? $post['feedback_email'] : '';

				$databaseName = '#__miniorange_saml_config';
				$updatefieldsarray = array(
					'uninstall_feedback' => 1,
				);
				$result = new Mo_Saml_Local_Util;
				$result->genericUpdateQuery($databaseName, $updatefieldsarray);
				$currentUser = Factory::getUser();

				$customerResult = new Mo_Saml_Local_Util;
				$customerResult = $customerResult->loadDbValues('#__miniorange_saml_customer_details');

				$dVar = new JConfig;
				$checkEmail = $dVar->mailfrom;
				$adminEmail = !empty($customerResult['admin_email']) ? $customerResult['admin_email'] : $checkEmail;
				$adminEmail = !empty($adminEmail) ? $adminEmail : self::getSuperUser();
				$adminPhone = $customerResult['admin_phone'];
				$data1 = $radio . ' : ' . $data;

				if (isset($post['mojspfree_skip_feedback']))
				{
					$data1 = 'Skipped the feedback';
				}

				if (file_exists(JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-customer-setup.php'))
				{
					require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-customer-setup.php';

					Mo_Saml_Local_Customer::submitFeedbackForm($adminEmail, $adminPhone, $data1, '', $feedbackEmail);
				}

				require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';

				foreach ($post['result'] as $fbkey)
				{
					$result = Mo_Saml_Local_Util::loadValuesBy('#__extensions', 'loadColumn', 'type',  'extension_id', $fbkey);
					$identifier = $fbkey;
					$type = 0;

					foreach ($result as $results)
					{
						$type = $results;
					}

					if ($type)
					{
						$installer = new Installer;

						// SetDatabase() exists only in Joomla 4+; Joomla 3 uses application DBO
						if (method_exists($installer, 'setDatabase'))
						{
							$installer->setDatabase(MoSamlDbHelper::getDb());
						}

						$installer->uninstall($type, $identifier);
					}
				}
			}
		}

		if ($tab)
		{
			$obj = new Mo_Saml_Local_Util;

			if (isset($get['morequest']) && $get['morequest'] == 'sso')
			{
				$pluginconfig = $obj->loadDbValues('#__miniorange_saml_config');
				$this->sendSamlRequest($pluginconfig);
			}
			elseif (isset($get['morequest']) && $get['morequest'] == 'metadata')
			{
				$customerResult = $obj->loadDbValues('#__miniorange_saml_customer_details');
				$this->generateMetadata($customerResult);
			}
			elseif (isset($get['morequest']) && $get['morequest'] == 'download_metadata')
			{
				$customerResult = $obj->loadDbValues('#__miniorange_saml_customer_details');
				$download = true;
				$this->generateMetadata($customerResult, $download);
			}
			elseif (isset($get['morequest']) && $get['morequest'] == 'acs')
			{
				$pluginconfig = $obj->loadDbValues('#__miniorange_saml_config');
				$this->getSamlResponse($pluginconfig);
			}
		}

	}

	public function loadLanguagePlugin()
	{
		$lang = Factory::getLanguage();
		$lang->load('plg_system_samlredirect', JPATH_ADMINISTRATOR);
	}


	public function onExtensionBeforeUninstall($id)
	{
		$this->loadLanguagePlugin();

		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		$tables = Factory::getDbo()->getTableList();
		$result = SAML_Utilities::loadDbValues('#__extensions', 'loadColumn', 'extension_id', 'element', 'com_miniorange_saml');
		$tables = Factory::getDbo()->getTableList();
		$tab = 0;
		$tables = Factory::getDbo()->getTableList();

		foreach ($tables as $table)
		{
			if (strpos($table, "miniorange_saml_config") !== false)
			{
				$tab = $table;
			}
		}

		if ($tab === 0)
		{
			return;
		}

		if ($tab)
		{
			$fid = new Mo_Saml_Local_Util;
			$fid = $fid->loadDbValues('#__miniorange_saml_config');
			$fid = isset($fid['uninstall_feedback']) ? $fid['uninstall_feedback'] : null;
			$tpostData = $post;
			$customerResult = new Mo_Saml_Local_Util;
			$customerResult = $customerResult->loadDbValues('#__miniorange_saml_customer_details');
			$dVar = new JConfig;
			$checkEmail = $dVar->mailfrom;
			$feedbackEmail = !empty($customerResult ['admin_email']) ? $customerResult ['admin_email'] : $checkEmail;

			if (1)
			{
				if ($fid == 0)
				{
					foreach ($result as $results)
					{
						if ($results == $id)
						{
							?>
							  <link rel="stylesheet" type="text/css" href="<?php echo Uri::base();?>/components/com_miniorange_saml/assets/css/mo_saml_style.css" />
								<div class="form-style-6 " style="width:35% !important; margin-left:33%; margin-top: 4%;">
									<form name="f" method="post" action="" id="mojspfree_feedback_form_close" style="display:inline;">
										   <h1><?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_14');?> 
										<input type="hidden" name="mojspfree_skip_feedback" value="mojspfree_skip_feedback"/>
										<button type="button" class="mojsp-close-btn" onClick="skipSAMLSPForm()" title="<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_SKIP_BTN'); ?>" aria-label="<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_SKIP_BTN'); ?>" style="cursor:pointer;font-size:36px;color:#fff;margin-left:6px;background:none;border:none;padding:0;line-height:1;display:inline-flex;align-items:center;">&times;</button>
										<?php
										$this->renderFeedbackHiddenIds($tpostData['cid']);
										?>
									</h1></form>
									<form name="f" method="post" action="" id="mojsp_feedback" style="background: #f3f1f1; padding: 10px;">
										<h3><?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_15');?> </h3>
										<input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>
										<div>
											<p style="margin-left:2%">
											<?php
											$deactivateReasons = array(
												Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_1'),
												Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_2'),
												Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_3'),
												Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_4'),
												Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_5'),
												Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_6'),
												Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_7')
											);

											$this->renderDeactivateReasonOptions($deactivateReasons);
											?>
											<br>

											<textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:3%;width: 100%" cols="50" minlength="10" placeholder="<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_WRITE_QUERY'); ?>" title="<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_WRITE_QUERY'); ?> (min 10 characters)"></textarea><br><br><br>
											<tr>
												<td width="20%"><strong><?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_EMAIL'); ?><span style="color: #ff0000;">*</span>:</strong></td>
												<td><input type="email" name="feedback_email" required value="<?php echo $feedbackEmail; ?>" placeholder="<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_ENTER_EMAIL'); ?>" style="width:80%"/></td>
											</tr>

											<?php
											$this->renderFeedbackHiddenIds($tpostData['cid']);
											?>
											<br><br>
											<div class="mojsp_modal-footer">
												<input style="cursor: pointer;font-size: large;" type="submit" name="miniorange_feedback_submit" class="button button-primary button-large" value="<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_SUBMIT_BTN'); ?>"/>
											</div>
										</div>
									</form>
								 
								</div>
								<script src="https://code.jquery.com/jquery-3.6.3.js"></script>
								<script>
									jQuery('input:radio[name="deactivate_plugin"]').click(function () {
										var reason = jQuery(this).val();
										jQuery('#query_feedback').removeAttr('required');
										if (reason === '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_1'); ?>') {
											jQuery('#query_feedback').attr("placeholder",'<?php echo  Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_8'); ?>');
										} else if (reason === '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_2'); ?>') {
											jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_10'); ?>');
										} else if (reason === '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_4'); ?>'){
											jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_11'); ?>');
										} else if (reason === '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_6'); ?>'){
											jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_9'); ?>');
										} else if (reason === '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_7'); ?>' || reason === '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_5'); ?>' ) {
											jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_12'); ?>');
										} else if (reason === '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_3'); ?>') {
											jQuery('#query_feedback').attr("placeholder", '<?php echo Text::_('PLG_SYSTEM_SAMLREDIRECT_FEEDBACK_13'); ?>');
										}
										jQuery('#query_feedback').prop('required', true).attr('minlength', 10);
									});

									function skipSAMLSPForm(){
										jQuery('#mojspfree_feedback_form_close').submit();
									}
								</script>
								<?php
								exit;
						}
					}
				}
			}
		}

	}

	public function sendSamlRequest($pluginconfig)
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$get = Factory::getApplication()->getInput()->getArray();
		}
		else
		{
			$get = Factory::getApplication()->input->getArray();
		}

		$siteUrl = Uri::root();
		$spBaseUrl = $siteUrl;

		$result = new Mo_Saml_Local_Util;
		$result = $result->loadDbValues('#__miniorange_saml_config');
		$obj = new Mo_Saml_Local_Util;
		$customerResult = $obj->loadDbValues('#__miniorange_saml_customer_details');
		$idpEntityId = isset($result['idp_entity_id']) && !empty($result['idp_entity_id']);
		$spEntityId = isset($customerResult['sp_entity_id']) ? $customerResult['sp_entity_id'] : "";

		if ($spEntityId == '')
		{
			$spEntityId = $siteUrl . 'plugins/authentication/miniorangesaml';
		}

		if (!defined('_JDEFINES'))
		{
			require_once JPATH_BASE . '/includes/defines.php';
		}

		require_once JPATH_BASE . '/includes/framework.php';

		// Instantiate the application.
		$app = Factory::getApplication('site');

		$jCmsVersion = SAML_Utilities::getJoomlaCmsVersion();
		$jCmsVersion = substr($jCmsVersion, 0, 3);

		if ($jCmsVersion < 4.0)
		{
			$app->initialise();
		}

		$loginUrl = $spBaseUrl;

		$user = Factory::getUser();

		// Get current user info

		$acsUrl = $spBaseUrl . '?morequest=acs';
		$ssoUrl = $pluginconfig['single_signon_service_url'];
		$ssoBindingType = $pluginconfig['binding'];
		$nameIdFormat = $pluginconfig['name_id_format'];

		$sendRelayState = $this->getRelayState($spBaseUrl, $_REQUEST);

		if (!$idpEntityId)
		{
			SAML_Utilities::samlNotConfigured($sendRelayState);
		}

		$samlRequest = SAML_Utilities::createAuthnRequest($acsUrl, $spEntityId, $ssoUrl, $nameIdFormat, 'false', $ssoBindingType);

		if (isset($get['q']))
		{
			if ($get['q'] == "sso")
			{
				$this->moSamlShowSamlLog($samlRequest, "displaySAMLRequest");
			}
		}

		$samlRequest = SAML_Utilities::samlRequestBind($samlRequest, $ssoBindingType);
		$this->sendSamlRequestByBindingType($samlRequest, $ssoBindingType, $sendRelayState, $ssoUrl);
	}

	public function moSamlShowSamlLog($samlRequestResponceXML, $type)
	{
		header("Content-Type: text/html");
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML($samlRequestResponceXML);

		if ($type == 'displaySAMLRequest')
		{
			$showValue = 'SAML Request';
		}
		else
		{
			$showValue = 'SAML Response';
		}

		$out = $doc->saveXML();

		$out1 = htmlentities($out);
		$out1 = rtrim($out1);
		$xml = simplexml_load_string($out);
		$json = json_encode($xml);
		$array = json_decode($json);

		echo '<link rel="stylesheet" type="text/css" href="' . Uri::root() . 'media/com_miniorange_saml/css/style_settings.css"/>
	            <div class="mo-display-logs" >
	                <p type="text"   id="SAML_type">' . $showValue . '</p>
	            </div>
	            <div type="text" id="SAML_display" class="mo-display-block">
	                <pre class=\'brush: xml;\'>' . $out1 . '</pre>
	            </div><br>
	            <div style="margin:3%;display:block;text-align:center;">
	                <div style="margin:3%;display:block;text-align:center;" ></div>
	                <button id="copy" onclick="copyDivToClipboard()" class="mo_saml_logs_css">Copy</button>&nbsp;
	                <input id="dwn-btn" class="mo_saml_download_css "type="button" value="Download">
	            </div>
	            </div>';

		ob_end_flush(); ?>

			<script>

				function copyDivToClipboard() {
					var aux = document.createElement("input");
					aux.setAttribute("value", document.getElementById("SAML_display").textContent);
					document.body.appendChild(aux);
					aux.select();
					document.execCommand("copy");
					document.body.removeChild(aux);
					document.getElementById('copy').textContent = "Copied";
					document.getElementById('copy').style.background = "grey";
					window.getSelection().selectAllChildren(document.getElementById("SAML_display"));
				}

				function download(filename, text) {
					var element = document.createElement('a');
					element.setAttribute('href', 'data:Application/octet-stream;charset=utf-8,' + encodeURIComponent(text));
					element.setAttribute('download', filename);

					element.style.display = 'none';
					document.body.appendChild(element);

					element.click();

					document.body.removeChild(element);
				}

				document.getElementById("dwn-btn").addEventListener("click", function () {

					var filename = document.getElementById("SAML_type").textContent + ".xml";
					var node = document.getElementById("SAML_display");
					htmlContent = node.innerHTML;
					text = node.textContent;
					console.log(text);
					download(filename, text);
				}, false);

			</script>
		<?php
		exit;
	}

	public function sendSamlRequestByBindingType($samlRequest, $ssoBindingType, $sendRelayState, $ssoUrl)
	{

		if (empty($ssoBindingType) || $ssoBindingType == 'HttpRedirect')
		{
			$samlRequest = "SAMLRequest=" . $samlRequest . "&RelayState=" . rawurlencode($sendRelayState);

			$param = array('type' => 'private');

			$redirect = $ssoUrl;

			if (strpos($ssoUrl, '?') !== false)
			{
				$redirect .= '&';
			}
			else
			{
				$redirect .= '?';
			}

			$redirect .= $samlRequest;

			header('Location: ' . $redirect);
			exit();
		}
	}

	public function getRelayState($spBaseUrl, $request)
	{

		$sendRelayState = $spBaseUrl;

		if (isset($request['q']))
		{
			if ($request['q'] == 'test_config')
			{
				$sendRelayState = 'testValidate';
			}
		}
		elseif (isset($request['RelayState']) && $request['RelayState'] != '/' && $request['RelayState'] != '')
		{
			$sendRelayState = $this->sanitizeRelayState($request['RelayState'], $spBaseUrl);
		}
		elseif (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '')
		{
			$sendRelayState = $this->sanitizeRelayState($_SERVER['HTTP_REFERER'], $spBaseUrl);
		}

		return $sendRelayState;
	}

	/**
	 * Restrict RelayState values to same-site URLs or known SAML flow tokens.
	 *
	 * @param   string  $relayState   Candidate RelayState from the request.
	 * @param   string  $defaultUrl   Fallback when the candidate is missing or untrusted.
	 *
	 * @return  string
	 */
	private function sanitizeRelayState($relayState, $defaultUrl)
	{
		if (!is_string($relayState))
		{
			return $defaultUrl;
		}

		$relayState = trim($relayState);

		if ($relayState === '' || $relayState === '/')
		{
			return $defaultUrl;
		}

		$allowedTokens = array('testValidate', 'response');

		if (in_array($relayState, $allowedTokens, true))
		{
			return $relayState;
		}

		if (preg_match('#^(javascript|data|vbscript):#i', $relayState) || strpos($relayState, '//') === 0)
		{
			return $defaultUrl;
		}

		$siteRoot = rtrim(Uri::root(), '/');
		$siteParts = parse_url($siteRoot);
		$siteHost = isset($siteParts['host']) ? strtolower($siteParts['host']) : '';

		if ($relayState[0] === '/')
		{
			return $siteRoot . $relayState;
		}

		$parts = parse_url($relayState);

		if ($parts === false || !isset($parts['host']))
		{
			return $defaultUrl;
		}

		$host = strtolower($parts['host']);

		if ($host !== $siteHost)
		{
			return $defaultUrl;
		}

		$scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';

		if ($scheme !== '' && $scheme !== 'http' && $scheme !== 'https')
		{
			return $defaultUrl;
		}

		return $relayState;
	}

	public function getSamlResponse($pluginconfig)
	{
		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$post = Factory::getApplication()->getInput()->post->getArray();
		}
		else
		{
			$post = Factory::getApplication()->input->post->getArray();
		}

		$obj = new Mo_Saml_Local_Util;

		if (!defined('_JDEFINES'))
		{
			require_once JPATH_BASE . '/includes/defines.php';
		}

		require_once JPATH_BASE . '/includes/framework.php';

		if (isset($post['fix_issuer_issue']) && $post['fix_issuer_issue'] == 'true')
		{
			$databaseName = '#__miniorange_saml_customer_details';

			if (isset($post['type']) && $post['type'] == 'change_sp_id')
			{
				$updatefieldsarray = array(
					'sp_entity_id' => ($post['issuer']),
				);
				$obj->genericUpdateQuery($databaseName, $updatefieldsarray);
			}
			elseif (isset($post['type']) && $post['type'] == 'change_idp_id')
			{
				$updatefieldsarray = array(
					'idp_entity_id' => ($post['issuer']),
				);
				$obj->genericUpdateQuery($databaseName, $updatefieldsarray);
			}
		}

		if (isset($post['quick_fix_cert']) && ($post['quick_fix_cert'] == 'true'))
		{
			$cert = isset($post['expected_cert']) ? $post['expected_cert'] : '';
			$databaseName = '#__miniorange_saml_config';
			$updatefieldsarray = array(
				'certificate' => $cert,
			);
			$obj->genericUpdateQuery($databaseName, $updatefieldsarray);
		}

		$authBase = JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'authentication' . DIRECTORY_SEPARATOR . 'miniorangesaml';
		include_once $authBase . DIRECTORY_SEPARATOR . 'saml2' . DIRECTORY_SEPARATOR . 'Response.php';
		jimport('miniorangesamlplugin.utility.encryption');
		jimport('joomla.application.application');
		jimport('joomla.html.parameter');
		$pluginconfig = $obj->loadDbValues('#__miniorange_saml_config');
		$customerDetails = $obj->loadDbValues('#__miniorange_saml_customer_details');
		$spBaseUrl = "";
		$spEntityId = "";

		if (isset($customerDetails['sp_base_url']))
		{
			$spBaseUrl = $customerDetails['sp_base_url'];
			$spEntityId = $customerDetails['sp_entity_id'];
		}

		if (isset($customerDetails['sp_entity_id']))
		{
			$spEntityId = $customerDetails['sp_entity_id'];
		}

		$siteUrl = Uri::root();

		if (empty($spBaseUrl))
		{
			$spBaseUrl = $siteUrl;
		}

		if (empty($spEntityId))
		{
			$spEntityId = $siteUrl . 'plugins/authentication/miniorangesaml';
		}

		$app = Factory::getApplication('site');

		$jCmsVersion = SAML_Utilities::getJoomlaCmsVersion();
		$jCmsVersion = substr($jCmsVersion, 0, 3);

		if ($jCmsVersion < 4.0)
		{
			$app->initialise();
		}

		if (method_exists(Factory::getApplication(), 'getInput'))
		{
			$get = Factory::getApplication()->getInput()->getArray();
		}
		else
		{
			$get = Factory::getApplication()->input->getArray();
		}

		if (array_key_exists('SAMLResponse', $post))
		{
			(new Mo_Saml_Hander)->getResource();
			$this->validateSamlResponse($post, $spBaseUrl, $spEntityId, $pluginconfig, $app);
		}
		else
		{
			throw new Exception('Missing SAMLRequest or SAMLResponse parameter.');
		}
	}

	public function validateSamlResponse($post, $spBaseUrl, $spEntityId, $attribute, $app)
	{
		$samlResponse = $post ['SAMLResponse'];

		if (array_key_exists('RelayState', $_REQUEST) && ($_REQUEST['RelayState'] != '') && ($_REQUEST['RelayState'] != '/'))
		{
			$relayState = $this->sanitizeRelayState($_REQUEST['RelayState'], $spBaseUrl);
		}
		else
		{
			$relayState = $spBaseUrl;
		}

		$samlResponse = base64_decode($samlResponse);

		$document = new DOMDocument;
		$document->loadXML($samlResponse);
		$samlResponseXml = $document->firstChild;
		$doc = $document->documentElement;

		$xpath = new DOMXpath($document);
		$xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
		$xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

		$notBeforeNode = $xpath->query("//saml:Conditions/@NotBefore")->item(0);
		$notOnOrAfterNode = $xpath->query("//saml:Conditions/@NotOnOrAfter")->item(0);
		$notBefore = $notBeforeNode ? strtotime($notBeforeNode->nodeValue) : null;
		$notOnOrAfter = $notOnOrAfterNode ? strtotime($notOnOrAfterNode->nodeValue) : null;
		$currentTime = time();
		$buffer = 120;

		if ($notBefore !== null && $notOnOrAfter !== null)
		{
			if (($currentTime + $buffer) < $notBefore
				|| ($currentTime - $buffer) >= $notOnOrAfter
			)
			{
				$message = "SAML Response is expired or not valid.";
				$cause = "The SAML Response time is outside the valid window.";
				$this->showExpiryError($message, $cause);
			}
		}

		$status = $xpath->query('/samlp:Response/samlp:Status/samlp:StatusCode', $doc);
		$statusString = $status->item(0)->getAttribute('Value');
		$statusChildString = '';

		if ($status->item(0)->firstChild !== null)
		{
			$statusChildString = $status->item(0)->firstChild->getAttribute('Value');
		}

		$stat = explode(":", $statusString);
		$status = $stat[7];

		if ($relayState == "response")
		{
			$this->moSamlShowSamlLog($samlResponse, "displaySAMLResponse");
		}

		if ($status != "Success")
		{
			if (!empty($statusChildString))
			{
				$stat = explode(":", $statusChildString);
				$status = $stat[7];
			}

			$this->showErrorMessage($status, $relayState);
		}

		$acsUrl = $spBaseUrl . '?morequest=acs';

		$certFromPlugin = $attribute['certificate'];

		if (!empty($certFromPlugin))
		{
			$certFromPlugin = SAML_Utilities::sanitizeCertificate($certFromPlugin);
		}

		$certfpFromPlugin = XMLSecurityKey::getRawThumbprint($certFromPlugin);

		try
		{
			$samlResponse = new SAML2_Response($samlResponseXml);

			$responseSignatureData = $samlResponse->getSignatureData();

			$assertionSignatureData = current($samlResponse->getAssertions())->getSignatureData();

			// Convert to UTF-8 character encoding

			$certfpFromPlugin = iconv("UTF-8", "CP1252//IGNORE", $certfpFromPlugin);

			// Remove whitespaces

			$certfpFromPlugin = preg_replace('/\s+/', '', $certfpFromPlugin);

			// /* Validate signature */
			if (!empty($certfpFromPlugin))
			{
				if (!empty($responseSignatureData))
				{
					$validSignature = SAML_Utilities::processResponse($acsUrl, $certfpFromPlugin, $responseSignatureData, $samlResponse, $certFromPlugin, $relayState, $post);

					if ($validSignature === false)
					{
						echo '' . Text::_('PLG_SYSTEM_SAMLREDIRECT_INVALID_SIGN_RESP') . '';
						exit;
					}
				}

				if (!empty($assertionSignatureData))
				{
					$validSignature = SAML_Utilities::processResponse($acsUrl, $certfpFromPlugin, $assertionSignatureData, $samlResponse, $certFromPlugin, $relayState, $post);

					if ($validSignature === false)
					{
						echo '' . Text::_('PLG_SYSTEM_SAMLREDIRECT_INVALID_SIGN_ASSERTION') . '';
						exit;
					}
				}
			}

			$db = MoSamlDbHelper::getDb();
			$appdata = new Mo_Saml_Local_Util;
			$appdata = $appdata->loadDbValues('#__miniorange_saml_config');
			$uid = SAML_Utilities::getSuperUser();
			$adminUserRecord = SAML_Utilities::loadDbValues('#__users', 'loadAssoc', '*', 'id', $uid);

			// Verify the issuer and audience from saml response
			$issuer = $appdata['idp_entity_id'];

			SAML_Utilities::validateIssuerAndAudience($samlResponse, $spEntityId, $issuer, $relayState, $post);

			$username = current(current($samlResponse->getAssertions())->getNameId());
			$attrs = current($samlResponse->getAssertions())->getAttributes();
			$attrs ['NameID'] = current(current($samlResponse->getAssertions())->getNameId());

			if ($relayState == 'testValidate')
			{
				SAML_Utilities::moSamlShowTestResult($username, $attrs, $spBaseUrl);
			}

			$sessionIndex = current($samlResponse->getAssertions())->getSessionIndex();
			$attrs ['ASSERTION_SESSION_INDEX'] = $sessionIndex;

			if (method_exists(Factory::getApplication(), 'getInput'))
			{
				$posts = Factory::getApplication()->getInput()->post->getArray();
			}
			else
			{
				$posts = Factory::getApplication()->input->post->getArray();
			}

			if (isset($posts['quick_fix_attributes']) && ($posts['quick_fix_attributes'] == 'true'))
			{
				foreach ($attrs as $attr)
				{
					if (filter_var($attr[0], FILTER_VALIDATE_EMAIL))
					{
						$username = $attr[0];
					}
				}

				$session = Factory::getSession();
				$session->set('quick_fix_attributes', "true");
				$session->set('attribute_email', $username);
			}

			$email = $username;
			$name = '';
			$samlGroups = '';

			$nameMapping = "";
			$usernameMapping = $attribute['username'];
			$mailMapping = $attribute['email'];

			if (!empty($usernameMapping) && isset($attrs[$usernameMapping]) && !empty($attrs[$usernameMapping]))
			{
				$username = $attrs[$usernameMapping];

				if (is_array($username))
				{
					$username = $username[0];
				}
			}

			if (!empty($mailMapping) && isset($attrs[$mailMapping]) && !empty($attrs[$mailMapping]))
			{
				$email = $attrs[$mailMapping];

				if (is_array($email))
				{
					$email = $email[0];
				}
			}

			if (!empty($nameMapping) && isset($attrs[$nameMapping]) && !empty($attrs[$nameMapping]))
			{
				$name = $attrs[$nameMapping];
			}

			if (is_array($name))
			{
				$name = $name[0];
			}

			if (!empty($groupsMapping) && isset($attrs[$groupsMapping]) && !empty($attrs[$groupsMapping]))
			{
				$samlGroups = $attrs[$groupsMapping];
			}
			else
			{
				$samlGroups = array();
			}

			$matcher = 'email';

			$result = SAML_Utilities::getUserFromJoomla($matcher, $username, $email);
			$loginUrl = isset($relayState) ? $this->sanitizeRelayState($relayState, $spBaseUrl) : $spBaseUrl;

			if ($result)
			{
				$this->loginCurrentUser($result, $attrs, $loginUrl, $name, $username, $email, $matcher, $app);
			}
			elseif (isset($adminUserRecord['authCount']) && $adminUserRecord['authCount'])
			{
				SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
				SAML_Utilities::keepRecords('SSO Status', 'Registration Flow');
				SAML_Utilities::rmex();
			}
			else
			{
				$this->registerCurrentUser($attrs, $loginUrl, $name, $username, $email, $samlGroups, $matcher, $app, $post);
			}
		}
		catch (Exception $e)
		{
			SAML_Utilities::planUpgradeMsg($e->getMessage());
		}

	}

	public static function showExpiryError($message, $cause)
	{
		?>
		 <div style="font-family:Calibri;padding:0 3%;">
			<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
			<div style="color: #a94442;font-size:14pt; margin-bottom:20px;">
				<p><strong>Error: </strong><?php echo  $message; ?></p>
				<p><strong>Possible Cause: </strong><?php echo  $cause; ?></p>
			</div>
			<form action="<?php echo Uri::root();?>">
				<div style="margin:3%;display:block;text-align:center;">
					<input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" type="submit" value="Done" onClick="self.close();">
					<a style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" href="https://plugins.miniorange.com/joomla-single-sign-on-sso#vpricing">Upgrade</a>
				</div>
			</form>
		</div>
		<?php
		exit;
	}

	public function loginCurrentUser($result, $attrs, $loginUrl, $name, $username, $email, $matcher, $app)
	{
		$this->loadLanguagePlugin();
		$user = User::getInstance($result->id);
		SAML_Utilities::updateCurrentUserName($user->id, $name, 'name');

		if ($user->block == 1)
		{
			SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
			SAML_Utilities::keepRecords('SSO Status', 'User is blocked by Administrator');
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('PLG_SYSTEM_SAMLREDIRECT_BLOCK_USER'), 'error');
			$app->redirect(Uri::root());
		}

		$roleMapping = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_role_mapping');
		$uid = SAML_Utilities::getSuperUser();
		$adminUserRecord = SAML_Utilities::loadDbValues('#__users', 'loadAssoc', '*', 'id', $uid);
		$result = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_config');
		$ssoCount = base64_decode($result['sso_test']);

		if (isset($roleMapping['enable_saml_role_mapping']))
		{
			if ($roleMapping['enable_saml_role_mapping'] == 1)
			{
				$enableRolemapping = 1;
			}
			else
			{
				$enableRolemapping = 0;
			}
		}
		else
		{
			$enableRolemapping = 0;
		}

		jimport('joomla.user.helper');

		if ($enableRolemapping)
		{
			if (isset($roleMapping['mapping_value_default']))
			{
				$defaultGroup = $roleMapping['mapping_value_default'];
			}

			UserHelper::addUserToGroup($user->id, $defaultGroup);

			foreach ($user->groups as $existingGroup)
			{
				if ($existingGroup != $defaultGroup && $existingGroup != 7 && $existingGroup != 8)
				{
					UserHelper::removeUserFromGroup($user->id, $existingGroup);
				}
			}
		}

		if (isset($adminUserRecord['authCount']) && $adminUserRecord['authCount'])
		{
			SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
			SAML_Utilities::keepRecords('SSO Status', 'Login Flow');
			SAML_Utilities::rmex();
		}

		$session = Factory::getSession();

		// Get current session vars
		// Register the needed session variables
		$session->set('user', $user);
		$session->set('MO_SAML_NAMEID', isset($attrs['NAME_ID']) ? $attrs['NAME_ID'] : '');
		$session->set('MO_SAML_SESSION_INDEX', isset($attrs['ASSERTION_SESSION_INDEX']) ? $attrs['ASSERTION_SESSION_INDEX'] : '');

		$app->checkSession();
		$sessionId = $session->getId();
		SAML_Utilities::updateUsernameToSessionId($user->id, $user->username, $sessionId);

		$ssoCount    = base64_encode($ssoCount + 1);

		$databaseName = '#__miniorange_saml_config';
		$updatefieldsarray = array(
		   'sso_test' => $ssoCount,
		);
		$updateDb = new Mo_Saml_Local_Util;
		$updateDb->genericUpdateQuery($databaseName, $updatefieldsarray);
		$OQEvKKrLDi = base64_decode($result['userslim']);

		if ($OQEvKKrLDi <= 1 || $OQEvKKrLDi % 5 == 0)
		{
			SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', true);
			SAML_Utilities::keepRecords('SSO Status', 'Login Flow');
		}

		$user->setLastVisit();
		$app->redirect(urldecode($loginUrl));

	}

	public function showErrorMessage($statusCode, $relayState)
	{
		$this->loadLanguagePlugin();

		if ($relayState == 'testValidate')
		{
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_HEADER") . ' </div>
            <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR") . ': </strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_ONE") . '</p>
            <p><strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_CAUSE") . '</strong>:' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_CASUSE_1") . ' ' . $statusCode . ' ' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_CASUSE_2") . ' . </p>
                            <p><strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_REASON") . '</strong>: ' . $this->getStatusMessage($statusCode) . '</p><br>
            </div>

            <div style="margin:3%;display:block;text-align:center;">
                <input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="' . Text::_("PLG_SYSTEM_SAMLREDIRECT_DONE_BTN") . '" onClick="self.close();">
            </div>';
			exit;
		}
		else
		{
			if ($statusCode == 'RequestDenied')
			{
				echo Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_MSG_1");
				exit;
			}
			else
			{
				echo Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_MSG_2");
				exit;
			}
		}
	}

	public function getStatusMessage($statusCode)
	{
		$this->loadLanguagePlugin();

		switch ($statusCode)
		{
			case 'RequestDenied':
				return Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_MSG_1");
				break;
			case 'Requester':
				return Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_MSG_3");
				break;
			case 'Responder':
				return Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_MSG_4");
				break;
			case 'VersionMismatch':
				return Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_MSG_5");
				break;
			default:
				return Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_MSG_6");
		}
	}

	public function generateMetadata($attribute, $download = false)
	{
		$spBaseUrl = "";
		$spEntityId = "";

		if (isset($attribute['sp_base_url']))
		{
			$spBaseUrl = $attribute['sp_base_url'];
			$spEntityId = $attribute['sp_entity_id'];
		}

		if (isset($attribute['sp_entity_id']))
		{
			$spEntityId = $attribute['sp_entity_id'];
		}

		$siteUrl = Uri::root();

		if (empty($spBaseUrl))
		{
			$spBaseUrl = $siteUrl;
		}

		if (empty($spEntityId))
		{
			$spEntityId = $siteUrl . 'plugins/authentication/miniorangesaml';
		}

		$acsUrl = $spBaseUrl . '?morequest=acs';

		$certificate = JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'authentication' . DIRECTORY_SEPARATOR . 'miniorangesaml' . DIRECTORY_SEPARATOR . 'saml2' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'sp-certificate.crt';
		$certificate = file_get_contents($certificate);
		$certificate = SAML_Utilities::desanitizeCertificate($certificate);

		if ($download)
		{
			header('Content-Disposition: attachment; filename="Metadata.xml"');
		}
		else
		{
			header('Content-Type: text/xml');
		}

		echo '<?xml version="1.0"?>
        <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2028-03-26T23:59:59Z" cacheDuration="PT1446808792S" entityID="' . $spEntityId . '">
          <md:SPSSODescriptor AuthnRequestsSigned="false" WantAssertionsSigned="true" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
            <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
            <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="' . $acsUrl . '" index="1"/>
          </md:SPSSODescriptor>
          <md:Organization>
            <md:OrganizationName xml:lang="en-US">miniOrange</md:OrganizationName>
            <md:OrganizationDisplayName xml:lang="en-US">miniOrange</md:OrganizationDisplayName>
            <md:OrganizationURL xml:lang="en-US">http://miniorange.com</md:OrganizationURL>
          </md:Organization>
          <md:ContactPerson contactType="technical">
            <md:GivenName>miniOrange</md:GivenName>
            <md:EmailAddress>info@xecurify.com</md:EmailAddress>
          </md:ContactPerson>
          <md:ContactPerson contactType="support">
            <md:GivenName>miniOrange</md:GivenName>
            <md:EmailAddress>info@xecurify.com</md:EmailAddress>
          </md:ContactPerson>
        </md:EntityDescriptor>';
		exit();
	}

	public function registerCurrentUser($attrs, $loginUrl, $name, $username, $email, $samlGroups, $matcher, $app, $post)
	{
		$roleMapping = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_role_mapping');
		$enableSamlRoleMapping = 0;

		if (isset($roleMapping['enable_saml_role_mapping']))
		{
			$enableSamlRoleMapping = json_decode($roleMapping['enable_saml_role_mapping']);
		}

		// User data
		$data['name'] = (isset($name) && !empty($name)) ? $name : $username;
		$data['username'] = $username;
		$data['email'] = $data['email1'] = $data['email2'] = PunycodeHelper::emailToPunycode($email);
		$data['password'] = $data['password1'] = $data['password2'] = UserHelper::genRandomPassword();
		$data['activation'] = '0';
		$data['block'] = '0';

		if ($enableSamlRoleMapping == 1)
		{
			$data['groups'][] = isset($roleMapping['mapping_value_default']) ? $roleMapping['mapping_value_default'] : 2;
		}
		else
		{
			$data['groups'][] = 2;
		}

		$result = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_config');

		// Get the model and validate the data.
		jimport('joomla.application.component.model');

		if (!defined('JPATH_COMPONENT'))
		{
			define('JPATH_COMPONENT', JPATH_BASE . '/components/');
		}

		$user = new User;

		// Write to database
		if (!$user->bind($data))
		{
			SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
			SAML_Utilities::keepRecords('SSO Status', 'Could not bind data');
			throw new Exception("Could not bind data. Error: " . $user->getError());
		}

		$result = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_config');

		$uid = SAML_Utilities::getSuperUser();
		$adminUserRecord = SAML_Utilities::loadDbValues('#__users', 'loadAssoc', '*', 'id', $uid);

		$session = Factory::getSession();

		if (!$user->save())
		{
			SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
			SAML_Utilities::keepRecords('SSO Status', 'Could not save user due to invalid email address (Attribute Mapping Issue)');

			if ($session->get('quick_fix_attributes') && ($session->get('quick_fix_attributes') == 'true') && $session->get('attribute_email') && (strcmp($session->get('attribute_email'), $user->email == 0)))
			{
				self::moUserNotRegisteredNameidIssue($user);
			}
			else
			{
				self::moUserNotRegisteredIssue($user, $post);
			}
		}

		$usrlim = (int) base64_decode($result['userslim']);
		$usrlim = base64_encode($usrlim + 1);
		$sso    = (int) base64_decode($result['sso_test']);
		$sso    = base64_encode($sso + 1);

		$databaseName = '#__miniorange_saml_config';
		$updatefieldsarray = array(
			'userslim' => $usrlim,
			'sso_test' => $sso,
		);
		$result = new Mo_Saml_Local_Util;
		$result->genericUpdateQuery($databaseName, $updatefieldsarray);

		if (isset($adminUserRecord['authCount']) && $adminUserRecord['authCount'])
		{
			SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
			SAML_Utilities::keepRecords('SSO Status', 'User creation limit Exceeded');
			SAML_Utilities::rmex();
		}

		$result = SAML_Utilities::getUserFromJoomla($matcher, $username, $email);

		if ($result)
		{
			$user = User::getInstance($result->id);

			 // Get current session vars
			// Register the needed session variables
			$session->set('user', $user);

			if (isset($attrs['NAME_ID']))
			{
				$session->set('MO_SAML_NAMEID', $attrs['NAME_ID']);
			}

			$session->set('MO_SAML_SESSION_INDEX', $attrs['ASSERTION_SESSION_INDEX']);

			$app->checkSession();
			$sessionId = $session->getId();
			SAML_Utilities::updateUsernameToSessionId($user->id, $user->username, $sessionId);

			SAML_Utilities::saveTestConfig('#__miniorange_saml_config', 'sso_status', true);
			SAML_Utilities::keepRecords('SSO Status', 'Registration Flow');

			// Update Last Visit Date

			$user->setLastVisit();
			$app->redirect(urldecode($loginUrl));
		}

	}

	public function moGetVersionInformations()
	{
		$arrayVersion = array();
		$arrayVersion["PHP_version"] = phpversion();
		$arrayVersion["OPEN_SSL"] = $this->moSamlIsOpensslInstalled();
		$arrayVersion["CURL"] = $this->moSamlIsCurlInstalled();
		$arrayVersion["ICONV"] = $this->moSamlIsIconvInstalled();
		$arrayVersion["DOM"] = $this->moSamlIsDomInstalled();

		return $arrayVersion;
	}

	public function moUserNotRegisteredNameidIssue($user)
	{
		$this->loadLanguagePlugin();
		$siteUrl = Uri::root();
		ob_end_clean();
		$siteUrl = $siteUrl . 'plugins/authentication/miniorangesaml/';
		echo '<div style="font-family:Calibri;padding:0 3%;">';
		echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">
        <img style="width:15px;"src="' . $siteUrl . 'images/wrong.png">' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_HEADER") . ' </div>
        <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR") . ' : </strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_USER_SAVE_ERR") . ' ' . $user->getError() . '</p>
        <p>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ATTR_ERR_MSG") . ' </p>
       </div>
            
        <div style="text-align:center;">
            <div style="display:inline-block">
                <a href="index.php" type="button" style="padding: 10px 20px;background: #226a8b;cursor: pointer;font-size:15px;border-radius: 3px;border-color:black;border-width: 1px;border-style: solid;color: #FFF;">' . Text::_("PLG_SYSTEM_SAMLREDIRECT_BACK_BTN") . '</a>
            </div>
            <div style="display:inline-block">
                <a href="https://plugins.miniorange.com/joomla-single-sign-on-sso#pricing" type="button" style="padding: 10px 20px;background: #226a8b;cursor: pointer;font-size:15px;border-radius: 3px;border-color:black;border-width: 1px;border-style: solid;color: #FFF;" target="_blank">' . Text::_("PLG_SYSTEM_SAMLREDIRECT_UPGRADE_BTN") . ' </a>
            </div>  
        </div>';
		exit;
	}

	public function moUserNotRegisteredIssue($user, $post)
	{
		$this->loadLanguagePlugin();
		$siteUrl = Uri::root();
		ob_end_clean();
		$siteUrl = $siteUrl . 'plugins/authentication/miniorangesaml/';
		echo '<div style="font-family:Calibri;padding:0 3%;">';
		echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">
        <img style="width:15px;"src="' . $siteUrl . 'images/wrong.png"> ' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR_HEADER") . ' </div>
        <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_ERR") . ' : </strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_USER_SAVE_ERR") . ' ' . $user->getError() . '</p>
        <p>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_USER_SAVE_ERR_1") . ' <strong>' . $user->email . '</strong> ' . Text::_("PLG_SYSTEM_SAMLREDIRECT_USER_SAVE_ERR_2") . '</p>
        <p>
            <strong>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_SOLUTION") . ':</strong>
            <ul>' . Text::_("PLG_SYSTEM_SAMLREDIRECT_OPTIONS") . '</ul>
        </p>
       </div>
            
        <div style="text-align:center;">
            <div style="display:inline-block">
                <a href="index.php" type="button" style="padding: 10px 20px;background: #226a8b;cursor: pointer;font-size:15px;border-radius: 3px;border-color:black;border-width: 1px;border-style: solid;color: #FFF;">' . Text::_("PLG_SYSTEM_SAMLREDIRECT_BACK_BTN") . '</a>
            </div>
            <div style="display:inline-block">
                <form  method="post" action="' . Uri::root() . '?morequest=acs" >
                     <input style="padding: 10px 20px;background: green;cursor: pointer;font-size:15px;border-radius: 3px;border-color:black;border-width: 1px;border-style: solid;color: #FFF;" type="submit" value="' . Text::_("PLG_SYSTEM_SAMLREDIRECT_QUICK_FIX_BTN") . '">
                     <input type="hidden" name="quick_fix_attributes" value="true" >
                     <input type="hidden" name="SAMLResponse" value="' . $post["SAMLResponse"] . '">
                     <input type="hidden" name="RelayState" value="' . $post["RelayState"] . '">
                 </form>
             </div>
          
            <div style="display:inline-block">
                <a href="https://plugins.miniorange.com/joomla-single-sign-on-sso#pricing" type="button" style="padding: 10px 20px;background: #226a8b;cursor: pointer;font-size:15px;border-radius: 3px;border-color:black;border-width: 1px;border-style: solid;color: #FFF;" target="_blank">' . Text::_("PLG_SYSTEM_SAMLREDIRECT_UPGRADE_BTN") . '</a>
            </div>
          
        </div>';
		exit;
	}

	public function moSamlIsOpensslInstalled()
	{
		if (in_array('openssl', get_loaded_extensions()))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function moSamlIsCurlInstalled()
	{
		if (in_array('curl', get_loaded_extensions()))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function moSamlIsIconvInstalled()
	{
		if (in_array('iconv', get_loaded_extensions()))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function moSamlIsDomInstalled()
	{
		if (in_array('dom', get_loaded_extensions()))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Render hidden extension id fields for uninstall feedback forms.
	 *
	 * @param   array  $cidList  Extension IDs.
	 *
	 * @return  void
	 */
	private function renderFeedbackHiddenIds($cidList)
	{
		foreach ($cidList as $key)
		{
			echo '<input type="hidden" name="result[]" value="' . (int) $key . '">';
		}
	}

	/**
	 * Render deactivate reason radio options.
	 *
	 * @param   array  $deactivateReasons  Reason labels.
	 *
	 * @return  void
	 */
	private function renderDeactivateReasonOptions($deactivateReasons)
	{
		foreach ($deactivateReasons as $deactivateReason)
		{
			?>
			<div class="radio" style="padding:1px;margin-left:2%">
				<label style="font-weight:normal;font-size:14.6px;font-family: cursive;" for="<?php echo $deactivateReason; ?>">
					<input type="radio" name="deactivate_plugin" value="<?php echo $deactivateReason; ?>" required>
					<?php echo $deactivateReason; ?></label>
			</div>
			<?php
		}
	}

}
