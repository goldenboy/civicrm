<?php

require_once 'CRM/Contacts/DAO/DomainBase.php';

/**
 * This is a dataobject class for Contact table.
 */
class CRM_Contacts_DAO_Contact extends CRM_Contacts_DAO_DomainBase 
{

  /**
   * what type of contact is this, avoids doing a lookup in multiple tables
   * @var enum
   */
  public $contact_type;
  
  /**
   * cache a composition of the first and last name to speed up sorting
   * @var string
   */
  public $sort_name;
  
  /**
   * where did we originally get the data for this context
   * should this be actually an FK to another more comprehensive table
   * @var string
   */
  public $source;

  /**
   * what mode of communication does the user prefer
   * @var enum
   */
  public $preferred_communication_method;
  
  /**
   * various boolean operators to comply with the
   * local / state / federal laws.
   * @var boolean
   */
  public $do_not_phone;
  public $do_not_email;
  public $do_not_mail;

  /**
   * random 64 bit number created at "insertion" time to give
   * the user some random value that is not monotonically increasing like
   * the id / uuid. Makes things a bit difficult to crack
   * @var int
   */
  public $hash;

  /**
   * This the constructor of the class
   */
  function __construct() 
  {
    parent::__construct();
  }

  /**
   * This function is used to create the array of the feilds from Contact table.
   * @return array array contains the feilds of the table
   */
  function dbFields() 
  {
    static $fields;
    if ($fields === null) {
      $fields = array_merge(
                            parent::dbFields(),
                            array(
                                  'contact_type' => array(self::TYPE_ENUM, self::NOT_NULL),
                                  'sort_name'    => array(self::TYPE_STRING, null),
                                  'source'       => array(self::TYPE_STRING, null),
                                  'preferred_communication_method' => array(self::TYPE_ENUM, null),
                                  'do_not_phone' => array(self::TYPE_BOOLEAN, null),
                                  'do_not_email' => array(self::TYPE_BOOLEAN, null),
                                  'do_not_mail'  => array(self::TYPE_BOOLEAN, null),
                                  'hash'         => array(self::TYPE_STRING, null),
                                  )
                            );
    }
    return $fields;
  }

}

?>
