<?php

require_once 'api/v2/Participant.php';

class TestOfParticipantAPIV2 extends UnitTestCase 
{
    protected $_participant;
            
    function setUp() 
    {
    }
    
    function tearDown() 
    {
    }

    function testCreateEmptyParticipant()
    {
        $params = array();   
        $params['contact_id'] = 35;
        $this->_participant = & civicrm_participant_create($params);

        $this->assertEqual( $this->_participant['is_error'], 1 );
    }
    
    function testCreateErrorParticipantWithoutEventId()
    {
        $params = array(
                        'status_id'     => 2,
                        'role_id'       => 1,
                        'register_date' => date( 'YmdHis' ),
                        'source'        => 'Wimbeldon',
                        'event_level'   => 'Payment'
                        );        
        $participant = & civicrm_participant_create($params);
        $this->assertEqual( $participant['is_error'], 1 );
    }


    function testCreateErrorParticipantWithoutContactId()
    {
        $params = array(
                        'event_id'      => 1,
                        'status_id'     => 2,
                        'role_id'       => 1,
                        'register_date' => date( 'YmdHis' ),
                        'source'        => 'Wimbeldon',
                        'event_level'   => 'Payment'
                        );        
        $participant = & civicrm_participant_create($params);
        $this->assertEqual( $participant['is_error'], 1 );
    }
    
    function testCreateParticipant()
    {
        $params = array(
                        'event_id'      => 1,
                        'status_id'     => 2,
                        'role_id'       => 1,
                        'register_date' => '2005-05-07',
                        'source'        => 'Wimbeldon',
                        'event_level'   => 'Payment',
                        'contact_id'    => 35,
                        );
       
        $this->_participant = & civicrm_participant_create($params);
        $this->assertEqual( $this->_participant['is_error'], 0 );
        $this->assertNotNull( $this->_participant['participant_id'] );
     }     

    function testGetParticipantsByEventId()
    {
        $params = array('event_id' => 1 );
        $participant = & civicrm_participant_get($params);
        //returns error when more than one participant are found
        $this->assertEqual($participant['is_error'], 1);               
    }

    function testDeleteParticipant()
    {
        $delete = & civicrm_participant_delete($this->_participant['participant_id']);
        $this->assertNull($delete);
    }
 }
?>
