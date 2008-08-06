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

class CRM_Upgrade_TwoOne_Form_Step4 extends CRM_Upgrade_Form {

    function verifyPreDBState( &$errorMessage ) {
        $errorMessage = ts('Pre-condition failed for upgrade step %1.', array(1 => '2'));
        
        if ( ! CRM_Core_DAO::checkTableExists( 'civicrm_cache' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_group_contact_cache' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_menu' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_discount' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_pledge' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_pledge_block' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_pledge_payment' )
             ) {
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
        if ( $this->checkVersion( '2.03' ) ) {
            $this->setVersion( '2.1' );
        } else {
            return false;
        } 

        // update config defaults
        require_once "CRM/Core/DAO/Domain.php";
        $domain =& new CRM_Core_DAO_Domain();
        $domain->selectAdd( );
        $domain->selectAdd( 'config_backend' );
        $domain->find(true);
        if ($domain->config_backend) {
            $defaults   = unserialize($domain->config_backend);
            // reset components
            $defaults['enableComponents']   = 
                array( 'CiviContribute','CiviPledge','CiviMember','CiviEvent', 'CiviMail' );
            $defaults['enableComponentIDs'] = array( 1, 6, 2, 3, 4 );
            // serialise settings 
            CRM_Core_BAO_Setting::add($defaults);            
        }
        
        return $this->checkVersion( '2.1' );
    }
    
    function buildQuickForm( ) {
    }

    function getTitle( ) {
        return ts( 'Database Upgrade to v2.1 Completed' );
    }

    function getTemplateMessage( ) {
        if ( $this->_config->userFramework == 'Drupal' ) {
            $upgradeDoc = 'http://wiki.civicrm.org/confluence/x/7IFH';
        } else {
            $upgradeDoc = 'http://wiki.civicrm.org/confluence/x/SoJH';
        }
        return '<p><strong>' . ts('Your CiviCRM database has been successfully upgraded to v2.1.') . '</strong></p><p>' . ts('Please be sure to follow the remaining steps in the <a href=\'%1\' target=\'_blank\'><strong>Upgrade Instructions</strong></a>.', array( 1 => $upgradeDoc )) . '</p><p>' . ts('Thank you for using CiviCRM.') . '</p>';
    }
}
