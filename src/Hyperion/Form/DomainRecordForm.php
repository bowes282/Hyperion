<?php
namespace Hyperion\Form;

use Zend\Form\Form,
    Zend\Form\Element,
    Zend\Form\Decorator,
    Zend\Form\DisplayGroup,
    Zend\Validator,
    Zucchi\Form\BootstrapForm
    ;

class DomainRecordForm extends BootstrapForm
{
    public function init()
    {
        $this->setName('record');
        $this->setMethod('post');
        $this->setAttrib('class', 'well form-horizontal');
        $this->setLegend('Domain Record');
        
        
        $name = new Element\Text('name');
        $name->setDescription('a description')
             ->setLabel('Name')
             ->setRequired(true)
        ;
        $this->addElement($name);
        
        $type = new Element\Select('type');
        $type->setDescription('Type of record')
             ->setLabel('Type')
             ->addMultiOptions(array(
                "A" => "A (Maps an IPV4 address to a domain)",
                "AAAA" => "AAAA (Maps an IPV6 address to a domain)",
                "CNAME" => "CNAME (Creates an alias for a domain)",
                "MX" => "MX (Designates a domain's mail server)",
                "NS" => "NS (Designates a domain's authoritative name server)",
                "TXT" => "TXT (Arbitrary text for a domain record)",
                "SRV" => "SRV (General service locator record for a domain)",
             ));
        $this->addElement($type);
        
        $data = new Element\Text('data');
        $data->setDescription('The content for the record')
             ->setLabel('Data')
             ->setRequired(true);
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
        
        $priority = new Element\Text('priority');
        $priority->setDescription('Define a priority for this record')
                 ->setLabel('Priority');
        $this->addElement($priority);
        
        $this->addElement(new Element\Submit('submit', array('btn' => 'primary')));
        $this->addElement(new Element\Reset('reset'));
        
    }
}