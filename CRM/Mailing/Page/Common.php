<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
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

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/Page.php';

class CRM_Mailing_Page_Common extends CRM_Core_Page 
{
    protected $_type = null;

    function run( ) {
        require_once 'CRM/Utils/Request.php';
        $job_id   = CRM_Utils_Request::retrieve( 'jid', 'Integer', CRM_Core_DAO::$_nullObject );
        $queue_id = CRM_Utils_Request::retrieve( 'qid', 'Integer', CRM_Core_DAO::$_nullObject );
        $hash     = CRM_Utils_Request::retrieve( 'h'  , 'String' , CRM_Core_DAO::$_nullObject );
        
        if ( ! $job_id   ||
             ! $queue_id ||
             ! $hash ) {
            CRM_Core_Error::fatal( ts( "Missing input parameters" ) );
        }

        require_once 'CRM/Mailing/Event/BAO/Queue.php';

        // verify that the three numbers above match
        $q =& CRM_Mailing_Event_BAO_Queue::verify($job_id, $queue_id, $hash);
        if ( ! $q ) {
            CRM_Core_Error::fatal( ts( "There was an error in your request" ) );
        }

        $cancel  = CRM_Utils_Request::retrieve( "_qf_{$this->_type}_cancel", 'String', CRM_Core_DAO::$_nullObject,
                                                false, null, $_REQUEST );
        if ( $cancel ) {
            $config = CRM_Core_Config::singleton( );
            CRM_Utils_System::redirect( $config->userFrameworkBaseURL );
        }
        
        $confirm = CRM_Utils_Request::retrieve( 'confirm', 'Boolean', CRM_Core_DAO::$_nullObject,
                                                false, null, $_REQUEST );

        list( $displayName, $email ) = CRM_Mailing_Event_BAO_Queue::getContactInfo($queue_id);
        $this->assign( 'display_name', $displayName);
        $this->assign( 'email'       , $email );
        $this->assign( 'confirm'     , $confirm );

        if ( $confirm ) { 
            require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
            if ( $this->_type == 'unsubscribe' ) {
                $groups =& CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_mailing($job_id, $queue_id, $hash);
                if ( count( $groups ) ) {
                    CRM_Mailing_Event_BAO_Unsubscribe::send_unsub_response($queue_id, $groups, false, $job_id);
                } else {
                    // should we indicate an error, or just ignore?
                }
            } elseif ( $this->_type == 'resubscribe' ) {
                require_once 'CRM/Mailing/Event/BAO/Resubscribe.php';
                $groups =& CRM_Mailing_Event_BAO_Resubscribe::resub_to_mailing($job_id, $queue_id, $hash);
                if ( count( $groups ) ) {
                    CRM_Mailing_Event_BAO_Resubscribe::send_resub_response($queue_id, $groups, false, $job_id);
                } else {
                    // should we indicate an error, or just ignore?
                }
            } else {
                if ( CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_domain($job_id, $queue_id, $hash) ) {
                    CRM_Mailing_Event_BAO_Unsubscribe::send_unsub_response($queue_id, null, true, $job_id );
                } else {
                    // should we indicate an error, or just ignore?
                }
            }
        } else {
            $confirmURL = CRM_Utils_System::url( "civicrm/mailing/{$this->_type}",
                                                 "reset=1&jid={$job_id}&qid={$queue_id}&h={$hash}&confirm=1" );
            $this->assign( 'confirmURL', $confirmURL );
        }
        
        parent::run();
    }
}
?>
