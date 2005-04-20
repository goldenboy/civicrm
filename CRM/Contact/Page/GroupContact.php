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

require_once 'CRM/Core/Page.php';

class CRM_Contact_Page_GroupContact {

    /**
     * class constructor
     */
    function __construct( ) {
    }

    static function view( $page, $groupId ) {
        /*
        $groupContact = new CRM_Contact_DAO_GroupContact( );
        $groupContact->id = $groupContactId;
        if ( $groupContact->find( true ) ) {
            $values = array( );
            $groupContact->storeValues( $values );
            $page->assign( 'groups', $values );
        }
        
        self::browse( $page );
        */
    }

    static function browse( $page ) {
  
        $contactId   = $page->getContactId( );

        $count = self::getContactGroup($contactId);
        
        $aGroupIn = self::getContactGroup($contactId, 'In' );
        $aGroupPending = self::getContactGroup($contactId, 'Pending' );
        $aGroupOut = self::getContactGroup($contactId, 'Out' );

        $page->assign( 'groupCount', $count );
        $page->assign( 'groupIn', $aGroupIn );
        $page->assign( 'groupPending', $aGroupPending );
        $page->assign( 'groupOut', $aGroupOut );
    }

    static function edit( $page, $mode, $groupId = null ) {

        $controller = new CRM_Controller_Simple( 'CRM_Contact_Form_GroupContact', 'Contact GroupContacts', $mode );

        // set the userContext stack
        $session = CRM_Session::singleton();
        $config  = CRM_Config::singleton();
        $session->pushUserContext( $config->httpBase . 'civicrm/contact/view/group&op=browse' );

        $controller->reset( );

        $controller->set( 'contactId'  , $page->getContactId( ) );
        $controller->set( 'groupId'   , $groupId );
 
        $controller->process( );
        $controller->run( );

    }

    static function run( $page ) {

        $contactId = $page->getContactId( );
        $page->assign( 'contactId', $contactId );

        $op = CRM_Request::retrieve( 'op', $page, false, 'browse' );
        $page->assign( 'op', $op );

        if ( $op == 'del' ) {
            $groupContactId = $_GET['gcid'];
            $status = $_GET['st'];
            if (is_numeric($groupContactId) && strlen(trim($status))) {
                self::delete( $groupContactId,$status );
            }
        }

        self::edit( $page, CRM_Form::MODE_ADD );
        self::browse( $page );
    }

    /**
     * function to get the list of groups for contact based on status of membership
     *
     * @param int $lngCntactId contact id 
     * @param string $status state of membership
     *
     * @return array|int $values is array when there the values the should be displayed in the listing
     *                    or $count is int when only count is returned
     *
     */
    function getContactGroup( $lngContactId, $status = null ) {
        $groupContact = new CRM_Contact_DAO_GroupContact( );
     
        $strSelect = "SELECT crm_group_contact.id as crm_group_contact_id, crm_group.name as crm_group_name,
                             crm_group_contact.in_date as in_date, crm_group_contact.out_date as out_date,
                             crm_group_contact.pending_date as pending_date, crm_group_contact.status as status,
                             crm_group_contact.pending_method as pending_method, crm_group_contact.in_method as in_method,
                             crm_group_contact.out_method as out_method";

        $strFrom = " FROM crm_group, crm_group_contact ";

        $strWhere = " WHERE crm_group.id = crm_group_contact.group_id
                          AND crm_group_contact.contact_id = ".$lngContactId;
        
        if (strlen($status)) {
            $strWhere .= " AND crm_group_contact.status = '".$status."'";
        }    

        $strSql = $strSelect.$strFrom.$strWhere;

        $groupContact->query($strSql);
     
        $count = 0;
        while ( $groupContact->fetch() ) {
            
            $values[$groupContact->crm_group_contact_id]['id'] = $groupContact->crm_group_contact_id;
            $values[$groupContact->crm_group_contact_id]['name'] = $groupContact->crm_group_name;
            $values[$groupContact->crm_group_contact_id]['in_date'] = $groupContact->in_date;
            $values[$groupContact->crm_group_contact_id]['out_date'] = $groupContact->out_date;
            $values[$groupContact->crm_group_contact_id]['pending_method'] = $groupContact->pending_method;
            $values[$groupContact->crm_group_contact_id]['in_method'] = $groupContact->in_method;
            $values[$groupContact->crm_group_contact_id]['out_method'] = $groupContact->out_method;

            $count++;
        }

        if (!strlen($status)) { 
            return $count;
        }
        return $values;
    }

    /*
     * function to remove/ rejoin the group
     *
     * @param int $lngGroupContactId id of crm_group_contact
     * @param string $status this is the status that should be updated.
     *
     */

    static function delete ($lngGroupContactId, $status ) {
        $groupContact = new CRM_Contact_DAO_GroupContact( );
        
        switch ($status) {
        case 'i' :
            $groupContact->status = 'In';
            $groupContact->in_date = date('Ymd');
            $groupContact->in_method = 'Admin';
            break;
        case 'p' :
            $groupContact->status = 'Pending';
            $groupContact->pending_date = date('Ymd');
            break;
        case 'o' :
            $groupContact->status = 'Out';
            $groupContact->out_date = date('Ymd');
            $groupContact->out_method = 'Admin';
            break;
        }
        
        $groupContact->id = $lngGroupContactId;

        $groupContact->save();

    }
}

?>