<?php

require_once 'CRM/Base.php';
require_once 'CRM/Controller/Simple.php';

require_once 'CRM/DAO/Domain.php';

require_once 'CRM/Contacts/BAO/Contact_Organization.php';

class CRM_Contacts_Organizations extends CRM_Base {
  
  protected $_controller;

  function __construct() {
    parent::__construct();
  }

  function run( $mode, $id = 0 ) {
    $session = CRM_Session::singleton();
    $config  = CRM_Config::singleton();

    // store the return url. Note that this is typically computed by the framework at runtime
    // based on multiple things (typically where the link was clicked from / http_referer
    // since we are just starting and figuring out navigation, we are hard coding it here
    $returnUrl = $config->httpBase . "crm/contact/add_org?reset=1";
    $session->pushReturnUrl( $returnUrl );

    $this->_controller = new CRM_Controller_Simple( 'CRM_Contacts_Form_ORG', 'Contact ORG Page', $mode );

    $this->_controller->process();
    $this->_controller->run();

    /**
    $contact    = new CRM_Contacts_BAO_Contact_Individual();

    $contact->domain_id = 1;
    $contact->find();
    while ( $contact->fetch() ) {
      // CRM_Utils::debug( 'contactInd', $contact );
    }

    $contact = new CRM_Contacts_BAO_Contact_Individual();
    $contact->contact_type = 'Individual';
    $contact->sort_name    = 'Donald Lobo';
    $contact->hash         = 9876543;
    $contact->domain_id    = 1;
    $contact->first_name   = 'Donald';
    $contact->last_name    = 'Lobo';
    $contact->insert();
    **/
  }

  function display() {
    return $this->_controller->getContent();
  }

}

?>