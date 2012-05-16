<?php

namespace Hyperion\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\View\Model\ViewModel,
    Hyperion\Rest\Client\RestClient;

class ControllerAbstract extends ActionController
{
    /**
     * variable to contain API config
     * @var array
     */
    protected $ApiConfig;
    
    /**
     * variable to contain Authenticated data
     * @var Zend_Session_Namespace
     */
    protected $authd;
    
    /**
     * Messages to pass to view
     * @var array
     */
    protected $messages = array();
    
    /**
     * default constructor
     */
    public function __construct()
    {
        $this->authd = new \Zend\Session\Container('hyperion\dns\authd');
        
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $this->messages = $flashMessenger->getMessages();
        }
        
    }
    
    protected function loadView($viewScript, $variables, $section = 'home')
    {
        
        $layout = new ViewModel(array(
            'messages' => $this->messages,
            'section' => $section,
        ));
        $layout->setTemplate('hyperion/layout');
        $view = new ViewModel($variables);
        $view->setTemplate($viewScript);
        $layout->addChild($view,'content');
        
        return $layout;
        
    }
    
    
    protected function request($type, $uri, $params = array(), $redirectParams = array())
    {
        $this->authenticate();
        
        $client = new RestClient($this->ApiConfig['Dns']['Url']);
        
        $client->setFormat('json');
        $client->setToken($this->authd->data->token->id);
        
        $client->setBody(json_encode($params));
        
        $method = 'rest' . ucfirst(strtolower($type));
        
        $response = $client->{$method}(
            '/' . $this->ApiConfig['Dns']['Ver'] . '/' .$this->ApiConfig['CustomerId'] . '/' . $uri,
            $params
        );
        
        if ($type !== 'get') {
            $redirectTo = array_merge(array('controller'=>'domains','action'=>'index'), $redirectParams);
            
            if ($response->isOk()) {
                $this->messages[] = 'Action Sucessfully Processed';
            } else if (in_array($response->getStatusCode(), array(202,204))) {
                $this->flashMessenger()->addMessage('Data Sent to Rackspace for processing');
                $this->redirect()->toRoute('hyperion/query', $redirectTo);
            } else {
                $data = json_decode($response->getBody());
                $this->messages[] = 'An Error Occured';
                \Zend\Debug::dump($data);
            }
        }
        
        return $response; 
    }
    
    /**
     * authenticate against rackspaces identity service
     */
    protected function authenticate()
    {
        if (isset($this->authd->data->token->expires)) {
            
            $now = new \DateTime();
            $expiry = new \DateTime($this->authd->data->token->expires);
            // subtract one hour to make sure no chance of token expiring during testing;
            if ($now < $expiry) {
                return;
            }
        }
        
        $client = new RestClient($this->ApiConfig['Auth']['Url']);
        
        $data = array(
            'credentials' => array(
                'username' => $this->ApiConfig['User'],
                'key' => $this->ApiConfig['Key'],
            ),
        );
        
        $client->setBody(json_encode($data));
        $client->setFormat('json');
        
        $response = $client->restPost(
            '/' . $this->ApiConfig['Auth']['Ver'] . '/auth', 
            $data
        );
        
        $data = json_decode($response->getBody());
        if (isset($data->unauthorized)) {
            unset($this->authd->data);
            throw new \RuntimeException($data->unauthorized->message, $data->unauthorized->code);
        }
        $this->authd->data = $data->auth;
        
    }
    
    public function setApiConfig($ApiConfig)
    {
        $this->ApiConfig = $ApiConfig;
        return $this;
    }
}
