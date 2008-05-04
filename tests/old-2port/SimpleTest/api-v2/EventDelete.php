<?php

require_once 'api/v2/Event.php';

class TestOfEventDeleteAPIV2 extends CiviUnitTestCase
{
    protected $_eventId;
    
    function setUp( )
    {
        $params = array(
                        'title'         => 'Annual CiviCRM meet',
                        'event_type_id' => 1,
                        'start_date'    => 20081021,
                        );
        $event = civicrm_event_create($params);
        
        $this->_eventId = $event['event_id'];
    }
    
    function testDeleteWithoutEventId( )
    {
        $params = array( );
        $result =& civicrm_event_delete($params);
        $this->assertEqual($result['is_error'], 1);
        
        // delete the event created for testing
        $event  = array( 'event_id' => $this->_eventId );
        $result = civicrm_event_delete( $event );
    }
    
    function testDelete( )
    {
        $params = array('event_id' => $this->_eventId);
        $result =& civicrm_event_delete($params);
        $this->assertNotEqual($result['is_error'], 1);
    }
    
    function testDeleteWithWrongEventId( )
    {
        $params = array('event_id' => $this->_eventId);
        $result =& civicrm_event_delete($params);
        // try to delete again - there's no such event anymore
        $params = array('event_id' => $this->_eventId);
        $result =& civicrm_event_delete($params);
        $this->assertEqual($result['is_error'], 1);
    }
    
    function tearDown( )
    {
    }
}
