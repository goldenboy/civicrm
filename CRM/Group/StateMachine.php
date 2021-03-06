<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Core/StateMachine.php';

class CRM_Group_StateMachine extends CRM_Core_StateMachine {

    /**
     * class constructor
     */
    function __construct( $controller, $action = CRM_Core_Action::NONE ) {
        parent::__construct( $controller, $action );
        
        $this->_pages = array(
                              'CRM_Group_Form_Edit' => null,
                              'CRM_Contact_Form_Search_Basic' => null,
                              'CRM_Contact_Form_Task_AddToGroup' => null,
                              'CRM_Contact_Form_Task_Result' => null,
                              );

        $this->addSequentialPages( $this->_pages, $action );
    }

    /**
     * return the form name of the task. This is 
     *
     * @return string
     * @access public
     */
    function getTaskFormName( ) {
        return CRM_Utils_String::getClassName( 'CRM_Contact_Form_Task_AddToGroup' );
    }

}


