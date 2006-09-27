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

require_once 'CRM/Quest/StateMachine/MatchApp.php';

/**
 * State machine for managing different states of the Quest process.
 *
 */
class CRM_Quest_StateMachine_MatchApp_School extends CRM_Quest_StateMachine_MatchApp {

    static $_dependency = null;

    public function rebuild( &$controller, $action = CRM_Core_Action::NONE ) {
        // ensure the states array is reset
        $this->_states = array( );

        $this->_pages = array( );
        self::setPages( $this->_pages, $this, $controller );

        parent::rebuild( $controller, $action );
    }

    static public function setPages( &$pages, &$stateMachine, &$controller ) {
        $pages['CRM_Quest_Form_MatchApp_HighSchool' ] = null;
        $pages['CRM_Quest_Form_MatchApp_SchoolOther'] = null; 
        $pages['CRM_Quest_Form_MatchApp_Academic'   ] = null;
        
        $grades = array( 'Nine'   => '9th Grade',
                         'Ten'    => '10th Grade',
                         'Eleven' => '11th Grade',
                         'Twelve' => '12th Grade',
                         'Summer' => 'Summer School' );
        foreach ( $grades as $grade => $title ) {
            $pages["Transcript-{$grade}"] = array( 'className' => "CRM_Quest_Form_MatchApp_Transcript_$grade",
                                                   'title'     => $title,
                                                   'options'   => array( ) );
        }
        
        $pages['CRM_Quest_Form_MatchApp_Testing'       ] = null;
        $pages['CRM_Quest_Form_MatchApp_Recommendation'] = null;
    }

    public function &getDependency( ) {
        if ( self::$_dependency == null ) {
            self::$_dependency = array( 'HighSchool'           => array( ),
                                        'SchoolOther'          => array( ),
                                        'ExtracurricularInfo'  => array( ),
                                        'WorkExperience'       => array( ),
                                        'Recommendation'       => array( 'HighSchool' => 1 ) );
        }

        return self::$_dependency;
    }

}

?>
