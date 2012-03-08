<?php

namespace Hyperion\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\View\Model\ViewModel,
    Hyperion\Rest\Client\RestClient,
    Hyperion\Controller\ControllerAbstract,
    Hyperion\Form\DomainRecordForm;

class IndexController extends ControllerAbstract
{
    
    /**
     * (non-PHPdoc)
     * @see Zend\Mvc\Controller.ActionController::indexAction()
     */
    public function indexAction()
    {
        return $this->loadView('hyperion/index', array(
            'messages' => $this->messages,
        ));
    }
}