<?php 	namespace Beanstream;

/**
 * Enpoints class to build, format and return API endpoint urls based on incoming platform and version
 *  
 * @author Kevin Saliba
 */
class Endpoints {
	
	/**
	 * Endpoints: Set BASE API Endpoint URL with inline {0} platform variable
	 */
	CONST BASE_URL_SHIFT4 = 'https://api.bam.shift4api.net';
	CONST BASE_URL_BAMBORANA = self::BASE_URL_SHIFT4;
	CONST BASE_URL_BEANSTREAM = self::BASE_URL_SHIFT4;

	/**
	 * Endpoint URL holders
	 * 
	 * Holds each of the URLS for the API endpoints
	 * platform and version are added in the constructor
	 * 
	 * @var	string	$basePaymentsURL
	 * @var	string	$getPaymentURL
	 * @var	string	$baseProfilesURL
	 * @var	string	$preAuthCompletionsURL
	 * @var	string	$returnsURL
	 * @var	string	$voidsURL
	 * @var	string	$profileURI
	 * @var	string	$cardsURI
	 * @var	string	$reportsURL
	 * @var	string	$continuationsURL
	 * @var	string	$tokenizationURL
	 */
	protected $basePaymentsURL;
	protected $getPaymentURL;
	protected $baseProfilesURL;
	protected $preAuthCompletionsURL;
	protected $returnsURL;
	protected $unreferencedReturnsURL;
	protected $voidsURL;
	protected $profileURI;
	protected $cardsURI;
	protected $reportsURL;
	protected $continuationsURL;
	protected $tokenizationURL;

    /**
     * Endpoint: incoming API Platform
     * 
     * @var string $_platform
     */
	protected $_platform;
	
    /**
     * Endpoint: incoming API Version
     * 
     * @var string $_version
     */
	protected $_version;


    /**
     * Resolve legacy platform names and full endpoint overrides to an API base.
     * REST URLs no longer use Beanstream's /api prefix.
     */
    public static function resolveBaseUrl($platform) {
        $platform = rtrim(trim((string) $platform), '/');
        $legacy = array(
            '', 'api', 'www', 'tls12-api',
            'api.na.bambora.com', 'tls12-api.na.bambora.com',
            'https://api.na.bambora.com', 'https://tls12-api.na.bambora.com',
            'api.beanstream.com/api', 'www.beanstream.com/api',
            'https://api.beanstream.com/api', 'https://www.beanstream.com/api'
        );
        if (in_array(strtolower($platform), $legacy, true)) {
            return self::BASE_URL_SHIFT4;
        }
        if (strpos($platform, '.') === false) {
            return 'https://' . $platform . '.na.bambora.com';
        }
        if (strpos($platform, '://') === false) {
            return 'https://' . $platform;
        }
        return $platform;
    }

    /**
     * Constructor
     * 
     * @param string $platform API Platform
     * @param string $version API Version
     */
	function __construct($platform, $version) {
			
		//assign endpoints
		$baseUrl = self::resolveBaseUrl($platform);
		
		//payments
		$this->basePaymentsURL = $baseUrl . '/{1}/payments';
		$this->preAuthCompletionsURL = $this->basePaymentsURL . '/{2}/completions';
		$this->returnsURL = $this->basePaymentsURL . '/{2}/returns';
		$this->unreferencedReturnsURL = $this->basePaymentsURL . '/0/returns';
		$this->voidsURL = $this->basePaymentsURL . '/{2}/void';
		$this->continuationsURL = $this->basePaymentsURL . '/{2}/continue';
		$this->tokenizationURL = $baseUrl . '/scripts/tokenization/tokens';
		
		//profiles
		$this->baseProfilesURL = $baseUrl . '/{1}/profiles';
		$this->profileURI = $this->baseProfilesURL . '/{2}';
		$this->cardsURI = $this->profileURI . '/cards';
		$this->cardURI = $this->cardsURI . '/{3}';

		//reporting
		$this->reportsURL = $baseUrl . '/{1}/reports';
		$this->getPaymentURL = $this->basePaymentsURL . '/{2}';
		
		//assign incoming platform and version
		$this->_platform = $platform;
		$this->_version = $version;
		
	}


	//methods to build out and return endpoints
	//payments
	
	/**
	 * getBasePaymentsURL() function
	 * 
	 * @return string	Endpoint URL
	 */	
	public function getBasePaymentsURL() {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->basePaymentsURL, array($this->_platform, $this->_version));
		
		//or use less-stringent str_replace instead of msgfmt above
		return str_replace(array('{0}', '{1}'), array($this->_platform, $this->_version), $this->basePaymentsURL);
	}

	/**
	 * getContinuationsURL() function
	 * 
     * @param string $merchant_data The IDEBIT_MERCHDATA value returned by the Interac response
	 * @return string	Endpoint URL
	 */	
	public function getContinuationsURL($merchant_data) {
		return str_replace(array('{0}', '{1}', '{2}'), array($this->_platform, $this->_version, $merchant_data), $this->continuationsURL);
	}
	
	/**
	 * getPreAuthCompletionsURL() function
	 * 
     * @param string $tid Transaction Id
	 * @return string Endpoint URL
	 */		
	public function getPreAuthCompletionsURL($tid) {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->preAuthCompletionsURL, array($this->_platform, $this->_version, $tid));
		
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}', '{2}'), array($this->_platform, $this->_version, $tid), $this->preAuthCompletionsURL);
	}

	/**
	 * getReturnsURL() function
	 * 
     * @param string $tid Transaction Id
	 * @return string Endpoint URL
	 */			
	public function getReturnsURL($tid) {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->returnsURL, array($this->_platform, $this->_version, $tid));
	
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}', '{2}'), array($this->_platform, $this->_version, $tid), $this->returnsURL);
	}

	/**
	 * getUnreferencedReturnsURL() function
	 * 
	 * @return string Endpoint URL
	 */			
	public function getUnreferencedReturnsURL() {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->unreferencedReturnsURL, array($this->_platform, $this->_version));
	
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}'), array($this->_platform, $this->_version), $this->unreferencedReturnsURL);
	}

	/**
	 * getVoidsURL() function
	 * 
     * @param string $tid Transaction Id
	 * @return string Endpoint URL
	 */		
	public function getVoidsURL($tid) {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->voidsURL, array($this->_platform, $this->_version, $tid));
	
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}', '{2}'), array($this->_platform, $this->_version, $tid), $this->voidsURL);
	}
	
	/**
	 * getTokenURL() function
	 * 
	 * @return string Endpoint URL
	 */		
	public function getTokenURL() {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->tokenizationURL, array($this->_platform));

		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}'), array($this->_platform), $this->tokenizationURL);
	}

	
	//profiles
	
	/**
	 * getProfilesURL() function
	 * 
	 * @return string Endpoint URL
	 */
	public function getProfilesURL() {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->baseProfilesURL, array($this->_platform, $this->_version));

		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}'), array($this->_platform, $this->_version), $this->baseProfilesURL);
	}
	
	/**
	 * getProfileURI() function
	 * 
     * @param string $pid Profile Id
	 * @return string Endpoint URL
	 */
	public function getProfileURI($pid) {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->profileURI, array($this->_platform, $this->_version, $pid));
	
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}', '{2}'), array($this->_platform, $this->_version, $pid), $this->profileURI);
	}
	
	/**
	 * getCardsURI() function
	 * 
     * @param string $pid Profile Id
	 * @return string Endpoint URL
	 */
	public function getCardsURI($pid) {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->cardsURI, array($this->_platform, $this->_version, $pid));
		
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}', '{2}'), array($this->_platform, $this->_version, $pid), $this->cardsURI);
	}
	
	/**
	 * getCardURI() function
	 * 
     * @param string $pid Profile Id
     * @param string $cid Card Id
	 * @return string Endpoint URL
	 */
	public function getCardURI($pid, $cid) {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->cardURI, array($this->_platform, $this->_version, $pid, $cid));
		
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}', '{2}', '{3}'), array($this->_platform, $this->_version, $pid, $cid), $this->cardURI);
	}

	
	//reporting

	/**
	 * getReportingURL() function
	 * 
	 * @return string Endpoint URL
	 */
	public function getReportingURL() {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->reportsURL, array($this->_platform, $this->_version));

		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}'), array($this->_platform, $this->_version), $this->reportsURL);
	}

	/**
	 * getPaymentUrl() function
	 * 
     * @param string $tid Transaction Id
	 * @return string Endpoint URL
	 */		
	public function getPaymentUrl($tid) {
		//parse url and replace variables via messageformat
		//return msgfmt_format_message('en_US', $this->getPaymentURL, array($this->_platform, $this->_version, $tid));
	
		//or use less-stringent str_replace instead of messageformat above
		return str_replace(array('{0}', '{1}', '{2}'), array($this->_platform, $this->_version, $tid), $this->getPaymentURL);
	}
	
}