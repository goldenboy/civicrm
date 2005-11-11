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

require_once 'CRM/Core/Controller/Simple.php';
require_once 'CRM/Core/DAO/UFGroup.php';
require_once 'CRM/Core/DAO/UFField.php';
require_once 'CRM/Contact/BAO/Contact.php';

/**
 *
 */
class CRM_Core_BAO_UFGroup extends CRM_Core_DAO_UFGroup {
    const 
        PUBLIC_VISIBILITY   = 1,
        ADMIN_VISIBILITY    = 2,
        LISTINGS_VISIBILITY = 4;

    /**
     * cache the match clause used in this transaction
     *
     * @var string
     */
    static $_matchFields = null;

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_UFGroup object
     * @access public
     * @static
     */
    static function retrieve(&$params, &$defaults)
    {
        return CRM_Core_DAO::commonRetrieve( 'CRM_Core_DAO_UFGroup', $params, $defaults );
    }
    
    /**
     * Get the form title.
     *
     * @param int $id id of uf_form
     * @return string title
     *
     * @access public
     * @static
     *
     */
    public static function getTitle( $id )
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $id, 'title' );
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive($id, $is_active) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_UFGroup', $id, 'is_active', $is_active );
    }

    /**
     * get all the registration fields
     *
     * @param int $action   what action are we doing
     *
     * @return array the fields that are needed for registration
     * @static
     * @access public
     */
    static function getRegistrationFields( $action ) {
        $ufGroups =& CRM_Core_PseudoConstant::ufGroup( );

        $fields = array( );
        foreach ( $ufGroups as $id => $title ) {
            $subset = self::getFields( $id, true, $action );

            // we do not allow duplicates. the first field is the winner
            foreach ( $subset as $name => $field ) {
                if ( ! CRM_Utils_Array::value( $name, $fields ) ) {
                    $fields[$name] = $field;
                }
            }
        }
        return $fields;
    }

    /** 
     * get all the listing fields 
     * 
     * @param int  $action            what action are we doing 
     * @param int  $visibility        visibility of fields we are interested in
     * @param bool $considerSelector  whether to consider the in_selector parameter
     * 
     * @return array the fields that are listings related
     * @static 
     * @access public 
     */ 
    static function getListingFields( $action, $visibility, $considerSelector = false ) {
        $ufGroups =& CRM_Core_PseudoConstant::ufGroup( ); 
 
        $fields = array( ); 
        foreach ( $ufGroups as $id => $title ) { 
            $subset = self::getFields( $id, false, $action, false, $visibility );
            if ($considerSelector) {
                // drop the fields not meant for the selector
                foreach ($subset as $name => $field) {
                    if (!$field['in_selector']) unset($subset[$name]);
                }
            }
            $fields = array_merge( $fields, $subset ); 
        } 
        return $fields; 
    } 

    /**
     * get the title of the group which contributes the largest number of fields
     * to the registered entries
     *
     * @return string title of the registered group
     * @static
     * @access public
     */
    static function getRegisteredTitle( ) {
        $ufGroups =& CRM_Core_PseudoConstant::ufGroup( ); 

        $size  = -1;
        $title = null;
        foreach ( $ufGroups as $id => $value ) { 
            $subset = self::getFields( $id, true, $action ); 
            if ( count( $subset ) > $size ) {
                $size  = count( $subset );
                $title = $value;
            }
        }
        return $title;
    }

    /**
     * get all the fields that belong to the group with the named title
     *
     * @param int $id       the id of the UF group
     * @param int $register are we interested in registration fields
     * @param int $action   what action are we doing
     * @param int $match    are we interested in match fields
     * @param int $visibility visibility of fields we are interested in
     *
     * @return array the fields that belong to this title
     * @static
     * @access public
     */
    static function getFields( $id, $register = false, $action = null, $match = false, $visibility = null ) {
        $group =& new CRM_Core_DAO_UFGroup( );

        $group->id = $id;
        if ( $group->find( true ) ) {
            $field =& new CRM_Core_DAO_UFField( );
            $field->uf_group_id = $group->id;
            $field->is_active   = 1;
            if ( $register ) {
                $field->is_registration = 1;
            }
            if ( $match ) {
                $field->is_match = 1;
            }
            if ( $visibility ) {
                $clause = array( );
                if ( $visibility & self::PUBLIC_VISIBILITY ) {
                    $clause[] = 'visibility = "Public User Pages"';
                }
                if ( $visibility & self::ADMIN_VISIBILITY ) {
                    $clause[] = 'visibility = "User and User Admin Only"';
                }
                if ( $visibility & self::LISTINGS_VISIBILITY ) {
                    $clause[] = 'visibility = "Public User Pages and Listings"';
                }
                $field->whereAdd( implode( ' OR ' , $clause ) );
            }

            $field->orderBy('weight', 'field_name');
            $field->find( );
            $fields = array( );
            $importableFields =& CRM_Contact_BAO_Contact::importableFields( );
            $importableFields['group']['title'] = ts('Group(s)');
            $importableFields['group']['where'] = null;
            $importableFields['tag'  ]['title'] = ts('Tag(s)');
            $importableFields['tag'  ]['where'] = null;

            while ( $field->fetch( ) ) {
                if ( ( $field->is_view && $action == CRM_Core_Action::VIEW ) || ! $field->is_view ) {
                    $name = $field->field_name;
                    if ($field->location_type_id) {
                        $name .= '-'.$field->location_type_id;
                    }
                    if ($field->phone_type) {
                        $name .= '-'.$field->phone_type;
                    }
                    
                    $fields[$name] =
                        array('name'             => $name,
                              'groupTitle'       => $group->title,
                              'groupHelpPre'     => $group->help_pre,
                              'groupHelpPost'    => $group->help_post,
                              'title'            => $importableFields[$field->field_name]['title'],
                              'where'            => $importableFields[$field->field_name]['where'],
                              'attributes'       => CRM_Core_DAO::makeAttribute( $importableFields[$field->field_name] ),
                              'is_required'      => $field->is_required,
                              'is_view'          => $field->is_view,
                              'is_match'         => $field->is_match,
                              'weight'           => $field->weight,
                              'help_post'        => $field->help_post,
                              'visibility'       => $field->visibility,
                              'in_selector'      => $field->in_selector,
                              'default'          => $field->default_value,
                              'rule'             => CRM_Utils_Array::value( 'rule', $importableFields[$field->field_name] ),
                              'options_per_line' => $importableFields[$field->field_name]['options_per_line'],
                              'location_type_id' => $field->location_type_id,
                              'phone_type'       => $field->phone_type,
                              );
                }
            }
            return $fields;
        }
        return null;
    }

    /**
     * check the data validity
     *
     * @param int    $userID    the user id that we are actually editing
     * @param string $title     the title of the group we are interested in
     * @pram  boolean $register is this the registrtion form
     * @param int    $action  the action of the form
     *
     * @return boolean   true if form is valid
     * @static
     * @access public
     */
    static function isValid( $userID, $title, $register = false, $action = null ) {
        $session =& CRM_Core_Session::singleton( );

        if ( $register ) {
            $controller =& new CRM_Core_Controller_Simple( 'CRM_Profile_Form_Dynamic', ts('Dynamic Form Creator'), $action );
            $controller->set( 'gid'     , $group->id );
            $controller->set( 'id'      , $userID );
            $controller->set( 'register', 1 );
            $controller->process( );
            return $controller->validate( );
        } else {
            // make sure we have a valid group
            $group =& new CRM_Core_DAO_UFGroup( );
            
            $group->title     = $title;
            $group->domain_id = CRM_Core_Config::domainID( );
            
            if ( $group->find( true ) && $userID ) {
                require_once 'CRM/Core/Controller/Simple.php';
                $controller =& new CRM_Core_Controller_Simple( 'CRM_Profile_Form_Dynamic', ts('Dynamic Form Creator'), $action );
                $controller->set( 'gid'     , $group->id );
                $controller->set( 'id'      , $userID );
                $controller->set( 'register', 0 );
                $controller->process( );
                return $controller->validate( );
            }
            return true;
        }
    }

    /**
     * get the html for the form that represents this particular group
     *
     * @param int     $userID   the user id that we are actually editing
     * @param string  $title    the title of the group we are interested in
     * @param int     $action   the action of the form
     * @param boolean $register is this the registration form
     * @param boolean $reset    should we reset the form?
     *
     * @return string       the html for the form
     * @static
     * @access public
     */
    static function getEditHTML( $userID, $title, $action = null, $register = false, $reset = false ) {
        $session =& CRM_Core_Session::singleton( );

        if ( $register ) {
            $controller =& new CRM_Core_Controller_Simple( 'CRM_Profile_Form_Dynamic', ts('Dynamic Form Creator'), $action );
            if ( $reset ) {
                // hack to make sure we do not process this form
                unset( $_POST['_qf_default'] );
                unset( $_REQUEST['_qf_default'] );
                $controller->reset( );
            }
            $controller->set( 'id'      , $userID );
            $controller->set( 'register', 1 );
            $controller->process( );
            $controller->setEmbedded( true );
            $controller->run( );

            $template =& CRM_Core_Smarty::singleton( );
            return trim( $template->fetch( 'CRM/Profile/Form/Dynamic.tpl' ) );
        } else {
            // make sure we have a valid group
            $group =& new CRM_Core_DAO_UFGroup( );
            
            $group->title     = $title;
            $group->domain_id = CRM_Core_Config::domainID( );
            
            if ( $group->find( true ) && $userID ) {
                require_once 'CRM/Core/Controller/Simple.php';
                $controller =& new CRM_Core_Controller_Simple( 'CRM_Profile_Form_Dynamic', ts('Dynamic Form Creator'), $action );
                if ( $reset ) {
                    $controller->reset( );
                }
                $controller->set( 'gid'     , $group->id );
                $controller->set( 'id'      , $userID );
                $controller->set( 'register', 0 );
                $controller->process( );
                $controller->setEmbedded( true );
                $controller->run( );
                
                $template =& CRM_Core_Smarty::singleton( );
                return trim( $template->fetch( 'CRM/Profile/Form/Dynamic.tpl' ) );
            }
        }
        return '';
    }

    /**
     * Get the UF match clause 
     *
     * @param array   $params  the list of values to be used in the where clause
     * @param boolean $flatten should we flatten the input params
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     *
     * @return string the where clause to include in a sql query
     * @static
     * @access public
     */
    static function getMatchClause( $params, &$tables, $flatten = false ) {
        if ( $flatten && is_array( $params['location'] ) ) {
            $params['email'] = array();
            $params['phone'] = array();
            $params['im']    = array();
            
            foreach($params['location'] as $loc) {
                foreach (array('email', 'phone', 'im') as $key) {
                    if (is_array($loc[$key])) {
                        foreach ($loc[$key] as $value) {
                            if ( ! empty( $value[$key] ) ) {
                                $value[$key] = strtolower( $value[$key] );
                                $params[$key][] = 
                                    '"' . addslashes($value[$key]) . '"';
                            }
                        }
                    }
                }
            }
            
            foreach (array('email', 'phone', 'im') as $key) {
                if (count($params[$key]) == 0) {
                    unset($params[$key]);
                }
            }
            
            foreach ( array( 'street_address', 'supplemental_address_1', 'supplemental_address_2',
                             'state_province_id', 'postal_code', 'country_id' ) as $fld ) {
                if ( ! empty( $params['location'][1]['address'][$fld] ) ) {
                    $params[$fld] = $params['location'][1]['address'][$fld];
                }
            }
        }
        
        if ( ! self::$_matchFields ) {
            $ufGroups =& CRM_Core_PseudoConstant::ufGroup( );

            self::$_matchFields = array( );
            foreach ( $ufGroups as $id => $title ) {
                $subset = self::getFields( $id, false, CRM_Core_Action::VIEW, true );
                self::$_matchFields = array_merge( self::$_matchFields, $subset );
            }
        }

        if ( empty( self::$_matchFields ) ) {
            return null;
        }
        require_once 'CRM/Contact/BAO/Query.php';
        return CRM_Contact_BAO_Query::getWhereClause( $params, self::$_matchFields, $tables, true );
    }

    /**
     * searches for a contact in the db with similar attributes
     *
     * @param array $params the list of values to be used in the where clause
     * @param int    $id          the current contact id (hence excluded from matching)
     * @param boolean $flatten should we flatten the input params
     *
     * @return contact_id if found, null otherwise
     * @access public
     * @static
     */
    public static function findContact( &$params, $id = null, $flatten = false ) {
        $tables = array( );
        $clause = self::getMatchClause( $params, $tables, $flatten );
        $emptyClause = 'civicrm_contact.domain_id = ' . CRM_Core_Config::domainID( );
        if ( ! $clause || trim( $clause ) === trim( $emptyClause ) ) {
            return null;
        }
        return CRM_Contact_BAO_Contact::matchContact( $clause, $tables, $id );
    }

    /**
     * Given a contact id and a field set, return the values from the db
     * for this contact
     *
     * @param int     $id       the contact id
     * @param array   $fields   the profile fields of interest
     * @param array   $values   the values for the above fields

     * @return void
     * @access public
     * @static
     */
    public static function getValues( $id, &$fields, &$values ) {
        $returnProperties = array( );
        foreach ( $fields as $name => $dontCare ) {
            $returnProperties[$name] = 1;
        }
        $options  = array( );
        $options1 = array( );
        $contact1 = CRM_Contact_BAO_Contact::contactDetails( $id, $options1, $returnProperties );
        $record['id'] = $record['contact_id'] = $id;
        $contact      = CRM_Contact_BAO_Contact::retrieve( $record , $options, $ids );
        if ( ! $contact ) {
            return;
        }
        //print_r($contact);
        //print_r($fields);
        //print_r($options);
        $params = array( );
        //###########################

        foreach ($fields as $name => $field ) {
            $index = $field['title'];
            if (substr($field['name'],0,5) == 'phone') {
                $index .= ' - '.$field['phone_type'];
            } 
            foreach ($options as $key => $val) {
                $nameValue = explode('-', $name);
                if (is_numeric($nameValue[1])) {
                    if (is_array($val)) {
                        foreach ($val as $key1 => $val1) {
                            if (is_array($val1)) {
                                if ( $val1['location_type_id'] == $nameValue[1] ) {
                                    //print_r($val1);
                                    foreach ($val1 as $key2 => $var) {
                                        if (is_array($var)) {
                                            foreach ($var as $k1 => $var1) {
                                                if (is_array($var1)) {
                                                    //set the phone values
                                                    if ($nameValue[0] == 'phone' && $nameValue[2] == $var1['phone_type']) {
                                                        $values[$index] = $var1['phone'];
                                                    }
                                                    //set the im values
                                                    if ($nameValue[0] == 'im') {
                                                        $values[$index] = $var1['name'];
                                                    }
                                                    //set the email values
                                                    if ($nameValue[0] == 'email') {
                                                        $values[$index] = $var1['email'];
                                                    }
                                                } else {
                                                    //set the address values
                                                    if ($nameValue[0] === 'country'  && substr($k1,0,7) === 'country') {
                                                        $country = array('country_id' => $var1 );
                                                        CRM_Contact_BAO_Contact::lookupValue( $country, 'country', CRM_Core_PseudoConstant::country(), false);
                                                        $values[$index] = $country['country'];
                                                        $params[$index] = $var1;
                                                    } else if ($nameValue[0] === 'state_province' && substr($k1,0,14)  === 'state_province' ) {
                                                        $stateProvince = array('state_province_id' => $var1 );
                                                        CRM_Contact_BAO_Contact::lookupValue( $stateProvince, 'state_province', CRM_Core_PseudoConstant::stateProvince(), false);
                                                        $values[$index] = $stateProvince['state_province'];
                                                        $params[$index] = $var1;
                                                    } else if ( $nameValue[0] == $k1 ) {
                                                        $values[$index] = $var1;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // echo $name . "=============" .$key . "<br>";
                  
                    if ( $name == 'group' ) {
                        $groups = CRM_Contact_BAO_GroupContact::getContactGroup( $id, 'Added' );
                        $title = array( );
                        $ids   = array( );
                        foreach ( $groups as $g ) {
                            if ( $g['visibility'] != 'User and User Admin Only' ) {
                                $title[] = $g['title'];
                                if ( $g['visibility'] == 'Public User Pages and Listings' ) {
                                    $ids[] = $g['group_id'];
                                }
                            }
                        }
                        $values[$index] = implode( ', ', $title );
                        $params[$index] = implode( ',' , $ids   );
                    } else if ( $name == 'tag' ) {
                        require_once 'CRM/Core/BAO/EntityTag.php';
                        $entityTags =& CRM_Core_BAO_EntityTag::getTag('civicrm_contact', $id );
                        $allTags    =& CRM_Core_PseudoConstant::tag();
                        $title = array( );
                        foreach ( $entityTags as $tagId ) {
                            $title[] = $allTags[$tagId];
                        }
                        $values[$index] = implode( ', ', $title );
                        $params[$index] = implode( ',' , $entityTags );
                    } else if ($name == 'individual_prefix' && $key == 'prefix_id') {
                        $prefix = array('prefix_id' => $val );
                        CRM_Contact_BAO_Contact::lookupValue( $prefix, 'prefix', CRM_Core_PseudoConstant::individualPrefix(), false);
                        $values[$index] = $prefix['prefix'];
                        $params[$index] = $val;
                    } else if ($name == 'individual_suffix' && $key == 'suffix_id') {                         
                        $suffix = array('suffix_id' => $val );
                        CRM_Contact_BAO_Contact::lookupValue( $suffix, 'suffix', CRM_Core_PseudoConstant::individualSuffix(), false);
                        $values[$index] = $suffix['suffix'];
                        $params[$index] = $val;
                    } else if ($name == 'gender' && $key =='gender_id') { 
                        $gender = array('gender_id' => $val );
                        CRM_Contact_BAO_Contact::lookupValue( $gender, 'gender', CRM_Core_PseudoConstant::gender(), false);
                        $values[$index] = $gender['gender'];
                        $params[$index] = $val;
                    } else if ($name == 'birth_date' && $key == 'birth_date') {
                        $values[$index] = CRM_Utils_Date::format( $val, '-' );
                        //$params[$index] = $val;
                    } else if (substr($name, 0, 6) === 'custom') {
                        require_once 'CRM/Core/BAO/CustomField.php';
                        if ( $cfID = CRM_Core_BAO_CustomField::getKeyID($name)) {
                            $params[$index] = $contact1->$name;
                            $values[$index] = CRM_Core_BAO_CustomField::getDisplayValue( $contact1->$name, $cfID, $options1);
                        }
                    } else if ($name === $key) {
                        $values[$index] = $val;
                    }
                }
            }

            if ( $field['visibility'] == "Public User Pages and Listings" &&
                 CRM_Utils_System::checkPermission( 'access CiviCRM Profile Listings' ) ) {

                if ( CRM_Utils_Array::value( $index, $params ) === null ) {
                    $params[$index] = $values[$index];
                }
                if ( empty( $params[$index] ) ) {
                    continue;
                }
                $fieldName = $field['name'];
                $url = CRM_Utils_System::url( 'civicrm/profile',
                                              'reset=1&' . 
                                              urlencode( $fieldName ) .
                                              '=' .
                                              urlencode( $params[$index] ) );
                if ( ! empty( $values[$index] ) ) {
                    $values[$index] = '<a href="' . $url . '">' . $values[$index] . '</a>';
                }
            }


        }
        
    }

     /**
     * Delete the profile Group.
     *
     * @param int id profile Id 
     * 
     * @return void
     *
     * @access public
     * @static
     *
     */

    public static function del($id) { 
        //check wheter this group contains  any profile fields
        $profileField = & new CRM_Core_DAO_UFField();
        $profileField->uf_group_id = $id;
        $profileField->find();
        while($profileField->fetch()) {
            return false;
            
        }
        //delete profile group
        $group = & new CRM_Core_DAO_UFGroup();
        $group->id = $id; 
        $group->delete();
        return true;
    }

    /**
     * build a form for the given UF group
     *
     * @param int           $id       the group id
     * @param CRM_Core_Form $form     the form element
     * @param int           $action   the form action
     * @return void
     * @static
     * @access public
     */
    public static function buildQuickForm( $id, &$form, $action ) {
        $fields =& CRM_Core_BAO_UFGroup::getFields( $id, false, $action );

        foreach ( $fields as $name => $field ) {
            $required = $field['is_required'];

            if ( substr($field['name'],0,14) === 'state_province' ) {

                $form->add('select', $name, $field['title'],
                           array('' => ts('- select -')) + CRM_Core_PseudoConstant::stateProvince(), $required);
            } else if ( substr($field['name'],0,7) === 'country' ) {
                $form->add('select', $name, $field['title'], 
                           array('' => ts('- select -')) + CRM_Core_PseudoConstant::country(), $required);
            } else if ( $field['name'] === 'birth_date' ) {  
                $form->add('date', $field['name'], $field['title'], CRM_Core_SelectValues::date('birth') );  
            } else if ( $field['name'] === 'gender' ) {  
                $genderOptions = array( );   
                $gender = CRM_Core_PseudoConstant::gender();   
                foreach ($gender as $key => $var) {   
                    $genderOptions[$key] = HTML_QuickForm::createElement('radio', null, ts('Gender'), $var, $key);   
                }   
                $form->addGroup($genderOptions, $field['name'], $field['title'] );  
            } else if ( $field['name'] === 'individual_prefix' ){
                $form->add('select', $name, $field['title'], 
                           array('' => ts('- select -')) + CRM_Core_PseudoConstant::individualPrefix());
            } else if ( $field['name'] === 'individual_suffix' ){
                $form->add('select', $name, $field['title'], 
                           array('' => ts('- select -')) + CRM_Core_PseudoConstant::individualSuffix());
            } else if ( $field['name'] === 'group' ) {
                require_once 'CRM/Contact/Form/GroupTag.php';
                CRM_Contact_Form_GroupTag::buildGroupTagBlock($form, 0,
                                                              CRM_Contact_Form_GroupTag::GROUP);
            } else if ( $field['name'] === 'tag' ) {
                require_once 'CRM/Contact/Form/GroupTag.php';
                CRM_Contact_Form_GroupTag::buildGroupTagBlock($form, 0,
                                                              CRM_Contact_Form_GroupTag::TAG );
            } else if (substr($field['name'], 0, 6) === 'custom') {
                $customFieldID = CRM_Core_BAO_CustomField::getKeyID($field['name']);
                CRM_Core_BAO_CustomField::addQuickFormElement($form, $name, $customFieldID, $inactiveNeeded, false);
                if ($required) {
                    $form->addRule($elementName, ts('%1 is a required field.', array(1 => $field['title'])) , 'required');
                }
            } else if  ( substr($field['name'],0,5) === 'phone' ) {
                $form->add('text', $name, $field['title'] . " - " . $field['phone_type'], $field['attributes'], $required);
            } else {
                $form->add('text', $name, $field['title'], $field['attributes'], $required );
            }

            if ( $field['rule'] ) {
                $form->addRule( $name, ts( 'Please enter a valid %1', array( 1 => $field['title'] ) ), $field['rule'] );
            }
        }
    }

}

?>
