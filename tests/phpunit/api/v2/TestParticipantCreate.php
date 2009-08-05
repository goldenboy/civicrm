<?php

require_once 'api/v2/Participant.php';

class api_v2_TestParticipantCreate extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_createdParticipants;
    protected $_participantID;
    protected $_eventID;

    function get_info( )
    {
        return array(
                     'name'        => 'Participant Create',
                     'description' => 'Test all Participant Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    } 
    
    function setUp() 
    {
        $event = $this->eventCreate();
        $this->_eventID = $event['event_id'];
        
        $this->_contactID = $this->individualCreate( ) ;
        $this->_createdParticipants = array( );
    }
    
    function tearDown()
    {
        // Cleanup all created participant records.
        foreach ( $this->_createdParticipants as $id ) {
            $result = $this->participantDelete( $this->_participantID );
        }
        // Cleanup test contact
        $result = $this->contactDelete( $this->_contactID ); 

	// Cleanup test event
	if ( $this->_eventID ) {
	    $this->eventDelete( $this->_eventID );
	}
    }
    

    function testParticipantCreateMissingContactID()
    {
        $params = array(
                        'event_id'      => $this->_eventID,
                        );
        $participant = & civicrm_participant_create($params);
        if ( CRM_Utils_Array::value('id', $participant) ) {
            $this->_createdParticipants[] = $participant['id'];
        }
        $this->assertEquals( $participant['is_error'],1 );
        $this->assertNotNull($participant['error_message']);
    }

    function testParticipantCreateMissingEventID()
    {
        $params = array(
                        'contact_id'    => $this->_contactID,
                        );
        $participant = & civicrm_participant_create($params); 
        if ( CRM_Utils_Array::value('id', $participant) ) {
            $this->_createdParticipants[] = $participant['id'];
        }
        $this->assertEquals( $participant['is_error'],1 );
        $this->assertNotNull($participant['error_message']);
    }
    
    function testParticipantCreateEventIdOnly()
    {
        $params = array(
                        'contact_id'    => $this->_contactID,
                        'event_id'      => $this->_eventID,
                        );
        $participant = & civicrm_participant_create($params);
        $this->assertNotEquals( $participant['is_error'],1 );
        $this->_participantID = $participant['result'];
        
        if ( ! $participant['is_error'] ) {
            $this->_createdParticipants[] = CRM_Utils_Array::value('result', $participant);
            // Create $match array with DAO Field Names and expected values
            $match = array(
                           'id'                         => CRM_Utils_Array::value('result', $participant)
                           );
            // assertDBState compares expected values in $match to actual values in the DB              
            $this->assertDBState( 'CRM_Event_DAO_Participant', $participant['result'], $match ); 
        }
    }
    
    function testParticipantCreateAllParams()
    {  
        $params = array(
                        'contact_id'    => $this->_contactID,
                        'event_id'      => $this->_eventID,
                        'status_id'     => 1,
                        'role_id'       => 1,
                        'register_date' => '2007-07-21',
                        'source'        => 'Online Event Registration: API Testing',
                        'event_level'   => 'Tenor'                        
                        );
        
        $participant = & civicrm_participant_create($params);
        $this->assertNotEquals( $participant['is_error'],1 );
        $this->_participantID = $participant['result'];
        if ( ! $participant['is_error'] ) {
            $this->_createdParticipants[] = CRM_Utils_Array::value('result', $participant);
            
            // Create $match array with DAO Field Names and expected values
            $match = array(
                           'id'         => CRM_Utils_Array::value('result', $participant)
                           );
            // assertDBState compares expected values in $match to actual values in the DB              
            $this->assertDBState( 'CRM_Event_DAO_Participant', $participant['result'], $match ); 
        }
    }
    
}
