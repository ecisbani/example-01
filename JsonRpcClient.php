<?php

#include('log4php/Logger.php');

/**
 * JSON-RPC fault class
 * 
 * @author Daniele Ribaudo
 *
 */
class JsonRpcFault extends Exception {}


/**
 * JSNO-RPC client class
 *
 * @author Daniele Ribaudo
 * @version 1.0
 * 
 */
class JsonRpcClient
{
    const VERSION               = '1.0';
    const DEF_USER_AGENT        = 'JSON-RPC Client/';
    const CONNECTION_HEADER     = 'Connection: close';
    const CONTENT_TYPE_HEADER   = 'Content-Type: application/json';
    const ACCEPT_HEADER         = 'Accept: application/json';
    
    /**
     * URL of the server
     *
     * @access private
     * @var string
     */
    private $url;

    /**
     * HTTP client timeout in sec
     *
     * @access private
     * @var integer
     */
    private $timeout;

    /**
     * Username for authentication
     *
     * @access private
     * @var string
     */
    private $username;

    /**
     * Password for authentication
     *
     * @access private
     * @var string
     */
    private $password;

    /**
     * True for a batch request
     *
     * @access public
     * @var boolean
     */
    public $is_batch = false;

    /**
     * Batch payload
     *
     * @access public
     * @var array
     */
    public $batch = array();

    /**
     * Enable debug verbose output
     *
     * @access public
     * @var boolean
     */
    public $debug = false;

    /**
     * Default HTTP headers to send to the server
     *
     * @access private
     * @var array
     */
    private $headers = array(
        self::CONNECTION_HEADER,
        self::CONTENT_TYPE_HEADER,
        self::ACCEPT_HEADER
    );

    /**
     * Proxy URL
     * 
     * @access private
     * @var string
     */
    private $proxy_url;
    
    /**
     * Proxy username
     * 
     * @access private
     * @var string
     */
    private $proxy_user;
    
    /**
     * Proxy password
     * 
     * @access private
     * @var string
     */
    private $proxy_passwd;
    
    /**
     * if true enable ssl certificate check
     * 
     * @access private
     * @var boolean
     */
    private $enable_ssl_check = true;
    
    /**
     * certificate file path for ssl client authentication
     * 
     * @access private
     * @var string
     */
    private $ssl_cert;
    
    /**
     * certificate password (if required) for ssl client authentication
     *
     * @access private
     * @var string
     */
    private $ssl_cert_passwd;
    
    /**
     * private key file path for ssl client authentication
     *
     * @access private
     * @var string
     */
    private $ssl_key;
    
    /**
     * private key password for ssl client authentication
     *
     * @access private
     * @var string
     */
    private $ssl_key_passwd;
    
    /**
     * Holds the logger
     * 
     * @access private
     * @var Logger
     */
    private $logger;
    
    /**
     * User-Agent to use
     * 
     * @access private
     * @var string
     */
    private static $user_agent;
    
    private static $json_pretty_print = 0;
    
    /**
     * Avoid to include explicitely the php file
     * 
     * @access public
     * @param string $class_name
     */
    public function __autoload($class_name) {
        include "$class_name.php";
    }
    
    /**
     * Constructor
     *
     * @access public
     * @param  string    $url         Server URL
     * @param  integer   $timeout     Server timeout in sec (default 5 sec)
     * @param  array     $headers     Custom HTTP headers
     */
    public function __construct($url, $timeout = 5, $headers = array())
    {
        $this->url = $url;
        $this->timeout = $timeout;
        $this->headers = array_merge($this->headers, $headers);
        
        if (class_exists('Logger')) {
            $this->logger = Logger::getLogger(__CLASS__);
        }
                
        self::$json_pretty_print = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0;
    }

    /**
     * Automatic mapping of procedures
     *
     * @access public
     * @param  string   $method   Procedure name
     * @param  array    $params   Procedure arguments
     * @return mixed
     */
    public function __call($method, array $params)
    {
        // Allow to pass an array and use named arguments
        if (count($params) === 1 && is_array($params[0])) {
            $params = $params[0];
        }

        return $this->execute($method, $params);
    }

    /**
     * Set authentication parameters
     *
     * @access public
     * @param  string   $username   Username
     * @param  string   $password   Password
     */
    public function authentication($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Set the proxy to be used
     * 
     * @param string $proxy_url     Proxy url (host:port)
     * @param string $proxy_user    Proxy user if any
     * @param string $proxy_passwd  Proxy password if any ($proxy_user must be set)
     */
    public function proxy($proxy_url, $proxy_user, $proxy_passwd)
    {
        $this->proxy_url= $proxy_url;
        $this->proxy_user = $proxy_user;
        $this->proxy_passwd = $proxy_passwd;
    }
    
    /**
     * Set certificate for ssl cliennt authentication
     * 
     * @access public
     * @param string $cert          Certificate path (in PEM format)
     * @param string $cert_passwd   Certificate password if include private key
     * @param string $key           Private key path (in PEM format)
     * @param string $key_passwd    Private key password
     */
    public function sslClientAuth($cert, $cert_passwd, $key=null, $key_passwd=null)
    {
        $this->ssl_cert = $cert;
        $this->ssl_cert_passwd = $cert_passwd;
        $this->ssl_key = $key;
        $this->ssl_key_passwd = $key_passwd;
    }
    
    /**
     * Start a batch request
     *
     * @access public
     * @return Client
     */
    public function batch()
    {
        $this->is_batch = true;
        $this->batch = array();

        return $this;
    }

    /**
     * Send a batch request
     *
     * @access public
     * @return array
     */
    public function send()
    {
        $this->is_batch = false;

        $response = $this->doRequest($this->batch);
        
        if ($this->debug) {
            foreach ($response as $resp) {
                $this->debug('==> Response: '.json_encode($resp, self::$json_pretty_print));
            }
        }
        
        return $this->parseResponse($response);
    }
    
    /**
     * Enable/disable ssl certificate check (default configuration is true)
     * 
     * @access public
     * @var boolean
     */
    public function sslCheck($boolean)
    {
        $this->enable_ssl_check = $boolean;
    }
    
    /**
     * Execute a procedure
     *
     * @access public
     * @param  string   $procedure   Procedure name
     * @param  array    $params      Procedure arguments
     * @return mixed
     */
    public function execute($procedure, array $params = array())
    {
        $payload = $this->prepareRequest($procedure, $params);
        
        if ($this->debug) {
            $this->debug('==> Request: '.json_encode($payload, self::$json_pretty_print));
        }
        
        if ($this->is_batch) {
            $this->batch[] = $payload;
            return $this;
        }
        
        $response = $this->doRequest($payload);
        
        if ($this->debug) {
            $this->debug('==> Response: '.json_encode($response, self::$json_pretty_print));
        }
        
        return $this->parseResponse($response);
    }

    /**
     * Prepare the payload
     *
     * @access private
     * @param  string   $procedure   Procedure name
     * @param  array    $params      Procedure arguments
     * @return array
     */
    private function prepareRequest($procedure, array $params = array())
    {
        $payload = array(
            'jsonrpc' => '2.0',
            'method' => $procedure,
            'id' => mt_rand()
        );

        if (! empty($params)) {
            $payload['params'] = $params;
        }

        return $payload;
    }

    /**
     * Parse the response and return the procedure result
     *
     * @access private
     * @param  array     $payload
     * @return mixed
     */
    private function parseResponse(array $payload)
    {
        if ($this->isBatchResponse($payload)) {

            $results = array();

            foreach ($payload as $response) {
                $results[] = $this->getResult($response);
            }

            return $results;
        }

        return $this->getResult($payload);
    }

    /**
     * Return true if we have a batch response
     *
     * @access public
     * @param  array    $payload
     * @return boolean
     */
    private function isBatchResponse(array $payload)
    {
        return array_keys($payload) === range(0, count($payload) - 1);
    }

    /**
     * Get a RPC call result
     *
     * @access private
     * @param  array    $payload
     * @return mixed
     */
    private function getResult(array $payload)
    {
        if (array_key_exists('result',$payload)) {
        //if (isset($payload['result'])) {
            return $payload['result'];
        }

        if (!isset($payload['error']) || !isset($payload['error']['code'])) {
            $code = -32700;
            $msg = 'Invalid response';
        }
        else {
            $code = $payload['error']['code'];
            $msg = $payload['error']['message'];
        }
        
        $this->error("json error: [$code] - $msg");
        
        throw new JsonRpcFault($msg, $code);
    }

    /**
     * Set the User-Agent to be used through the project
     * 
     * @access public
     * @param  string   $user_agent User-Agent
     */
    public static function setUserAgent($user_agent) {
        self::$user_agent = $user_agent;
    }
    
    /**
     * Do the HTTP request
     *
     * @access private
     * @param  string   $payload   Data to send
     */
    private function doRequest($payload)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, (!empty(self::$user_agent) ? self::$user_agent : self::DEF_USER_AGENT.self::VERSION));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        
        /* enable/disable ssl peer certificate check */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->enable_ssl_check);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ($this->enable_ssl_check ? 2 : 0));
        
        /* ssl client authentication */
        if (!empty($this->ssl_cert)) {
            curl_setopt($ch, CURLOPT_SSLCERT, $this->ssl_cert);
            if (!empty($this->ssl_cert_passwd)) {
                curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->ssl_cert_passwd);
            }
//             curl_setopt($ch, CURLOPT_SSLCERTTYPE, null);

            if (!empty($this->ssl_key)) {
                curl_setopt($ch, CURLOPT_SSLKEY, $this->ssl_key);
                curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->ssl_key_passwd);
//                 curl_setopt($ch, CURLOPT_SSLKEYTYPE, null);
            }
        }
        
        if (!empty($this->proxy_url)) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy_url);
            if (!empty($this->proxy_user) && !empty($this->proxy_passwd)) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$this->proxy_user:$this->proxy_passwd");
            }
        }
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        if ($this->username && $this->password) {
            curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        }

        $http_body = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->error("curl error: [".curl_errno($ch)."] - ".curl_error($ch));
            throw new JsonRpcFault(curl_error($ch), -1);
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code === 401 || $http_code === 403) {
            throw new JsonRpcFault('Access Denied', -1);
        }

        $response = json_decode($http_body, true);

        curl_close($ch);

        return is_array($response) ? $response : array();
    }
    
    /* useful method for logging with fallback if log4php is not used */
    
    public function info($msg) {
        if (isset($this->logger)) {
            $this->logger->info($msg);
        }
        else if ($this->debug) {
            echo "INFO - $msg\n";
        }
    }
    
    public function debug($msg) {
        if (isset($this->logger)) {
            $this->logger->debug($msg);
        }
        else if ($this->debug) {
            echo "DEBUG - $msg\n";
        }
    }
    
    public function error($msg) {
        if (isset($this->logger)) {
            $this->logger->error($msg);
        }
        else if ($this->debug) {
            echo "ERROR - $msg\n";
        }
    }
    
    public function warn($msg) {
        if (isset($this->logger)) {
            $this->logger->warn($msg);
        }
        else if ($this->debug) {
            echo "WARN - $msg\n";
        }
    }
    
}

