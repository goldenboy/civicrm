<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.1                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Social Source Foundation                        |
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
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Social Source Foundation (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Contribute/Form/Contribution.php';

/**
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Contribute_Form_Contribution_Confirm extends CRM_Contribute_Form_Contribution {

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {

        parent::preProcess( );

        if ( $this->_contributeMode == 'express' ) {
            $nullObject = null;
            // rfp == redirect from paypal
            $rfp = CRM_Utils_Request::retrieve( 'rfp', $nullObject, false, null, 'GET' );
            if ( $rfp ) {
                require_once 'CRM/Utils/Payment/PayPal.php'; 
                $paypal =& CRM_Utils_Payment_PayPal::singleton( );
                $this->_params = $paypal->getExpressCheckoutDetails( $this->get( 'token' ) );

                // set a few other parameters for PayPal
                $this->_params['token']          = $this->get( 'token' );

                $this->_params['amount'        ] = $this->get( 'amount' );
                $this->_params['currencyID'    ] = 'USD';
                $this->_params['payment_action'] = 'Sale';

                $this->set( 'getExpressCheckoutDetails', $this->_params );
            } else {
                $this->_params = $this->get( 'getExpressCheckoutDetails' );
            }
        } else {
            $this->_params = $this->controller->exportValues( 'Main' );

            $this->_params['state_province'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation( $this->_params['state_province_id'] ); 
            $this->_params['country']        = CRM_Core_PseudoConstant::countryIsoCode( $this->_params['country_id'] ); 
            $this->_params['year'   ]        = $this->_params['credit_card_exp_date']['Y'];  
            $this->_params['month'  ]        = $this->_params['credit_card_exp_date']['M'];  
            $this->_params['ip_address']     = $_SERVER['REMOTE_ADDR']; 

            $this->_params['amount'        ] = $this->get( 'amount' );
            $this->_params['currencyID'    ] = 'USD';
            $this->_params['payment_action'] = 'Sale';
        }

        $this->set( 'params', $this->_params );
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $this->assignToTemplate( );

        $this->addButtons(array(
                                array ( 'type'      => 'next',
                                        'name'      => ts('Make Contribution'),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                        'isDefault' => true   ),
                                array ( 'type'      => 'cancel',
                                        'name'      => ts('<< Go Back') ),
                                )
                          );

    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return void
     */
    function setDefaultValues()
    {
        $defaults = array();
        return $defaults;
    }

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        $contactID = $this->get( 'contactID' );
        if ( ! $contactID ) {
            // so now we have a confirmed financial transaction
            // lets create or update a contact first
            require_once 'api/crm.php';
            $contact_id = CRM_Core_BAO_UFGroup::findContact( $this->_params );
            $contact = null;
            if ( $contact_id ) {
                $contact =& crm_get_contact( array( 'contact_id' => $contact_id ) );
            }
            
            $ids = array( );
            if ( ! $contact || ! is_a( $contact, 'CRM_Contact_BAO_Contact' ) ) {
                $contact =& CRM_Contact_BAO_Contact::createFlat( $this->_params, $ids );
            } else {
                // need to fix and unify all contact creation
                $params = array( 'id' => $contact_id, 'contact_id' => $contact_id );
                $defaults = array( );
                CRM_Contact_BAO_Contact::retrieve( $params, $defaults, $ids );
                $contact =& CRM_Contact_BAO_Contact::createFlat( $this->_params, $ids );
            }

            if ( is_a( $contact, 'CRM_Core_Error' ) ) {
                CRM_Core_Error::fatal( "Failed creating contact for contributor" );
            }

            $contactID = $contact->id;
            $this->set( 'contactID', $contactID );
        }

        require_once 'CRM/Utils/Payment/PayPal.php';
        $paypal =& CRM_Utils_Payment_PayPal::singleton( );

        if ( $this->_contributeMode == 'express' ) {
            $result =& $paypal->doExpressCheckout( $this->_params );
        } else {
            $result =& $paypal->doDirectPayment( $this->_params );
        }

        if ( is_a( $result, 'CRM_Core_Error' ) ) {
            CRM_Core_Error::displaySessionError( $result );
            CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contribute/contribution', '_qf_Contribution_display=true' ) );
        }

        $now = date( 'YmdHis' );

        $this->_params = array_merge( $this->_params, $result );
        $this->_params['receipt_date'] = $now;
        $this->set( 'params', $this->_params );
        $this->assign( 'trxn_id', $result['trxn_id'] );

        // result has all the stuff we need
        // lets archive it to a financial transaction
        $config =& CRM_Core_Config::singleton( );

        CRM_Core_DAO::transaction( 'BEGIN' );

        // first create the transaction record
        $params = array(
                        'entity_table'      => 'civicrm_contact',
                        'entity_id'         => $contactID,
                        'trxn_date'         => $now,
                        'trxn_type'         => 'Debit',
                        'total_amount'      => $result['gross_amount'],
                        'fee_amount'        => CRM_Utils_Array::value( 'fee_amount', $result, 0 ),
                        'net_amount'        => CRM_Utils_Array::value( 'net_amount', $result, 0 ),
                        'currency'          => $this->_params['currencyID'],
                        'payment_processor' => $config->paymentProcessor,
                        'trxn_id'           => $result['trxn_id'],
                        );
                        
        require_once 'CRM/Contribute/BAO/FinancialTrxn.php';
        $trxn =& CRM_Contribute_BAO_FinancialTrxn::create( $params );

        $receiptDate = null;
        if ( $this->_values['is_email_receipt'] ) {
            $receiptDate = $now;
        }

        $contributionType =& new CRM_Contribute_DAO_ContributionType( );
        $contributionType->id = $this->_values['contribution_type_id'];
        if ( ! $contributionType->find( true ) ) {
            CRM_Utils_System::fatal( "Could not find a system table" );
        }

        if ( $contributionType->is_deductible ) {
            $nonDeductibeAmount = $result['gross_amount'];
        } else {
            $nonDeductibeAmount = 0.00;
        }

        // check contribution Type
        // next create the contribution record
        $params = array(
                        'contact_id'            => $contactID,
                        'contribution_type_id'  => $contributionType->id,
                        'payment_instrument_id' => 1,
                        'receive_date'          => $now,
                        'non_deductible_amount' => $nonDeductibeAmount,
                        'total_amount'          => $result['gross_amount'],
                        'fee_amount'            => CRM_Utils_Array::value( 'fee_amount', $result, 0 ),
                        'net_amount'            => CRM_Utils_Array::value( 'net_amount', $result, 0 ),
                        'trxn_id'               => $result['trxn_id'],
                        'currency'              => $this->_params['currencyID'],
                        'receipt_date'          => $receiptDate,
                        'source'                => ts( 'Online Contribution: ' ) . $this->_values['title'],
                        );
        $ids = array( );
        $contribution =& CRM_Contribute_BAO_Contribution::add( $params, $ids );

        // also create an activity history record
        $params = array('entity_table'     => 'civicrm_contact', 
                        'entity_id'        => $contactID, 
                        'activity_type'    => $contributionType->name,
                        'module'           => 'CiviContribute', 
                        'callback'         => 'CRM_Contribute_Page_Contribution::details',
                        'activity_id'      => $contribution->id, 
                        'activity_summary' => 'Online - $' . $this->_params['amount'],
                        'activity_date'    => $now,
                        );
        if ( is_a( crm_create_activity_history($params), 'CRM_Core_Error' ) ) { 
            CRM_Utils_System::fatal( "Could not create a system record" );
        }

        CRM_Core_DAO::transaction( 'COMMIT' );

        // finally send an email receipt
        if ( $this->_values['is_email_receipt'] ) {
            list( $displayName, $email ) = CRM_Contact_BAO_Contact::getEmailDetails( $contactID );

            $template =& CRM_Core_Smarty::singleton( );
            $subject = trim( $template->fetch( 'CRM/Contribute/Form/Contribution/ReceiptSubject.tpl' ) );
            $message = $template->fetch( 'CRM/Contribute/Form/Contribution/ReceiptMessage.tpl' );
            
            $this->_values['receipt_from_email'] = '"Donald A. Lobo" <lobo@yahoo.com>';

            require_once 'CRM/Utils/Mail.php';
            CRM_Utils_Mail::send( $this->_values['receipt_from_email'],
                                  $displayName,
                                  $email,
                                  $subject,
                                  $message,
                                  $this->_values['cc_receipt'],
                                  $this->_values['bcc_receipt']
                                  );
        }
    }
}

?>
