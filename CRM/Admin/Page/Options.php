<?php
/* 
 +--------------------------------------------------------------------+
 | CiviCRM version 1.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                |
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
 | at http://www.civicrm.org/licensing/                               |
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

require_once 'CRM/Core/Page/Basic.php';

/**
 * Page for displaying list of Gender
 */
class CRM_Admin_Page_Options extends CRM_Core_Page_Basic 
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * The option group name
     *
     * @var array
     * @static
     */
    static $_gName = null;

    /**
     * The option group name in display format (capitalized, without underscores...etc)
     *
     * @var array
     * @static
     */
    static $_GName = null;

    /**
     * The option group id
     *
     * @var array
     * @static
     */
    static $_gId = null;

    /**
     * Obtains the group name from url and sets the title.
     *
     * @return void
     * @access public
     *
     */
    function preProcess( )
    {
        if ( ! self::$_gName ) {
            self::$_gName = CRM_Utils_Request::retrieve('group','String', CRM_Core_DAO::$_nullObject,false,null,'GET');
            self::$_gId   = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', self::$_gName, 'id', 'name');
        }
        if (self::$_gName) {
            $this->set( 'gName', self::$_gName );
        } else {
            self::$_gName = $this->get( 'gName' );
        }
        self::$_GName = ucwords(str_replace('_', ' ', self::$_gName));

        $this->assign('gName', self::$_gName);
        $this->assign('GName', self::$_GName);

        if ( self::$_gName == 'acl_role' ) {
            CRM_Utils_System::setTitle(ts('Manage ACL Roles'));
            // set breadcrumb to append to admin/access
            $breadCrumbPath = CRM_Utils_System::url( 'civicrm/admin/access', 'reset=1' );
            $additionalBreadCrumb = "<a href=\"$breadCrumbPath\">" . ts('Access Control') . '</a>';
            CRM_Utils_System::appendBreadCrumb( $additionalBreadCrumb );
        } else {
            CRM_Utils_System::setTitle(ts(self::$_GName . ' Options'));
        }
    }

    /**
     * Get BAO Name
     *
     * @return string Classname of BAO.
     */
    function getBAOName() 
    {
        return 'CRM_Core_BAO_OptionValue';
    }

    /**
     * Get action Links
     *
     * @return array (reference) of action links
     */
    function &links()
    {
        if (!(self::$_links)) {
            // helper variable for nicer formatting
            $disableExtra = ts('Are you sure you want to disable this ' . self::$_GName . '?') . '\n\n' . ts('Users will no longer be able to select this value when adding or editing ' . self::$_GName . '.');
            
            self::$_links = array(
                                  CRM_Core_Action::UPDATE  => array(
                                                                    'name'  => ts('Edit'),
                                                                    'url'   => 'civicrm/admin/options',
                                                                    'qs'    => 'group=' . self::$_gName . '&action=update&id=%%id%%&reset=1',
                                                                    'title' => ts('Edit ' . self::$_gName) 
                                                                    ),
                                  CRM_Core_Action::DISABLE => array(
                                                                    'name'  => ts('Disable'),
                                                                    'url'   => 'civicrm/admin/options',
                                                                    'qs'    => 'group=' . self::$_gName . '&action=disable&id=%%id%%',
                                                                    'extra' => 'onclick = "return confirm(\'' . $disableExtra . '\');"',
                                                                    'title' => ts('Disable ' . self::$_gName) 
                                                                    ),
                                  CRM_Core_Action::ENABLE  => array(
                                                                    'name'  => ts('Enable'),
                                                                    'url'   => 'civicrm/admin/options',
                                                                    'qs'    => 'group=' . self::$_gName . '&action=enable&id=%%id%%',
                                                                    'title' => ts('Enable ' . self::$_gName) 
                                                                    ),
                                  CRM_Core_Action::DELETE  => array(
                                                                    'name'  => ts('Delete'),
                                                                    'url'   => 'civicrm/admin/options',
                                                                    'qs'    => 'group=' . self::$_gName . '&action=delete&id=%%id%%',
                                                                    'title' => ts('Delete ' . self::$_gName . ' Type') 
                                                                   )
                                 );
        }
        return self::$_links;
    }

    /**
     * Run the basic page (run essentially starts execution for that page).
     *
     * @return void
     */
    function run()
    {
        $this->preProcess();
        parent::run();
    }
    
    /**
     * Browse all options
     *  
     * 
     * @return void
     * @access public
     * @static
     */
    function browse()
    {
        require_once 'CRM/Core/OptionValue.php';
        
        $groupParams = array( 'name' => self::$_gName );
        $optionValue = CRM_Core_OptionValue::getRows($groupParams, $this->links(), 'weight');
        
        $this->assign('rows', $optionValue);
    }
    
    /**
     * Get name of edit form
     *
     * @return string Classname of edit form.
     */
    function editForm() 
    {
        return 'CRM_Admin_Form_Options';
    }
    
    /**
     * Get edit form name
     *
     * @return string name of this page.
     */
    function editName() 
    {
        return self::$_GName;
    }
    
    /**
     * Get user context.
     *
     * @return string user context.
     */
    function userContext($mode = null) 
    {
        return 'civicrm/admin/options';
    }

    /**
     * function to get userContext params
     *
     * @param int $mode mode that we are in
     *
     * @return string
     * @access public
     */
    function userContextParams( $mode = null ) {
        return 'group=' . self::$_gName . '&reset=1&action=browse';
    }

}

?>
