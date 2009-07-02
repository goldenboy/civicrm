<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

/**
 * form helper class for an Email object
 */
class CRM_Contact_Form_Edit_Email 
{
    /**
     * build the form elements for an email object
     *
     * @param CRM_Core_Form $form       reference to the form object
     * @param array         $location   the location object to store all the form elements in
     * @param int           $locationId the locationId we are dealing with
     * @param int           $count      the number of blocks to create
     *
     * @return void
     * @access public
     * @static
     */
    static function buildQuickForm( &$form ) 
    {
        //FIXME &$location, $locationId, $count
        
        $blockId = ( $form->get( 'Email_Block_Count' ) ) ? $form->get( 'Email_Block_Count' ) : 1;
        
        //max block index.
        $form->addElement( 'hidden', 'hidden_Email_Count', $blockId, array( 'id' => 'hidden_Email_Count') );
        
        //Email box
        $form->addElement('text',"email[$blockId][email]", ts('Email'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email'));
        
        //Block type
        $form->addElement('select',"email[$blockId][location_type_id]", '' , CRM_Core_PseudoConstant::locationType());
        
        //On-hold checkbox
        $form->addElement('advcheckbox', "email[$blockId][on_hold]",null);
        
        //suppress Bulk Mailings (CRM-2881)
        if ( is_object( $form ) && !( $form instanceof CRM_Event_Form_ManageEvent_Location ) ) {     
            //Bulkmail checkbox
            $js = array( 'id' => 'bulk_email', 'onClick' => 'singleSelect(this, this.id);');
            $form->addElement('advcheckbox', "email[$blockId][is_bulkmail]", null, '', $js);
        }
        
        //is_Primary radio
        $js = array( 'id' => 'primary_email', 'onClick' => 'singleSelect(this, this.id);');
        $form->addElement('radio', "email[$blockId][is_primary]", null,'', $blockId, $js );
    }
}


