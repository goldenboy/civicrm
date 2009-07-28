<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.0                                                |
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

class CRM_Bridge_OG_Utils {

    const
        aclEnabled      = 1,
        syncFromCiviCRM = 1;

    static function aclEnabled( ) {
        return self::aclEnabled;
    }

    static function syncFromCiviCRM( ) {
        // make sure that acls are not enabled
        return ! self::aclEnabled & self::syncFromCiviCRM;
    }
    
    static function ogSyncName( $ogID ) {
        return "OG Sync Group :{$ogID}:";
    }

    static function ogSyncACLName( $ogID ) {
        return "OG Sync Group ACL :{$ogID}:";
    }

    static function ogID( $groupID, $abort = true ) {
        $source = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group',
                                               $groupID,
                                               'source' );

        if ( strpos( $source, 'OG Sync Group' ) !== false ) {
            preg_match( '/:(\d+):$/', $source, $matches );
            if ( is_numeric( $matches[1] ) ) {
                return $matches[1];
            }
        }
        if ( $abort ) {
            CRM_Core_Error::fatal( );
        }
        return null;
    }

    static function contactID( $ufID ) {
        require_once 'api/UFGroup.php';
        $contactID = crm_uf_get_match_id( $ufID );
        if ( $contactID ) {
            return $contactID;
        }

        // else create a contact for this user
        $user = user_load( array( 'uid' => $ufID ) );
        $params = array( 'contact_type' => 'Individual',
                         'email'        => $user->mail, );

        require_once 'api/v2/Contact.php';
        $values = civicrm_contact_add( $params );
        if ( $values['is_error'] ) {
            CRM_Core_Error::fatal( );
        }
        return $values['contact_id'];
    }

    static function groupID( $source, $title = null, $abort = false ) {
        $query  = "
SELECT id
  FROM civicrm_group
 WHERE source = %1";
        $params = array( 1 => array( $source, 'String' ) );

        if ( $title ) {
            $query .= " OR title = %2";
            $params[2] = array( $title, 'String' );
        }
                         
        $groupID = CRM_Core_DAO::singleValueQuery( $query, $params );
        if ( $abort &&
             ! $groupID ) {
            CRM_Core_Error::fatal( );
        }

        return $groupID;
    }


}


