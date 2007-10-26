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

require_once 'CRM/Member/Form.php';
require_once 'CRM/Member/PseudoConstant.php';

/**
 * This class generates form components for Membership Renewal
 * 
 */
class CRM_Member_Form_MembershipRenewal extends CRM_Member_Form
{

    public function preProcess()  
    {  
        // check for edit permission
        if ( ! CRM_Core_Permission::check( 'edit memberships' ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );
        }

        // action
        $this->_action    = CRM_Utils_Request::retrieve( 'action', 'String',
                                                         $this, false, 'add' );
        $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Positive',
                                                         $this );
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                                         $this );
        $this->_memType   = CRM_Utils_Request::retrieve( 'subType', 'Positive',
                                                         $this );
        
        if ( ! $this->_memType ) {
            if ( $this->_id ) {
                $this->_memType = CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership",$this->_id,"membership_type_id");
            } else {
                $this->_memType = "Membership";
            }
        }     
        
        $this->assign( "endDate", CRM_Utils_Date::customFormat(
                                                               CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership",
                                                                                           $this->_id,
                                                                                           "end_date")
                                                               )
                       );

        $this->assign( "membershipStatus", 
                       CRM_Core_DAO::getFieldValue("CRM_Member_DAO_MembershipStatus",
                                                   CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership",
                                                                               $this->_id,
                                                                               "status_id"
                                                                               ),
                                                   "name")
                       );
        

        $this->assign( "memType",  CRM_Core_DAO::getFieldValue("CRM_Member_DAO_MembershipType",$this->_memType,"name") );

        parent::preProcess( );
    }

    /**
     * This function sets the default values for the form.
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    public function setDefaultValues( ) {
        $defaults = array( );
        $defaults =& parent::setDefaultValues( );
        $defaults["membership_type_id"]    =  $this->_memType;
               
        $renewalDate = getDate();
        $defaults['renewal_date']['M'] = $renewalDate['mon'];
        $defaults['renewal_date']['d'] = $renewalDate['mday'];
        $defaults['renewal_date']['Y'] = $renewalDate['year'];

        if ($defaults['id']) {
            $defaults['record_contribution'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipPayment', 
                                                                            $defaults['id'], 
                                                                            'contribution_id', 
                                                                            'membership_id' );
        }
        
        $defaults['contribution_type_id'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                                         $this->_memType, 
                                                                         'contribution_type_id' );
        
        $defaults['total_amount'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                                 $this->_memType, 
                                                                 'minimum_fee' );
        
        $defaults['record_contribution'] = 0;
        if ($defaults['record_contribution']) {
            $contributionParams   = array( 'id' => $defaults['record_contribution'] );
            $contributionIds      = array( );
            
            require_once "CRM/Contribute/BAO/Contribution.php";
            CRM_Contribute_BAO_Contribution::getValues( $contributionParams, $defaults, $contributionIds );
        }
        
        $defaults['send_receipt'] = 1; 

        $this->assign( "member_is_test", CRM_Utils_Array::value('member_is_test',$defaults) );
        
        return $defaults;
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );

        $selOrgMemType[0][0] = $selMemTypeOrg[0] = ts('-- select --');

        $dao =& new CRM_Member_DAO_MembershipType();
        $dao->find();
        while ($dao->fetch()) {
            if ($dao->is_active) {
                if ( !CRM_Utils_Array::value($dao->member_of_contact_id,$selMemTypeOrg) ) {
                    $selMemTypeOrg[$dao->member_of_contact_id] = 
                        CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', 
                                                     $dao->member_of_contact_id, 
                                                     'display_name', 
                                                     'id' );
                    $selOrgMemType[$dao->member_of_contact_id][0] = ts('-- select --');
                }                
                if ( !CRM_Utils_Array::value($dao->id,$selOrgMemType[$dao->member_of_contact_id]) ) {
                    $selOrgMemType[$dao->member_of_contact_id][$dao->id] = $dao->name;
                }
            }
        }
        
        // show organization by default, if only one organization in
        // the list 
        if ( count($selMemTypeOrg) == 2 ) {
            unset($selMemTypeOrg[0], $selOrgMemType[0][0]);
        }
        
        $sel =& $this->addElement('hierselect', 
                                  'membership_type_id', 
                                  ts('Renewal Membership Organization and Type'), 
                                  array('onChange' => "if (this.value) reload(true); else return false") );  
        $sel->setOptions(array($selMemTypeOrg,  $selOrgMemType));

        $this->applyFilter('__ALL__', 'trim');
        
        $this->add('date', 'renewal_date', ts('Renewal Date'), CRM_Core_SelectValues::date('manual', 20, 1), false );    
        $this->addRule('renewal_date', ts('Select a valid date.'), 'qfDate');
        
        $this->addElement('checkbox', 
                          'record_contribution', 
                          ts('Record Contribution for Renewal?'), null, 
                          array( 'onClick' => 'showRecordContribution()'));
        
        require_once 'CRM/Contribute/PseudoConstant.php';
        $this->add('select', 'contribution_type_id', 
                   ts( 'Contribution Type' ), 
                   array(''=>ts( '-select-' )) + CRM_Contribute_PseudoConstant::contributionType( )
                   );
        
        $this->add('text', 'total_amount', ts('Amount'));
        
        $this->add('select', 'payment_instrument_id', 
                   ts( 'Paid By' ), 
                   array(''=>ts( '-select-' )) + CRM_Contribute_PseudoConstant::paymentInstrument( )
                   );
        
        $this->add('select', 'contribution_status_id',
                   ts('Contribution Status'), 
                   CRM_Contribute_PseudoConstant::contributionStatus( )
                   );

        $this->addElement('checkbox', 
                          'send_receipt', 
                          ts('Send Renewal Notice?'), null, 
                          array( 'onClick' => 'showReceiptText()'));

        $this->add('textarea', 'receipt_text', ts('Renewal Message') );

        $this->addFormRule(array('CRM_Member_Form_MembershipRenewal', 'formRule'));

    }

    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    public function formRule( &$params ) 
    {
        $errors = array( );
        if (!$params['membership_type_id'][1]) {
            $errors['membership_type_id'] = "Please select a Membership Type.";
        }
        return empty($errors) ? true : $errors;
    }
       
    /**
     * Function to process the renewal form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        
    }
}
?>
