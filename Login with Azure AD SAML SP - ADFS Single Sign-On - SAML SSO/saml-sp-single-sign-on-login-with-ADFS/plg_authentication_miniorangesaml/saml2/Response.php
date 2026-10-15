<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  plg_authentication_miniorangesaml
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die;

include 'Assertion.php';

/**
 * Class for SAML2 Response messages.
 *
 */
class SAML2_Response
{
	/**
	 * The assertions in this response.
	 *
	 * @var    array
	 */
	private $assertions;

	/**
	 * The destination URL in this response.
	 *
	 * @var    string
	 */
	private $destination;

	/**
	 * X509 certificates.
	 *
	 * @var    array
	 */
	private $certificates;

	/**
	 * Signature validation data.
	 *
	 * @var    array
	 */
	private $signatureData;

	/**
	 * The Response ID attribute.
	 *
	 * @var    string|null
	 */
	private $id;

	/**
	 * Constructor for SAML 2 response messages.
	 *
	 * @param   DOMElement|NULL $xml The input message.
	 */
	public function __construct(?DOMElement $xml = null)
	{
		// Parent::__construct('Response', $xml);

		$this->assertions = array();
		 $this->assertions = array();
		$this->certificates = array();

		if ($xml === null)
		{
			return;
		}

		if ($xml->hasAttribute('ID'))
		{
			$this->id = $xml->getAttribute('ID');
		}

		$sig = SAML_Utilities::validateElement($xml);

		if ($sig !== false)
		{
			$this->certificates = $sig['Certificates'];
			$this->signatureData = $sig;
		}

		// Set the destination from saml response

		if ($xml->hasAttribute('Destination'))
		{
			$this->destination = $xml->getAttribute('Destination');
		}

		for ($node = $xml->firstChild; $node !== null; $node = $node->nextSibling)
		{
			if ($node->namespaceURI !== 'urn:oasis:names:tc:SAML:2.0:assertion')
			{
				continue;
			}

			if ($node->localName === 'Assertion')
			{
				$this->assertions[] = new SAML2_Assertion($node);
			}
			elseif ($node->localName === 'EncryptedAssertion')
			{
				throw new Exception('Encrypted assertions are supported in licensed version of the plugin.');
			}
		}
	}

	/**
	 * Retrieve the assertions in this response.
	 *
	 * @return SAML2_Assertion[]|SAML2_EncryptedAssertion[]
	 */
	public function getAssertions()
	{
		return $this->assertions;
	}

	/**
	 * Set the assertions that should be included in this response.
	 *
	 * @param SAML2_Assertion[]|SAML2_EncryptedAssertion[] The assertions.
	 */
	public function setAssertions(array $assertions)
	{
		$this->assertions = $assertions;
	}

	public function getDestination()
	{
		return $this->destination;
	}


	public function getCertificates()
	{
		return $this->certificates;
	}

	public function getSignatureData()
	{
		return $this->signatureData;
	}

	/**
	 * Retrieve the Response ID.
	 *
	 * @return  string|null
	 */
	public function getId()
	{
		return $this->id;
	}

}
