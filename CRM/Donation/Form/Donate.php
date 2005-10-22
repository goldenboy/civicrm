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

require_once 'CRM/Core/Form.php';

/**
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Donation_Form_Donate extends CRM_Core_Form {

    /**
     * the donation page id
     *
     * @var int
     * @access protected
     */
    protected $_id;

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        $this->_id = $this->get('id');
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $donationAmounts = array( 1, 2, 4, 8, 16, 32, 64 );
        $amounts = array( );
        foreach ( $donationAmounts as $amount ) {
            $amounts[] = HTML_QuickForm::createElement('radio', null, '', $amount, $amount );
        }
        $this->addGroup( $amounts, 'amount', ts( 'Donation Amount' ) );

        // add credit card fields
        $this->add('text',
                   'name',
                   ts('Name on Credit Card'),
                   array( 'size' => 30, 'maxlength' => 60 ),
                   true );

        $this->add('text', 
                   'email', 
                   ts('Email Address on Paypal'), 
                   array( 'size' => 30, 'maxlength' => 60 ), 
                   true );

        $this->addButtons(array(
                                array ( 'type'      => 'next',
                                        'name'      => ts('Donate via PayPal'),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                        'isDefault' => true   ),
                                array ( 'type'      => 'cancel',
                                        'name'      => ts('Cancel') ),
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
        // get the submitted form values.
        $params = $this->controller->exportValues( $this->_name );

        $this->set( 'amount', $params['amount'] );

        $params['currencyID'] = 'USD';
        $donateURL = CRM_Utils_System::url( 'civicrm/donation/donate', '_qf_Donate_display=1' );
        $params['cancelURL' ] = CRM_Utils_System::url( 'civicrm/donation/donate', '_qf_Donate_display=1', true, null, false );
        $params['returnURL' ] = CRM_Utils_System::url( 'civicrm/donation/donate', '_qf_Confirm_display=1&rfp=1', true, null, false );

        require_once 'CRM/Utils/Payment/PayPal.php';
        $paypal =& CRM_Utils_Payment_PayPal::singleton( );
        $token = $paypal->setExpressCheckout( $params );
        $this->set( 'token', $token );

        $paypalURL = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=$token";
        CRM_Utils_System::redirect( $paypalURL );
    }
}

?>
