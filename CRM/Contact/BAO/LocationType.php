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

class CRM_Contact_BAO_LocationType extends CRM_Contact_DAO_LocationType {

    /**
     * static holder for the default LT
     */
    static $_defaultLocationType = null;


    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

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
     * @return object CRM_Core_BAO_LocaationType object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) {
        $locationType =& new CRM_Contact_DAO_LocationType( );
        $locationType->copyValues( $params );
        if ( $locationType->find( true ) ) {
            CRM_Core_DAO::storeValues( $locationType, $defaults );
            return $locationType;
        }
        return null;
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
    static function setIsActive( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Contact_DAO_LocationType', $id, 'is_active', $is_active );
    }


    /**
     * retrieve the default location_type
     *
     * @param void
     * @return object           The default location type object on success,
     *                          null otherwise
     * @static
     * @access public
     */
    static function &getDefault() {
        if (self::$_defaultLocationType == null) {
            $params = array('is_default' => 1);
            $defaults = array();
            self::$_defaultLocationType = self::retrieve($params, $defaults);
        }
        return self::$_defaultLocationType;
    }

}

?>
