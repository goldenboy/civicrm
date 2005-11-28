<?php

require_once 'api/crm.php';

class TestOfGetGroups extends UnitTestCase 
{

    
    function setUp() 
    {
    }

    function tearDown() 
    {
    }

    function testGetAllGroups()
    {
        $groups = crm_get_groups();
        $this->assertNotA($groups,'CRM_Core_Error');
        foreach($groups as  $group) {
            $this->assertIsA($group,'CRM_Contact_DAO_Group');

        }
    }

    function testGetFilterdGroup()
    {
        $params = array('name'=>'Summer Program Volunteers');
        $return_prop = array('name','title');
        $groups = crm_get_groups($params,$return_prop);
        $this->assertNotA($group,'CRM_Core_Error');
        foreach($groups as  $group) {
            $this->assertIsA($group,'CRM_Contact_DAO_Group');

        }
        
        
    }
    
    function testGetFilterdGroupGroupCount()
    {
       
        $return_prop = array('name','title','member_count');
        $groups = crm_get_groups($params);
        $this->assertNotA($group,'CRM_Core_Error');
        foreach($groups as  $group) {
            $this->assertIsA($group,'CRM_Contact_DAO_Group');

        }
        
        
    }

    function testCreateGroup( ) {
        $params = array( 'title' => 'New api Group 2' );
        $group = crm_create_group( $params );
        CRM_Core_Error::debug( 'g', $group );
    }


}
?>