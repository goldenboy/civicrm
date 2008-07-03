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

require_once 'CRM/Core/Page.php';

class CRM_Pledge_Page_Payment extends CRM_Core_Page
{
    /**
     * This function is the main function that is called when the page loads, it decides the which action has to be taken for the page.
     * 
     * return null
     * @access public
     */
    function run( ) 
    {
        $pledgeId = CRM_Utils_Request::retrieve( 'pledgeId', 'Positive', $this );
        $contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        
        require_once 'CRM/Pledge/BAO/Pledge.php';
        $paymentDetails = CRM_Pledge_BAO_Pledge::getPledgePayments( $pledgeId );

        $this->assign( 'rows'     , $paymentDetails );
        $this->assign( 'pledgeId' , $pledgeId );
        $this->assign( 'contactId', $contactId );
        
        // check if we can process credit card contribs
        $processors = CRM_Core_PseudoConstant::paymentProcessor( false, false,
                                                                 "billing_mode IN ( 1, 3 )" );
        if ( count( $processors ) > 0 ) {
            $this->assign( 'newCredit', true );
        } else {
            $this->assign( 'newCredit', false );
        }

        // check is the user has view/edit signer permission
        $permission = 'view';
        if ( CRM_Core_Permission::check( 'edit pledge records' ) ) {
            $permission = 'edit';
        }
        $this->assign( 'permission', $permission );

        parent::run();
    }
}


