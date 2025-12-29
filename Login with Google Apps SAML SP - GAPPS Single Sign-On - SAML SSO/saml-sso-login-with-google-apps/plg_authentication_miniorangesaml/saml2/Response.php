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
     */
    private $assertions;
	
	/**
     * The destination URL in this response.
     */
	private $destination;
	private $certificates;
	private $signatureData;

    /**
     * Constructor for SAML 2 response messages.
     *
     * @param DOMElement|NULL $xml The input message.
     */
    public function __construct(?DOMElement $xml = NULL)
    {
        //parent::__construct('Response', $xml);

        $this->assertions = array();
		 $this->assertions = array();
		$this->certificates = array();
		
        if ($xml === NULL) {
            return;
        }
		
		$sig = SAML_Utilities::validateElement($xml);
	
	if ($sig !== FALSE) {
			$this->certificates = $sig['Certificates'];
			$this->signatureData = $sig;
		}
		
		/* set the destination from saml response */
		if ($xml->hasAttribute('Destination')) {
            $this->destination = $xml->getAttribute('Destination');
        }
		
		for ($node = $xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
			if ($node->namespaceURI !== 'urn:oasis:names:tc:SAML:2.0:assertion') {
				continue;
			}
			
			if ($node->localName === 'Assertion') {
				$this->assertions[] = new SAML2_Assertion($node);
			}else if ( $node->localName === 'EncryptedAssertion'){
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

}
