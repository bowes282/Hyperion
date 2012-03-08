<?php

namespace Hyperion\Dns\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\View\Model\ViewModel,
    Hyperion\Rest\Client\RestClient,
    Hyperion\Controller\ControllerAbstract,
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
        ));
    }
    
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
        ));
    }
    
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
                    'domains/' . $domain . '/records',
                    array('records' => array(
                        $form->getValues()
                    ))
                );
                
                if ($response->isOk()) {
                    $this->flashMessenger()->addMessage('New Record Added Sucessfully.');
                    return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
                } else if ($response->getStatusCode() == 202) {
                    $this->flashMessenger()->addMessage('New Record Sent to Rackspace for processing');
                    return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
                } else {
                    $data = json_decode($response->getBody());
                    $this->messages[] = 'An Error Occured';
                    \Zend\Debug::dump($data);
                }
            }
        }
        
        
        return $this->loadView('domains/add-record', array(
            'domainId' => $domain,
            'form' => $form,
        ));
    }
    
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
                
                if ($response->isOk()) {
                    $this->flashMessenger()->addMessage('New Record Added Sucessfully.');
                    return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
                } else if ($response->getStatusCode() == 202) {
                    $this->flashMessenger()->addMessage('New Record Sent to Rackspace for processing');
                    return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
                } else {
                    $data = json_decode($response->getBody());
                    $this->messages[] = 'An Error Occured';
                    \Zend\Debug::dump($data);
                }
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
        ));
    }
    
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
            
            if (in_array($response->getStatusCode(), array(200,202,204))) {
                $this->flashMessenger()->addMessage('Delete request sent to rackspace for processing');
                return $this->redirect()->toRoute('hyperion/query', array('controller'=>'domains','action'=>'detail','id'=>$domain));
            }
            
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
        ));
    }
    
    
}