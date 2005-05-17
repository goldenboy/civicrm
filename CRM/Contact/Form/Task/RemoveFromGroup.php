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

/**
 * This class provides the functionality to delete a group of
 * contacts. This class provides functionality for the actual
 * addition of contacts to groups.
 */
class CRM_Contact_Form_Task_RemoveFromGroup extends CRM_Contact_Form_Task {
    /**
     * The context that we are working on
     *
     * @var string
     */
    protected $_context;

    /**
     * the groupId retrieved from the GET vars
     *
     * @var int
     */
    protected $_id;

    /**
     * the title of the group
     *
     * @var string
     */
    protected $_title;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        /*
         * initialize the task and row fields
         */
        parent::preProcess( );

        $this->_context = $this->get( 'context' );
        $this->_id      = $this->get( 'amtgID'  );
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
        // add select for groups
        $group = array( '' => ' - select group - ') + CRM_Core_PseudoConstant::group( );
        $groupElement = $this->add('select', 'group_id', 'Select Group', $group, true);
        $this->_title  = $group[$this->_id];

        if ( $this->_context === 'amtg' ) {
            $groupElement->freeze( );

            // also set the group title
            $groupValues = array( 'id' => $this->_id, 'title' => $this->_title );
            $this->assign_by_ref( 'group', $groupValues );
        }
         
        // Set dynamic page title for 'Remove Members Group (confirm)'
        if ( $this->_id ) {
            CRM_Utils_System::setTitle( 'Remove Members: ' . $this->_title );
        }
        else {
            CRM_Utils_System::setTitle( 'Remove Members from Group ');
        }

        $this->addDefaultButtons( 'Remove From Group' );
    }

    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues() {
        $defaults = array();

        if ( $this->_context === 'amtg' ) {
            $defaults['group_id'] = $this->_id;
        }
        return $defaults;
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        $groupId    = $this->controller->exportValue( 'RemoveFromGroup', 'group_id'  );
        
        list( $total, $removed, $notRemoved ) = CRM_Contact_BAO_GroupContact::removeContactsFromGroup( $this->_contactIds, $groupId );
        $status = array(
                        'Removed Contact(s) from '     . $this->_title,
                        'Total Selected Contact(s): '  . $total
                        );
        if ( $removed ) {
            $status[] = 'Total Contact(s) removed from group: ' . $removed;
        }
        if ( $notRemoved ) {
            $status[] = 'Total Contact(s) not in group: ' . $notRemoved;
        }
        CRM_Core_Session::setStatus( $status );

    }//end of function


}

?>