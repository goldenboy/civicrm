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

class CRM_Core_Payment_BaseIPN {

    static $_now = null;

    function __construct( ) {
        self::$_now = date( 'YmdHis' );
    }

    function validateData( &$input, &$ids, &$objects ) {

        // make sure contact exists and is valid
        require_once 'CRM/Contact/DAO/Contact.php';
        $contact =& new CRM_Contact_DAO_Contact( );
        $contact->id = $ids['contact'];
        if ( ! $contact->find( true ) ) {
            CRM_Core_Error::debug_log_message( "Could not find contact record: $contactID" );
            echo "Failure: Could not find contact record: $contactID<p>";
            return false;
        }

        // make sure contribution exists and is valid
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $contribution =& new CRM_Contribute_DAO_Contribution( );
        $contribution->id = $ids['contribution'];
        if ( ! $contribution->find( true ) ) {
            CRM_Core_Error::debug_log_message( "Could not find contribution record: $contributionID" );
            echo "Failure: Could not find contribution record for $contributionID<p>";
            return false;
        }
        $contribution->receive_date = CRM_Utils_Date::isoToMysql($contribution->receive_date); 

        $objects['contact']          =& $contact;
        $objects['contribution']     =& $contribution;

        if ( ! $this->loadObjects( $input, $ids, $objects ) ) {
            return false;
        }

        return true;
    }

    function createContact( &$input, &$ids, &$objects ) {
        $params    = array( );
        $billingID = $ids['billing'];
        $lookup    = array( "first_name"                  ,
                            "last_name"                   ,
                            "street_address-{$billingID}" ,
                            "city-{$billingID}"           ,
                            "state-{$billingID}"          ,
                            "postal_code-{$billingID}"    ,
                            "country-{$billingID}"        , );
        foreach ( $lookup as $name ) {
            $params[$name] = $input[$name];
        }

        if ( ! empty( $params ) ) {
            // update contact record
            require_once "CRM/Contact/BAO/Contact.php";
            $contact =& CRM_Contact_BAO_Contact::createProfileContact( $params, CRM_Core_DAO::$_nullArray, $ids['contact'] );
        }
        
        return true;
    }

    function loadObjects( &$input, &$ids, &$objects ) {
        $contribution =& $objects['contribution'];

        $objects['membership']        = null;
        $objects['contributionRecur'] = null;
        $objects['contributionType']  = null;
        $objects['event']             = null;
        $objects['participant']       = null;

        require_once 'CRM/Contribute/DAO/ContributionType.php';
        $contributionType =& new CRM_Contribute_DAO_ContributionType( );
        $contributionType->id = $contribution->contribution_type_id;
        if ( ! $contributionType->find( true ) ) {
            CRM_Core_Error::debug_log_message( "Could not find contribution type record: $contributionTypeID" );
            echo "Failure: Could not find contribution type record for $contributionTypeID<p>";
            return false;
        }
        $objects['contributionType'] = $contributionType;

        if ( $input['component'] == 'contribute' ) {
            // get the contribution page id from the contribution
            // and then initialize the payment processor from it
            if ( ! $contribution->contribution_page_id ) {
                CRM_Core_Error::debug_log_message( "Could not find contribution page for contribution record: $contributionID" );
                echo "Failure: Could not find contribution page for contribution record: $contributionID<p>";
                return false;
            }

            // get the payment processor id from contribution page
            $paymentProcessorID = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage',
                                                               $contribution->contribution_page_id,
                                                               'payment_processor_id' );

            // now retrieve the optional objects
            if ( isset( $ids['membership'] ) ) {
                require_once 'CRM/Member/DAO/Membership.php';
                $membership = new CRM_Member_DAO_Membership( );
                $membership->id = $ids['membership'];
                if ( ! $membership->find( true ) ) {
                    CRM_Core_Error::debug_log_message( "Could not find membership record: $membershipID" );
                    echo "Failure: Could not find membership record: $membershipID<p>";
                    return false;
                }
                $membership->join_date     = CRM_Utils_Date::isoToMysql( $membership->join_date      );
                $membership->start_date    = CRM_Utils_Date::isoToMysql( $membership->start_date     );
                $membership->end_date      = CRM_Utils_Date::isoToMysql( $membership->end_date       );
                $membership->reminder_date = CRM_Utils_Date::isoToMysql( $membership->reminder_date  );

                $objects['membership'] =& $membership;
            }
            
            if ( isset( $ids['contributionRecur'] ) ) {
                require_once 'CRM/Contribute/DAO/ContributionRecur.php';
                $recur =& new CRM_Contribute_DAO_ContributionRecur( );
                $recur->id = $ids['contributionRecur'];
                if ( ! $recur->find( true ) ) {
                    CRM_Core_Error::debug_log_message( "Could not find recur record: $contributionRecurID" );
                    echo "Failure: Could not find recur record: $contributionRecurID<p>";
                    return false;
                }
                $objects['contributionRecur'] =& $recur;
            }
        } else {
            // we are in event mode
            // make sure event exists and is valid
            require_once 'CRM/Event/DAO/Event.php';
            $event =& new CRM_Event_DAO_Event( );
            $event->id = $ids['event'];
            if ( $ids['event'] &&
                 ! $event->find( true ) ) {
                CRM_Core_Error::debug_log_message( "Could not find event: $eventID" );
                echo "Failure: Could not find event: $eventID<p>";
                return false;
            }

            $objects['event'] =& $event;

            require_once 'CRM/Event/DAO/Participant.php';
            $participant =& new CRM_Event_DAO_Participant( );
            $participant->id = $ids['participant'];
            if ( $ids['participant'] &&
                 ! $participant->find( true ) ) {
                CRM_Core_Error::debug_log_message( "Could not find participant: $participantID" );
                echo "Failure: Could not find participant: $participantID<p>";
                return false;
            }
            $participant->register_date = CRM_Utils_Date::isoToMysql( $participant->register_date );

            $objects['participant'] =& $participant;

            $paymentProcessorID = $objects['event']->payment_processor_id;
        }

        if ( ! $paymentProcessorID ) {
            CRM_Core_Error::debug_log_message( "Could not find payment processor for contribution record: $contributionID" );
            echo "Failure: Could not find payment processor for contribution record: $contributionID<p>";
            return false;
        }

        require_once 'CRM/Core/BAO/PaymentProcessor.php';
        $paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $paymentProcessorID,
                                                                       $contribution->is_test ? 'test' : 'live' );

        $ids['paymentProcessor']       =  $paymentProcessorID;
        $objects['paymentProcessor']   =& $paymentProcessor;

        return true;
    }

    function failed( &$objects, &$transaction ) {
        $contribution =& $objects['contribution'];
        $membership   =& $objects['membership']  ;
        $participant  =& $objects['participant'] ;

        $contribution->contribution_status_id = 4;
        $contribution->save( );

        if ( $membership ) {
            $membership->status_id = 4;
            $membership->save( );
        }

        if ( $participant ) {
            $participant->status_id = 4;
            $participant->save( );
        }
            
        $transaction->commit( );
        CRM_Core_Error::debug_log_message( "Setting contribution status to failed" );
        //echo "Success: Setting contribution status to failed<p>";
        return true;
    }

    function pending( &$objects, &$transaction ) {
        $transaction->commit( );
        CRM_Core_Error::debug_log_message( "returning since contribution status is pending" );
        echo "Success: Returning since contribution status is pending<p>";
        return true;
    }

    function cancelled( &$objects, &$transaction ) {
        $contribution =& $objects['contribution'];
        $membership   =& $objects['membership']  ;
        $participant  =& $objects['participant'] ;

        $contribution->contribution_status_id = 3;
        $contribution->cancel_date = self::$_now;
        $contribution->cancel_reason = CRM_Utils_Array::value( 'reasonCode', $input );
        $contribution->save( );

        if ( $membership ) {
            $membership->status_id = 6;
            $membership->save( );
        }

        if ( $participant ) {
            $participant->status_id = 4;
            $participant->save( );
        }

        $transaction->commit( );
        CRM_Core_Error::debug_log_message( "Setting contribution status to cancelled" );
        //echo "Success: Setting contribution status to cancelled<p>";
        return true;
    }

    function unhandled( &$objects, &$transaction ) {
        $transaction->rollback( );
        // we dont handle this as yet
        CRM_Core_Error::debug_log_message( "returning since contribution status: $status is not handled" );
        echo "Failure: contribution status $status is not handled<p>";
        return false;
    }

    function completeTransaction( &$input, &$ids, &$objects, &$transaction, $recur = false ) {
        $contribution =& $objects['contribution'];
        $membership   =& $objects['membership']  ;
        $participant  =& $objects['participant'] ;
        $event        =& $objects['event']       ;
        
        $values = array( );
        if ( $input['component'] == 'contribute' ) {
            require_once 'CRM/Contribute/BAO/ContributionPage.php';
            CRM_Contribute_BAO_ContributionPage::setValues( $contribution->contribution_page_id, $values );
            $contribution->source                  = ts( 'Online Contribution:' ) . ' ' . $values['title'];
            
            if ( $values['is_email_receipt'] ) {
                $contribution->receipt_date = self::$_now;
            }

            if ( $membership ) {
                $membership->status_id = 2;
                $membership->save( );
            }
        } else {
            // event
            $eventParams     = array( 'id' => $objects['event']->id );
            $values['event'] = array( );

            require_once 'CRM/Event/BAO/Event.php';
            CRM_Event_BAO_Event::retrieve( $eventParams, $values['event'] );
        
            $eventParams = array( 'event_id' => $objects['event']->id );
            $values['event_page'] = array( );

            require_once 'CRM/Event/BAO/EventPage.php';
            CRM_Event_BAO_EventPage::retrieve( $eventParams, $values['event_page'] );

            //get location details
            /** FIXME location schema change
            $locationParams = array( 'entity_id' => $eventID ,'entity_table' => 'civicrm_event' );
            require_once 'CRM/Core/BAO/Location.php';
            require_once 'CRM/Event/Form/ManageEvent/Location.php';
            CRM_Core_BAO_Location::getValues($locationParams, $values, 
                                             CRM_Core_DAO::$_nullArray, 
                                             CRM_Event_Form_ManageEvent_Location::LOCATION_BLOCKS );
            **/

            require_once 'CRM/Core/BAO/UFJoin.php';
            $ufJoinParams = array( 'entity_table' => 'civicrm_event',
                                   'entity_id'    => $ids['event'],
                                   'weight'       => 1 );
        
            $values['custom_pre_id'] = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );
        
            $ufJoinParams['weight'] = 2;
            $values['custom_post_id'] = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );

            $contribution->source                  = ts( 'Online Event Registration:' ) . ' ' . $values['event']['title'];

            if ( $values['event_page']['is_email_confirm'] ) {
                $contribution->receipt_date = self::$_now;
            }

            $participant->status_id = 1;
            $participant->save( );
        }

        $contribution->contribution_status_id  = 1;
        $contribution->is_test    = $input['is_test'];
        $contribution->fee_amount = $input['fee_amount'];
        $contribution->net_amount = $input['net_amount'];
        $contribution->trxn_id    = $input['trxn_id'];
        $contribution->save( );
        
        // next create the transaction record
        $trxnParams = array(
                            'contribution_id'   => $contribution->id,
                            'trxn_date'         => isset( $input['trxn_date'] ) ? $input['trxn_date'] : self::$_now,
                            'trxn_type'         => 'Debit',
                            'total_amount'      => $input['amount'],
                            'fee_amount'        => $contribution->fee_amount,
                            'net_amount'        => $contribution->net_amount,
                            'currency'          => $contribution->currency,
                            'payment_processor' => $objects['paymentProcessor']['payment_processor_type'],
                            'trxn_id'           => $contribution->trxn_id,
                            );
        
        require_once 'CRM/Contribute/BAO/FinancialTrxn.php';
        $trxn =& CRM_Contribute_BAO_FinancialTrxn::create( $trxnParams );

        // create an activity record
        if ( $input['component'] == 'contribute' ) {
            require_once "CRM/Contribute/BAO/Contribution.php";
            CRM_Contribute_BAO_Contribution::addActivity( $contribution );
        } else { // event 
            require_once "CRM/Event/BAO/Participant.php";
            CRM_Event_BAO_Participant::addActivity( $participant );
        }

        CRM_Core_Error::debug_log_message( "Contribution record updated successfully" );
        $transaction->commit( );

        // add the new contribution values
        $template =& CRM_Core_Smarty::singleton( );
        if ( $input['component'] == 'contribute' ) {
            $template->assign( 'title', $values['title']);
            $template->assign( 'amount' , $input['amount'] );
        } else {
            $template->assign( 'title', $values['event']['title']);
            $template->assign( 'amount' , $input['amount'] );
        }

        $template->assign( 'trxn_id', $contribution->trxn_id );
        $template->assign( 'receive_date', 
                           CRM_Utils_Date::mysqlToIso( $contribution->receive_date ) );
        $template->assign( 'contributeMode', 'notify' );
        $template->assign( 'action', $contribution->is_test ? 1024 : 1 );
        $template->assign( 'receipt_text', $values['receipt_text'] );
        $template->assign( 'is_monetary', 1 );
        $template->assign( 'is_recur', $recur );
        if ( $recur ) {
            require_once 'CRM/Contribute/Form/ContributionBase.php';
            $url = CRM_Contribute_Form_ContributionBase::cancelSubscriptionURL( $objects['paymentProcessor'] );
            $template->assign( 'cancelSubscriptionUrl', $url );
        }
        
        require_once 'CRM/Utils/Address.php';
        $template->assign( 'address', CRM_Utils_Address::format( $input ) );

        if ( $input['component'] == 'event' ) { 
            $template->assign( 'event', $values['event'] );
            $template->assign( 'eventPage', $values['event_page'] );
            $template->assign( 'location', $values['location'] );
            $template->assign( 'customPre', $values['custom_pre_id'] );
            $template->assign( 'customPost', $values['custom_post_id'] );

            require_once "CRM/Event/BAO/EventPage.php";
            CRM_Event_BAO_EventPage::sendMail( $ids['contact'], $values, $participant->id );
        } else {
            CRM_Contribute_BAO_ContributionPage::sendMail( $ids['contact'], $values, $contribution->id );
        }

        CRM_Core_Error::debug_log_message( "Success: Database updated and mail sent" );

        /* echo commented since creates problem when this method is
           called from some other file, inside civicrm (not from files
           in extern dir).                                            */
        //echo "Success: Database updated<p>";
    }
    
    function getBillingID( &$ids ) {
        // get the billing location type
        require_once "CRM/Core/PseudoConstant.php";
        $locationTypes  =& CRM_Core_PseudoConstant::locationType( );
        $ids['billing'] =  array_search( 'Billing',  $locationTypes );
        if ( ! $ids['billing'] ) {
            CRM_Core_Error::debug_log_message( ts( 'Please set a location type of %1', array( 1 => 'Billing' ) ) );
            echo "Failure: Could not find billing location type<p>";
            return false;
        }
        return true;
    }
}

?>
