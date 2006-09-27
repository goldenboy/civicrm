<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                  |
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
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2006
 * $Id$
 *
 */

require_once 'CRM/ACL/DAO/Cache.php';

/**
 *  Access Control Cache
 */
class CRM_ACL_BAO_Cache extends CRM_ACL_DAO_Cache {

    static $_cache = null;

    static function &build( $id ) {
        // in testing phase reset cache every time
        self::resetCache( );

        if ( ! self::$_cache ) {
            self::$_cache = array( );
        }
        
        if ( array_key_exists( $id, self::$_cache ) ) {
            return self::$_cache[$id];
        }

        // check if this entry exists in db
        // if so retrieve and return
        self::$_cache[$id] = self::retrieve( $id );
        if ( self::$_cache[$id] ) {
            return self::$_cache[$id];
        }

        require_once 'CRM/ACL/BAO/ACL.php';
        self::$_cache[$id] = CRM_ACL_BAO_ACL::getAllByContact( $id );
        self::store( $id, self::$_cache[$id] );
        return self::$_cache[$id];
    }

    static function retrieve( $id ) {
        $query = "
SELECT acl_id, whereClause, whereTables, selectTables
  FROM civicrm_acl_cache
 WHERE contact_id = %1
";
        $params = array( 1 => array( $id, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );

        $cache = array( );
        while ( $dao->fetch( ) ) {
            $cache[$dao->acl_id] = array( $dao->whereClause, $dao->whereTables, $dao->selectTables );
        }
        return $cache;
    }

    static function store( $id, &$cache ) {
        $dynamic = array( 'whereClause', 'selectTables', 'whereTables' );
        require_once 'CRM/Contact/BAO/SavedSearch.php';
        foreach ( $cache as $aclID => $data ) {
            $dao =& new CRM_ACL_DAO_Cache( );
            $dao->contact_id = $id;
            $dao->acl_id     = $aclID;
            $dao->whereClause = $dao->selectTables = $dao->whereTables = null;
            if ( $data['object_table'] == 'civicrm_saved_search' &&
                 $data['object_id'] ) {
                $tables = $whereTables = array( );
                $dao->whereClause = CRM_Contact_BAO_SavedSearch::whereClause( $data['object_id'],
                                                                              $tables, $whereTables );
                if ( $tables ) {
                    $dao->selectTables = serialize( $tables );
                }
                if ( $whereTables ) {
                    $dao->whereTables  = serialize( $whereTables );
                }
            }

            $cache[$aclID] = array( $dao->whereClause,
                                    $dao->selectTables,
                                    $dao->whereTables );

            $dao->save( );
        }
    }

    static function deleteEntry( $id ) {
        if ( self::$_cache &&
             array_key_exists( $id, self::$_cache ) ) {
            unset( self::$_cache[$id] );
        }

        $query = "
DELETE FROM civicrm_acl_cache
WHERE contact_id = %1
";
        $params = array( 1 => array( $id, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
    }

    static function updateEntry( $id ) {
        self::delete( $id );
        self::build( $id );
    }

    // deletes all the cache entries for this domain
    static function resetCache( ) {
        self::$_cache = null;

        $domainID = CRM_Core_Config::domainID( );
        $query = "
DELETE FROM civicrm_acl_cache c
USING civicrm_acl_cache c,
      civicrm_acl       a
WHERE c.acl_id = a.id
  AND a.domain_id = $domainID
";

        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
    }

}

?>