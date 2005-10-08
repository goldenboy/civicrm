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
 * This class contains function to build the note form.
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Social Source Foundation (c) 2005
 * $Id$
 *
 */


Class CRM_Contact_Form_GroupTag
{
    const
        GROUP = 1,
        TAG   = 2,
        ALL   = 3;

    /**
     * This function is to build form elements
     * params object $form object of the form
     *
     * @static
     * @access public
     */
    static function buildGroupTagBlock(&$form, $contactId = 0, $type = CRM_Contact_Form_GroupTag::ALL, $visibility = false ) {
        $type = (int ) $type;
        if ( $type & CRM_Contact_Form_GroupTag::GROUP ) {
            $elements = array( );
            $group  =& CRM_Core_PseudoConstant::group( );
            foreach ($group as $id => $name) {
                if ( $visibility ) {
                    // make sure that this group has public visibility. not very efficient
                    $dao =& new CRM_Contact_DAO_Group( );
                    $dao->id = $id;
                    if ( $dao->find( true ) ) {
                        if ( $dao->visibility == 'User and User Admin Only' ) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
                $elements[] =& HTML_QuickForm::createElement('checkbox', $id, null, $name);
            }
            if ( ! empty( $elements ) ) {
                $form->addGroup( $elements, 'group', ts( 'Group(s)' ), '<br />' );
            }
        }
        
        if ( $type & CRM_Contact_Form_GroupTag::TAG ) {
            $elements = array( );
            $tag =& CRM_Core_PseudoConstant::tag  ( );
            foreach ($tag as $id => $name) {
                $elements[] =& HTML_QuickForm::createElement('checkbox', $id, null, $name);
            }
            if ( ! empty( $elements ) ) { 
                $form->addGroup( $elements, 'tag', ts( 'Tag(s)' ), '<br />' );
            }
        }
    }

    static function setDefaults( $id, &$defaults, $type = CRM_Contact_Form_GroupTag::ALL ) {
        $type = (int ) $type; 
        if ( $type & CRM_Contact_Form_GroupTag::GROUP ) { 
            $contactGroup =& CRM_Contact_BAO_GroupContact::getContactGroup( $id, 'Added' );  
            if ( $contactGroup ) {  
                foreach ( $contactGroup as $group ) {  
                    $defaults['group'][$group['group_id']] = 1;  
                } 
            }
        }

        if ( $type & CRM_Contact_Form_GroupTag::TAG ) {
            $contactTag =& CRM_Core_BAO_EntityTag::getTag('civicrm_contact', $id);  
            if ( $contactTag ) {  
                foreach ( $contactTag as $tag ) {  
                    $defaults['tag'][$tag] = 1;  
                }  
            }  
        }

    }

}


?>
