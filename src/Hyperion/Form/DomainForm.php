<?php
namespace Hyperion\Form;

use Zend\Form\Form,
    Zend\Form\Element,
    Zend\Form\Decorator,
    Zend\Form\DisplayGroup,
    Zend\Validator,
    Zucchi\Form\BootstrapForm
    ;

class DomainForm extends BootstrapForm
{
    public function init()
    {
        $this->setName('record');
        $this->setMethod('post');
        $this->setAttrib('class', 'well form-horizontal');
        $this->setLegend('Domain');
        
        
        $name = new Element\Text('name');
        $name->setDescription('a description')
             ->setLabel('Name')
             ->setRequired(true)
        ;
        $this->addElement($name);
        
        $data = new Element\Text('emailAddress');
        $data->setDescription('email address for the domain')
             ->setLabel('Email Address')
             ->setRequired(true)
             ->addValidator('EmailAddress');
        $this->addElement($data);
        
        $ttl = new Element\Text('ttl');
        $ttl->setDescription('The records Time To Live')
             ->setLabel('TTL')
             ->setRequired(true);
        $this->addElement($ttl);
        
        $comment = new Element\Text('comment');
        $comment->setDescription('A comment about the record')
                ->setLabel('Comment');
        $this->addElement($comment);
        
        $this->addElement(new Element\Submit('submit', array('btn' => 'primary')));
        $this->addElement(new Element\Reset('reset'));
        
    }
}