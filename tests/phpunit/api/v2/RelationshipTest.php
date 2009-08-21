<?php

require_once 'api/v2/Relationship.php';
require_once 'api/v2/CustomGroup.php';
require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * Class contains api test cases for "civicrm_relationship"
 *
 */
class api_v2_RelationshipTest extends CiviUnitTestCase 
{
    protected $_cId_a;
    protected $_cId_b;
    protected $_relTypeID;
    protected $_ids  = array( );
    protected $_customGroupId = null;
    protected $_customFieldId = null;
    
    function get_info( )
    {
        return array(
                     'name'        => 'Relationship Create',
                     'description' => 'Test all Relationship Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    } 
    
    function setUp() 
    {
        parent::setUp();
        
        $this->_cId_a  = $this->individualCreate( );
        $this->_cId_b  = $this->organizationCreate( );
        
	//  Create a relationship type
        $relTypeParams = array(
                               'name_a_b'       => 'Relation 1 for delete',
                               'name_b_a'       => 'Relation 2 for delete',
                               'description'    => 'Testing relationship type',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'is_reserved'    => 1,
                               'is_active'      => 1
                               );
        $this->_relTypeID = $this->relationshipTypeCreate($relTypeParams );        



    }
    
    function testRelationshipTypeCreate( )
    {
        $relTypeParams = array(
                               'name_a_b'       => 'Relation 1 for relationship create',
                               'name_b_a'       => 'Relation 2 for relationship create',
                               'description'    => 'Testing relationship type',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'is_reserved'    => 1,
                               'is_active'      => 1
                               );
        $relationshiptype =& civicrm_relationship_type_add( $relTypeParams );
        $this->_relTypeID= $relationshiptype['id'];
    }

    /**
     * check with empty array
     */
    function testRelationshipCreateEmpty( )
    {
        $params = array( );
        $result =& civicrm_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'No input parameter present' );
    }
    
    /**
     * check with No array
     */
    function testRelationshipCreateParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input parameter is not an array' );
    }
    
    /**
     * check if required fields are not passed
     */
    function testRelationshipCreateWithoutRequired( )
    {
        $params = array(
                        'start_date' => '2007-08-01',
                        'end_date'   => '2007-08-30',
                        'is_active'  => 1
                        );
        
        $result =& civicrm_relationship_create($params);
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Missing required parameters' );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipCreateWithIncorrectData( )
    {
        $params = array(
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => 'Breaking Relationship'
                        );

        $result =& civicrm_relationship_create( $params ); 
        $this->assertEquals( $result['is_error'], 1 );
    }
   
    /**
     * check relationship creation
     */
    function testRelationshipCreate( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => date('Ymd'),
                         'is_active'            => 1
                         );
        
        $result = & civicrm_relationship_create( $params );
        $this->assertNotNull( $result['result']['id'] );   
        $relationParams = array(
                                'id' => CRM_Utils_Array::value('id', $result['result'])
                           );
        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['result']['id'], $relationParams ); 
        
        $params['id'] = $result['result']['id'] ; 
        $result = & civicrm_relationship_delete( $params );
    }
    
    /**
     * check relationship creation with custom data
     */
    function testRelationshipCreateWithCustomData( )
    {         
        $customGroup = $this->createCustomGroup( );
        $this->_customGroupId = $customGroup['id'];
        $this->_ids  = $this->createCustomField( );     
        //few custom Values for comparing
        $custom_params = array("custom_{$this->_ids[0]}" => 'Hello! this is custom data for relationship',
                               "custom_{$this->_ids[1]}" => 'Y',
                               "custom_{$this->_ids[2]}" => '2009-07-11 00:00:00',
                               "custom_{$this->_ids[3]}" => 'http://example.com',
                               );
        
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => date('Ymd'),
                         'is_active'            => 1
                         );
        $params = array_merge( $params, $custom_params );
        $result = & civicrm_relationship_create( $params );
        
        $this->assertNotNull( $result['result']['id'] );   
        $relationParams = array(
                                'id'     => CRM_Utils_Array::value('id', $result['result'])
                                );
        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['result']['id'], $relationParams ); 
        
        $params['id'] = $result['result']['id'] ; 
        $result = & civicrm_relationship_delete( $params );
        $this->relationshipTypeDelete( $this->_relTypeID ); 
        if ( $this->_customFieldId ) {
            $this->customFieldDelete( $this->_customFieldId );
        }
        
        if ( $this->_ids ) {
            //deleting custom fields
            foreach ( $this->_ids as $id ){
                $this->customFieldDelete( $id );
            }
        }
        $this->customGroupDelete( $this->_customGroupId );
    }

    function createCustomGroup( )
    {
        $params = array(
                        'title'            => 'Test Custom Group',
                        'extends'          => array ( 'Relationship' ),
                        'weight'           => 5,
                        'style'            => 'Inline',
                        'is_active'        => 1,
                        'max_multiple'     => 0
                        );
        $customGroup =& civicrm_custom_group_create($params);
        return null; //$customGroup;
    }

    function createCustomField( )
    {
        $ids = array( );
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'label'           => 'Enter text about relationship',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'default_value'   => 'xyz',
                        'weight'          => 1,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                         );
        
        $customField =& civicrm_custom_field_create( $params );
        $customField = null;
        $ids[] = $customField['result']['customFieldId'];
        
        $optionValue[] = array (
                                'label'     => 'Red',
                                'value'     => 'R',
                                'weight'    => 1,
                                'is_active' => 1
                                );
        $optionValue[] = array (
                                'label'     => 'Yellow',
                                'value'     => 'Y',
                                'weight'    => 2,
                                'is_active' => 1
                                );
        $optionValue[] = array (
                                'label'     => 'Green',
                                'value'     => 'G',
                                'weight'    => 3,
                                'is_active' => 1
                                );
        
        $params = array(
                        'label'           => 'Pick Color',
                        'html_type'       => 'Select',
                        'data_type'       => 'String',
                        'weight'          => 2,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                        'option_values'   => $optionValue,
                        'custom_group_id' => $this->_customGroupId,
                        );
        
        $customField  =& civicrm_custom_field_create( $params );
        
        $ids[] = $customField['result']['customFieldId'];
        
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'name'            => 'test_date',
                        'label'           => 'test_date',
                        'html_type'       => 'Select Date',
                        'data_type'       => 'Date',
                        'default_value'   => '20090711',
                        'weight'          => 3,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField  =& civicrm_custom_field_create( $params );			

        $ids[] = $customField['result']['customFieldId'];
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'name'            => 'test_link',
                        'label'           => 'test_link',
                        'html_type'       => 'Link',
                        'data_type'       => 'Link',
                        'default_value'   => 'http://civicrm.org',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField  =& civicrm_custom_field_create( $params );
        $ids[] = $customField['result']['customFieldId'];
        return $ids;
    }

    /**
     * check with empty array
     */
    function testRelationshipDeleteEmpty( )
    {
        $params = array( );
        $result =& civicrm_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'No input parameter present' );
    }
    
    /**
     * check with No array
     */
    
    function testRelationshipDeleteParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input parameter is not an array' );
    }
    
    /**
     * check if required fields are not passed
     */
    function testRelationshipDeleteWithoutRequired( )
    {
        $params = array(
                        'start_date' => '2007-08-01',
                        'end_date'   => '2007-08-30',
                        'is_active'  => 1
                        );
        
        $result =& civicrm_relationship_delete( $params ); 
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Missing required parameter' );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipDeleteWithIncorrectData( )
    {
        $params = array(
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => 'Breaking Relationship'
                        );
        
        $result =& civicrm_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Missing required parameter' );
    }
   
    /**
     * check relationship creation
     */
    function testRelationshipDelete( )
    {
        $params['id']=$this->_relationID;
        
        $result = & civicrm_relationship_delete( $params );
        $this->relationshipTypeDelete( $this->_relTypeID ); 
        throw new PHPUnit_Framework_IncompleteTestError(
                      "test not implemented" );
    }
    
    /**
     * create relationship with custom data 
     * ( will do this, once custom * v2 api are ready 
         with all changed schema for custom data  )
    */
    function testRelationshipDeleteWithCustomData( )
    {        
        throw new PHPUnit_Framework_IncompleteTestError(
                      "test not implemented" );
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
     
    function tearDown() 
    {
        $this->contactDelete( $this->_cId_a );
        $this->contactDelete( $this->_cId_b );
    }
}
 
?> 