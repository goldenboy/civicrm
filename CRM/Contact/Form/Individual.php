<?php
/**
 +----------------------------------------------------------------------+
 | CiviCRM version 1.0                                                  |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                    |
 +----------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                      |
 |                                                                      |
 | CiviCRM is free software; you can redistribute it and/or modify it   |
 | under the terms of the Affero General Public License Version 1,      |
 | March 2002.                                                          |
 |                                                                      |
 | CiviCRM is distributed in the hope that it will be useful, but       |
 | WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
 | See the Affero General Public License for more details at            |
 | http://www.affero.org/oagpl.html                                     |
 |                                                                      |
 | A copy of the Affero General Public License has been been            |
 | distributed along with this program (affero_gpl.txt)                 |
 +----------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

require_once 'CRM/Form.php';
require_once 'CRM/SelectValues.php';
require_once 'CRM/ShowHideBlocks.php';

/**
 * Auxilary class to provide support to the Contact Form class. Does this by implementing
 * a small set of static methods
 *
 */
class CRM_Contact_Form_Individual {
    /**
     * This function provides the HTML form elements that are specific to the Individual Contact Type
     * 
     * @access public
     * @return None 
     */
    public function buildQuickForm( $form ) 
    {
        // prefix
        $form->addElement('select', 'prefix', null, CRM_SelectValues::$prefixName);

        $attributes = CRM_DAO::getAttribute('CRM_Contact_DAO_Individual');

        // first_name
        $form->addElement('text', 'first_name', 'First Name', $attributes['first_name'] );
        
        // last_name
        $form->addElement('text', 'last_name', 'Last Name', $attributes['last_name'] );
        
        // suffix
        $form->addElement('select', 'suffix', null, CRM_SelectValues::$suffixName);
        
        // greeting type
        $form->addElement('select', 'greeting_type', 'Greeting type :', CRM_SelectValues::$greeting);
        
        // job title
        $form->addElement('text', 'job_title', 'Job title :', $attributes['job_title']);
        
        // radio button for gender
        $genderOptions = array( );
        $genderOptions[] = HTML_QuickForm::createElement('radio', 'gender', 'Gender', 'Female', 'Female');
        $genderOptions[] = HTML_QuickForm::createElement('radio', 'gender', 'Gender', 'Male', 'Male');
        $genderOptions[] = HTML_QuickForm::createElement('radio', 'gender', 'Gender', 'Transgender','Transgender');
        $form->addGroup( $genderOptions, 'gender', 'Gender' );
        
        $form->addElement('checkbox', 'is_deceased', null, 'Contact is deceased');
        
        $form->addElement('date', 'birth_date', 'Date of birth', CRM_SelectValues::$date);
        $form->addRule('birth_date', 'Select a valid date.', 'qfDate' );

        CRM_ShowHideBlocks::links( $this, 'demographics', '[+] show demographics' , '[-] hide demographics'  );
    }

    static function formRule( &$fields ) {
        $errors = array( );
        
        $primaryEmail = null;

        // make sure that at least one field is marked is_primary
        if ( array_key_exists( 'location', $fields ) && is_array( $fields['location'] ) ) {
            $locationKeys = array_keys( $fields['location']);
            $isPrimary = false;
            foreach ( $locationKeys as $locationId ) {
                if ( array_key_exists( 'is_primary', $fields['location'][$locationId] ) ) {
                    if ( $fields['location'][$locationId]['is_primary'] ) {
                        if ( $isPrimary ) {
                            $errors["location[$locationId][is_primary]"] =
                                array( 'label'   => '', 
                                       'message' => "Only one location can be marked as primary." );
                        }
                        $isPrimary = true;
                    }

                    // only harvest email from the primary locations
                    if ( array_key_exists( 'email', $fields['location'][$locationId] ) &&
                         is_array( $fields['location'][$locationId]['email'] )         &&
                         empty( $primaryEmail ) ) {
                        foreach ( $fields['location'][$locationId]['email'] as $idx => &$email ) {
                            if ( array_key_exists( 'email', $email ) ) {
                                $primaryEmail = $email['email'];
                                break;
                            }
                        }
                    }
                }
            }
            
            if ( ! $isPrimary ) {
                $errors["location[1][is_primary]"] =
                    array( 'label'   => '',
                           'message' => "One location needs to be marked as primary." );
            }

        }
        
        // make sure that firstName and lastName or a primary email is set
        if (! ( (CRM_Array::value( 'first_name', $fields ) && 
                 CRM_Array::value( 'last_name' , $fields )    ) ||
                !empty( $primaryEmail ) ) ) {
            $errors['first_name'] = array( 'label'   => '',
                                           'message' => "First Name and Last Name OR an email in the Primary Location should be set." );
        }
        
        // add code to make sure that the uniqueness criteria is satisfied

        if ( ! empty( $errors ) ) {
            return $errors;
        }
        return true;
    }

}


    
?>