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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

/**
 * form helper class for an IM object 
 */
class CRM_Contact_Form_Edit_IM
{
    /**
     * build the form elements for an IM object
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
    static function buildQuickForm( &$form ) {
        
        //FIXME : &$location, $locationId, $count 
        
        $blockId = ( $form->get( 'IM_Block_Count' ) ) ? $form->get( 'IM_Block_Count' ) : 1;
        
        // only add hidden element when processing first block 
        // for remaining blocks we'll calculate at run time w/ jQuery. 
        if ( $blockId == 1 ) {
            $form->addElement( 'hidden', 'hidden_IM_Instances', $blockId, array( 'id' => 'hidden_IM_Instances') );
        }
        
        //IM provider select
        $form->addElement('select', "im[$blockId][provider_id]", '',
                          array('' => ts('- select service -')) + CRM_Core_PseudoConstant::IMProvider() );
        
        //Block type select
        $form->addElement('select',"im[$blockId][location_type_id]", '' , CRM_Core_PseudoConstant::locationType());
        
        //IM box
        $form->addElement('text', "im[$blockId][name]", ts('Instant Messenger'),
                          CRM_Core_DAO::getAttribute('CRM_Core_DAO_IM', 'name') );
        
        //is_Primary radio
        $js = array( 'id' => "IM_".$blockId."_IsPrimary", 'onClick' => 'singleSelect( "IM",'. $blockId . ', "IsPrimary" );');
        $choice[] =& $form->createElement( 'radio', null, '', null, '1', $js );
        $form->addGroup( $choice, "im[$blockId][is_primary]" );
    }
}



