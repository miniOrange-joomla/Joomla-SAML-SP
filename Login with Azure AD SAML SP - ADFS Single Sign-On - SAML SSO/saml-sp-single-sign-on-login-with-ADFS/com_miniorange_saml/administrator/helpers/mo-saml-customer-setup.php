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


/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
require_once 'MoConstants.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'mo-saml-utility.php';

class Mo_Saml_Local_Customer
{
	/**
	 * @var    string
	 */
	public $email;

	/**
	 * @var    string
	 */
	public $phone;

	/**
	 * @var    string
	 */
	public $customerKey;

	/**
	 * @var    string
	 */
	public $transactionId;

	public function createCustomer()
	{
		if (!Mo_Saml_Local_Util::isCurlInstalled())
		{
			return json_encode(
				array(
					"statusCode" => 'ERROR',
					'statusMessage' => 'Error while creating user. Please check your configuration. Also check troubleshooting under saml configuration.'
				)
			);
		}

		$hostname = Mo_Saml_Local_Util::getHostname();

		$url = $hostname . '/moas/rest/customer/add';
		$currentUser = Factory::getUser();
		$customerDetails = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_customer_details');
		$this->email = $customerDetails['email'];
		$this->phone = $customerDetails['admin_phone'];
		$password = $customerDetails['password'];

		if (!empty($password))
		{
			$password = base64_decode($password);
		}

		$fields = array(
			'companyName' => self::getSiteHost(),
			'areaOfInterest' => 'Joomla SAML 2.0 SP SSO Plugin',
			'firstname' => $currentUser->username,
			'email' => $this->email,
			'phone' => $this->phone,
			'password' => $password
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 1);
	}

	public function getCustomerKey($email, $password)
	{
		if (!Mo_Saml_Local_Util::isCurlInstalled())
		{
			return json_encode(
				array(
					"apiKey" => 'CURL_ERROR',
					'token' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'
				)
			);
		}

		$hostname = Mo_Saml_Local_Util::getHostname();

		$url = $hostname . "/moas/rest/customer/key";

		$fields = array(
			'email' => $email,
			'password' => $password
		);

		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 0);
	}


	public static function submitFeedbackForm($email, $phone, $query, $cause, $feedbackEmail = '')
	{

		$url = 'https://login.xecurify.com/moas/api/notify/send';

		$customerKey = MoConstants::getDefaultCustomerKey();
		$phpVersion = phpversion();
		$jCmsVersion = SAML_Utilities::getJoomlaCmsVersion();
		$moPluginVersion = SAML_Utilities::getPluginVersion();
		$osVersion    = SAML_Utilities::getOsInfo();
		$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
		$webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
		$pluginName    = (new Mo_Saml_Local_Customer)->pluginName($moPluginVersion);
		$query1 = '[' . $pluginName . ' | Joomla ' . $jCmsVersion . ' | PHP ' . $phpVersion . ' | OS ' . $osVersion . ' | Web Server: ' . $webServer . '] ';

		$ccEmail = 'joomlasupport@xecurify.com';
		$bccEmail = 'joomlasupport@xecurify.com';
		$resolvedFeedbackEmail = !empty($feedbackEmail) ? $feedbackEmail : $email;
		$fromEmail = $resolvedFeedbackEmail;
		$siteHost = self::escapeSiteHost(self::getSiteHost());
		$content = '<div >Hello, <br><br><strong>Company :<a href="' . $siteHost . '" target="_blank" ></strong>' . $siteHost . '</a><br><br><strong>Email :<a href="mailto:' . $resolvedFeedbackEmail . '" target="_blank">' . $resolvedFeedbackEmail . '</a></strong><br><br><strong>Plugin Deactivated: </strong>' . $query1 . '<br><br><strong>Reason: </strong>' . $query . '</div>';
		$subject = "miniOrange Joomla SAML SP Free Feedback - " . $resolvedFeedbackEmail;

		$fields = array(
			'customerKey' => $customerKey,
			'sendEmail' => true,
			'email' => array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'bccEmail' 		=> $bccEmail,
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> $ccEmail,
				'toName' 		=> $bccEmail,
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 0);

	}

	public function submitContactUs($qEmail, $qPhone, $query, $clientTimezone = '', $clientTimezoneOffset = '')
	{
		if (!Mo_Saml_Local_Util::isCurlInstalled())
		{
			return json_encode(
				array(
					"status" => 'CURL_ERROR',
					'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'
				)
			);
		}

		$hostname = Mo_Saml_Local_Util::getHostname();
		$url = $hostname . "/moas/rest/customer/contact-us";
		$currentUser = Factory::getUser();
		$phpVersion = phpversion();
		$jVersion = new Version;
		$jCmsVersion = $jVersion->getShortVersion();
		$moPluginVersion = Mo_Saml_Local_Util::getPluginVersion();
		$pluginName = (new Mo_Saml_Local_Customer)->pluginName($moPluginVersion);
		$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
		$webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
		$result = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_config');
		$nousercreated = base64_decode($result['userslim']);
		$testConfiguration = ($result['test_configuration'] == true) ? 'Successful' : 'Unsuccessful';
		$osVersion    = SAML_Utilities::getOsInfo();

		// Timezone: browser tz -> Joomla user tz -> global config offset -> UTC
		$user = Factory::getUser();
		$timezone = $user->getParam('timezone');

		if (empty($timezone))
		{
			try
			{
				$config = Factory::getConfig();
				$timezone = $config->get('offset');
			}
			catch (\Throwable $e)
			{
				$timezone = null;
			}
		}

		$tzName = trim((string) $clientTimezone);

		if ($tzName === '')
		{
			$tzName = (string) $timezone;
		}

		if (trim($tzName) === '')
		{
			$tzName = 'UTC';
		}

		$utcOffset = '';
		$tzOffsetMinutes = trim((string) $clientTimezoneOffset);

		if ($tzOffsetMinutes !== '' && preg_match('/^-?\d+$/', $tzOffsetMinutes))
		{
			$m = (int) $tzOffsetMinutes;
			$sign = $m > 0 ? '-' : '+';
			$abs = abs($m);
			$hh = str_pad((string) floor($abs / 60), 2, '0', STR_PAD_LEFT);
			$mm = str_pad((string) ($abs % 60), 2, '0', STR_PAD_LEFT);
			$utcOffset = $sign . $hh . ':' . $mm;
		}
		else
		{
			try
			{
				$tzObj = new \DateTimeZone($tzName);
				$dt = new \DateTime('now', $tzObj);
				$offsetSeconds = (int) $dt->getOffset();
				$sign = $offsetSeconds >= 0 ? '+' : '-';
				$abs = abs($offsetSeconds);
				$hh = str_pad((string) floor($abs / 3600), 2, '0', STR_PAD_LEFT);
				$mm = str_pad((string) floor(($abs % 3600) / 60), 2, '0', STR_PAD_LEFT);
				$utcOffset = $sign . $hh . ':' . $mm;
			}
			catch (\Exception $e)
			{
				$utcOffset = '+00:00';
				$tzName = 'UTC';
			}
		}

		$userTimezone = $utcOffset !== '' ? $tzName . ' (' . $utcOffset . ')' : $tzName;

		$query = '[' . $pluginName . ' | Joomla ' . $jCmsVersion . ' ] PHP ' . $phpVersion . ' | OS ' . $osVersion . ' | Web Server: ' . $webServer . ' | Timezone: ' . $userTimezone . ' | Auto created Users: ' . $nousercreated . ' | Test Configuration:' . $testConfiguration . ' || Query :' . $query;

		$fields = array(
			'firstName' => $currentUser->username,
			'company' => self::getSiteHost(),
			'email' => $qEmail,
			'ccEmail' => 'joomlasupport@xecurify.com',
			'phone' => $qPhone,
			'query' => $query
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 1);
	}

	public function sendOtpToken($authType, $phone)
	{

		if (!Mo_Saml_Local_Util::isCurlInstalled())
		{
			return json_encode(
				array(
					"status" => 'CURL_ERROR',
					'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'
				)
			);
		}

		$customerDetails = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_customer_details');
		$hostname = Mo_Saml_Local_Util::getHostname();
		$url = $hostname . '/moas/api/auth/challenge';
		$username = $customerDetails['email'];

		if ($authType == "EMAIL")
		{
			$fields = array(
				'customerKey' => MoConstants::getDefaultCustomerKey(),
				'email' => $username,
				'authType' => $authType,
				'transactionName' => 'Joomla SAML 2.0 SP SSO Plugin'
			);
		}
		else
		{
			$fields = array(
				'customerKey' => MoConstants::getDefaultCustomerKey(),
				'phone' => $phone,
				'authType' => $authType,
				'transactionName' => 'Joomla SAML 2.0 SP SSO Plugin'
			);
		}

		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 0);
	}

	public function validateOtpToken($transactionId, $otpToken)
	{
		if (!Mo_Saml_Local_Util::isCurlInstalled())
		{
			return json_encode(
				array(
					"status" => 'CURL_ERROR',
					'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'
				)
			);
		}

		$hostname = Mo_Saml_Local_Util::getHostname();
		$url = $hostname . '/moas/api/auth/validate';
		$fields = '';

		$fields = array(
			'txId' => $transactionId,
			'token' => $otpToken,
		);

		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 0);
	}

	public function checkCustomer($email)
	{
		if (!Mo_Saml_Local_Util::isCurlInstalled())
		{
			return json_encode(
				array(
					"status" => 'CURL_ERROR',
					'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'
				)
			);
		}

		$hostname = Mo_Saml_Local_Util::getHostname();
		$url = $hostname . "/moas/rest/customer/check-if-exists";

		$fields = array(
			'email' => $email,
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 0);
	}

	public function requestForTrial($email, $plan, $demo, $description = '')
	{
		$url = 'https://login.xecurify.com/moas/api/notify/send';
		$customerKey = MoConstants::getDefaultCustomerKey();
		$fromEmail = $email;
		$subject = 'miniOrange Joomla SAML SP Request for ' . $demo;
		$phpVersion = phpversion();
		$jCmsVersion = SAML_Utilities::getJoomlaCmsVersion();
		$moPluginVersion = SAML_Utilities::getPluginVersion();
		$pluginName = (new Mo_Saml_Local_Customer)->pluginName($moPluginVersion);
		$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
		$webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';
		$pluginInfo = '[' . $pluginName . ' | Joomla ' . $jCmsVersion . ' | PHP ' . $phpVersion . ' | Web Server: ' . $webServer . '] : ' . $plan;

		$siteHost = self::escapeSiteHost(self::getSiteHost());
		$content = '<div >Hello, <br>
                        <br><strong>Company :</strong><a href="' . $siteHost . '" target="_blank" >' . $siteHost . '</a><br><br>
                        <strong>Email :</strong><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        <strong>Plugin Info: </strong>' . $pluginInfo . '<br><br>
                        <strong>Description: </strong>' . $description . '</div>';

		$fields = array(
			'customerKey' => MoConstants::getDefaultCustomerKey(),
			'sendEmail' => true,
			'email' => array(
				'customerKey' => MoConstants::getDefaultCustomerKey(),
				'fromEmail' => $fromEmail,
				'fromName' => 'miniOrange',
				'toEmail' => 'joomlasupport@xecurify.com',
				'toName' => 'joomlasupport@xecurify.com',
				'subject' => $subject,
				'content' => $content
			),
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 0);
	}

	public function requestForSetupCall($email, $query, $description, $callDate, $timeZone)
	{
		$url = 'https://login.xecurify.com/moas/api/notify/send';
		$customerKey = MoConstants::getDefaultCustomerKey();
		$fromEmail = $email;
		$subject = "miniOrange Joomla SAML SP Free - Screen Share/Call Request";
		$phpVersion = phpversion();
		$jCmsVersion = SAML_Utilities::getJoomlaCmsVersion();
		$moPluginVersion = SAML_Utilities::getPluginVersion();
		$pluginName = (new Mo_Saml_Local_Customer)->pluginName($moPluginVersion);
		$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
		$webServer = !empty($serverSoftware) ? trim(explode('/', $serverSoftware)[0]) : 'Unknown';

		$pluginInfo = '[' . $pluginName . ' | Joomla ' . $jCmsVersion . ' | PHP ' . $phpVersion . ' | Web Server: ' . $webServer . '] : ';

		$siteHost = self::escapeSiteHost(self::getSiteHost());
		$content = '<div>Hello, <br><br>
                        <strong>Company :</strong><a href="' . $siteHost . '" target="_blank" >' . $siteHost . '</a><br><br>
                        <strong>Plugin Info: </strong>' . $pluginInfo . '<br><br>
                        <strong>Email :</strong><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        <strong>Time Zone:</strong> ' . $timeZone . '<br><br><strong>Date to set up call: </strong>' . $callDate . '<br><br>
                        <strong>Issue : </strong>' . $query . '<br><br>
                        <strong>Description:</strong> ' . $description . '</div>';

		$fields = array(
			'customerKey' => $customerKey,
			'sendEmail' => true,
			'email' => array(
				'customerKey' => $customerKey,
				'fromEmail' => $fromEmail,
				'fromName' => 'miniOrange',
				'toEmail' => 'joomlasupport@xecurify.com',
				'toName' => 'joomlasupport@xecurify.com',
				'subject' => $subject,
				'content' => $content
			),
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString, 0);
	}

	public function pluginName($moPluginVersion)
	{
		if (MoConstants::MO_SAML_SP == "ALL")
		{
			$pluginName = 'Joomla SAML SP Free Plugin ' . $moPluginVersion . '';
		}
		elseif (MoConstants::MO_SAML_SP == "ADFS")
		{
			$pluginName = 'Joomla SAML SP Free Plugin - Login with ADFS ' . $moPluginVersion . '';
		}
		elseif (MoConstants::MO_SAML_SP == "GOOGLEAPPS")
		{
			$pluginName = 'Joomla SAML SP Free Plugin - Login with Google Apps ' . $moPluginVersion . '';
		}

		return $pluginName;
	}

	/**
	 * Site host taken from the Joomla request.
	 *
	 * @return  string
	 */
	private static function getSiteHost()
	{
		return Uri::getInstance()->getHost();
	}

	/**
	 * Escape a site host before it is placed in an HTML email.
	 *
	 * @param   string  $host  Site host.
	 *
	 * @return  string
	 */
	private static function escapeSiteHost($host)
	{
		return htmlspecialchars($host, ENT_QUOTES, 'UTF-8');
	}

	public static function curlCall($url, $fieldString, $proxyNeeded)
	{
		$ch = curl_init($url);
		$credentials = self::resolveCloudApiCredentials();
		$customerKey = $credentials['customerKey'];
		$apiKey = $credentials['apiKey'];

		// Current time in milliseconds since midnight, January 1, 1970 UTC.

		$currentTimeInMillis = round(microtime(true) * 1000);

		// Creating the Hash using SHA-512 algorithm

		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
		$authorizationHeader = "Authorization: " . $hashValue;
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
			$timestampHeader, $authorizationHeader)
		);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);

		if ($proxyNeeded)
		{
			$proxy = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_proxy_setup');
			$proxyHostName = isset($proxy['proxy_host_name']) ? $proxy['proxy_host_name'] : '';
			$portNumber = isset($proxy['port_number']) ? $proxy['port_number'] : '';
			$username = isset($proxy['username']) ? $proxy['username'] : '';
			$password = isset($proxy['password']) ? base64_decode($proxy['password']) : '';

			if (!empty($proxyHostName))
			{
				curl_setopt($ch, CURLOPT_PROXY, $proxyHostName);
				curl_setopt($ch, CURLOPT_PROXYPORT, $portNumber);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . $password);
			}
		}

		$content = curl_exec($ch);

		if ($content === false)
		{
			$curlError = curl_error($ch);
			curl_close($ch);

			return json_encode(
				array(
					'status' => 'Error',
					'message' => 'Request Error: ' . $curlError,
				)
			);
		}

		curl_close($ch);

		return $content;
	}

	/**
	 * Resolve miniOrange cloud API credentials from the database or configured defaults.
	 *
	 * @return  array
	 */
	private static function resolveCloudApiCredentials()
	{
		$customerDetails = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_customer_details');

		if (!Mo_Saml_Local_Util::isCustomerRegistered())
		{
			return array(
				'customerKey' => MoConstants::getDefaultCustomerKey(),
				'apiKey' => MoConstants::getDefaultApiKey(),
			);
		}

		return array(
			'customerKey' => $customerDetails['customer_key'],
			'apiKey' => $customerDetails['api_key'],
		);
	}
}
