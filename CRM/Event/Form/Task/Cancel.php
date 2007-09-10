<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.9                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Event/Form/Task.php';

 /**
  * This class provides the functionality for cancel registration for event participations
  */
class CRM_Event_Form_Task_Cancel extends CRM_Event_Form_Task 
{ 
    /**
     * variable to store redirect path
     *
     */
    protected $_userContext;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        /*
         * initialize the task and row fields
         */
        parent::preProcess( );

        $session =& CRM_Core_Session::singleton();
        $this->_userContext = $session->readUserContext( );
    }
  
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        CRM_Utils_System::setTitle( ts('Cancel Registration for Event Participation') );        
        $session =& CRM_Core_Session::singleton( );
        $this->addDefaultButtons( ts('Continue'), 'done' );
    }
    

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $this->exportValues( );
        $value  = array( );

        foreach( $this->_participantIds as $participantId ) {
            $ids['participant']    = $participantId;      
            
            // Cancelled status id = 4
            $value['status_id']    = 4;
            require_once 'CRM/Event/BAO/Participant.php';            
            CRM_Event_BAO_Participant::add( $value ,$ids );   
        }
    }//end of function
}
?>
