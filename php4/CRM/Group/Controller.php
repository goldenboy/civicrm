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



require_once 'CRM/Core/Controller.php';
require_once 'CRM/Group/StateMachine.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Controller.php';

class CRM_Group_Controller extends CRM_Core_Controller {

    /**
     * class constructor
     */
    function CRM_Group_Controller( $title = null, $action = CRM_CORE_ACTION_NONE, $modal = true ) {
        parent::CRM_Core_Controller( $title, $modal );

        $this->_stateMachine = new CRM_Group_StateMachine( $this, $action );

        // create and instantiate the pages
        $this->addPages( $this->_stateMachine, $action );

        // hack for now, set Search to Basic mode
        $this->_pages['Search']->setAction( CRM_CORE_ACTION_BASIC );

        // add all the actions
        $config =& CRM_Core_Config::singleton( );
        $this->addActions( );
    }

    function run( ) {
        return parent::run( );
    }

}

?>