<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                  |
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
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/Location.php';

require_once 'CRM/Core/BAO/Block.php';
require_once 'CRM/Core/BAO/Address.php';
require_once 'CRM/Core/BAO/Phone.php';
require_once 'CRM/Core/BAO/Email.php';
require_once 'CRM/Core/BAO/IM.php';


require_once 'CRM/Contact/Form/Location.php';

/**
 * BAO object for crm_location table
 */
class CRM_Core_BAO_Location extends CRM_Core_DAO_Location {
    /**
     * takes an associative array and creates a contact object
     *
     * the function extract all the params it needs to initialize the create a
     * contact object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     * @param array  $ids            the array that holds all the db ids
     * @param array  $locationId     
     *
     * @return object   CRM_Core_BAO_Location object on success, null otherwise
     * @access public
     * @static
     */
    static function add( &$params, &$ids, $locationId, $fixAddress = true ) {
        if ( ! self::dataExists( $params, $locationId, $ids ) ) {
            return null;
        }

        $location =& new CRM_Core_BAO_Location( );
        
        if (! isset($params['contact_id'])) {
            require_once 'CRM/Core/BAO/Domain.php';
            $location->entity_table = $params['entity_table'];
            $location->entity_id    = $params['entity_id'];
        } else {
            $location->entity_table = CRM_Contact_BAO_Contact::getTableName();
            $location->entity_id    = $params['contact_id'];
        }
        $location->location_type_id = CRM_Utils_Array::value( 'location_type_id', $params['location'][$locationId] );
        // For backward compatibility, checking for name AND location_name. At some point, migrate to only using location_name.
        $location->name             = CRM_Utils_Array::value( 'location_name', $params['location'][$locationId] );
        if ( ! $location->name ) {
            $location->name             = CRM_Utils_Array::value( 'name', $params['location'][$locationId] );
        }
        $location->is_primary       = CRM_Utils_Array::value( 'is_primary', $params['location'][$locationId], false );

        // check if there exists another location has is_primary set, and if so reset that
        // if no location has is_primary, make this one is_primart
        if ( $location->is_primary ) {
            // reset all other locations with the same entity table entity id
            $sql = "UPDATE " . self::getTableName( ) . "
 SET is_primary = 0 WHERE 
 entity_table = %1 AND
 entity_id    = %2 ";
            $sqlParams = array( 1 => array( $location->entity_table, 'String' ),
                                2 => array( $location->entity_id   , 'Integer' ) );
            CRM_Core_DAO::executeQuery( $sql, $sqlParams );
        } else {
            // make sure there is at once location with is_primary set
            $sql = "SELECT count( " . self::getTableName( ) . ".id )
 FROM " . self::getTableName( ) . " WHERE
 entity_table = %1 AND
 entity_id    = %2 AND
 is_primary   = 1";
            $sqlParams = array( 1 => array( $location->entity_table, 'String' ),
                                2 => array( $location->entity_id   , 'Integer' ) );
            $count = CRM_Core_DAO::singleValueQuery( $sql, $sqlParams );
            if ( $count == 0 ) {
                $location->is_primary = true;
            }
        }
        
        $location->id = CRM_Utils_Array::value( 'id', $ids['location'][$locationId] );
        $location->save( );

        $params['location'][$locationId]['id'] = $location->id;
        $address_object = CRM_Core_BAO_Address::add( $params, $ids, $locationId, $fixAddress );
        $location->address = $address_object;
        // set this to true if this has been made the primary IM.
        // the rule is the first entered value is the primary object
        $isPrimaryPhone = $isPrimaryEmail = $isPrimaryIM = true;

        $location->phone = array( );
        $location->email = array( );
        $location->im    = array( );
        
        for ( $i = 1; $i <= CRM_Contact_Form_Location::BLOCKS; $i++ ) {
            $location->phone[$i] = CRM_Core_BAO_Phone::add( $params, $ids, $locationId, $i, $isPrimaryPhone );
            $location->email[$i] = CRM_Core_BAO_Email::add( $params, $ids, $locationId, $i, $isPrimaryEmail );
            $location->im   [$i] = CRM_Core_BAO_IM::add   ( $params, $ids, $locationId, $i, $isPrimaryIM    );
        }
        return $location;
    }

    /**
     * Check if there is data to create the object
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     * @param array  $locationId     
     * @param array  $ids            (reference ) the array that holds all the db ids
     *
     * @return boolean
     * @access public
     * @static
     */
    static function dataExists( &$params, $locationId, &$ids ) {
        if ( CRM_Utils_Array::value( 'id', $ids['location'][$locationId] ) ) {
            return true;
        }

        // return if no data present
        if ( ! array_key_exists( 'location' , $params ) ||
             ! array_key_exists( $locationId, $params['location'] ) ) {
            return false;
        }
        
        //if location name exists return true
        // For backward compatibility, checking for name AND location_name. At some point, migrate to only using location_name.
        if ( CRM_Utils_Array::value( 'location_name', $params['location'][$locationId] ) ||
             CRM_Utils_Array::value( 'name', $params['location'][$locationId] )) {
            return  true;
        }
        
        if ( CRM_Core_BAO_Address::dataExists( $params, $locationId, $ids ) ) {
            return true;
        }

        for ( $i = 1; $i <= CRM_Contact_Form_Location::BLOCKS; $i++ ) {
           
            if ( CRM_Core_BAO_Phone::dataExists( $params, $locationId, $i, $ids ) ||
                 CRM_Core_BAO_Email::dataExists( $params, $locationId, $i, $ids ) ||
                 CRM_Core_BAO_IM::dataExists   ( $params, $locationId, $i, $ids ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params        input parameters to find object
     * @param array $values        output values of the object
     * @param array $ids           the array that holds all the db ids
     * @param int   $locationCount number of locations to fetch
     *
     * @return array   array of objects(CRM_Core_BAO_Location)
     * @access public
     * @static
     */
    static function &getValues( &$params, &$values, &$ids, $locationCount = 0, $microformat = false ) {
        $location =& new CRM_Core_BAO_Location( );
        $location->copyValues( $params );
        if ( CRM_Utils_Array::value( 'contact_id', $params ) ) {
            $location->entity_table = 'civicrm_contact';
            $location->entity_id    = $params['contact_id'];
        } else if ( CRM_Utils_Array::value( 'domain_id', $params ) ) {
            $location->entity_table = 'civicrm_domain';
            $location->entity_id    = $params['domain_id'];
        }

        $flatten = false;
        if ( empty($locationCount) ) {
            $locationCount = 1;
            $flatten       = true;
        } else {
            $values['location'] = array();
            $ids['location']    = array();
        }

        // we first get the primary location due to the order by clause
        $location->orderBy( 'is_primary desc, id' );
        $location->find( );
        $locations = array( );
        for ($i = 0; $i < $locationCount; $i++) {
            if ($location->fetch()) {
                $params['location_id'] = $location->id;
                if ($flatten) {
                    $ids['location'] = $location->id;
                    CRM_Core_DAO::storeValues( $location, $values );
                    self::getBlocks( $params, $values, $ids, 0, $location, $microformat );
                } else {
                    $values['location'][$i+1] = array();
                    $ids['location'][$i+1]    = array();
                    $ids['location'][$i+1]['id'] = $location->id;
                    CRM_Core_DAO::storeValues( $location, $values['location'][$i+1] );
                    self::getBlocks( $params, $values['location'][$i+1], $ids['location'][$i+1],
                                     CRM_Contact_Form_Location::BLOCKS, $location, $microformat );
                }
                $locations[$i + 1] = clone($location);
            }
        }
        if ( empty( $values['location'] ) ) {
            // mark the first location as primary if none exists
            $values['location'][1] = array( );
            $values['location'][1]['is_primary'] = 1;
        }
        return $locations;
    }

    /**
     * simple helper function to dispatch getCall to lower comm blocks
     */
    static function getBlocks( &$params, &$values, &$ids, $blockCount = 0, &$parent, $microformat = false ) {
        $parent->address =& CRM_Core_BAO_Address::getValues( $params, $values, $ids, $blockCount, $microformat );

        $parent->phone   =& CRM_Core_BAO_Phone::getValues( $params, $values, $ids, $blockCount );
        $parent->email   =& CRM_Core_BAO_Email::getValues( $params, $values, $ids, $blockCount );
        $parent->im      =& CRM_Core_BAO_IM::getValues   ( $params, $values, $ids, $blockCount );
    }

    /**
     * Delete the object records that are associated with this contact
     *
     * @param  int  $contactId id of the contact to delete
     *
     * @return void
     * @access public
     * @static
     */
    static function deleteContact( $contactId ) {
        $location =& new CRM_Core_DAO_Location( );
        $location->entity_id = $contactId;
        require_once 'CRM/Contact/DAO/Contact.php';
        $location->entity_table = CRM_Contact_DAO_Contact::getTableName();
        $location->find( );
        while ( $location->fetch( ) ) {
            self::deleteLocationBlocks( $location->id );
            $location->delete( );
        }

    }

    /**
     * Delete the object records that are associated with this location
     *
     * @param  int  $locationId id of the location to delete
     *
     * @return void
     * @access public
     * @static
     */
    static function deleteLocationBlocks( $locationId ) {
        static $blocks = array( 'Address', 'Phone', 'IM' );
        foreach ($blocks as $name) {
            require_once(str_replace('_', DIRECTORY_SEPARATOR, "CRM_Core_DAO_" . $name) . ".php");
            eval( '$object =& new CRM_Core_DAO_' . $name . '( );' );
            $object->location_id = $locationId;
            $object->delete( );
        }
        
        CRM_Core_BAO_Email::deleteLocation($locationId);
    }

}

?>
