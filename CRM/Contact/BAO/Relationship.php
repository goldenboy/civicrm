<?php
/*
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
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

require_once 'CRM/Contact/DAO/Relationship.php';
require_once 'CRM/Contact/DAO/RelationshipType.php';
require_once 'CRM/Contact/BAO/Block.php';

class CRM_Contact_BAO_Relationship extends CRM_Contact_DAO_Relationship {
    
    /**
     * const the max number of relationships we display at any given time
     * @var int
     */
    const MAX_RELATIONSHIPS = 10;

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }

   
    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params        input parameters to find object
     * @param array $values        output values of the object
     * @param array $ids           the array that holds all the db ids
     * @param int   $numRelationships      the maximum number of relationships to return (0 if all)
     *
     * @return void
     * @access public
     * @static
     */
    static function getValues( &$params, &$values, &$ids, $numRelationships = self::MAX_RELATIONSHIPS ) {
        $relationship = new CRM_Contact_BAO_Relationship( );
        
        $str_select1 = $str_from1 = $str_where1 = $str_select2 = $str_from2 = $str_where2 = $str_order = $str_limit = '';
        
        $str_select1 = "( SELECT crm_relationship.id as crm_relationship_id,
                              crm_contact.sort_name as sort_name,
                              crm_address.street_address as street_address,
                              crm_address.city as city,
                              crm_address.postal_code as postal_code,
                              crm_state_province.abbreviation as state,
                              crm_country.name as country,
                              crm_email.email as email,
                              crm_phone.phone as phone,
                              crm_contact.id as crm_contact_id,
                              crm_contact.contact_type as contact_type,
                              crm_relationship.contact_id_b as contact_id_b,
                              crm_relationship.contact_id_a as contact_id_a,
                              crm_relationship_type.name_b_a as relation";

        $str_from1 = " FROM crm_contact 
                        LEFT OUTER JOIN crm_location ON (crm_contact.id = crm_location.contact_id AND crm_location.is_primary = 1)
                        LEFT OUTER JOIN crm_address ON (crm_location.id = crm_address.location_id )
                        LEFT OUTER JOIN crm_phone ON (crm_location.id = crm_phone.location_id AND crm_phone.is_primary = 1)
                        LEFT OUTER JOIN crm_email ON (crm_location.id = crm_email.location_id AND crm_email.is_primary = 1)
                        LEFT OUTER JOIN crm_state_province ON (crm_address.state_province_id = crm_state_province.id)
                        LEFT OUTER JOIN crm_country ON (crm_address.country_id = crm_country.id),
                        crm_relationship,crm_relationship_type
                       ";

        // add where clause 
        $str_where1 = " WHERE crm_relationship.relationship_type_id = crm_relationship_type.id 
                         AND crm_relationship.contact_id_b = ".$params['contact_id']." 
                         AND crm_relationship.contact_id_a = crm_contact.id )
                         UNION ";

        $str_select2 = " (SELECT crm_relationship.id as crm_relationship_id,
                              crm_contact.sort_name as sort_name,
                              crm_address.street_address as street_address,
                              crm_address.city as city,
                              crm_address.postal_code as postal_code,
                              crm_state_province.abbreviation as state,
                              crm_country.name as country,
                              crm_email.email as email,
                              crm_phone.phone as phone,
                              crm_contact.id as crm_contact_id,
                              crm_contact.contact_type as contact_type,
                              crm_relationship.contact_id_b as contact_id_b,
                              crm_relationship.contact_id_a as contact_id_a,
                              crm_relationship_type.name_a_b as relation";

        $str_from2 = " FROM crm_contact 
                        LEFT OUTER JOIN crm_location ON (crm_contact.id = crm_location.contact_id AND crm_location.is_primary = 1)
                        LEFT OUTER JOIN crm_address ON (crm_location.id = crm_address.location_id )
                        LEFT OUTER JOIN crm_phone ON (crm_location.id = crm_phone.location_id AND crm_phone.is_primary = 1)
                        LEFT OUTER JOIN crm_email ON (crm_location.id = crm_email.location_id AND crm_email.is_primary = 1)
                        LEFT OUTER JOIN crm_state_province ON (crm_address.state_province_id = crm_state_province.id)
                        LEFT OUTER JOIN crm_country ON (crm_address.country_id = crm_country.id),
                      crm_relationship,crm_relationship_type
                       ";

        // add where clause 
        $str_where2 = " WHERE crm_relationship.relationship_type_id = crm_relationship_type.id 
                         AND crm_relationship.contact_id_a = ".$params['contact_id']." 
                         AND crm_relationship.contact_id_b = crm_contact.id)";


        // $str_order1 = " GROUP BY crm_contact.id ORDER BY crm_contact.id ";
        //$str_limit1 = " LIMIT 0, $numRelationships ";

        // building the query string
        $query_string = $str_select1.$str_from1.$str_where1.$str_select2.$str_from2.$str_where2;
        $relationship->query($query_string);
    
   
        $relationships       = array( );
        $ids['relationship'] = array( );
        $count = 0;
        while ( $relationship->fetch() ) {
            
            $values['relationship'][$relationship->crm_relationship_id] = array();
            $ids['relationship'][] = $relationship->crm_relationship_id;
            
            $values['relationship'][$relationship->crm_relationship_id]['id'] = $relationship->crm_relationship_id;
            $values['relationship'][$relationship->crm_relationship_id]['cid'] = $relationship->crm_contact_id;
            $values['relationship'][$relationship->crm_relationship_id]['relation'] = $relationship->relation;
            $values['relationship'][$relationship->crm_relationship_id]['name'] = $relationship->sort_name;
            $values['relationship'][$relationship->crm_relationship_id]['email'] = $relationship->email;
            $values['relationship'][$relationship->crm_relationship_id]['phone'] = $relationship->phone;
            $values['relationship'][$relationship->crm_relationship_id]['city'] = $relationship->city;
            $values['relationship'][$relationship->crm_relationship_id]['state'] = $relationship->state;
            
            $relationship->storeValues( $values['relationship'][$relationship->crm_relationship_id] );
            
            $relationships = $relationship;
            $count++;
        }

        // get the total count of relationships
        if ($count > 0) $values['relationshipsCount'] = $count;

        //   print_r($relationships);
        return $relationships;
    }


  /**
   * takes an associative array and creates a relationship object 
   *
   *
   * @param array $params (reference ) an assoc array of name/value pairs
   * @param array $ids    the array that holds all the db ids
   *
   * @return object CRM_Contact_BAO_Relationship object 
   * @access public
   * @static
   */
    static function create( &$params, &$ids ) {
      
        $dataExists = self::dataExists( $params );
        if ( ! $dataExists ) {
            return null;
        }
        
        CRM_DAO::transaction( 'BEGIN' );
        
        foreach ( $params['contact_check'] as $lng_key => $value) {
            $relationship = self::add( $params, $ids, $lng_key );
        }
        
        CRM_DAO::transaction( 'COMMIT' );

        return $relationship;
    }


    /**
     * takes an associative array and creates a note object
     *
     * the function extract all the params it needs to initialize the create a
     * note object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     * @param integer $lngContactId  this is contact id for adding relationship
     * @param array $ids    the array that holds all the db ids  
     * 
     * @return object CRM_Contact_BAO_Relationship 
     * @access public
     * @static
     */
    static function add( &$params, &$ids, $lngContactId ) 
    {
        // create relationship object
        $relationship                = new CRM_Contact_BAO_Relationship( );
        $relationship->contact_id_b  = CRM_Array::value( 'contact', $ids );;
        $relationship->contact_id_a  = $lngContactId;
        $relationship->relationship_type_id = CRM_Array::value( 'relationship_type_id', $params );
        
        $sdate = CRM_Array::value( 'start_date', $params );
        $relationship->start_date = null;
        if ( $sdate              &&
             !empty($sdate['M']) &&
             !empty($sdate['d']) &&
             !empty($sdate['Y']) ) {
            $sdate['M'] = ( $sdate['M'] < 10 ) ? '0' . $sdate['M'] : $sdate['M'];
            $sdate['d'] = ( $sdate['d'] < 10 ) ? '0' . $sdate['d'] : $sdate['d'];
            $relationship->start_date = $sdate['Y'] . $sdate['M'] . $sdate['d'];
        }

        $edate = CRM_Array::value( 'end_date', $params );
        $relationship->end_date = null;
        if ( $edate              &&
             !empty($edate['M']) &&
             !empty($edate['d']) &&
             !empty($edate['Y']) ) {
            $edate['M'] = ( $edate['M'] < 10 ) ? '0' . $edate['M'] : $edate['M'];
            $edate['d'] = ( $edate['d'] < 10 ) ? '0' . $edate['d'] : $edate['d'];
            $relationship->end_date = $edate['Y'] . $edate['M'] . $edate['d'];
        }
        
        $relationship->id = CRM_Array::value( 'relationship', $ids );
        return  $relationship->save( );

    }

    /**
     * Check if there is data to create the object
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return boolean
     * @access public
     * @static
     */
    static function dataExists( &$params ) 
    {
        // return if no data present
        if ( ! is_array( $params['contact_check']) ) {
            return false;
        } 
        return true;
     }

    /**
     * Function to get get list of relationship type based on the contact type.
     *
     * @param int contact_id this is the contact id of the current contact.

     * @access public
     * @static
     *
     * @return array - array reference of all relationship types with context to current contact.
     *
     */
    function getContactRelationshipType($contactId)
    {
        $aRelationshipType = array();
        $relationshipType = array();
        $aRelationshipType = CRM_PseudoConstant::getRelationshipType();
        
        $contact = new CRM_Contact_BAO_Contact();
        
        $contact->id = $contactId;
        $contact->find(true);

        foreach ($aRelationshipType as $lng_key => $var_value) {
            if ($var_value['contact_type_a'] == $contact->contact_type) {
                if (!in_array($var_value['name_a_b'], $relationshipType)) {
                    if ($var_value['contact_type_a'] == $var_value['contact_type_b'] && $var_value['name_a_b'] != $var_value['name_b_a']) {
                        $relationshipType[$lng_key] = $var_value['name_a_b']." / ".$var_value['name_b_a'];
                    } else {
                        $relationshipType[$lng_key] = $var_value['name_a_b'];
                    }
                }
            }
            
            if ($var_value['contact_type_b'] == $contact->contact_type) {
                if (!in_array($var_value['name_b_a'], $relationshipType)) {
                    if ($var_value['contact_type_a'] != $var_value['contact_type_b']) {
                        $relationshipType[$lng_key] = $var_value['name_b_a'];
                    }
                }
            }
        }
        
        return $relationshipType;
    }
    
}

?>