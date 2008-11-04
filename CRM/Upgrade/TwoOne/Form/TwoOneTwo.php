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

require_once 'CRM/Upgrade/Form.php';

class CRM_Upgrade_TwoOne_Form_TwoOneTwo extends CRM_Upgrade_Form {

    function verifyPreDBState( &$errorMessage ) {
        $errorMessage = ts('Pre-condition failed for upgrade to v2.1.2.');
        
        if ( ! CRM_Core_DAO::checkTableExists( 'civicrm_cache' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_group_contact_cache' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_menu' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_discount' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_pledge' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_pledge_block' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_pledge_payment' )
             ) {
            $errorMessage .= ' Few important tables were found missing.';
            return false;
        }
        
        // check fields which MUST be present if a proper 2.1 db
        if ( ! CRM_Core_DAO::checkFieldExists( 'civicrm_cache', 'group_name' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_cache', 'created_date' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_cache', 'expired_date' ) ||
                      
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_discount', 'option_group_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_discount', 'end_date' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_group_contact_cache', 'contact_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_menu', 'path_arguments' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_menu', 'is_exposed' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_menu', 'page_type' )||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_option_value', 'component_id' ) ||
             
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_option_group', 'id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_option_group', 'name' )
             ) {
            // db looks to have stuck somewhere between 2.0 & 2.1
            $errorMessage .= ' Few important fields were found missing in some of the tables.';
            return false;
        }

        return true;
    }
    
    function upgrade( ) {
        $currentDir = dirname( __FILE__ );
        
        $sqlFile    = implode( DIRECTORY_SEPARATOR,
                               array( $currentDir, '../sql', 'two_one_two.mysql' ) );
        $this->source( $sqlFile );

        // CRM-3707, Price Set Export has zeros in all columns
        $query     = "SELECT distinct(price_field_id) FROM civicrm_line_item";
        $lineItem  = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        while ( $lineItem->fetch( ) ) {
            $grpName = "civicrm_price_field.amount." . $lineItem->price_field_id;
            $query   = "SELECT id FROM civicrm_option_group WHERE name='$grpName'";
            $optGrp  = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
            if ( $optGrp->fetch( ) ) {
                // update line_item table
                $query   = "UPDATE civicrm_line_item SET option_group_id={$optGrp->id} WHERE price_field_id={$lineItem->price_field_id}";
                CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
            }
        }

        $this->setVersion( '2.1.2' );
    }

    function verifyPostDBState( &$errorMessage ) {
        $errorMessage = ts('Post-condition failed for upgrade to v2.1.2.');
        return $this->checkVersion( '2.1.2' );
    }
}
