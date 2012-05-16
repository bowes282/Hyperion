*Hyperion* - Rackspace Cloud Management Tool

Hyperion is a Cloud Management tool for the Rackspace suite of services to 
allow finer grained control than is available through the default control panel 
provided by Rackspace

Module makes heavy use of Twitter Bootstrap CSS. 

Module Has Dependancy on \Zucchi\Form\BootstrapForm class from Zucchi Framework which auto formats forms to fit Bootstrap CSS framework. To remove dependancy change All forms in module to Extend Base \Zend\Form Class.

To install you need only define your Rackspace username,API key and Customer ID in a local.config.php file

<pre>
return array(
    'di' => array(
        'instance' => array(
            'Hyperion\Controller\ControllerAbstract' => array(
                'parameters' => array(
                    'ApiConfig' => array(
                        'User' => 'enter-your-username-here',
                        'Key' => 'enter-your-API-key-here',
                        'CustomerId' => 'customer-id-here',
                    ),
                ),
            ),
        ),
    ),
);
</pre>

