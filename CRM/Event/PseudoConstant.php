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

/**
 * This class holds all the Pseudo constants that are specific to Event. This avoids
 * polluting the core class and isolates the Event
 */
class CRM_Event_PseudoConstant extends CRM_Core_PseudoConstant 
{
    /**
     * Event
     *
     * @var array
     * @static
     */
    private static $event; 
    
    /**
     * Participant Status 
     *
     * @var array
     * @static
     */
    private static $participantStatus; 
    
    /**
     * Participant Role
     *
     * @var array
     * @static
     */
    private static $participantRole; 
    
    /**
     * Get all the n events
     *
     * @access public
     * @return array - array reference of all events if any
     * @static
     */
    public static function &event( $id = null )
    {
        if ( ! self::$event ) {
            CRM_Core_PseudoConstant::populate( self::$event,
                                               'CRM_Event_DAO_Event',
                                               false, 'title', 'is_active', null, null);
        }
        if ($id) {
            if (array_key_exists($id, self::$event)) {
                return self::$event[$id];
            } else {
                return null;
            }
        }
        return self::$event;
    }
    
    /**
     * Get all the n participant statuses
     *
     * @access public
     * @return array - array reference of all participant statuses if any
     * @static
     */
    public static function &participantStatus( )
    {
        if ( ! self::$participantStatus ) {
            self::$participantStatus = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$participantStatus = CRM_Core_OptionGroup::values("participant_status");
        }
        return self::$participantStatus;
    }
    
    /**
     * Get all the n participant roles
     *
     * @access public
     * @return array - array reference of all participant roles if any
     * @static
     */
    public static function &participantRole( )
    {
        if ( ! self::$participantRole ) {
            self::$participantRole = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$participantRole = CRM_Core_OptionGroup::values("participant_role");
        }
        return self::$participantRole;
    }
}
?>