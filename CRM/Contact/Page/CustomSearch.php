<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';

/**
 * Main page for viewing all Saved searches.
 *
 */
class CRM_Contact_Page_CustomSearch extends CRM_Core_Page {

    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * Browse all custom searches.
     *
     * @return content of the parents run method
     *
     */
    function browse()
    {
        $rows = array();

        $sql = "
SELECT v.value, v.label, v.description
FROM   civicrm_option_group g,
       civicrm_option_value v
WHERE  v.option_group_id = g.id
AND    g.name = 'custom_search'
AND    v.is_active = 1
";
        $dao = CRM_Core_DAO::executeQuery( $sql,
                                           CRM_Core_DAO::$_nullArray );

        while ( $dao->fetch( ) ) {
            $row = array( );
            if ( trim( $dao->description ) ) {
                $row['name']    = $dao->description;
            } else {
                $row['name']    = $dao->label;
            }
            $row['csid']        = $dao->value;
            $rows[] = $row;
        }
        $this->assign('rows', $rows);
        return parent::run();
    }

    /**
     * run this page (figure out the action needed and perform it).
     *
     * @return void
     */
    function run() {
        $action = CRM_Utils_Request::retrieve( 'action',
                                               'String',
                                               $this, false, 'browse' );

        $this->assign( 'action', $action );
        $this->browse( );
    }

}


