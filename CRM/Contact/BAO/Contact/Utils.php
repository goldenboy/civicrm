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

class CRM_Contact_BAO_Contact_Utils 
{
    /**
     * given a contact type, get the contact image
     *
     * @param string $contact_type
     *
     * @return string
     * @access public
     * @static
     */
    static function getImage( $contactType ) 
    {
        $config =& CRM_Core_Config::singleton( );
        $image = '<img src="' . $config->resourceBase . 'i/contact_';
        switch ( $contactType ) { 
        case 'Individual' : 
            $image .= 'ind.gif" alt="' . ts('Individual') . '" />'; 
            break; 
        case 'Household' : 
            $image .= 'house.png" alt="' . ts('Household') . '" height="16" width="16" />'; 
            break; 
        case 'Organization' : 
            $image .= 'org.gif" alt="' . ts('Organization') . '" height="16" width="18" />'; 
            break; 
        } 
        return $image;
    }
    
    /**
     * function check for mix contact ids(individual+household etc...)
     *
     * @param array $contactIds array of contact ids
     *
     * @return boolen true or false true if mix contact array else fale
     *
     * @access public
     * @static
     */
    public static function checkContactType(&$contactIds)
    {
        if ( empty( $contactIds ) ) {
            return false;
        }

        $idString = implode( ',', $contactIds );
        $query = "
SELECT count( DISTINCT contact_type )
FROM   civicrm_contact
WHERE  id IN ( $idString )
";
        $count = CRM_Core_DAO::singleValueQuery( $query,
                                                 CRM_Core_DAO::$_nullArray );
        return $count > 1 ? true : false;
    }

    /**
     * Generate a checksum for a contactID
     *
     * @param int    $contactID
     * @param int    $ts         timestamp that checksum was generated
     * @param int    $live       life of this checksum in hours
     *
     * @return array ( $cs, $ts, $live )
     * @static
     * @access public
     */
    static function generateChecksum( $contactID, $ts = null, $live = null ) 
    {
        $hash = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                             $contactID, 'hash' );
        if ( ! $hash ) {
            $hash = md5( uniqid( rand( ), true ) );
            CRM_Core_DAO::setFieldValue( 'CRM_Contact_DAO_Contact',
                                         $contactID,
                                         'hash', $hash );
        }

        if ( ! $ts ) {
            $ts = time( );
        }
        
        if ( ! $live ) {
            $live = 24 * 7;
        }

        $cs = md5( "{$hash}_{$contactID}_{$ts}_{$live}" );
        return "{$cs}_{$ts}_{$live}";
        
    }

    /**
     * Make sure the checksum is valid for the passed in contactID
     *
     * @param int    $contactID
     * @param string $cs         checksum to match against
     * @param int    $ts         timestamp that checksum was generated
     * @param int    $live       life of this checksum in hours
     *
     * @return boolean           true if valid, else false
     * @static
     * @access public
     */
    static function validChecksum( $contactID, $inputCheck ) 
    {
        $input =  explode( '_', $inputCheck );
        
        $inputCS = CRM_Utils_Array::value( 0,$input);
        $inputTS = CRM_Utils_Array::value( 1,$input);
        $inputLF = CRM_Utils_Array::value( 2,$input); 

        $check = self::generateChecksum( $contactID, $inputTS, $inputLF );

        if ( $check != $inputCheck ) {
            return false;
        }

        // checksum matches so now check timestamp
        $now = time( );
        return ( $inputTS + ( $inputLF * 60 * 60 ) >= $now ) ? true : false;
    }

    /**
     * Function to get the count of  contact loctions
     * 
     * @param int $contactId contact id
     *
     * @return int $locationCount max locations for the contact
     * @static
     * @access public
     */
    static function maxLocations( $contactId )
    {
        // find the system config related location blocks
        require_once 'CRM/Core/BAO/Preferences.php';
        $locationCount = CRM_Core_BAO_Preferences::value( 'location_count' );
        
        $contactLocations = array( );

        // find number of location blocks for this contact and adjust value accordinly
        // get location type from email
        $query = "
( SELECT location_type_id FROM civicrm_email   WHERE contact_id = {$contactId} )
UNION
( SELECT location_type_id FROM civicrm_phone   WHERE contact_id = {$contactId} )
UNION
( SELECT location_type_id FROM civicrm_im      WHERE contact_id = {$contactId} )
UNION
( SELECT location_type_id FROM civicrm_address WHERE contact_id = {$contactId} )
";
        $dao      = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        $locCount = $dao->N;
        if ( $locCount &&  $locationCount < $locCount ) {
            $locationCount = $locCount;
        }

        return $locationCount;
    }

    /**
     * Create Current employer relationship for a individual
     *
     * @param int    $contactID        contact id of the individual
     * @param string $organization     it can be name or id of organization
     * 
     * @access public
     * @static
     */
    static function createCurrentEmployerRelationship( $contactID, $organization ) 
    {
        $exists = false;
        // if organization id is passed.
        if ( is_numeric( $organization ) ) {
            $organizationId = $organization;
            $exists = true;
        } else {
            require_once "CRM/Contact/DAO/Contact.php";
            $contact =& new CRM_Contact_DAO_Contact( );
            $contact->organization_name = $organization;
        
            $contact->find( );
            $dupeIds = array( );
            while ( $contact->fetch( ) ) {
                $dupeIds[$contact->id] = $contact->id;
            }

            // if duplicates are not found create new organization
            if ( empty($dupeIds) ) {
                //create new organization
                $newOrg = array ( 'contact_type'      => 'Organization',
                                  'organization_name' => $organization );
                
                $org = CRM_Contact_BAO_Contact::add( $newOrg );
                $organizationId = $org->id;
                $exists = true;
            }
        }

        //get the relationship type id of "Employee of"
        $relTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', 'Employee of', 'id', 'name_a_b'  );
        
        //build params for creating relationship
        $relationshipParams['relationship_type_id'] = $relTypeId.'_a_b';
        $relationshipParams['is_active'           ] = 1;
        
        $cid = array('contact' => $contactID );
        
        $currentEmployerParams = array( );
        if ( $exists ) {
            //create relationship
            $relationshipParams['contact_check'][$organizationId] = 1;
            CRM_Contact_BAO_Relationship::create($relationshipParams, $cid);

            // build current employer params
            $currentEmployerParams = array( $contactID => $organizationId );
        } else {
            //if more than one matching organizations found, we
            //add relationships to all those organizations
            foreach ( $dupeIds as $orgId ) {
                $relationshipParams['contact_check'][$orgId] = 1;
                CRM_Contact_BAO_Relationship::create($relationshipParams, $cid);
                
                // build current employer params
                $currentEmployerParams[$contactID] = $orgId;
            }
        }
        
        //create current employer
        self::setCurrentEmployer( $currentEmployerParams );
    }

    /**
     * Function to set current employer id and organization name
     *
     * @param $currentEmployerParams array $currentEmployerParams associated array of contact id and its employer id
     *
     */
    static function setCurrentEmployer( $currentEmployerParams )
    {
        foreach( $currentEmployerParams as $contactId => $orgId ) {
            $query .= "UPDATE civicrm_contact contact_a,civicrm_contact contact_b
SET contact_a.employer_id=contact_b.id, contact_a.organization_name=contact_b.organization_name 
WHERE contact_a.id ={$contactId} AND contact_b.id={$orgId}; ";
        }

        $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );        
    }

    /**
     * Function to build form for related contacts / on behalf of organization.
     * 
     * @param $form              object  invoking Object
     * @param $contactType       string  contact type
     * @param $title             string  fieldset title
     * @param $maxLocationBlocks int     number of location blocks
     * 
     * @static
     *
     */
    static function buildOnBehalfForm( &$form, 
                                       $contactType       = 'Individual', 
                                       $countryDefault    = null,
                                       $stateDefault      = null,
                                       $title             = 'Contact Information',
                                       $contactEditMode   = false,
                                       $maxLocationBlocks = 1 )
    {
        if ($title == 'Contact Information') $title = ts('Contact Information');
        require_once 'CRM/Contact/Form/Location.php';
        $config =& CRM_Core_Config::singleton( );

        $form->assign( 'contact_type' , $contactType );
        $form->assign( 'fieldSetTitle', $title );
        $form->assign( 'contactEditMode' , $contactEditMode );

        $attributes = CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact');

        switch ( $contactType ) {
        case 'Organization':
            $session   =& CRM_Core_Session::singleton( );
            $contactID = $session->get( 'userID' );

            if ( $contactID ) {
                require_once 'CRM/Contact/BAO/Relationship.php';
                $employers = CRM_Contact_BAO_Relationship::getPermissionedEmployer( $contactID );
            }

            if ( !$contactEditMode && $contactID && ( count($employers) >= 1 ) ) {
                $filterAttributes = array( 'dojoType'     => 'dijit.form.FilteringSelect',
                                           'mode'         => 'remote',
                                           'store'        => 'employerStore',
                                           'style'        => 'width:225px; border: 1px solid #cfcfcf;',
                                           'class'        => 'tundra',
                                           'pageSize'     => 10,
                                           'onChange'     => 'loadLocationData(this.getValue())'
                                           );
                $locDataURL = CRM_Utils_System::url( 'civicrm/ajax/permlocation', "cid=", 
                                                     true, null, false );
                $form->assign( 'locDataURL', $locDataURL );
                
                $dataURL = CRM_Utils_System::url( 'civicrm/ajax/employer', 
                                                  "cid=" . $contactID, 
                                                  true, null, false );
                $form->assign( 'employerDataURL', $dataURL );
                
                $form->add('text', 'organization_id', 
                           ts('Select an existing related Organization OR Enter a new one'), $filterAttributes);
                
                $orgOptions     = array( '0' => ts('Create new organization'), 
                                         '1' => ts('Select existing organization') );
                $orgOptionExtra = array( 'onclick' => "showHideByValue('org_option','true','select_org','table-row','radio',true);showHideByValue('org_option','true','create_org','table-row','radio',false);");
                $form->addRadio( 'org_option', ts('options'),  $orgOptions, $orgOptionExtra );
                $form->assign( 'relatedOrganizationFound', true );
            }
            $form->add('text', 'organization_name', ts('Organization Name'), $attributes['organization_name']);
            break;
        case 'Household':
            $form->add('text', 'household_name', ts('Household Name'), 
                       $attributes['household_name']);
            break;
        default:
            // individual
            $form->addElement('select', 'prefix_id', ts('Prefix'), 
                              array('' => ts('- prefix -')) + CRM_Core_PseudoConstant::individualPrefix());
            $form->addElement('text',   'first_name',  ts('First Name'),  
                              $attributes['first_name'] );
            $form->addElement('text',   'middle_name', ts('Middle Name'), 
                              $attributes['middle_name'] );
            $form->addElement('text',   'last_name',   ts('Last Name'),   
                              $attributes['last_name'] );
            $form->addElement('select', 'suffix_id',   ts('Suffix'), 
                              array('' => ts('- suffix -')) + CRM_Core_PseudoConstant::individualSuffix());

        }

        // add country state selector using new hs-widget method.
        $form->assign( 'dojoIncludes', "dojo.require('civicrm.HierSelect');" );
        $attributes = array( 'dojoType'     => 'civicrm.HierSelect',
                             'url1'         => CRM_Utils_System::url( $config->resourceBase . 'bin/ajax.php', 
                                                                      'return=countries', false ),
                             'url2'         => CRM_Utils_System::url( $config->resourceBase . 'bin/ajax.php', 
                                                                      'return=states', false ),
                             'default1'     => $countryDefault,
                             'default2'     => $stateDefault,
                             'firstInList'  => "true",
                             );
        $form->add( 'text', "location[1][address][country_state]", ts( 'Country - State' ), $attributes );

        // remove country & state from address sequence since address.tpl uses old approach 
        // and not the new hier-select widget approach / method. So we will add them separately 
        // keeping in mind whether they are found in addressSequence / preferences. 

        $addressSequence = $config->addressSequence();

        $key = array_search( 'country', $addressSequence);
        if ( $key ) {
            $form->assign( 'addressSequenceCountry', true );
        }
        unset($addressSequence[$key]);
        
        $key = array_search( 'state_province', $addressSequence);
        if ( $key ) {
            $form->assign( 'addressSequenceState', true );
        }
        unset($addressSequence[$key]);

        $form->assign( 'addressSequence', array_fill_keys($addressSequence, 1) );

        //Primary Phone 
        $form->addElement('text',
                          "location[1][phone][1][phone]", 
                          ts('Primary Phone'),
                          CRM_Core_DAO::getAttribute('CRM_Core_DAO_Phone',
                                                     'phone'));
        //Primary Email
        $form->addElement('text', 
                          "location[1][email][1][email]",
                          ts('Primary Email'),
                          CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email',
                                                     'email'));
        //build the address block
        $location   = array(); 
        CRM_Contact_Form_Address::buildAddressBlock($form, $location, 1 );
    }

}
