<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Rest
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Hyperion\Rest\Client;

use Zend\Http\Client as HttpClient,
    Zend\Uri,
    Zend\Rest\Client\RestClient as ZendRestClient;

/**
 * @category   Zend
 * @package    Zend_Rest
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class RestClient extends ZendRestClient
{
    protected $body;
    
    protected $format;
    
    protected $token;
    
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function setFormat($format) 
    {
        $this->format = $format;
        return $this;
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }
        
    public function setHeaders($client)
    {
        $headers = array();
        
        if ($this->token) {
            $headers['X-Auth-Token'] = $this->token;
        }
        
        if ($this->format) {
            $headers['Content-Type'] = 'application/' . $this->format;
            $headers['Accept'] = 'application/' . $this->format;
            if ($this->body) {
                switch ($this->format) {
                    case 'json':
                        $client->setRawBody($this->body);
                        break;
                }
            }
        }
        
        $client->setHeaders($headers);
    }
    
    
    /**
     * Performs an HTTP GET request to the $path.
     *
     * @param string $path
     * @param array  $query Array of GET parameters
     * @throws Zend\Http\Client\Exception
     * @return Zend\Http\Response
     */
    public function restGet($path, array $query = null)
    {
        $this->prepareRest($path);
        $client = $this->getHttpClient();
        if (is_array($query)) {
            $client->setParameterGet($query);
        }
        
        $this->setHeaders($client);
        
        return $client->setMethod('GET')->send();
    }
    
        
    /**
     * Perform a POST or PUT
     *
     * Performs a POST or PUT request. Any data provided is set in the HTTP
     * client. String data is pushed in as raw POST data; array or object data
     * is pushed in as POST parameters.
     *
     * @param mixed $method
     * @param mixed $data
     * @return \Zend\Http\Response
     */
    protected function performPost($method, $data = null)
    {
        $client = $this->getHttpClient();
        $client->setMethod($method);
    
        $request = $client->getRequest();
        if (is_string($data)) {
            $request->setContent($data);
        } elseif (is_array($data) || is_object($data)) {
            $request->post()->fromArray((array) $data);
        }
    
        $this->setHeaders($client);
        
        return $client->send($request);
    }
    
    /**
     * Performs an HTTP DELETE request to $path.
     *
     * @param string $path
     * @throws \Zend\Http\Client\Exception
     * @return \Zend\Http\Response
     */
    public function restDelete($path)
    {
        $this->prepareRest($path);
        
        $client = $this->getHttpClient();
        
        $this->setHeaders($client);
        
        return $client->setMethod('DELETE')->send();
    }
}
