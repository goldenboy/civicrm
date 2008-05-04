<?php

require_once 'api/v2/Membership.php';

class TestOfMembershipTypeCreateAPIV2 extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_contributionTypeID;
    
    function setUp() 
    {
        $this->_contactID           = $this->organizationCreate( ) ;
        $this->_contributionTypeID  = $this->contributionTypeCreate( );
              
    }
    
    function testMembershipTypeCreateEmpty()
    {
        $params = array();        
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEqual( $membershiptype['is_error'], 1 );
    }
          
    function testMembershipTypeCreateWithoutMemberOfContactId()
    {
        $params = array(
                        'name'                 => '60+ Membership',
                        'description'          => 'people above 60 are given health instructions',                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
        
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEqual( $membershiptype['is_error'], 1 );
  
    }
    
    function testMembershipTypeCreateWithoutContributionTypeId()
    {
      $params = array(
                        'name'                 => '70+ Membership',
                        'description'          => 'people above 70 are given health instructions',                        'member_of_contact_id' => $this->_contactID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEqual( $membershiptype['is_error'], 1 );
    }   
         
    function testMembershipTypeCreateWithoutDurationUnit()
    {
        
        $params = array(
                        'name'                 => '80+ Membership',
                        'description'          => 'people above 80 are given health instructions',                        'member_of_contact_id' => $this->_contactID,
                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',                 
                        'visibility'           => 'public'
                        );
        
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEqual( $membershiptype['is_error'], 0 );
        $this->assertNotNull( $membershiptype['id'] );   
        $this->membershipTypeDelete( $membershiptype['id'] );
        
    }
       
    function testMembershipTypeCreateWithoutName()
    {
        $params = array(
                        'description'          => 'people above 50 are given health instructions',                        'member_of_contact_id' => $this->_contactID,
                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
        
        $membershiptype = & civicrm_membership_type_create($params);  
        $this->assertEqual( $membershiptype['is_error'], 0 );
        $this->assertNotNull( $membershiptype['id'] );   
        $this->membershipTypeDelete( $membershiptype['id'] );
    }
    
    function testMembershipTypeCreate()
    {
        $params = array(
                        'name'                 => '40+ Membership',
                        'description'          => 'people above 40 are given health instructions',                        'member_of_contact_id' => $this->_contactID,
                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
	
        $membershiptype = & civicrm_membership_type_create($params);  
        $this->assertEqual( $membershiptype['is_error'], 0 );
        $this->assertNotNull( $membershiptype['id'] );   
        $this->membershipTypeDelete( $membershiptype['id'] );
    }
    
    function tearDown() 
    {
        $this->contactDelete( $this->_contactID ) ;
        $this->contributionTypeDelete($this->_contributionTypeID);
    }
}
 
?> 