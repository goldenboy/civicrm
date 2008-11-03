<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
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

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for processing a ontribution 
 * 
 */
class CRM_Contribute_Form_ContributionBase extends CRM_Core_Form
{
    
    /**
     * the id of the contribution page that we are proceessing
     *
     * @var int
     * @public
     */
    public $_id;

    /**
     * the mode that we are in
     * 
     * @var string
     * @protect
     */
    public $_mode;

    /**
     * the contact id related to a membership
     *
     * @var int
     * @public
     */
    public $_membershipContactID;

    /**
     * the values for the contribution db object
     *
     * @var array
     * @protected
     */
    public $_values;

    /**
     * the paymentProcessor attributes for this page
     *
     * @var array
     * @protected
     */
    public $_paymentProcessor;
    protected $_paymentObject = null;

    /**
     * The membership block for this page
     *
     * @var array
     * @protected
     */
    public $_membershipBlock = null;

    /**
     * the default values for the form
     *
     * @var array
     * @protected
     */
    protected $_defaults;

    /**
     * The params submitted by the form and computed by the app
     *
     * @var array
     * @public
     */
    public $_params;

    /** 
     * The fields involved in this contribution page
     * 
     * @var array 
     * @public
     */ 
    public $_fields;

    /**
     * The billing location id for this contribiution page
     *
     * @var int
     * @protected
     */
    public $_bltID;

    /**
     * Cache the amount to make things easier
     *
     * @var float
     * @public
     */
    public $_amount;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess()  
    {  
        $config  =& CRM_Core_Config::singleton( );
        $session =& CRM_Core_Session::singleton( );

        // current contribution page id 
        $this->_id = CRM_Utils_Request::retrieve( 'id', 'Positive',
                                                  $this );
        if ( ! $this->_id ) {
            $pastContributionID = $session->get( 'pastContributionID' );
            if ( ! $pastContributionID ) {
                CRM_Core_Error::fatal( ts( 'We could not find contribution details for your request. Please try your request again.' ) );
            } else {
                CRM_Core_Error::fatal( ts( 'This contribution has already been submitted. Click <a href=\'%1\'>here</a> if you want to make another contribution.', array( 1 => CRM_Utils_System::url( 'civicrm/contribute/transact', 'reset=1&id=' . $pastContributionID ) ) ) );
            }
        } else {
            $session->set( 'pastContributionID', $this->_id );
        }

        if ( $session->get('userID') ) {
            $mid = CRM_Utils_Request::retrieve( 'mid', 'Positive', $this );
            if ( $mid ) {
                require_once 'CRM/Member/DAO/Membership.php';
                $membership =& new CRM_Member_DAO_Membership( );
                $membership->id = $mid;
                
                if ( $membership->find(true) ) {
                    $this->_defaultMemTypeId = $membership->membership_type_id;
                    if ( $membership->contact_id != $session->get('userID') ) {
                        require_once 'CRM/Contact/BAO/Relationship.php';
                        $employers = 
                            CRM_Contact_BAO_Relationship::getPermissionedEmployer( $session->get('userID') );
                        if ( array_key_exists($membership->contact_id, $employers) ) {
                            $this->_membershipContactID = $membership->contact_id;
                            $this->assign('membershipContactID', $this->_membershipContactID);
                        } else {
                            CRM_Core_Session::setStatus( ts("Oops. The membership you're trying to renew appears to be invalid. Contact your site administrator if you need assistance. If you continue, you will be issued a new membership.") );
                        }
                    }
                } else {
                    CRM_Core_Session::setStatus( ts("Oops. The membership you're trying to renew appears to be invalid. Contact your site administrator if you need assistance. If you continue, you will be issued a new membership.") );
                }
                unset($membership);
            }
        }

        // we do not want to display recently viewed items, so turn off
        $this->assign       ( 'displayRecent' , false );
        // Contribution page values are cleared from session, so can't use normal Printer Friendly view.
        // Use Browser Print instead.
        $this->assign( 'browserPrint', true  );
        
        // action
        $this->_action = CRM_Utils_Request::retrieve( 'action', 'String',
                                                      $this, false, 'add' );
        $this->assign( 'action'  , $this->_action   ); 

        // current mode
        $this->_mode = ( $this->_action == 1024 ) ? 'test' : 'live';

        $this->_values           = $this->get( 'values' );
        $this->_fields           = $this->get( 'fields' );
        $this->_bltID            = $this->get( 'bltID'  );
        $this->_paymentProcessor = $this->get( 'paymentProcessor' );

        if ( ! $this->_values ) {
            // get all the values from the dao object
            $this->_values = array( );
            $this->_fields = array( );

            require_once 'CRM/Contribute/BAO/ContributionPage.php';
            CRM_Contribute_BAO_ContributionPage::setValues( $this->_id, $this->_values );

            // check if form is active
            if ( ! $this->_values['is_active'] ) {
                // form is inactive, die a fatal death
                CRM_Core_Error::fatal( ts( 'The page you requested is currently unavailable.' ) );
            }

            // also check for billing informatin
            // get the billing location type
            $locationTypes =& CRM_Core_PseudoConstant::locationType( );
            $this->_bltID = array_search( 'Billing',  $locationTypes );
            if ( ! $this->_bltID ) {
                CRM_Core_Error::fatal( ts( 'Please set a location type of %1', array( 1 => 'Billing' ) ) );
            }
            $this->set   ( 'bltID', $this->_bltID );

            // check for is_monetary status
            $isMonetary = CRM_Utils_Array::value( 'is_monetary', $this->_values );
            $isPayLater = CRM_Utils_Array::value( 'is_pay_later', $this->_values );

            if ( $isMonetary && 
                 ( ! $isPayLater || CRM_Utils_Array::value( 'payment_processor_id', $this->_values ) ) ) {
                $ppID = CRM_Utils_Array::value( 'payment_processor_id', $this->_values );
                if ( ! $ppID ) {
                    CRM_Core_Error::fatal( ts( 'A payment processor must be selected for this contribution page (contact the site administrator for assistance).' ) );
                }
                
                $ppID = CRM_Utils_Array::value( 'payment_processor_id', $this->_values );
                
                if ( !$ppID ) {
                    CRM_Core_Error::fatal( ts( 'Please set a payment processor in your contribution page' ) );
                }
                
                require_once 'CRM/Core/BAO/PaymentProcessor.php';
                $this->_paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $ppID,
                                                                                      $this->_mode );

                // ensure that processor has a valid config
                $this->_paymentObject =&
                    CRM_Core_Payment::singleton( $this->_mode, 'Contribute', $this->_paymentProcessor );
                $error = $this->_paymentObject->checkConfig( );
                if ( ! empty( $error ) ) {
                    CRM_Core_Error::fatal( $error );
                }

                $this->set( 'paymentProcessor', $this->_paymentProcessor );
            }
            
            // this avoids getting E_NOTICE errors in php
            $setNullFields = array( 'amount_block_is_active',
                                    'honor_block_is_active' ,
                                    'is_allow_other_amount' ,
                                    'footer_text' );
            foreach ( $setNullFields as $f ) {
                if ( ! isset( $this->_values[$f]  ) ) {
                    $this->_values[$f] = null;
                }
            }
            
            //check if Membership Block is enabled, if Membership Fields are included in profile
            //get membership section for this contribution page
            require_once 'CRM/Member/BAO/Membership.php';
            $this->_membershipBlock = CRM_Member_BAO_Membership::getMembershipBlock( $this->_id );
            $this->set( 'membershipBlock', $this->_membershipBlock );
            
            require_once "CRM/Core/BAO/UFField.php";
            if ( $this->_values['custom_pre_id'] ) {
                $preProfileType  = CRM_Core_BAO_UFField::getProfileType( $this->_values['custom_pre_id'] );
            }
            
            if ( $this->_values['custom_post_id'] ) {
                $postProfileType = CRM_Core_BAO_UFField::getProfileType( $this->_values['custom_post_id'] );
            }

            // also set cancel subscription url
            if ( !$isPayLater && $this->_paymentObject ) {    
                $this->_values['cancelSubscriptionUrl'] = $this->_paymentObject->cancelSubscriptionURL( );

            }
            if ( ( ( isset($postProfileType) && $postProfileType == 'Membership' ) ||
                   ( isset($preProfileType ) && $preProfileType == 'Membership' ) ) &&
                 ! $this->_membershipBlock['is_active'] ) {
                CRM_Core_Error::fatal( ts('This page includes a Profile with Membership fields - but the Membership Block is NOT enabled. Please notify the site administrator.') );
            }
            
            require_once 'CRM/Pledge/BAO/PledgeBlock.php';
            $pledgeBlock = CRM_Pledge_BAO_PledgeBlock::getPledgeBlock( $this->_id );

            if ( $pledgeBlock ) {
                $this->_values['pledge_block_id'        ] = $pledgeBlock['id'];
                $this->_values['max_reminders'          ] = $pledgeBlock['max_reminders'];
                $this->_values['initial_reminder_day'   ] = $pledgeBlock['initial_reminder_day'];
                $this->_values['additional_reminder_day'] = $pledgeBlock['additional_reminder_day'];

                //set pledge id in values
                $pledgeId = CRM_Utils_Request::retrieve( 'pledgeId', 'Positive', $this );
                
                //authenticate pledge user for pledge payment.
                if ( $pledgeId ) {
                    $this->_values['pledge_id'] = $pledgeId;
                    self::authenticatePledgeUser( );
                }
            }

            $this->set( 'values', $this->_values );
            $this->set( 'fields', $this->_fields );
        }

        //set pledge block if block id is set
        if ( CRM_Utils_Array::value( 'pledge_block_id', $this->_values ) ) {
            $this->assign( 'pledgeBlock', true );
        }
        
        // we do this outside of the above conditional to avoid 
        // saving the country/state list in the session (which could be huge)
        if ( ( $this->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_FORM ) &&
             CRM_Utils_Array::value('is_monetary', $this->_values) ) {
            require_once 'CRM/Core/Payment/Form.php';
            CRM_Core_Payment_Form::setCreditCardFields( $this );
        }

        $this->assign_by_ref( 'paymentProcessor', $this->_paymentProcessor );

        // check if this is a paypal auto return and redirect accordingly
        if ( CRM_Core_Payment::paypalRedirect( $this->_paymentProcessor ) ) {
            $url = CRM_Utils_System::url( 'civicrm/contribute/transact',
                                          "_qf_ThankYou_display=1&qfKey={$this->controller->_key}" );
            CRM_Utils_System::redirect( $url );
        }
        
        // make sure we have a valid payment class, else abort
        if ( CRM_Utils_Array::value('is_monetary',$this->_values) &&
             ! $this->_paymentProcessor['class_name'] &&
             !CRM_Utils_Array::value( 'is_pay_later',$this->_values ) ) {
            CRM_Core_Error::fatal( ts( 'Payment processor is not set for this page' ) );
        }

        // check if one of the (amount , membership)  bloks is active or not
        require_once 'CRM/Member/BAO/Membership.php';
        $this->_membershipBlock = $this->get( 'membershipBlock' );

        if ( ! $this->_values['amount_block_is_active'] &&
             ! $this->_membershipBlock['is_active'] ) {
            CRM_Core_Error::fatal( ts( 'The requested online contribution page is missing a required Contribution Amount section or Membership section. Please check with the site administrator for assistance.' ) );
        }

        if ( $this->_values['amount_block_is_active'] ) {
            $this->set('amount_block_is_active',$this->_values['amount_block_is_active' ]);
        }

        if ( ! empty($this->_membershipBlock) &&
             $this->_membershipBlock["is_separate_payment"] &&
             $this->_paymentProcessor['payment_processor_type'] == "PayPal_Standard" ) {
            CRM_Core_Error::fatal( ts( 'This contribution page is configured to support separate contribution and membership payments. The PayPal Website Payments Standard plugin does not currently support multiple simultaneous payments. Please contact the site administrator and notify them of this error' ) );
        }

        $this->_contributeMode = $this->get( 'contributeMode' );
        $this->assign( 'contributeMode', $this->_contributeMode ); 

        //assigning is_monetary and is_email_receipt to template
        $this->assign( 'is_monetary', $this->_values['is_monetary'] );
        $this->assign( 'is_email_receipt', $this->_values['is_email_receipt'] );
        $this->assign( 'bltID', $this->_bltID );

        //assign cancelSubscription URL to templates
        $this->assign( 'cancelSubscriptionUrl',
                       CRM_Utils_Array::value( 'cancelSubscriptionUrl', $this->_values ) );
        
        // assigning title to template in case someone wants to use it, also setting CMS page title
        $this->assign( 'title', $this->_values['title'] );
        CRM_Utils_System::setTitle($this->_values['title']);  
        
        $this->_defaults = array( );
        
        $this->_amount   = $this->get( 'amount' );
    }

    /** 
     * set the default values
     *                                                           
     * @return void 
     * @access public 
     */ 
    function setDefaultValues( ) {
        return $this->_defaults;
    }

    /** 
     * assign the minimal set of variables to the template
     *                                                           
     * @return void 
     * @access public 
     */ 
    function assignToTemplate( ) {
        $name = CRM_Utils_Array::value( 'billing_first_name', $this->_params );
        if ( CRM_Utils_Array::value( 'billing_middle_name', $this->_params ) ) {
            $name .= " {$this->_params['billing_middle_name']}";
        }
        $name .= ' ' . CRM_Utils_Array::value( 'billing_last_name', $this->_params );
        $name = trim( $name );
        $this->assign( 'billingName', $name );
        $this->set( 'name', $name );

        $vars = array( 'amount', 'currencyID',
                       'credit_card_type', 'trxn_id', 'amount_level' );
 
        $config =& CRM_Core_Config::singleton( );
        if ( isset($this->_values['is_recur']) && 
             $this->_paymentProcessor['is_recur'] ) {
            $this->assign( 'is_recur_enabled', 1 );
            $vars = array_merge( $vars, array( 'is_recur', 'frequency_interval', 'frequency_unit',
                                               'installments' ) );
        }

        if ( in_array('CiviPledge', $config->enableComponents ) && 
             CRM_Utils_Array::value( 'is_pledge', $this->_params ) == 1 ) {
            $this->assign( 'pledge_enabled', 1 );

            $vars = array_merge( $vars, array( 'is_pledge',
                                               'pledge_frequency_interval', 
                                               'pledge_frequency_unit',
                                               'pledge_installments') );
        }
        
        if( isset($this->_params['amount_other']) || isset($this->_params['selectMembership']) ) {
            $this->_params['amount_level'] = '';
        }
       
        foreach ( $vars as $v ) {
            if ( CRM_Utils_Array::value( $v, $this->_params ) ) {
                $this->assign( $v, $this->_params[$v] );
            }
        }

        // assign the address formatted up for display
        $addressParts  = array( "street_address-{$this->_bltID}",
                                "city-{$this->_bltID}",
                                "postal_code-{$this->_bltID}",
                                "state_province-{$this->_bltID}",
                                "country-{$this->_bltID}");

        $addressFields = array( );
        foreach ($addressParts as $part) {
            list( $n, $id ) = explode( '-', $part );
            $addressFields[$n] = CRM_Utils_Array::value( $part, $this->_params );
        }

        require_once 'CRM/Utils/Address.php';
        $this->assign('address', CRM_Utils_Address::format($addressFields));

        if ( CRM_Utils_Array::value( 'is_for_organization', $this->_params ) ) {
            $this->assign('onBehalfName', $this->_params['organization_name']);
            $this->assign('onBehalfEmail', $this->_params['location'][1]['email'][1]['email']);
            $this->assign('onBehalfAddress', CRM_Utils_Address::format($this->_params['location'][1]['address']));
        }
        
        //fix for CRM-3767
        $assignCCInfo = false;
        if ( $this->_amount > 0.0 ) {
            $assignCCInfo = true;
        } else if ( CRM_Utils_array::value( 'selectMembership', $this->_params ) ) {
            $memFee = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', $this->_params['selectMembership'], 'minimum_fee' );
            if (  $memFee > 0.0  ) {
                $assignCCInfo = true; 
            }
        }
        
        if ( $this->_contributeMode == 'direct' && $assignCCInfo ) {
            $date = CRM_Utils_Date::format( $this->_params['credit_card_exp_date'] );
            $date = CRM_Utils_Date::mysqlToIso( $date );
            $this->assign( 'credit_card_exp_date', $date );
            $this->assign( 'credit_card_number',
                           CRM_Utils_System::mungeCreditCard( $this->_params['credit_card_number'] ) );
        }
        
        $this->assign( 'email',
                       $this->controller->exportValue( 'Main', "email-{$this->_bltID}" ) );
        
        // also assign the receipt_text
        if ( isset( $this->_values['receipt_text'] ) ) {
            $this->assign( 'receipt_text', $this->_values['receipt_text'] );
        }

        // assign pay later stuff
        $this->_params['is_pay_later'] = CRM_Utils_Array::value( 'is_pay_later', $this->_params, false );
        $this->assign( 'is_pay_later', $this->_params['is_pay_later'] );
        if ( $this->_params['is_pay_later'] ) {
            $this->assign( 'pay_later_text'   , $this->_values['pay_later_text']    );
            $this->assign( 'pay_later_receipt', $this->_values['pay_later_receipt'] );
        }
    }

    /**  
     * Function to add the custom fields
     *  
     * @return None  
     * @access public  
     */ 
    function buildCustom( $id, $name, $viewOnly = false ) 
    {
        if ( $id ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            require_once 'CRM/Profile/Form.php';
            $session =& CRM_Core_Session::singleton( );
            $contactID = $session->get( 'userID' );
            
            // we don't allow conflicting fields to be
            // configured via profile - CRM 2100
            $fieldsToIgnore = array( 'receive_date'           => 1,
                                     'trxn_id'                => 1,
                                     'invoice_id'             => 1,
                                     'net_amount'             => 1,
                                     'fee_amount'             => 1,
                                     'non_deductible_amount'  => 1,
                                     'total_amount'           => 1,
                                     'amount_level'           => 1,
                                     'contribution_status_id' => 1
                                     );

            $fields = null;
            if ( $contactID ) {
                require_once "CRM/Core/BAO/UFGroup.php";
                if ( CRM_Core_BAO_UFGroup::filterUFGroups($id, $contactID)  ) {
                    $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD );
                }
            } else {
                $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD ); 
            }

            if ( $fields ) {
                // unset any email-* fields since we already collect it, CRM-2888
                foreach ( array_keys( $fields ) as $fieldName ) {
                    if ( substr( $fieldName, 0, 6 ) == 'email-' ) {
                        unset( $fields[$fieldName] );
                    }
                }
                
                if (array_intersect_key($fields, $fieldsToIgnore)) {
                    $fields = array_diff_key( $fields, $fieldsToIgnore );
                    CRM_Core_Session::setStatus("Some of the profile fields cannot be configured for this page.");
                }
                
                $this->assign( $name, $fields );
                
                $addCaptcha = false;
                foreach($fields as $key => $field) {
                    if ( $viewOnly &&
                         isset( $field['data_type'] ) &&
                         $field['data_type'] == 'File' ) {
                        // ignore file upload fields
                        continue;
                    }
                    CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE);
                    $this->_fields[$key] = $field;
                    if ( $field['add_captcha'] ) {
                        $addCaptcha = true;
                    }
                }

                if ( $addCaptcha &&
                     ! $viewOnly ) {
                    require_once 'CRM/Utils/ReCAPTCHA.php';
                    $captcha =& CRM_Utils_ReCAPTCHA::singleton( );
                    $captcha->add( $this );
                    $this->assign( "isCaptcha" , true );
                }
            }
        }
    }
    
    function getTemplateFileName() 
    {
        if ( $this->_id ) {
            $templateFile = "CRM/Contribute/Form/Contribution/{$this->_id}/{$this->_name}.tpl";
            $template =& CRM_Core_Form::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }
        }
        return parent::getTemplateFileName( );
    }

    /**
     * Function to authenticate pledge user during online payment.
     *
     * @access public
     * @return None
     */
    public function authenticatePledgeUser( ) 
    {
        //get the userChecksum and contact id
        $userChecksum = CRM_Utils_Request::retrieve( 'cs', 'String', $this );
        $contactID    = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );

        //get pledge status and contact id
        $pledgeValues = array( );
        $pledgeParams = array( 'id' => $this->_values['pledge_id'] );
        $returnProperties = array('contact_id', 'status_id');
        CRM_Core_DAO::commonRetrieve('CRM_Pledge_DAO_Pledge', $pledgeParams, $pledgeValues, $returnProperties );
        
        //get user id.
        $session =& CRM_Core_Session::singleton( );
        $userID = $session->get('userID');
        
        //get all status
        require_once 'CRM/Contribute/PseudoConstant.php';
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( );
        $validStatus = array( array_search( 'Pending', $allStatus ), 
                              array_search( 'In Progress', $allStatus ),
                              array_search( 'Overdue', $allStatus ), );
        
        $validUser = false;
        if ( $userID &&
             $userID == $pledgeValues['contact_id'] ) {
            //check for authenticated  user. 
            $validUser = true;
        } else if ( $userChecksum && $pledgeValues['contact_id'] ) {
            //check for anonymous user.
            require_once 'CRM/Contact/BAO/Contact/Utils.php';
            $validUser = CRM_Contact_BAO_Contact_Utils::validChecksum( $pledgeValues['contact_id'], $userChecksum );

            //make sure cid is same as pledge contact id
            if ( $validUser && ( $pledgeValues['contact_id'] != $contactID ) ) {
                $validUser = false;
            }
        }
            
        if ( !$validUser ) {
            CRM_Core_Error::fatal( ts( "Oops. It looks like you have an incorrect or incomplete link (URL). Please make sure you've copied the entire link, and try again. Contact the site administrator if this error persists." ) );    
        }
        
        //check for valid pledge status.
        if ( !in_array( $pledgeValues['status_id'], $validStatus ) ) {
            CRM_Core_Error::fatal(ts('Oops. You cannot make a payment for this pledge - pledge status is %1.', array(1 => CRM_Utils_Array::value($pledgeValues['status_id'], $allStatus)))); 
        }
    }
    
}


