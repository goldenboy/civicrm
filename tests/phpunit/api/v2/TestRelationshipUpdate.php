<?php

require_once 'api/v2/Relationship.php';

/**
 * Class contains api test cases for "civicrm_relationship"
 *
 */

class api_v2_TestRelationshipUpdate extends CiviUnitTestCase 
{
    
    protected $_cId_a;
    protected $_cId_b;
    protected $_relTypeID;
    protected $_relationID;

    function get_info( )
    {
        return array(
                     'name'        => 'Relationship Update',
                     'description' => 'Test all Relationship Update API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }

    function setUp( ) 
    {
        $this->_cId_a  = $this->individualCreate( );
        
        $this->_cId_b  = $this->organizationCreate( );

    }
    function testRelationshipTypeCreate( )
    {
      
        $relTypeParams = array(
                               'name_a_b'       => 'Relation 1 for relationship update',
                               'name_b_a'       => 'Relation 2 for relationship update',
                               'description'    => 'Testing relationship type',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'is_reserved'    => 1,
                               'is_active'      => 1
                               );
        
        $this->_relTypeID = $this->relationshipTypeCreate( $relTypeParams );
        
    }
    
    /**
     * check with empty array
     */
    function testRelationshipUpdateEmpty( )
    {
        $params = array( );
        $result =& civicrm_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'No input parameter present' );
    }
    
    /**
     * check with No array
     */
    function testRelationshipUpdateParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input parameter is not an array' );
    }

    /**
     * check if required fields are not passed
     */
    function testRelationshipUpdateWithoutRequired( )
    {
        $params = array(
                        'start_date' => '2007-08-01',
                        'end_date'   => '2007-08-30',
                        'is_active'  => 1
                        );
        
        $result =& civicrm_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Missing required parameters' );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipUpdateWithIncorrectData( )
    {
        $params = array(
                        'contact_id_a'          => $this->_cId_a,
                        'contact_id_b'          => $this->_cId_b,
                        'relationship__type_id' => 'Breaking Relationship'
                        );
        
        $result =& civicrm_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Invalid value for relationship type ID' );
    }
    
   
    /**
     * check relationship creation
     */
    function testRelationshipUpdate( )
    {
        $relParams     = array(
                               'contact_id_a'         => $this->_cId_a,
                               'contact_id_b'         => $this->_cId_b,
                               'relationship_type_id' => $this->_relTypeID,
                               'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2005'),
                               'end_date'             => array('d'=>'10','M'=>'1','Y'=>'2006'),
                               'is_active'            => 1
                               );

        $result = & civicrm_relationship_create( $relParams );
        $this->_relationID =$result['result']['id'];
        $this->assertNotNull( $result['result']['id'] );  


        $params = array(
                        'id'                   => $this->_relationID,
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => $this->_relTypeID,
                        'start_date'           => array('d'=>'11','M'=>'1','Y'=>'2005'),
                        'end_date'             => array('d'=>'11','M'=>'1','Y'=>'2006'),
                        'is_active'            => 0
                        );
        $result = & civicrm_relationship_create( $params ); 
        $this->assertNotNull( $result['result']['id'] );   
        
        // assertDBState compares expected values in $result to actual values in the DB          
        // fix params
        $v = explode( '_', $params['relationship_type_id'] );
        $params['relationship_type_id']  = $v[0];
        $params['start_date'] = '2005-01-11';
        $params['end_date'  ] = '2006-01-11';
        unset( $params['contact_check'] );
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['result']['id'], $params ); 
        
        //delete created relationship
        $params['id']=$this->_relationID;
        
        $result = & civicrm_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 0 );
        
        //delete created relationship type
        
        $this->relationshipTypeDelete( $this->_relTypeID ); 
    }
    
    /**
     * update relationship with custom data 
     * ( will do this, once custom * v2 api are ready 
         with all changed schema for custom data  )
    */
    function testRelationshipUpdateWithCustomData( )
    { 
        
       
    }
    
    function tearDown( ) 
    {
        $this->contactDelete( $this->_cId_a );
        $this->contactDelete( $this->_cId_b );
    }
}
?> 