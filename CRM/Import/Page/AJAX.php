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

/**
 * This class contains all the function that are called using AJAX
 */
class CRM_Import_Page_AJAX
{
 
    /**
     * Function to show import status
     */
    function status( ) 
    {
        // make sure we get an id
        if ( ! isset( $_GET['id'] ) ) {
            return;
        }

        $config =& CRM_Core_Config::singleton( );    
        $file = "{$config->uploadDir}status_{$_GET['id']}.txt";
        if ( file_exists( $file ) ) {
            $str = file_get_contents( $file );
            echo $str;
        } else {
            require_once 'Services/JSON.php';
            $json =& new Services_JSON( );
            $status = "<div class='description'>&nbsp; " . ts('No processing status reported yet.') . "</div>";
            echo $json->encode( array( 0, $status ) );
        }
		exit;
    }

}
