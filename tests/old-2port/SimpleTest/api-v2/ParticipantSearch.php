<?php

require_once 'api/v2/Participant.php';

class TestOfParticipantSearchAPIV2 extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_contactID2;
    protected $_participantID;
    protected $_participantID2;
    protected $_participantID3;
    protected $_evnetID;
    
    
    function setUp() 
    {
        $event = $this->eventCreate();
        $this->_eventID = $event['event_id'];

        // Creates one contact with two participant records for above event
        $this->_contactID = $this->individualCreate( ) ;
        $this->_participantID = $this->participantCreate( array ('contactID' => $this->_contactID, 'eventID' => $this->_eventID ) );
        $this->_participantID2 = $this->participantCreate( array ('contactID' => $this->_contactID, 'eventID' => $this->_eventID ) );
        $this->_participantID3 = $this->participantCreate( array ('contactID' => $this->_contactID, 'eventID' => $this->_eventID ) );
    }
    
    function tearDown()
    {
        // Cleanup created participant records.
        $result = $this->participantDelete( $this->_participantID );
        $result = $this->participantDelete( $this->_participantID2 );
        $result = $this->participantDelete( $this->_participantID3 );
        
        // Cleanup test contacts.
        $result = $this->contactDelete( $this->_contactID ); 

        // Cleanup test event.
        $result = $this->eventDelete($this->_eventID);
    }
    
    
    function testParticipantSearchParticipantIdOnly()
    {
        $params = array(
                        'participant_id'      => $this->_participantID,
                        );
        $participant = & civicrm_participant_search($params);
        $this->assertEqual($participant[$this->_participantID]['event_id'], $this->_eventID);
        $this->assertEqual($participant[$this->_participantID]['participant_status_id'],2);
        $this->assertEqual($participant[$this->_participantID]['participant_role_id'],1);
        $this->assertEqual($participant[$this->_participantID]['participant_register_date'], '2007-02-19 00:00:00');
        $this->assertEqual($participant[$this->_participantID]['participant_source'],'Wimbeldon');
        $this->assertEqual($participant[$this->_participantID]['event_level'],'Payment');
    }
    
    function testParticipantSearchContactIdOnly()
    {
        // Should get 2 participant records for this contact.
        $params = array(
                        'contact_id'      => $this->_contactID,
                        );
        $participant = & civicrm_participant_search($params);
        $this->assertEqual( count( $participant ), 3 );
    }
    
    
    function testParticipantSearchByEvent()
    {
        // Should get >= 3 participant records for this event. Also testing that last_name and event_title are returned.
        $params = array(
                        'event_id'      => $this->_eventID,
                        'return.last_name' => 1,
                        'return.event_title' => 1,
                        );
        $participant = & civicrm_participant_search($params);
        if ( count( $participant ) < 3 ) {
            $this->fail("Event search returned less than expected miniumum of 3 records.");
        }
        
        $this->assertEqual($participant[$this->_participantID]['last_name'],'Anderson');
        $this->assertEqual($participant[$this->_participantID]['event_title'],'Annual CiviCRM meet');        
    }
    

    function testParticipantSearchByEventWithLimit()
    {
        // Should 2 participant records since we're passing rowCount = 2.
        $params = array(
                        'event_id'      => $this->_eventID,
                        'rowCount'      => 6,
                        );
        $participant = & civicrm_participant_search($params);
        
        $this->assertEqual( count( $participant ), 6 );
    }
    
}

