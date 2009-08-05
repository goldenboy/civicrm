<?php

require_once 'api/v2/Tag.php';

class api_v2_TestTagDelete extends CiviUnitTestCase 
{
    function setUp( )
    {
    }

    function testTagDeleteEmtyTag( )
    {
        $tag = array( );
        $tagDelete =& civicrm_tag_delete( $tag );
        $this->assertEquals( $tagDelete['is_error'], 1 );
        $this->assertEquals( $tagDelete['error_message'],'Could not find tag_id in input parameters' );
    }
    
    function testTagDeleteError( )
    {
        $tag = "noID";
        
        $tagDelete =& civicrm_tag_delete($tag); 
        $this->assertEquals( $tagDelete['is_error'], 1 ); 
        $this->assertEquals( $tagDelete['error_message'],'Could not find tag_id in input parameters' );            
    }
    
    function testTagDelete( )
    {
        $tagID = $this->tagCreate(null); 
        $params = array('tag_id'=> $tagID);
        $tagDelete =& civicrm_tag_delete($params ); 
        $this->assertEquals( $tagDelete['is_error'], 0 );
    }
    
    function tearDown() 
    {
    }
}

