<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.3                                                |
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

require_once 'CRM/Core/Permission/Drupal.php';
require_once 'CRM/Core/Permission/Mambo.php';

/**
 * This is the basic permission class wrapper
 */
class CRM_Core_Permission {
    /**
     * Static strings used to compose permissions
     *
     * @const
     * @var string
     */
    const
        EDIT_GROUPS = 'edit contacts in ',
        VIEW_GROUPS = 'view contacts in ';

    /**
     * The various type of permissions
     * 
     * @var int
     */
    const
        EDIT = 1,
        VIEW = 2;

    /**
     * get the current permission of this user
     *
     * @return string the permission of the user (edit or view or null)
     */
    public static function getPermission( ) {
        $config   =& CRM_Core_Config::singleton( );
        return eval( 'return ' . $config->userPermissionClass . '::getPermission( );' );
    }

    /**
     * Get the permissioned where clause for the user
     *
     * @param int $type the type of permission needed
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     *
     * @return string the group where clause for this user
     * @access public
     */
    public static function whereClause( $type, &$tables ) {
        $config   =& CRM_Core_Config::singleton( );
        return eval( 'return ' . $config->userPermissionClass . '::whereClause( $type, $tables );' );
    }

    /**
     * Get all groups from database, filtered by permissions
     * for this user
     *
     * @access public
     * @static
     *
     * @return array - array reference of all groups.
     *
     */
    public static function &group( ) {
        $config   =& CRM_Core_Config::singleton( );
        return eval( 'return ' . $config->userPermissionClass . '::group( );' );
    }

}

?>
