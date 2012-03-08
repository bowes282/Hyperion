<?php

namespace Hyperion\Dns\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\View\Model\ViewModel,
    Hyperion\Rest\Client\RestClient,
    Hyperion\Controller\ControllerAbstract,
    Hyperion\Form\DomainForm,
    Hyperion\Form\DomainRecordForm;

class DomainsController extends ControllerAbstract
{
    
    /**
     * (non-PHPdoc)
     * @see Zend\Mvc\Controller.ActionController::indexAction()
     */
    public function indexAction()
    {
        $response = $this->request('get', 'domains');
        $data = json_decode($response->getBody());

        return $this->loadView('domains/index', array(
            'domains' => $data->domains,
        ),'dns');
    }
    
    public function jobsAction()
    {
        $response = $this->request('get', 'status');
        $data = json_decode($response->getBody());

        \Zend\Debug::dump($data);
        return $this->loadView('domains/jobs', array(
            'data' => $data,
        ),'dns');
    }
    
    public function limitsAction()
    {
        $response = $this->request('get', 'limits');
        $data = json_decode($response->getBody());
        
        \Zend\Debug::dump($data);
        return $this->loadView('domains/limits', array(
            'data' => $data,
        ),'dns');
    }
    
    /**
     * action to display domain details
     * @throws \RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function detailAction()
    {
        $request = $this->getRequest();
        if (!$id = $request->query()->get('id', false)) {
            throw new \RuntimeException('Domain ID must be specified');
        }
        
        $response = $this->request(
            'get', 
            'domains/' . $id, 
            array(
                'showRecords' => 'true',
                'showSubdomains' => 'true',
            )
        );
        
        if ($response->isOk()) {
            $data = json_decode($response->getBody());
        }
        
        
        return $this->loadView('domains/detail', array(
            'domain' => $data,
        ),'dns');
    }
    
    /**
     * create domain
     */
    public function createAction()
    {
       $request = $this->getRequest();
        $form = new DomainForm();
        $form->setLegend('Create Domain');
        
        if ($request->isPost()){
            if ($form->isValid($request->post()->toArray())) {
                $response = $this->request(
                    'post', 
                    'domains',
                    array('domains' => array(
                        $form->getValues()
                    ))
                );
                
            }
        }
        
        return $this->loadView('domains/create', array(
            'form' => $form,
        ),'dns');
    }
    
    /**
     * edit domain
     * @throws \RuntimeException
     */
    public function editAction()
    {
        $request = $this->getRequest();
       
        if (!$domain = $request->query()->get('id', false)) {
            throw new \RuntimeException('Domain ID must be specified');
        }
       
        $form = new DomainForm();
        $form->setLegend('Edit Domain');
        $form->removeElement('name');
        
        if ($request->isPost()){
            if ($form->isValid($request->post()->toArray())) {
                $response = $this->request(
                    'put', 
                    'domains/' . $domain,
                    $form->getValues()
                );
                
            }
        } else {
            $response = $this->request(
                'get',
                'domains/' . $domain
            );
            if ($response->isOk()) {
                $form->populate((array)json_decode($response->getBody()));
            }
            
        }
        
        return $this->loadView('domains/edit', array(
            'form' => $form,
        ),'dns');
    }
    
    
/**
     * delete domain record
     * @throws \RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $request = $this->getRequest();

        if (!$domain = $request->query()->get('id', false)) {
            throw new \RuntimeException('Domain ID must be specified');
        }
        
        if ($request->isPost()){
            $response = $this->request(
                'delete',
                'domains/' . $domain
            );
            
        }
        
        $response = $this->request(
            'get',
            'domains/' . $domain
        );
        
        if (!$response->isOk()) {
            $this->flashMessenger()->addMessage('Could not load record Data');
            return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
        }
        
        return $this->loadView('domains/delete', array(
            'domain' => json_decode($response->getBody()),
        ),'dns');
    }
    
    
    
    /**
     * Add record to domain
     * @throws \RuntimeException
     */
    public function addRecordAction()
    {
        $request = $this->getRequest();
        $form = new DomainRecordForm();
        $form->setLegend('Add Domain Record');
        
        if (!$domain = $request->query()->get('domain', false)) {
            throw new \RuntimeException('Domain ID must be specified');
        }
        
        
        if ($request->isPost()){
            if ($form->isValid($request->post()->toArray())) {
                $response = $this->request(
                    'post', 
                    'domains/' . $domain . '/records/',
                    array('records' => array(
                        $form->getValues()
                    )),
                    array('action' => 'detail', 'id' => $domain)
                );
                
            }
        } 
        
        
        return $this->loadView('domains/add-record', array(
            'domainId' => $domain,
            'form' => $form,
        ),'dns');
    }
    
    /**
     * edit domain record
     * @throws \RuntimeException
     */
    public function editRecordAction()
    {
        $request = $this->getRequest();

        if (!$domain = $request->query()->get('domain', false)) {
            throw new \RuntimeException('Domain ID must be specified');
        }
        
        if (!$record = $request->query()->get('record', false)) {
            throw new \RuntimeException('Record ID must be specified');
        }
        
        $form = new DomainRecordForm();
        if ($request->isPost()){
            if ($form->isValid($request->post()->toArray())) {
                $response = $this->request(
                    'put', 
                    'domains/' . $domain . '/records/' . $record,
                    $form->getValues()
                );
                
            }
        } else {
            $response = $this->request(
                'get',
                'domains/' . $domain . '/records/' . $record
            );
            if ($response->isOk()) {
                $form->populate((array)json_decode($response->getBody()));
                
            } else {
                $this->flashMessenger()->addMessage('Could not load record Data');
                return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
            }
            
        }
        
        return $this->loadView('domains/edit-record', array(
            'domainId' => $domain,
            'form' => $form,
        ),'dns');
    }
    
    /**
     * delete domain record
     * @throws \RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteRecordAction()
    {
        $request = $this->getRequest();

        if (!$domain = $request->query()->get('domain', false)) {
            throw new \RuntimeException('Domain ID must be specified');
        }
        
        if (!$record = $request->query()->get('record', false)) {
            throw new \RuntimeException('Record ID must be specified');
        }
        
        if ($request->isPost()){
            $response = $this->request(
                'delete',
                'domains/' . $domain . '/records/' . $record
            );
            
        }
        
        $response = $this->request(
            'get',
            'domains/' . $domain . '/records/' . $record
        );
        
        if (!$response->isOk()) {
            $this->flashMessenger()->addMessage('Could not load record Data');
            return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
        }
        
        return $this->loadView('domains/delete-record', array(
            'record' => json_decode($response->getBody()),
        ),'dns');
    }
    
    
}