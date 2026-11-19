<?php
/**
 * @package     Joomla.Library
 * @subpackage  lib_miniorangesamlplugin
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'DbHelper.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-utility.php';

$lang = Factory::getLanguage();
$lang->load('lib_miniorangesamlplugin', JPATH_SITE);
include "xmlseclibs.php";
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'myaccount.php';
include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-customer-setup.php';

class SAML_Utilities
{
	public static function getPluginVersion()
	{
		$db = MoSamlDbHelper::getDb();
		$dbQuery = $db->getQuery(true)
			->select('manifest_cache')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_saml'));
		$db->setQuery($dbQuery);
		$manifest = json_decode($db->loadResult());

		return($manifest->version);
	}

	public static function getJoomlaCmsVersion()
	{
		$jVersion   = new Version;

		return($jVersion->getShortVersion());
	}

	public static function getValuesFromTable($tableName)
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);
		$query->select(array('*'));
		$query->from($db->quoteName($tableName));
		$query->where($db->quoteName('id') . " = 1");
		$db->setQuery($query);
		$customerResult = $db->loadAssoc();

		return $customerResult;
	}

	public static function generateID()
	{
		return '_' . self::stringToHex(self::generateRandomBytes(21));
	}

	public static function stringToHex($bytes)
	{
		$ret = '';

		for ($i = 0; $i < strlen($bytes); $i++)
		{
			$ret .= sprintf('%02x', ord($bytes[$i]));
		}

		return $ret;
	}

	public static function generateRandomBytes($length, $fallback = true)
	{
		return openssl_random_pseudo_bytes($length);
	}


	public static function createAuthnRequest($acsUrl, $issuer, $destination, $nameIdFormat, $forceAuthn = 'false', $ssoBindingType = 'HttpRedirect')
	{
		$requestXmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
			'<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="' . self::generateID() .
			'" Version="2.0" IssueInstant="' . self::generateTimestamp() . '"';

		if ($forceAuthn == 'true')
		{
			$requestXmlStr .= ' ForceAuthn="true"';
		}

		$requestXmlStr .= ' ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="' . $acsUrl .
			'" Destination="' . $destination . '"><saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer><samlp:NameIDPolicy AllowCreate="true" Format="' . $nameIdFormat . '"
                        /></samlp:AuthnRequest>';

		/**
		 * Dont delete this
		 */
		/*
		If empty($ssoBindingType) || $ssoBindingType == 'HttpRedirect') {

			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}
		*/

		return $requestXmlStr;

	}

	public static function getOsInfo()
	{

		if (isset($_SERVER))
		{
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
		}
		else
		{
			if (isset($GLOBALS['HTTP_SERVER_VARS']))
			{
				$userAgent = $GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'];
			}
			else
			{
				$userAgent = $GLOBALS['HTTP_USER_AGENT'];
			}
		}

		$osArray = [
			'windows nt 10' => 'Windows 10',
			'windows nt 6.3' => 'Windows 8.1',
			'windows nt 6.2' => 'Windows 8',
			'windows nt 6.1|windows nt 7.0' => 'Windows 7',
			'windows nt 6.0' => 'Windows Vista',
			'windows nt 5.2' => 'Windows Server 2003/XP x64',
			'windows nt 5.1' => 'Windows XP',
			'windows xp' => 'Windows XP',
			'windows nt 5.0|windows nt5.1|windows 2000' => 'Windows 2000',
			'windows me' => 'Windows ME',
			'windows nt 4.0|winnt4.0' => 'Windows NT',
			'windows ce' => 'Windows CE',
			'windows 98|win98' => 'Windows 98',
			'windows 95|win95' => 'Windows 95',
			'win16' => 'Windows 3.11',
			'mac os x 10.1[^0-9]' => 'Mac OS X Puma',
			'macintosh|mac os x' => 'Mac OS X',
			'mac_powerpc' => 'Mac OS 9',
			'linux' => 'Linux',
			'ubuntu' => 'Linux - Ubuntu',
			'iphone' => 'iPhone',
			'ipod' => 'iPod',
			'ipad' => 'iPad',
			'android' => 'Android',
			'blackberry' => 'BlackBerry',
			'webos' => 'Mobile',

			'(media center pc).([0-9]{1,2}\.[0-9]{1,2})' => 'Windows Media Center',
			'(win)([0-9]{1,2}\.[0-9x]{1,2})' => 'Windows',
			'(win)([0-9]{2})' => 'Windows',
			'(windows)([0-9x]{2})' => 'Windows',

			'Win 9x 4.90' => 'Windows ME',
			'(windows)([0-9]{1,2}\.[0-9]{1,2})' => 'Windows',
			'win32' => 'Windows',
			'(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})' => 'Java',
			'(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}' => 'Solaris',
			'dos x86' => 'DOS',
			'Mac OS X' => 'Mac OS X',
			'Mac_PowerPC' => 'Macintosh PowerPC',
			'(mac|Macintosh)' => 'Mac OS',
			'(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'SunOS',
			'(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'BeOS',
			'(risc os)([0-9]{1,2}\.[0-9]{1,2})' => 'RISC OS',
			'unix' => 'Unix',
			'os/2' => 'OS/2',
			'freebsd' => 'FreeBSD',
			'openbsd' => 'OpenBSD',
			'netbsd' => 'NetBSD',
			'irix' => 'IRIX',
			'plan9' => 'Plan9',
			'osf' => 'OSF',
			'aix' => 'AIX',
			'GNU Hurd' => 'GNU Hurd',
			'(fedora)' => 'Linux - Fedora',
			'(kubuntu)' => 'Linux - Kubuntu',
			'(ubuntu)' => 'Linux - Ubuntu',
			'(debian)' => 'Linux - Debian',
			'(CentOS)' => 'Linux - CentOS',
			'(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - Mandriva',
			'(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - SUSE',
			'(Dropline)' => 'Linux - Slackware (Dropline GNOME)',
			'(ASPLinux)' => 'Linux - ASPLinux',
			'(Red Hat)' => 'Linux - Red Hat',
			'(linux)' => 'Linux',
			'(amigaos)([0-9]{1,2}\.[0-9]{1,2})' => 'AmigaOS',
			'amiga-aweb' => 'AmigaOS',
			'amiga' => 'Amiga',
			'AvantGo' => 'PalmOS',
			'[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})' => 'Linux',
			'(webtv)/([0-9]{1,2}\.[0-9]{1,2})' => 'WebTV',
			'Dreamcast' => 'Dreamcast OS',
			'GetRight' => 'Windows',
			'go!zilla' => 'Windows',
			'gozilla' => 'Windows',
			'gulliver' => 'Windows',
			'ia archiver' => 'Windows',
			'NetPositive' => 'Windows',
			'mass downloader' => 'Windows',
			'microsoft' => 'Windows',
			'offline explorer' => 'Windows',
			'teleport' => 'Windows',
			'web downloader' => 'Windows',
			'webcapture' => 'Windows',
			'webcollage' => 'Windows',
			'webcopier' => 'Windows',
			'webstripper' => 'Windows',
			'webzip' => 'Windows',
			'wget' => 'Windows',
			'Java' => 'Unknown',
			'flashget' => 'Windows',
			'MS FrontPage' => 'Windows',
			'(msproxy)/([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
			'(msie)([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
			'libwww-perl' => 'Unix',
			'UP.Browser' => 'Windows CE',
			'NetAnts' => 'Windows',
		];

		$archRegex = '/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
		$arch = preg_match($archRegex, $userAgent) ? '64' : '32';

		foreach ($osArray as $regex => $value)
		{
			if (preg_match('{\b(' . $regex . ')\b}i', $userAgent))
			{
				return $value . ' x' . $arch;
			}
		}

		return 'Unknown';
	}

	public static function samlRequestBind($requestXmlStr, $ssoBindingType)
	{

		if (empty($ssoBindingType) || $ssoBindingType == 'HttpRedirect')
		{
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}

		return $requestXmlStr;

	}

	public static function generateTimestamp($instant = null)
	{
		if ($instant === null)
		{
			$instant = time();
		}

		return gmdate('Y-m-d\TH:i:s\Z', $instant);
	}

	public static function xpQuery(DOMNode $node, $query)
	{
		static $xpCache = null;

		if ($node instanceof DOMDocument)
		{
			$doc = $node;
		}
		else
		{
			$doc = $node->ownerDocument;
		}

		if ($xpCache === null || !$xpCache->document->isSameNode($doc))
		{
			$xpCache = new DOMXPath($doc);
			$xpCache->registerNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
			$xpCache->registerNamespace('saml_protocol', 'urn:oasis:names:tc:SAML:2.0:protocol');
			$xpCache->registerNamespace('saml_assertion', 'urn:oasis:names:tc:SAML:2.0:assertion');
			$xpCache->registerNamespace('saml_metadata', 'urn:oasis:names:tc:SAML:2.0:metadata');
			$xpCache->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
			$xpCache->registerNamespace('xenc', 'http://www.w3.org/2001/04/xmlenc#');
		}

		$results = $xpCache->query($query, $node);
		$ret = array();

		for ($i = 0; $i < $results->length; $i++)
		{
			$ret[$i] = $results->item($i);
		}

		return $ret;
	}

	public static function parseNameId(DOMElement $xml)
	{
		$ret = array('Value' => trim($xml->textContent));

		foreach (array('NameQualifier', 'SPNameQualifier', 'Format') as $attr)
		{
			if ($xml->hasAttribute($attr))
			{
				$ret[$attr] = $xml->getAttribute($attr);
			}
		}

		return $ret;
	}

	public static function xsDateTimeToTimestamp($time)
	{
		$matches = array();

		// We use a very strict regex to parse the timestamp.
		$regex = '/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?Z$/D';

		if (preg_match($regex, $time, $matches) == 0)
		{
			throw new Exception(
				'Invalid SAML2 timestamp passed to xsDateTimeToTimestamp: ' . $time
			);
		}

		// Extract the different components of the time from the  matches in the regex.
		// Intval will ignore leading zeroes in the string.
		$year = intval($matches[1]);
		$month = intval($matches[2]);
		$day = intval($matches[3]);
		$hour = intval($matches[4]);
		$minute = intval($matches[5]);
		$second = intval($matches[6]);

		// We use gmmktime because the timestamp will always be given
		// in UTC.
		$ts = gmmktime($hour, $minute, $second, $month, $day, $year);

		return $ts;
	}

	public static function extractStrings(DOMElement $parent, $namespaceURI, $localName)
	{
		$ret = array();

		for ($node = $parent->firstChild; $node !== null; $node = $node->nextSibling)
		{
			if ($node->namespaceURI !== $namespaceURI || $node->localName !== $localName)
			{
				continue;
			}

			$ret[] = trim($node->textContent);
		}

		return $ret;
	}

	public static function validateElement(DOMElement $root)
	{
		// Create an XML security object.

		$objXMLSecDSig = new XMLSecurityDSig;

		// Both SAML messages and SAML assertions use the 'ID' attribute.

		$objXMLSecDSig->idKeys[] = 'ID';

		// Locate the XMLDSig Signature element to be used.

		$signatureElement = self::xpQuery($root, './ds:Signature');

		if (count($signatureElement) === 0)
		{
			// We don't have a signature element to validate.

			return false;
		}
		elseif (count($signatureElement) > 1)
		{
			echo "XMLSec: more than one signature element in root.";
			exit;
		}

		$signatureElement = $signatureElement[0];
		$objXMLSecDSig->sigNode = $signatureElement;

		// Canonicalize the XMLDSig SignedInfo element in the message.

		$objXMLSecDSig->canonicalizeSignedInfo();

		// Validate referenced xml nodes.

		if (!$objXMLSecDSig->validateReference())
		{
			echo "XMLsec: digest validation failed";
			exit;
		}

		// Check that $root is one of the signed nodes.

		$rootSigned = false;
		/** @var DOMNode $signedNode */
		foreach ($objXMLSecDSig->getValidatedNodes() as $signedNode)
		{
			if ($signedNode->isSameNode($root))
			{
				$rootSigned = true;
				break;
			}
			elseif ($root->parentNode instanceof DOMDocument && $signedNode->isSameNode($root->ownerDocument))
			{
				// $root is the root element of a signed document.

				$rootSigned = true;
				break;
			}
		}

		if (!$rootSigned)
		{
			echo "XMLSec: The root element is not signed.";
			exit;
		}

		// Now we extract all available X509 certificates in the signature element.

		$certificates = array();

		foreach (self::xpQuery($signatureElement, './ds:KeyInfo/ds:X509Data/ds:X509Certificate') as $certNode)
		{
			$certData = trim($certNode->textContent);
			$certData = str_replace(array("\r", "\n", "\t", ' '), '', $certData);
			$certificates[] = $certData;
		}

		$ret = array(
			'Signature' => $objXMLSecDSig,
			'Certificates' => $certificates,
		);

		return $ret;
	}


	public static function validateSignature(array $info, XMLSecurityKey $key)
	{

		/** @var XMLSecurityDSig $objXMLSecDSig */
		$objXMLSecDSig = $info['Signature'];

		$sigMethod = self::xpQuery($objXMLSecDSig->sigNode, './ds:SignedInfo/ds:SignatureMethod');

		if (empty($sigMethod))
		{
			throw new Exception('Missing SignatureMethod element.');
		}

		$sigMethod = $sigMethod[0];

		if (!$sigMethod->hasAttribute('Algorithm'))
		{
			throw new Exception('Missing Algorithm-attribute on SignatureMethod element.');
		}

		$algo = $sigMethod->getAttribute('Algorithm');

		if ($key->type === XMLSecurityKey::RSA_SHA1 && $algo !== $key->type)
		{
			$key = self::castKey($key, $algo);
		}

		// Check the signature.

		if (!$objXMLSecDSig->verify($key))
		{
			throw new Exception("Unable to validate Signature");
		}
	}

	public static function castKey(XMLSecurityKey $key, $algorithm, $type = 'public')
	{
		// Do nothing if algorithm is already the type of the key
		if ($key->type === $algorithm)
		{
			return $key;
		}

		$keyInfo = openssl_pkey_get_details($key->key);

		if ($keyInfo === false)
		{
			throw new Exception('Unable to get key details from XMLSecurityKey.');
		}

		if (!isset($keyInfo['key']))
		{
			throw new Exception('Missing key in public key details.');
		}

		$newKey = new XMLSecurityKey($algorithm, array('type' => $type));
		$newKey->loadKey($keyInfo['key']);

		return $newKey;
	}

	public static function processResponse($currentURL, $certFingerprint, $signatureData, SAML2_Response $response, $certFromPlugin, $relayState,$post)
	{
		self::loadLanguage();
		$resCert = $signatureData['Certificates'][0];
		$siteUrl = Uri::root();

		// Validate Response-element destination.

		$msgDestination = $response->getDestination();

		if ($msgDestination !== null && $msgDestination !== $currentURL)
		{
			self::saveTestConfig('#__miniorange_saml_config', $relayState == 'testValidate' ? 'test_configuration' : 'sso_status', 0);
			self::keepRecords($relayState == 'testValidate' ? 'Test Configuration' : 'SSO Status', 'Destination in response doesn\'t match the current URL.');

			echo '<div style="font-family:Calibri;padding:0 3%;">
            <div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR_HEADER") . ' ERROR</div>
            <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . 'Error: </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_ONE") . '</p>
                         <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_DESTINATION") . ': </strong>' . self::escapeHtml($msgDestination) . '</p>
                          <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_CUR_URL") . ': </strong>' . self::escapeHtml($currentURL) . '</p>
                      </div>
            </div>';
			?>
			<div style="margin:3%;display:block;text-align:center;"><a href="<?php echo $siteUrl ?> "><input
							style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"
							type="button" value="<?php echo Text::_("LIB_MINIORANGESAMLPLUGIN_DONE_BTN"); ?>" onClick="self.close();"></a></div>';
			<?php
			exit;
		}

		$responseSigned = self::checkSign($certFingerprint, $signatureData, $certFromPlugin, $relayState, $resCert, $post);

		// Returning boolean $responseSigned

		return $responseSigned;
	}

	public static function checkSign($certFingerprint, $signatureData, $certFromPlugin, $relayState, $resCert,$post)
	{

		$certificates = $signatureData['Certificates'];

		if (count($certificates) === 0)
		{
			$pemCert = $certFromPlugin;
		}
		else
		{
			$fpArray = array();
			$fpArray[] = $certFingerprint;
			$pemCert = self::findCertificate($fpArray, $certificates, $relayState, $resCert, $post);
		}

		$lastException = null;

		$key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'public'));
		$key->loadKey($pemCert);

		try
		{
			/*
			 * Make sure that we have a valid signature
			 */
			// assert('$key->type === XMLSecurityKey::RSA_SHA1');
			self::validateSignature($signatureData, $key);

			return true;
		}
		catch (Exception $e)
		{
			echo 'Validation with key failed with exception: ' . self::escapeHtml($e->getMessage());
			$lastException = $e;
		}

		// We were unable to validate the signature with any of our keys.

		if ($lastException !== null)
		{
			throw $lastException;
		}
		else
		{
			return false;
		}
	}

	public static function validateIssuerAndAudience($samlResponse, $spEntityId, $issuerToValidateAgainst, $relayState,$post)
	{

		$issuer = current($samlResponse->getAssertions())->getIssuer();
		$audience = current(current($samlResponse->getAssertions())->getValidAudiences());

		if (strcmp($issuerToValidateAgainst, $issuer) === 0)
		{
			if (strcmp($audience, $spEntityId) === 0)
			{
				return true;
			}
			else
			{
				$type = "change_sp_id";

				if ($relayState == 'testValidate')
				{
					self::saveTestConfig('#__miniorange_saml_config', 'test_configuration', 0);
					self::keepRecords('Test Configuration', 'Issuer cannot be verified.');
					self::showInvalidIssuerMessage($audience, $spEntityId, $post, $type, $relayState);
				}
				else
				{
					self::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
					self::keepRecords('SSO Status', 'Issuer cannot be verified.');
					self::showInvalidIssuerMessage($audience, $spEntityId, $post, $type, $relayState);
				}
			}
		}
		else
		{
			if ($relayState == 'testValidate')
			{
				$type = "change_idp_id";
				self::saveTestConfig('#__miniorange_saml_config', 'test_configuration', 0);
				self::keepRecords('Test Configuration', 'Issuer cannot be verified.');
				self::showInvalidIssuerMessage($issuer, $issuerToValidateAgainst, $post, $type, $relayState);
			}
			else
			{
				self::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
				self::keepRecords('SSO Status', 'Issuer cannot be verified.');
				self::showInvalidIssuerMessage($issuer, $issuerToValidateAgainst, $post, $type, $relayState);
			}
		}
	}

	public static function saveTestConfig($dbName, $column, $value)
	{
		$updatefieldsarray = array(
			$column => isset($value) ? $value : false,
		);
		$result = new Mo_Saml_Local_Util;
		$result->genericUpdateQuery($dbName, $updatefieldsarray);
	}
	public static function showInvalidAudianceMessage($issuer,$issuerToValidateAgainst)
	{
		self::loadLanguage();
		ob_end_clean();
		echo '<div style="font-family:Calibri;padding:0 3%;">';
		echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR_HEADER") . '</div>
                <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_THREE") . '</p>
                <p>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERROR_REPORT") . ':</p>
                <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERROR_CAUSE") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_FOUR") . '</p>
                <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_EXPECTED_ID") . ': </strong>' . self::escapeHtml($issuer) . '<p>
                <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ACUTAL_ID") . ': </strong>' . self::escapeHtml($issuerToValidateAgainst) . '</p>
                <div style="margin:3%;display:block;text-align:center;">
                    <input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" type="button" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_DONE_BTN") . '" onClick="self.close();">
                </div>
            </div>
                ';
		exit;
	}
	public static function showAdminToolsMsg()
	{
		self::loadLanguage();
		ob_end_clean();
		echo '
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="' . Uri::base() . 'components/com_miniorange_saml/assets/js/samlUtility.js"></script>
        <link rel="stylesheet" href="' . Uri::base() . 'components/com_miniorange_saml/assets/css/mo_saml_style.css" type="text/css">
        <link rel="stylesheet" href="' . Uri::base() . 'components/com_miniorange_saml/assets/css/miniorange_boot.css" type="text/css">
        <link rel="stylesheet" href="' . Uri::base() . 'components/com_miniorange_saml/assets/css/bootstrap-select-min.css" type="text/css">
        
        <div id="my_admintool_modal"  class="TC_modals">
            <div class="TC_modal-content">
                <div class=" mo_boot_mt-3">
                    <div class="mo_boot_col-sm-12">
                        <h3 class="mo_boot_text-center" style="color:red"><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ADMIN_TOOL_FAQ") . '</strong></h3><hr>
                        <p>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ADMIN_TOOL_MSG") . '</p>
                        <p>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ADMIN_TOOL_LINK") . '<a href="https://faq.miniorange.com/knowledgebase/joomla-saml-sso-not-working-while-using-admin-tools/" target="_blank" >' . Text::_("LIB_MINIORANGESAMLPLUGIN_ADMIN_TOOL_GIVEN_LINK") . '</a> ' . Text::_("LIB_MINIORANGESAMLPLUGIN_ADMIN_TOOL_ISSUE") . '</p>
                        <div> 
                            <form method="post" name="f" id ="do_not_show_again" action="' . Route::_('index.php?option=com_miniorange_saml&task=myaccount.doNotShowAgain') . '" > 
                                ' . HTMLHelper::_('form.token') . '
                                <input type="checkbox"  name="do_not_show_again" class="mo_saml_custom_checkbox" value="1">' . Text::_("LIB_MINIORANGESAMLPLUGIN_CHECKBOX_TEXT") . '
                            </form> 
                        </div>
                        <div class="mo_boot_text-center">
                            <button onclick="save_value()" class="mo_boot_btn mo_boot_btn-secondary">' . Text::_("LIB_MINIORANGESAMLPLUGIN_OK_BTN") . '</button>
                            <button onclick="close_popup()" class="mo_boot_btn mo_boot_btn-danger">' . Text::_("LIB_MINIORANGESAMLPLUGIN_CLOSE_BTN") . '</button>
                        </div>
                    </div>
                </div>
                <form method="post" id="close_popup" name="f" action="' . Route::_('index.php?option=com_miniorange_saml&task=myaccount.closePopup') . '" > 
                ' . HTMLHelper::_('form.token') . '
                </form>  
            
            </div>
        </div>';

		return;

	}


	/**
	 * Stop SSO when signature or certificate requirements fail (Finding 1).
	 *
	 * @param   string  $relayState   Relay state from the ACS request.
	 * @param   string  $errorKey     Language key for the error summary (test config).
	 * @param   string  $causeKey     Language key for the possible cause (test config).
	 * @param   string  $logContext   Short label for keepRecords.
	 *
	 * @return  void
	 */
	public static function showSamlSecurityError($relayState, $errorKey, $causeKey, $logContext)
	{
		self::loadLanguage();
		ob_end_clean();

		if ($relayState == 'testValidate')
		{
			self::saveTestConfig('#__miniorange_saml_config', 'test_configuration', 0);
			self::keepRecords('Test Configuration', $logContext);
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> '
				. Text::_('LIB_MINIORANGESAMLPLUGIN_ERR_HEADER') . '</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;">
                    <p><strong>' . Text::_('LIB_MINIORANGESAMLPLUGIN_ERR') . ': </strong>' . Text::_($errorKey) . '</p>
                    <p><strong>' . Text::_('LIB_MINIORANGESAMLPLUGIN_ERROR_CAUSE') . ': </strong>' . Text::_($causeKey) . '</p>
                    </div>
                    <div style="margin:3%;display:block;text-align:center;">
                    <input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" type="button" value="'
				. Text::_('LIB_MINIORANGESAMLPLUGIN_DONE_BTN') . '" onClick="self.close();">
                    </div></div>';
			exit;
		}

		self::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
		self::keepRecords('SSO Status', $logContext);
		echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_('LIB_MINIORANGESAMLPLUGIN_ERR') . ': </strong>'
			. Text::_('LIB_MINIORANGESAMLPLUGIN_SSO_ERR_EIGHT') . '</p></div>
            <div style="margin:3%;display:block;text-align:center;">
              <form action="' . Uri::root() . '"><input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="'
			. Text::_('LIB_MINIORANGESAMLPLUGIN_BACK_BTN') . '"></form></div>';
		exit;
	}

	/**
	 * Require a signed Response or Assertion and verify against the configured IdP certificate.
	 *
	 * @param   SAML2_Response  $samlResponse     Parsed SAML response.
	 * @param   string          $acsUrl           ACS URL used for destination checks.
	 * @param   string          $certFromPlugin   IdP certificate from plugin configuration.
	 * @param   string          $relayState       Relay state from the ACS request.
	 * @param   array           $post             POST data.
	 *
	 * @return  void
	 */
	public static function validateAndVerifySamlSignatures($samlResponse, $acsUrl, $certFromPlugin, $relayState, $post)
	{
		self::loadLanguage();
		$assertions = $samlResponse->getAssertions();

		if (empty($assertions))
		{
			self::showSamlSecurityError(
				$relayState,
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_NO_ASSERTION',
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_NO_ASSERTION_CAUSE',
				'No SAML assertion in response'
			);
		}

		$responseSignatureData = $samlResponse->getSignatureData();
		$assertionSignatureData = current($assertions)->getSignatureData();
		$hasResponseSignature = !empty($responseSignatureData);
		$hasAssertionSignature = !empty($assertionSignatureData);

		if (!$hasResponseSignature && !$hasAssertionSignature)
		{
			self::showSamlSecurityError(
				$relayState,
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_UNSIGNED',
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_UNSIGNED_CAUSE',
				'Unsigned SAML Response'
			);
		}

		if (empty(trim((string) $certFromPlugin)))
		{
			self::showSamlSecurityError(
				$relayState,
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_MISSING_IDP_CERT',
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_MISSING_IDP_CERT_CAUSE',
				'Missing IdP certificate'
			);
		}

		$certFromPlugin = self::sanitizeCertificate($certFromPlugin);
		$certFingerprint = XMLSecurityKey::getRawThumbprint($certFromPlugin);
		$certFingerprint = is_string($certFingerprint) ? iconv('UTF-8', 'CP1252//IGNORE', $certFingerprint) : '';
		$certFingerprint = is_string($certFingerprint) ? preg_replace('/\s+/', '', $certFingerprint) : '';

		if ($certFingerprint === '')
		{
			self::showSamlSecurityError(
				$relayState,
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_MISSING_IDP_CERT',
				'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_MISSING_IDP_CERT_CAUSE',
				'Invalid IdP certificate'
			);
		}

		if ($hasResponseSignature)
		{
			$validSignature = self::processResponse($acsUrl, $certFingerprint, $responseSignatureData, $samlResponse, $certFromPlugin, $relayState, $post);

			if ($validSignature === false)
			{
				self::showSamlSecurityError(
					$relayState,
					'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_INVALID_SIGN_RESP',
					'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SIX',
					'Invalid SAML Response signature'
				);
			}
		}

		if ($hasAssertionSignature)
		{
			$validSignature = self::processResponse($acsUrl, $certFingerprint, $assertionSignatureData, $samlResponse, $certFromPlugin, $relayState, $post);

			if ($validSignature === false)
			{
				self::showSamlSecurityError(
					$relayState,
					'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_INVALID_SIGN_ASSERTION',
					'LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SIX',
					'Invalid SAML Assertion signature'
				);
			}
		}
	}

	public static function showInvalidIssuerMessage($issuer, $issuerToValidateAgainst,$post,$type,$relayState)
	{
		self::loadLanguage();
		ob_end_clean();

		if ($relayState == 'testValidate')
		{
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR_HEADER") . '</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_THREE") . '</p>
                    <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERROR_CAUSE") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_FOUR") . '</p>
                    <p style="font-size:15px;"><strong  style="margin-left:15px;">';

			if ($type == 'change_sp_id')
			{
				echo Text::_("LIB_MINIORANGESAMLPLUGIN_EXPECTED_ID");
			}
			else
			{
				echo Text::_("LIB_MINIORANGESAMLPLUGIN_EXPECTED_IDP_ID");
			}echo': </strong>' . self::escapeHtml($issuer) . '<p>
                    <p style="font-size:15px;"><strong  style="margin-left:15px;">';

			if ($type == 'change_sp_id')
			{
				echo Text::_("LIB_MINIORANGESAMLPLUGIN_ACUTAL_ID");
			}
			else
			{
				echo Text::_("LIB_MINIORANGESAMLPLUGIN_ACUTAL_IDP_ID");
			}echo': </strong>' . self::escapeHtml($issuerToValidateAgainst) . '</p>';

			if ($type == 'change_sp_id')
			{
				echo'
                        <p><strong>' . Text::_('LIB_MINIORANGESAMLPLUGIN_SOLUTION_LABEL') . '</strong></p>
                        <ul>
                            <li style="margin-left:15px;font-size:15px;">' . Text::_('LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SOLUTION_SP_ONE') . '</li>
                            <li style="margin-left:15px;font-size:15px;">' . Text::_('LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SOLUTION_SP_TWO') . '</li>
                        </ul>';
			}
			else
			{
				echo'
                        <p><strong>' . Text::_('LIB_MINIORANGESAMLPLUGIN_SOLUTION_LABEL') . '</strong> ' . Text::_('LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SOLUTION_IDP') . '</p>';
			}

					echo'
                    </p>
                    </div>
                    <hr><br>
                    <div style="font-size:14pt;">' . Text::_("LIB_MINIORANGESAMLPLUGIN_EXPORT_CONFIG") . '</div>
                    <div style="margin:3%;display:block;text-align:center;">
                        <form name="f" method="post" action="' . Route::_('index.php?option=com_miniorange_saml&task=myaccount.importexport') . '">
                                ' . HTMLHelper::_('form.token') . '
                                <input type="hidden" name="test_configuration" value="true">
                                <input id="mo_sp_exp_exportconfig" type="button"  style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" onclick="submit();" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_EXPORT_CONFIG_BTN") . '" />
                                <input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" type="button" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_DONE_BTN") . '" onClick="self.close();">
                        </form>
                        
                    </div>';
			exit;
		}
		else
		{
			echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_EIGHT") . '</p></div>
            <div style="margin:3%;display:block;text-align:center;">
              <form action=' . Uri::root() . '><input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_BACK_BTN") . '"></form></div>';
			exit;
		}

	}

	public static function getSuperUser()
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true)->select('user_id')->from('#__user_usergroup_map')->where('group_id=' . $db->quote(8));
		$db->setQuery($query);
		$results = $db->loadColumn();

		return  $results[0];
	}

	private static function getSamlCertificateFingerprint($cert)
	{
		$pem = "-----BEGIN CERTIFICATE-----\n" .
			chunk_split($cert, 64) .
			"-----END CERTIFICATE-----\n";
		$fingerprint = openssl_x509_fingerprint($pem, 'sha1');

		if ($fingerprint === false)
		{
			return '';
		}

		return strtolower(str_replace(':', '', $fingerprint));
	}

	private static function findCertificate(array $certFingerprints, array $certificates, $relayState, $resCert,$post)
	{
		self::loadLanguage();
		$result        = (new Mo_Saml_Local_Util)->loadDbValues('#__miniorange_saml_config');
		$ssoUrl       = isset($result['single_signon_service_url']) ? $result['single_signon_service_url'] : '';

		if (strpos($ssoUrl, 'login.microsoftonline.com'))
		{
			$cause = Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_FIVE");
			$isAzureAd = 1;
			$redirect = "https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_sso_enterprise_plan";
		}
		else
		{
			$cause = Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SIX");
			$isAzureAd = 0;
		}

		$candidates = array();

		foreach ($certificates as $cert)
		{
			$fp = self::getSamlCertificateFingerprint($cert);

			if (!in_array($fp, $certFingerprints, true))
			{
				$candidates[] = $fp;
				continue;
			}

			// We have found a matching fingerprint.

			$pem = "-----BEGIN CERTIFICATE-----\n" .
				chunk_split($cert, 64) .
				"-----END CERTIFICATE-----\n";

			return $pem;
		}

		if ($relayState == 'testValidate')
		{
			$expectedCert = self::sanitizeCertificate($resCert);
			self::saveTestConfig('#__miniorange_saml_config', 'test_configuration', 0);
			self::keepRecords('Test Configuration', 'Unable to find matching certificate');
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR_HEADER") . '</div>
            <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SEVEN") . '</p>
            <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERROR_CAUSE") . ': </strong>' . $cause . '</p>
			<p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_EXPECTED_VAL") . ':</strong>' . $expectedCert . '</p>
			<p><strong>' . Text::_('LIB_MINIORANGESAMLPLUGIN_SOLUTION_LABEL') . '</strong> ' . Text::_('LIB_MINIORANGESAMLPLUGIN_SSO_ERR_SOLUTION_CERT') . '</p>';
			echo str_repeat('&nbsp;', 15);
			echo '</div>

                <div style="margin:3%;display:block;text-align:center;">';

			$btnBase = 'display:inline-block;vertical-align:middle;margin:6px;padding:10px 24px;min-width:120px;font-size:15px;line-height:1.4;text-align:center;cursor:pointer;border-width:1px;border-style:solid;border-radius:4px;box-sizing:border-box;white-space:nowrap;color:#FFF;text-decoration:none;';
			$btnBlue = $btnBase . 'background:#3d618f;border-color:#3d618f;box-shadow:0px 1px 0px rgba(120, 200, 230, 0.6) inset;';

			if ($isAzureAd == 1)
			{
				echo '<a href="' . $redirect . '" style="' . $btnBlue . '" type="button">' . Text::_("LIB_MINIORANGESAMLPLUGIN_UPGRADE_BTN") . '</a> ';
			}

			echo '<input style="' . $btnBlue . '" type="button" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_DONE_BTN") . '" onClick="self.close();">
                </div></div>';
			exit;
		}
		else
		{
			self::saveTestConfig('#__miniorange_saml_config', 'sso_status', 0);
			self::keepRecords('SSO Status', 'Unable to find matching certificate');
			echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_EIGHT") . '</p></div>
                  <div style="margin:3%;display:block;text-align:center;">
                    <form action=' . Uri::root() . '><input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_BACK_BTN") . '"></form></div>';
			exit;
		}
	}

	public static function samlNotConfigured($relayState)
	{
		self::loadLanguage();

		if ($relayState == 'testValidate')
		{
			echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_NOT_CONFIGURED_ERR") . '</p></div>
                <div style="margin:3%;display:block;text-align:center;">
              <form action=' . Uri::root() . '><input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;;" type="submit" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_BACK_BTN") . '"></form></div>';
			exit;
		}
		else
		{
			echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_ERR_EIGHT") . '</p></div>
                  <div style="margin:3%;display:block;text-align:center;">
                    <form action=' . Uri::root() . '><input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;" type="submit" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_BACK_BTN") . '"></form></div>';
			exit;
		}
	}

	public static function stVal()
	{
		$uid = self::getSuperUser();
		self::updateCurrentUserName($uid, 1, 'authCount');
		$databaseName = '#__miniorange_saml_customer_details';
		$timeInterval = 60 * 60 * 24 * 3;
		$updatefieldsarray = array(
			'mo_cron_period' => time() + $timeInterval,
		);
		$result = new Mo_Saml_Local_Util;
		$result->genericUpdateQuery($databaseName, $updatefieldsarray);

	}



	/**
	 * Decrypt an encrypted element.
	 *
	 * This is an internal helper function.
	 *
	 * @param   DOMElement $encryptedData The encrypted data.
	 * @param   XMLSecurityKey $inputKey The decryption key.
	 * @param   array          &$blacklist Blacklisted decryption algorithms.
	 * @return DOMElement     The decrypted element.
	 * @throws Exception
	 */
	private static function doDecryptElement(DOMElement $encryptedData, XMLSecurityKey $inputKey, array &$blacklist)
	{
		$enc = new XMLSecEnc;
		$enc->setNode($encryptedData);

		$enc->type = $encryptedData->getAttribute("Type");
		$symmetricKey = $enc->locateKey($encryptedData);

		if (!$symmetricKey)
		{
			throw new Exception('Could not locate key algorithm in encrypted data.');
		}

		$symmetricKeyInfo = $enc->locateKeyInfo($symmetricKey);

		if (!$symmetricKeyInfo)
		{
			throw new Exception('Could not locate <dsig:KeyInfo> for the encrypted key.');
		}

		$inputKeyAlgo = $inputKey->getAlgorith();

		if ($symmetricKeyInfo->isEncrypted)
		{
			$symKeyInfoAlgo = $symmetricKeyInfo->getAlgorith();

			if (in_array($symKeyInfoAlgo, $blacklist, true))
			{
				throw new Exception('Algorithm disabled: ' . var_export($symKeyInfoAlgo, true));
			}

			if ($symKeyInfoAlgo === XMLSecurityKey::RSA_OAEP_MGF1P && $inputKeyAlgo === XMLSecurityKey::RSA_1_5)
			{
				/*
				 * The RSA key formats are equal, so loading an RSA_1_5 key
				 * into an RSA_OAEP_MGF1P key can be done without problems.
				 * We therefore pretend that the input key is an
				 * RSA_OAEP_MGF1P key.
				 */
				$inputKeyAlgo = XMLSecurityKey::RSA_OAEP_MGF1P;
			}

			// Make sure that the input key format is the same as the one used to encrypt the key.

			if ($inputKeyAlgo !== $symKeyInfoAlgo)
			{
				throw new Exception(
					'Algorithm mismatch between input key and key used to encrypt ' .
					' the symmetric key for the message. Key was: ' .
					var_export($inputKeyAlgo, true) . '; message was: ' .
					var_export($symKeyInfoAlgo, true)
				);
			}

			/** @var XMLSecEnc $encKey */
			$encKey = $symmetricKeyInfo->encryptedCtx;
			$symmetricKeyInfo->key = $inputKey->key;
			$keySize = $symmetricKey->getSymmetricKeySize();

			if ($keySize === null)
			{
						/*
				 To protect against "key oracle" attacks, we need to be able to create a
						 * symmetric key, and for that we need to know the key size.
						 */
						throw new Exception('Unknown key size for encryption algorithm: ' . var_export($symmetricKey->type, true));
			}

			try
			{
						$key = $encKey->decryptKey($symmetricKeyInfo);

				if (strlen($key) != $keySize)
				{
					throw new Exception(
						'Unexpected key size (' . strlen($key) * 8 . 'bits) for encryption algorithm: ' .
						var_export($symmetricKey->type, true)
					);
				}
			}
			catch (Exception $e)
			{
						// We failed to decrypt this key. Log it, and substitute a "random" key.

						/*
						 * Create a replacement key, so that it looks like we fail in the same way as if the key was correctly padded.
						 * We base the symmetric key on the encrypted key and private key, so that we always behave the
						 * same way for a given input key.
						 */
						$encryptedKey = $encKey->getCipherValue();
						$pkey = openssl_pkey_get_details($symmetricKeyInfo->key);
						$pkey = hash('sha256', serialize($pkey), true);
						$key = hash('sha256', $encryptedKey . $pkey, true);

						// Make sure that the key has the correct length.

				if (strlen($key) > $keySize)
				{
					$key = substr($key, 0, $keySize);
				}
				elseif (strlen($key) < $keySize)
				{
					$key = str_pad($key, $keySize);
				}
			}

			$symmetricKey->loadkey($key);
		}
		else
		{
			$symKeyAlgo = $symmetricKey->getAlgorith();

			// Make sure that the input key has the correct format.

			if ($inputKeyAlgo !== $symKeyAlgo)
			{
				throw new Exception(
					'Algorithm mismatch between input key and key in message. ' .
					'Key was: ' . var_export($inputKeyAlgo, true) . '; message was: ' .
					var_export($symKeyAlgo, true)
				);
			}

			$symmetricKey = $inputKey;
		}

		$algorithm = $symmetricKey->getAlgorith();

		if (in_array($algorithm, $blacklist, true))
		{
			throw new Exception('Algorithm disabled: ' . var_export($algorithm, true));
		}

		/** @var string $decrypted */
		$decrypted = $enc->decryptNode($symmetricKey, false);

		/*
		 * This is a workaround for the case where only a subset of the XML
		 * tree was serialized for encryption. In that case, we may miss the
		 * namespaces needed to parse the XML.
		 */
		$xml = '<root xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ' .
			'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
			$decrypted .
			'</root>';
		$newDoc = self::loadUntrustedXml($xml);

		if ($newDoc === false)
		{
			throw new Exception('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?');
		}

		$decryptedElement = $newDoc->firstChild->firstChild;

		if ($decryptedElement === null)
		{
			throw new Exception('Missing encrypted element.');
		}

		if (!($decryptedElement instanceof DOMElement))
		{
			throw new Exception('Decrypted element was not actually a DOMElement.');
		}

		return $decryptedElement;
	}

	/**
	 * Decrypt an encrypted element.
	 *
	 * @param   DOMElement $encryptedData The encrypted data.
	 * @param   XMLSecurityKey $inputKey The decryption key.
	 * @param   array $blacklist Blacklisted decryption algorithms.
	 * @return DOMElement     The decrypted element.
	 * @throws Exception
	 */
	public static function decryptElement(DOMElement $encryptedData, XMLSecurityKey $inputKey, array $blacklist = array())
	{
		try
		{
			return self::doDecryptElement($encryptedData, $inputKey, $blacklist);
		}
		catch (Exception $e)
		{
			/*
			 * Something went wrong during decryption, but for security
			 * reasons we cannot tell the user what failed.
			 */
			throw new Exception('Failed to decrypt XML element.');
		}
	}

	public static function getUserFromJoomla($matcher, $username, $email)
	{
		// Check if email exist in database
		$db = MoSamlDbHelper::getDb();

		switch ($matcher)
		{
			case 'username':
				$query = $db->getQuery(true)
					->select('id')
					->from('#__users')
					->where('username=' . $db->quote($username));
				break;
			case 'email':
			default:
				$query = $db->getQuery(true)
					->select('id')
					->from('#__users')
					->where('email=' . $db->quote($email));
				break;
		}

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;

	}

	public static function sanitizeCertificate($certificate)
	{
		$certificate = preg_replace("/[\r\n]+/", "", $certificate);
		$certificate = str_replace("-", "", $certificate);
		$certificate = str_replace("BEGIN CERTIFICATE", "", $certificate);
		$certificate = str_replace("END CERTIFICATE", "", $certificate);
		$certificate = str_replace(" ", "", $certificate);
		$certificate = chunk_split($certificate, 64, "\r\n");
		$certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";

		return $certificate;
	}

	public static function desanitizeCertificate($certificate)
	{
		$certificate = preg_replace("/[\r\n]+/", "", $certificate);
		$certificate = str_replace("-----BEGIN CERTIFICATE-----", "", $certificate);
		$certificate = str_replace("-----END CERTIFICATE-----", "", $certificate);
		$certificate = str_replace(" ", "", $certificate);

		return $certificate;
	}

	public static function sanitizeUrl($url)
	{
		$url = preg_replace('/\s+/', '', $url);

		return $url;
	}

	/**
	 *
	 * @param   mixed  $value  Value taken from SAML, the database, or an exception.
	 *
	 * @return  string
	 */
	public static function escapeHtml($value)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	/**
	 * Parse XML from a SAML response, metadata file, or decrypted assertion.
	 *
	 * External entities and DOCTYPE declarations are rejected. A normal SAML
	 * document has neither, so login and metadata import are unchanged.
	 *
	 * @param   string  $xml  Untrusted XML.
	 *
	 * @return  DOMDocument|false
	 */
	public static function loadUntrustedXml($xml)
	{
		if (!is_string($xml) || stripos($xml, '<!DOCTYPE') !== false)
		{
			return false;
		}

		libxml_set_external_entity_loader(
			function () {
				return null;
			}
		);

		try
		{
			$document = new DOMDocument;
			$loaded = $document->loadXML($xml, LIBXML_NONET);
		}
		finally
		{
			libxml_set_external_entity_loader(null);
		}

		if (!$loaded)
		{
			return false;
		}

		return $document;
	}

	/**
	 * Reject a SAML response or assertion that was already accepted.
	 *
	 * The #__miniorange_saml_replay table is created by install and update SQL.
	 * A new IdP login has new IDs, so a normal SSO click is unchanged.
	 *
	 * @param   string|null  $responseId    Response ID attribute.
	 * @param   string|null  $assertionId   Assertion ID attribute.
	 * @param   int|null     $notOnOrAfter  Assertion expiry timestamp.
	 *
	 * @return  boolean  True when this message was already used.
	 */
	public static function isReplayedSamlMessage($responseId, $assertionId, $notOnOrAfter)
	{
		$ids = array();

		if (is_string($responseId) && $responseId !== '')
		{
			$ids[] = hash('sha256', 'R:' . $responseId);
		}

		if (is_string($assertionId) && $assertionId !== '')
		{
			$ids[] = hash('sha256', 'A:' . $assertionId);
		}

		if (!$ids)
		{
			return false;
		}

		$expires = ($notOnOrAfter !== null) ? ((int) $notOnOrAfter + 120) : (time() + 300);

		try
		{
			$db = MoSamlDbHelper::getDb();
			$db->setQuery(
				$db->getQuery(true)
					->delete($db->quoteName('#__miniorange_saml_replay'))
					->where($db->quoteName('expires') . ' < ' . (int) time())
			);
			$db->execute();

			foreach ($ids as $id)
			{
				$db->setQuery(
					$db->getQuery(true)
						->insert($db->quoteName('#__miniorange_saml_replay'))
						->columns(array($db->quoteName('saml_id'), $db->quoteName('expires')))
						->values($db->quote($id) . ',' . (int) $expires)
				);
				$db->execute();
			}
		}
		catch (\Exception $e)
		{
			return self::isDuplicateKey($e);
		}

		return false;
	}

	/**
	 * True when the database rejected an insert because the ID is already stored.
	 *
	 * @param   \Exception  $e  Database exception.
	 *
	 * @return  boolean
	 */
	private static function isDuplicateKey($e)
	{
		$message = $e->getMessage();

		if ($e->getPrevious())
		{
			$message .= ' ' . $e->getPrevious()->getMessage();
		}

		return strpos($message, '1062') !== false || stripos($message, 'Duplicate') !== false;
	}

	public static function moSamlShowTestResult($username, $attrs, $siteUrl)
	{
		self::loadLanguage();
		$ssoUrl = $siteUrl . '?morequest=sso';
		$trialURL = $siteUrl . 'administrator/index.php?option=com_miniorange_saml&tab=request_demo';
		$siteUrls = $siteUrl . '/plugins/authentication/miniorangesaml/';

		if (ob_get_contents())
		{
			ob_end_clean();
		}

		echo '
               <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script> 
               <script src="' . $siteUrl . 'administrator/components/com_miniorange_saml/assets/js/samlUtility.js" type="text/javascript"></script>
               <link rel="stylesheet" href="' . $siteUrl . 'media/com_miniorange_saml/css/miniorange_saml.css" type="text/css">
            <div style="font-family:Calibri;padding:0 3%; margin:100px;">';

		if (!empty($username))
		{
			echo '<div style="color: #3c763d; background-color: #dff0d8; padding:5px; text-align:center; font-size:14pt; display:inline-flex; align-items:center; gap:6px; width:fit-content; ">
                <svg width="30" height="30" viewBox="0 0 40 40">
                    <defs>
                        <linearGradient id="successGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#4CAF50;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#45a049;stop-opacity:1" />
                        </linearGradient>
                        <filter id="glow">
                            <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                            <feMerge> 
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <circle cx="20" cy="20" r="18" fill="url(#successGradient)" filter="url(#glow)"/>
                    <path d="M12,20 L16,24 L24,16" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
                <span>' . Text::_("LIB_MINIORANGESAMLPLUGIN_SUCC_TEST_CONFIG") . '</span>
            </div>';
		}
		else
		{
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">' . Text::_("LIB_MINIORANGESAMLPLUGIN_UNSUCC_TEST_CONFIG") . '</div>
				<div style="color: #a94442;font-size:14pt; margin-bottom:20px;">' . Text::_("LIB_MINIORANGESAMLPLUGIN_TEST_WARNING") . '</div>
				<div style="display:block;text-align:center;margin-bottom:4%;"><img style="width:15%;"src="' . $siteUrls . 'images/wrong.png"></div>';
		}

		if (!empty($username))
		{
			echo '<div style="margin-bottom:20px;margin-top:20px;"><span style="font-size:14pt;"><strong>Hello</strong>, ' . self::escapeHtml($username) . '</span></div>';
		}

		echo '<div class="saml-test-container" style="overflow-x:auto;">
                <table class="saml-test-table" style="table-layout:fixed;width:100%;">
                    <thead>
                        <tr>
                            <th style="width:50%;">' . Text::_("LIB_MINIORANGESAMLPLUGIN_ATTR_NAME") . '</th>
                            <th style="width:50%;">' . Text::_("LIB_MINIORANGESAMLPLUGIN_ATTR_VAL") . '</th>
                        </tr>
                    </thead>
                    <tbody>';

		if (!empty($attrs))
		{
			$rowCount = 0;

			foreach ($attrs as $key => $value)
			{
				$rowCount++;
				$rowClass = ($rowCount % 2 == 0) ? 'background: #f8f9fa;' : 'background: #ffffff;';
				$safeValues = array_map('SAML_Utilities::escapeHtml', (array) $value);
				echo "<tr style='{$rowClass} transition: background-color 0.2s ease;'>
                        <td style='padding: 18px; border-bottom: 1px solid #e9ecef; font-weight: 500; font-size: 16px;color:rgb(0, 0, 0); word-break: break-word; overflow-wrap: break-word; max-width: 0; vertical-align: top;'>" . self::escapeHtml($key) . "</td>
                        <td style='padding: 18px; border-bottom: 1px solid #e9ecef; font-size: 16px;color:rgb(0, 0, 0); word-break: break-word; overflow-wrap: break-word; max-width: 0; vertical-align: top;'>" . implode('<br/>', $safeValues) . "</td>
                      </tr>";
			}
		}
		else
		{
			echo '<tr><td colspan="2" style="padding: 40px; text-align: center; color: #6c757d; font-style: italic;">' . Text::_("LIB_MINIORANGESAMLPLUGIN_ATTR_NOT_RECIEVED") . '</td></tr>';
		}

		echo '</tbody></table></div><br>
        <div style="font-size:14pt;"> ' . Text::_("LIB_MINIORANGESAMLPLUGIN_SSO_URL") . '<br><br><strong>SSO URL: </strong>' . $ssoUrl . '</div></div>';

		self::saveTestConfig('#__miniorange_saml_config', 'test_configuration', true);
		self::keepRecords('Test Configuration', 'No error');
		exit;
	}


	public static function rmex()
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('enabled') . ' = ' . $db->quote(0),
		);
		$conditions = array(
			$db->quoteName('element') . ' = ' . $db->quote('com_miniorange_saml') . 'OR' . $db->quoteName('element') . ' = ' . $db->quote('miniorangesaml') . 'OR' . $db->quoteName('element') . ' = ' . $db->quote('samlredirect') . 'OR' . $db->quoteName('element') . ' = ' . $db->quote('pkg_MiniorangeSamlSSO') . 'OR' . $db->quoteName('element') . ' = ' . $db->quote('miniorangesamlplugin'),
		);

		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		$app = Factory::getApplication('site');
		$app->redirect(urldecode(Uri::root()));
		exit;
	}


	public static function keepRecords($status,$cause)
	{
	}

	/**
	 * Insert a Signature-node.
	 *
	 * @param   XMLSecurityKey $key The key we should use to sign the message.
	 * @param   array $certificates The certificates we should add to the signature node.
	 * @param   DOMElement $root The XML node we should sign.
	 * @param   DOMNode $insertBefore The XML element we should insert the signature element before.
	 */
	public static function insertSignature(
		XMLSecurityKey $key,
		array $certificates,
		?DOMElement $root = null,
		?DOMNode $insertBefore = null
	)
	{
		$objXMLSecDSig = new XMLSecurityDSig;
		$objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

		switch ($key->type)
		{
			case XMLSecurityKey::RSA_SHA256:
				$type = XMLSecurityDSig::SHA256;
				break;
			case XMLSecurityKey::RSA_SHA384:
				$type = XMLSecurityDSig::SHA384;
				break;
			case XMLSecurityKey::RSA_SHA512:
				$type = XMLSecurityDSig::SHA512;
				break;
			default:
				$type = XMLSecurityDSig::SHA1;
		}

		$objXMLSecDSig->addReferenceList(
			array($root),
			$type,
			array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N),
			array('id_name' => 'ID', 'overwrite' => false)
		);

		$objXMLSecDSig->sign($key);

		foreach ($certificates as $certificate)
		{
			$objXMLSecDSig->add509Cert($certificate, true);
		}

		$objXMLSecDSig->insertSignature($root, $insertBefore);
	}


	public static function updateCurrentUserName($id, $name, $col)
	{
		if (empty($name))
		{
			return;
		}

		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);

		$fields = array(
			$db->quoteName($col) . ' = ' . $db->quote($name),
		);
		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($id),
		);
		$query->update($db->quoteName('#__users'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();
	}

	public static function updateUsernameToSessionId($userID, $username, $sessionId)
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('username') . ' = ' . $db->quote($username),
			$db->quoteName('guest') . ' = ' . $db->quote('0'),
			$db->quoteName('userid') . ' = ' . $db->quote($userID),
		);

		$conditions = array(
			$db->quoteName('session_id') . ' = ' . $db->quote($sessionId),
		);

		$query->update($db->quoteName('#__session'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();
	}

	public static function loadDbValues($table, $loadBy, $colName = '*', $idName = 'id', $idValue = 1)
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);

		$query->select($colName);

		$query->from($db->quoteName($table));

		if (is_numeric($idValue))
		{
			$query->where($db->quoteName($idName) . " = $idValue");
		}
		else
		{
			$query->where($db->quoteName($idName) . " = " . $db->quote($idValue));
		}

		$db->setQuery($query);

		if ($loadBy == 'loadAssoc')
		{
			$defaultConfig = $db->loadAssoc();
		}
		elseif ($loadBy == 'loadResult')
		{
			$defaultConfig = $db->loadResult();
		}
		elseif ($loadBy == 'loadColumn')
		{
			$defaultConfig = $db->loadColumn();
		}

		return $defaultConfig;
	}

	public static function loadUserDbValues($table, $loadBy)
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName($table));
		$db->setQuery($query);

		if ($loadBy == 'loadAssoc')
		{
			$defaultConfig = $db->loadAssoc();
		}
		elseif ($loadBy == 'loadResult')
		{
			$defaultConfig = $db->loadResult();
		}
		elseif ($loadBy == 'loadColumn')
		{
			$defaultConfig = $db->loadColumn();
		}

		return $defaultConfig;
	}

	public static function addColumn()
	{
		$db = MoSamlDbHelper::getDb();
		$query = $db->getQuery(true);
		$query = 'ALTER TABLE `#__users` ADD COLUMN `authCount` int(11) DEFAULT 0';
		$db->setQuery($query);
		$db->execute();

	}

	public static function invokeFeedbackForm($post, $id)
	{
		self::loadLanguage();
		$tables = MoSamlDbHelper::getDb()->getTableList();
		$result = self::loadDbValues('#__extensions', 'loadColumn', 'extension_id', 'element', 'com_miniorange_saml');
		$tables = MoSamlDbHelper::getDb()->getTableList();
		$tab = 0;

		foreach ($tables as $table)
		{
			if (strpos($table, "miniorange_saml_config"))
			{
				$tab = $table;
			}
		}

		if ($tab)
		{
			$fid = new Mo_Saml_Local_Util;
			$fid = $fid->loadDbValues('#__miniorange_saml_config');
			$fid = $fid['uninstall_feedback'];
			$tpostData = $post;

			if (1)
			{
				if ($fid == 0)
				{
					foreach ($result as $results)
					{
						if ($results == $id)
						{
							$deactivateReasons = array(
								Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_1'),
								Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_2'),
								Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_3'),
								Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_4'),
								Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_5'),
								Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_6'),
								Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_7')
							);
							$deactivateReasonsHtml = '';

							foreach ($deactivateReasons as $deactivateReason)
							{
								$deactivateReasonsHtml .= '<div class="radio" style="padding:1px;margin-left:2%">'
									. '<label style="font-weight:normal;font-size:14.6px;font-family: cursive;" for="' . $deactivateReason . '">'
									. '<input type="radio" name="deactivate_plugin" value="' . $deactivateReason . '" required>'
									. $deactivateReason . '</label></div>';
							}

							$feedbackHiddenFields = '';

							foreach ($tpostData['cid'] as $key)
							{
								$feedbackHiddenFields .= '<input type="hidden" name="result[]" value=' . $key . '>';
							}

							?>
						  <link rel="stylesheet" type="text/css" href="<?php echo Uri::base();?>/components/com_miniorange_saml/assets/css/mo_saml_style.css" />
							<div class="form-style-6 " style="width:35% !important; margin-left:33%; margin-top: 4%;">
								<h1><?php echo Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_14'); ?></h1>
								<form name="f" method="post" action="" id="mojsp_feedback" style="background: #f3f1f1; padding: 10px;">
									<h3><?php echo Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_15'); ?></h3>
									<input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>
									<div>
										<p style="margin-left:2%">
											<?php echo $deactivateReasonsHtml; ?>
										<br>

										<textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:3%;width: 91%" cols="50" placeholder="Write your query here"></textarea><br><br><br>
										<tr>
											<td width="20%"><strong>Email<span style="color: #ff0000;">*</span>:</strong></td>
											<td><input type="email" name="feedback_email" required placeholder="Enter email to contact." style="width:55%"/></td>
										</tr>

										<?php echo $feedbackHiddenFields; ?>
										<br><br>
										<div class="mojsp_modal-footer">
											<input style="cursor: pointer;font-size: large;" type="submit" name="miniorange_feedback_submit" class="button button-primary button-large" value="Submit"/>
										</div>
									</div>
								</form>
								<form name="f" method="post" action="" id="mojspfree_feedback_form_close">
									<input type="hidden" name="mojspfree_skip_feedback" value="mojspfree_skip_feedback"/>
									<div style="text-align:center">
										<a href="#" onClick="skipSAMLSPForm()">Skip Feedback</a>
									</div>
									<?php echo $feedbackHiddenFields; ?>
								</form>
							</div>
							<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
							<script>
								jQuery('input:radio[name="deactivate_plugin"]').click(function () {
									var reason = jQuery(this).val();
									jQuery('#query_feedback').removeAttr('required')

									if (reason === Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_1')) {
										jQuery('#query_feedback').attr("placeholder", Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_8'));
									} else if (reason === Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_2')) {
										jQuery('#query_feedback').attr("placeholder", Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_10'));
									} else if (reason === Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_4')){
										jQuery('#query_feedback').attr("placeholder", Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_11'));
									}else if (reason === Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_6')){
										jQuery('#query_feedback').attr("placeholder", Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_9'));
									} else if (reason === Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_7') || reason === Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_5') ) {
										jQuery('#query_feedback').attr("placeholder", Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_12'));
										jQuery('#query_feedback').prop('required', true);
									} else if (reason === Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_3')) {
										jQuery('#query_feedback').attr("placeholder", Text::_('LIB_MINIORANGESAMLPLUGIN_FEEDBACK_13'));
									}
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

	public static function genericUpdateQuery($databaseName, $updatefieldsarray)
	{

		$db = MoSamlDbHelper::getDb();

		$query = $db->getQuery(true);

		foreach ($updatefieldsarray as $key => $value)
		{
			$databaseFields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
		}

		$query->update($db->quoteName($databaseName))->set($databaseFields)->where($db->quoteName('id') . " = 1");
		$db->setQuery($query);
		$db->execute();
	}

	public  static function getpluginType()
	{
		if (MoConstants::MO_SAML_SP == "ALL")
		{
			$pluginName = 'ALL';
		}
		elseif (MoConstants::MO_SAML_SP == "ADFS")
		{
			$pluginName = 'ADFS';
		}
		elseif (MoConstants::MO_SAML_SP == "GOOGLEAPPS")
		{
			$pluginName = 'GOOGLEAPPS';
		}

		return $pluginName;
	}

	public static function planUpgradeMsg($msg)
	{
		self::loadLanguage();
		$redirect = "https://portal.miniorange.com/initializePayment?requestOrigin=joomla_saml_sso_enterprise_plan";
		echo '<div style="font-family:Calibri;padding:0 3%;">';
		echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR_HEADER") . '</div>
        <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERR") . ': </strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_UNSUCCESSFUL_SSO") . '</p>
        <p><strong>' . Text::_("LIB_MINIORANGESAMLPLUGIN_ERROR_CAUSE") . ': </strong>' . self::escapeHtml($msg) . '</p>';
		echo str_repeat('&nbsp;', 15);
		echo '</div>
            <div style="margin:3%;display:block;text-align:center;">
            <a href="' . $redirect . '" style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;text-decoration:none"type="button" >' . Text::_("LIB_MINIORANGESAMLPLUGIN_UPGRADE_BTN") . '</a>
            <input style="padding:1%;background: #3d618f!important;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #3d618f;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="' . Text::_("LIB_MINIORANGESAMLPLUGIN_DONE_BTN") . '" onClick="self.close();">
            </div>
            </div>';
		exit;
	}

	public static function loadLanguage()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_miniorangesamlplugin', JPATH_SITE);
	}

	public static function setupGuides()
	{
		return '
        {
                "1": {
                  "name": "Azure AD",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-sso-using-azure-ad-idp"
                },
                "2": {
                  "name": "ADFS",
                  "link": "https://plugins.miniorange.com/guide-joomla-single-sign-sso-using-adfs-idp"
                },
                "3": {
                  "name": "Okta",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-sso-using-okta-idp"
                },
                "4": {
                  "name": "Google Apps",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-sso-using-google-apps-idp"
                },
                "5": {
                  "name": "Salesforce",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-sso-using-salesforce-idp"
                },
                "6": {
                  "name": "Onelogin",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-sso-using-onelogin-idp"
                },
                "7": {
                  "name": "Office 365",
                  "link": "https://plugins.miniorange.com/saml-single-sign-on-sso-for-joomla-using-office-365-as-idp"
                },
                "8": {
                  "name": "SimpleSAML",
                  "link": "https://plugins.miniorange.com/saml-single-sign-on-sso-for-joomla-using-simplesaml-as-idp"
                },
                "9": {
                  "name": "miniOrange",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-sso-using-miniorange-idp"
                },
                "10": {
                  "name": "Auth0",
                  "link": "https://plugins.miniorange.com/guide-for-auth0-as-idp-with-joomla"
                },
                "11": {
                  "name": "Shibboleth",
                  "link": "https://plugins.miniorange.com/guide-to-setup-shibboleth3-as-idp-with-joomla"
                },
                "12": {
                  "name": "Ping Federate",
                  "link": "https://plugins.miniorange.com/guide-for-pingfederate-as-idp-with-joomla"
                },
                "13": {
                  "name": "Ping One",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-sso-using-pingone-idp"
                },
                "14": {
                  "name": "Keycloak",
                  "link": "http://plugins.miniorange.com/joomla-single-sign-on-sso-using-jboss-keycloak-idp"
                },
                "15": {
                  "name": "Drupal",
                  "link": "https://plugins.miniorange.com/joomla-saml-single-sign-on-sso-with-drupal"
                },
                "16": {
                  "name": "Duo",
                  "link": "https://plugins.miniorange.com/joomla-saml-single-sign-on-sso-using-duo-as-idp"
                },
                "17": {
                  "name": "LastPass",
                  "link": "https://plugins.miniorange.com/guide-to-configure-lastpass-as-an-idp-saml-sp"
                },
                "18": {
                  "name": "FusionAuth",
                  "link": "https://plugins.miniorange.com/joomla-saml-single-sign-on-sso-using-fusionauth-as-idp"
                },
                "19": {
                    "name": "Wordpress",
                    "link": "https://plugins.miniorange.com/wordpress-single-sign-on-sso-using-joomla"
                },
                "20": {
                  "name": "Centrify",
                  "link": "https://plugins.miniorange.com/joomla-single-sign-on-sso-using-centrify-as-idp"
                },
                "21": {
                  "name": "CyberArk",
                  "link": "https://plugins.miniorange.com/joomla-saml-single-sign-on-sso-using-cyberark-as-idp"
                },
                "22": {
                  "name": "IBM Verify",
                  "link": "https://plugins.miniorange.com/login-to-joomla-using-ibm-security-verify-saml-sso"
                },
                "23": {
                  "name": "OpenAm",
                  "link": "https://plugins.miniorange.com/guide-for-openam-as-idp-with-joomla"
                },
                "24": {
                    "name": "Amazon AWS",
                    "link": "https://plugins.miniorange.com/single-sign-on-into-joomla-with-aws-as-idp"
                  },
                "25": {
                    "name": "Other IdPs",
                    "link": "https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=saml-sp"
                }
              
              
        }';
	}
}
