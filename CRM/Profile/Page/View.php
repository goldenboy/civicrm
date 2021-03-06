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
 * Main page for viewing contact.
 *
 */
require_once 'CRM/Core/Page.php';
class CRM_Profile_Page_View extends CRM_Core_Page 
{
    /**
     * The id of the contact
     *
     * @var int
     */
    protected $_id;

    /** 
     * The group id that we are editing
     * 
     * @var int 
     */ 
    protected $_gid; 

    /**
     * Heart of the viewing process. The runner gets all the meta data for
     * the contact and calls the appropriate type of page to view.
     *
     * @return void
     * @access public
     *
     */
    function preProcess( )
    {
        $this->_id = CRM_Utils_Request::retrieve('id', 'Positive',
                                                 $this, false);
        if ( ! $this->_id ) {
            $session =& CRM_Core_Session::singleton();
            $this->_id = $session->get( 'userID' );
            if ( ! $this->_id ) {
                CRM_Core_Error::fatal( ts( 'Could not find the required contact id parameter (id=) for viewing a contact record with a Profile.' ) );
            }
        }
        $this->assign( 'cid', $this->_id );

        $this->_gid = CRM_Utils_Request::retrieve('gid', 'Positive',
                                                  $this);
        
        if ($this->_gid) {
            require_once 'CRM/Profile/Page/Dynamic.php';
            $page =& new CRM_Profile_Page_Dynamic($this->_id, $this->_gid, 'Profile' );
            $profileGroup            = array( );
            $profileGroup['title']   = null;
            $profileGroup['content'] = $page->run();
            $profileGroups[]         = $profileGroup;
            $map = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'is_map' );
            if ( $map ) {
                $this->assign( 'mapURL',
                               CRM_Utils_System::url( "civicrm/profile/map",
                                                      "reset=1&pv=1&cid={$this->_id}&gid={$this->_gid}" ) );
            }
            $this->assign( 'listingURL',
                           CRM_Utils_System::url( "civicrm/profile",
                                                  "force=1&gid={$this->_gid}" ) );
        } else {
            require_once 'CRM/Core/BAO/UFGroup.php';
            $ufGroups =& CRM_Core_BAO_UFGroup::getModuleUFGroup('Profile'); 

            $profileGroups = array();
            foreach ($ufGroups as $groupid => $group) {
                require_once 'CRM/Profile/Page/Dynamic.php';
                $page =& new CRM_Profile_Page_Dynamic( $this->_id, $groupid, 'Profile');
                $profileGroup = array( );
                $profileGroup['title'] = $group['title'];
                $profileGroup['content'] = $page->run();
                $profileGroups[] = $profileGroup;
            }
            $this->assign( 'listingURL',
                           CRM_Utils_System::url( "civicrm/profile",
                                                  "force=1" ) );
        }
        
        $this->assign( 'groupID', $this->_gid );

        $this->assign('profileGroups', $profileGroups);
        $this->assign('recentlyViewed', false);

        $title    = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'title' );
        
        //CRM-4131.
        $sortName    = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $this->_id, 'display_name' );
        if ( $sortName ) {
            require_once 'CRM/Core/Permission.php';
            require_once 'CRM/Contact/BAO/Contact/Permission.php';
            $session   = CRM_Core_Session::singleton( );
            $config    = CRM_Core_Config::singleton( );
            if ( $session->get( 'userID' ) && 
                 CRM_Core_Permission::check('access CiviCRM') &&
                 CRM_Contact_BAO_Contact_Permission::allow( $session->get( 'userID' ), CRM_Core_Permission::VIEW ) &&
                 !$config->userFrameworkFrontend ) {
                $sortNameUrl = CRM_Utils_System::url('civicrm/contact/view', "action=view&reset=1&cid={$this->_id}", true);
                $sortName = "<a href=\"$sortNameUrl\">{$sortName}</a>";
            } 
            $title .= ' - ' . $sortName;
        }
        
        CRM_Utils_System::setTitle( $title );
    }


    /**
     * build the outcome basing on the CRM_Profile_Page_Dynamic's HTML
     *
     * @return void
     * @access public
     *
     */
    function run()
    {
        $this->preProcess();
        parent::run();
    }

    function getTemplateFileName() {
        if ( $this->_gid ) {
            $templateFile = "CRM/Profile/Page/{$this->_gid}/View.tpl";
            $template     =& CRM_Core_Page::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }
        }
        return parent::getTemplateFileName( );
    }

}


