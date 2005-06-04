<?php
/**
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
 * The Contact Wrapper is a wrapper class which is called by
 * contact.module after it parses the menu path.
 *
 * The key elements of the wrapper are the controller and the 
 * run method as explained below.
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */



require_once 'CRM/Core/Controller/Simple.php';
require_once 'CRM/Core/Base.php';
require_once 'CRM/Core/Controller/Simple.php';

class CRM_Utils_Wrapper 
{
    /**
     * Simple Controller
     *
     * The controller which will handle the display and processing of this page.
     *
     * @access protected
     */
    var $_controller;

    /**
     * Run.
     *
     * The heart of the callback processing is done by this method.
     * forms are of different type and have different operations.
     *
     * @param string $formName    name of the form processing this action
     * @param string $formLabel   label for the above form
     * @param int    $mode        mode of operation.
     *
     * @return none.
     * @access public
     */
    function run($formName, $formLabel, $mode ) {
        $this->_controller = new CRM_Core_Controller_Simple( $formName, $formLabel, $mode );
        if ( $data ) {
            $this->_controller->set( $data );
        }
        $this->_controller->process();
        $this->_controller->run();
    }

}

?>
