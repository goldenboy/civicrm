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
 * Drupal module file.
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */



include_once 'config.inc.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Core/Block.php';
require_once 'CRM/Utils/System.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Core/BAO/Drupal.php';
require_once 'CRM/Admin/Page/LocationType.php';
require_once 'CRM/Admin/Page/IMProvider.php';
require_once 'CRM/Admin/Page/MobileProvider.php';
require_once 'CRM/Admin/Page/RelationshipType.php';
require_once 'CRM/Admin/Page/Tag.php';
require_once 'CRM/Custom/Page/Group.php';
require_once 'CRM/Custom/Page/Field.php';
require_once 'CRM/Core/Session.php';
require_once 'CRM/Group/Controller.php';
require_once 'CRM/Group/Page/Group.php';
require_once 'CRM/Import/Controller.php';
require_once 'CRM/Contact/Page/View.php';
require_once 'CRM/Utils/Wrapper.php';
require_once 'CRM/Contact/Page/SavedSearch.php';
require_once 'CRM/Contact/Controller/Search.php';


require_once 'PEAR.php';

require_once 'CRM/Core/Action.php';
require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Block.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Core/PseudoConstant.php';

require_once 'CRM/Utils/Wrapper.php';

require_once 'CRM/Contact/Page/View.php';

// only used for debugging purposes - remove in production system
require_once 'CRM/Core/Error.php';


/**
 * Provides a link to the CSS stylesheet associated with this module.
 *
 * @return a &lt;style&gt; tag that indicates what file browsers should import
 */
function civicrm_html_head()
{
    $config = CRM_Core_Config::singleton();
    return '<style type="text/css">@import url(' . $config->resourceBase . 'css/civicrm.css);</style>';
}

/**
 * @file
 * This is an example outlining how a module can be used to display a
 * custom page at a given URL.
 */

/**
 * Implementation of hook_help().
 *
 * Throughout Drupal, hook_help() is used to display help text at the top of
 * pages. Some other parts of Drupal pages get explanatory text from these hooks
 * as well. We use it here to provide a description of the module on the
 * module administration page. This example also illustrates how to add help
 * text to the pages your module defines.
 */
function civicrm_help($section)
{
    switch ($section) {
    case 'admin/modules#description':
        // This description is shown in the listing at admin/modules.
        return ts('CiviCRM module v0.1');
    }
}

/**
 * Implementation of hook_perm().
 *
 * Since the access to our new custom pages will be granted based on
 * special permissions, we need to define what those permissions are here.
 * This ensures that they are available to enable on the user role
 * administration pages.
 */
function civicrm_perm()
{
    // static permissions
    $cPerm = array('add contacts', 'view all contacts', 'edit all contacts', 'edit groups', 'administer CiviCRM', 'access CiviCRM' );

    // dynamic permissions - access by group (title)
    $groups =& CRM_Core_PseudoConstant::allGroup();
    foreach ( $groups as $title ) {
        $cPerm[] = 'view ' . $title;
        $cPerm[] = 'edit ' . $title;
    }

    $savedSearches =& CRM_Core_PseudoConstant::allSavedSearch( );
    foreach ( $savedSearches as $title ) {
        $cPerm[] = 'view ' . $title;
        $cPerm[] = 'edit ' . $title;
    }

    return $cPerm;
}

/**
 * Implementation of hook_block().
 *
 * This hook both declares to Drupal what blocks are provided by the module, and
 * generates the contents of the blocks themselves.
 */

function civicrm_block($op='list', $delta='0')
{
    if ( ( arg(0) != 'civicrm' && arg(0) != 'admin' ) || ( ! user_access( 'access CiviCRM' ) ) ) {
        return;
    }

    // The $op parameter determines what piece of information is being requested.
    global $user;
    if ($user->uid) {
        $menu_arr = civicrm_menu(true);
        if ($op == 'list') {
            return CRM_Core_Block::getInfo();
        } else {
            return CRM_Core_Block::getContent ($delta);
        }
    }
}

/**
 * Implementation of hook_menu().
 *
 * You must implement hook_menu() to emit items to place in the main menu.
 * This is a required step for modules wishing to display their own pages,
 * because the process of creating the links also tells Drupal what
 * callback function to use for a given URL. The menu items returned
 * here provide this information to the menu system.
 *
 * With the below menu definitions, URLs will be interpreted as follows:
 *
 * If the user accesses http://example.com/?q=foo, then the menu system
 * will first look for a menu item with that path. In this case it will
 * find a match, and execute civicrm_foo().
 *
 */
function civicrm_menu($may_cache)
{
    $items = array();
    
    // The $may_cache parameter is used to divide menu items into two parts. Those
    // returned when $may_cache is true must be consistently applicable for the
    // current user at all times; the others may change or be defined at only
    // certain paths. Most modules will have excusively cacheable menu items.
    
    if ($may_cache) {
        // This is the minimum information you can provide for a menu item.
        $items[] = array(
                         'path'   => 'civicrm/admin',
                         'title'  => ts('Administer CiviCRM'),
                         'qs'     => 'reset=1',
                         'access' => user_access('administer CiviCRM') && user_access( 'access CiviCRM' ),
                         'type'   => MENU_NORMAL_ITEM,
                         'weight' => 40,
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/admin/tag',
                         'title'  => ts('Tags'),
                         'type'   => MENU_DEFAULT_LOCAL_TASK,
                         'weight' => -10
                         );

        $items[] = array(
                         'path'   => 'civicrm/admin/reltype',
                         'title'  => ts('Relationship Types'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => -8
                         );

        $items[] = array(
                         'path'   => 'civicrm/admin/locationType',
                         'title'  => ts('Location Types'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => -6
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/admin/custom/group',
                         'title'  => ts('Custom Data'),
                         'qs'     => 'reset=1',
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => -5
                         );

        $items[] = array(
                         'path'   => 'civicrm/admin/custom/group/field',
                         'title'  => ts('Custom Data Fields'),
                         'qs'     => 'reset=1',
                         'type'   => MENU_CALLBACK,
                         'weight' => 3
                         );
        $items[] = array(
                         'path'   => 'civicrm/admin/IMProvider',
                         'title'  => ts('IM Services'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => -4
                         );

       $items[] = array(
                         'path'   => 'civicrm/admin/mobileProvider',
                         'title'  => ts('Mobile Providers'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => -2
                         );
    
        $items[] = array(
                         'path'     => 'civicrm',
                         'title'    => ts('CiviCRM'),
                         'access'   => user_access( 'access CiviCRM' ),
                         'callback' => 'civicrm_invoke',
                         'type'     => MENU_CALLBACK,
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/search',
                         'title'  => ts('Contacts'),
                         'qs'     => 'reset=1',
                         'type'   => MENU_NORMAL_ITEM,
                         'access'   => user_access( 'access CiviCRM' ),
                         'weight' => 10,
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/contact/search/basic',
                         'title'  => ts('Find Contacts'),
                         'qs'     => 'reset=1',
                         'type'   => MENU_DEFAULT_LOCAL_TASK,
                         'weight' => 0
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/search/advanced',
                         'title'  => ts('Advanced Search'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 1
                         );
        $items[] = array(
                         'path'   => 'civicrm/contact/search/saved',
                         'title'  => ts('Saved Searches'),
                         'qs'     => 'reset=1',
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 2
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/addI',
                         'title'  => ts('New Individual'),
                         'qs'     => 'reset=1',
                         'access' => user_access('add contacts') && user_access( 'access CiviCRM' ),
                         'type'   => MENU_CALLBACK,
                         'weight' => 1
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/contact/addO',
                         'title'  => ts('New Organization'),
                         'qs'     => 'reset=1',
                         'access' => user_access('add contacts') && user_access( 'access CiviCRM' ),
                         'type'   => MENU_CALLBACK,
                         'weight' => 1
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/contact/addH',
                         'title'  => ts('New Household'),
                         'qs'     => 'reset=1',
                         'access' => user_access('add contacts') && user_access( 'access CiviCRM' ),
                         'type'   => MENU_CALLBACK,
                         'weight' => 1
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/contact/edit',
                         'title'  => ts('Edit Contact Name and Location'),
                         'type'   => MENU_CALLBACK,
                         'weight' => 1
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/contact/view',
                         'title'  => ts('View Contact'),
                         'type'   => MENU_CALLBACK
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/view/basic',
                         'title'  => ts('Contact Summary'),
                         'type'   => MENU_DEFAULT_LOCAL_TASK,
                         'weight' => 0
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/view/rel',
                         'title'  => ts('Relationships'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 1
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/contact/view/note',
                         'title'  => ts('Notes'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 3
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/view/group',
                         'title'  => ts('Groups'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 2
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/contact/view/tag',
                         'title'  => ts('Tags'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 4
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/view/cd',
                         'title'  => ts('Custom Data'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 5
                         );

        $items[] = array(
                         'path'   => 'civicrm/contact/view/activity',
                         'title'  => ts('Activity'),
                         'type'   => MENU_LOCAL_TASK,
                         'weight' => 6
                         );

        $items[] = array(
                         'path'   => 'civicrm/group',
                         'title'  => ts('Manage Groups'),
                         'qs'     => 'reset=1',
                         'type'   => MENU_NORMAL_ITEM,
                         'access'   => user_access( 'access CiviCRM' ),
                         'weight' => 20,
                         );

        $items[] = array(
                         'path'   => 'civicrm/group/search',
                         'title'  => ts('Group Members'),
                         'type'   => MENU_CALLBACK,
                         );
        
        $items[] = array(
                         'path'   => 'civicrm/group/add',
                         'title'  => ts('Create New Group'),
                         'type'   => MENU_CALLBACK,
                         );
        
       $items[] = array(
                         'path'   => 'civicrm/import',
                         'title'  => ts('Import Contacts'),
                         'qs'     => 'reset=1',
                         'access' => user_access('administer CiviCRM') && user_access( 'access CiviCRM' ),
                         'type'   => MENU_NORMAL_ITEM,
                         'weight' => 30,
                         );
 
    }

    return $items;
}

function civicrm_hack_path($path, $qs)
{
    $items = explode('/', $path);
    $len   = count($items) ;
    if (arg($len - 1) != $items[$len - 1]) {
        return CRM_Utils_System::url($path, $qs, false);
    } else {
        return $path;
    }
}

static $_civicrmSession;

/**
 * implementation of hook_init
 *
 */
function civicrm_init()
{
    if ( arg(0) != 'civicrm' && arg(0) != 'admin' && arg(0) != 'user' ) {
        return;
    }

    menu_rebuild();

    $config = CRM_Core_Config::singleton();
    CRM_Core_DAO::init($config->dsn, $config->daoDebug);
    
    $factoryClass = 'CRM_Contact_DAO_Factory';
    
    CRM_Core_DAO::setFactory(new $factoryClass());

    // Add module-specific stylesheet to page header
    drupal_set_html_head(civicrm_html_head());

    // set error handling
    PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array('CRM_Core_Error', 'handle'));
}

/**
 * exit hook, make sure we sync between drupal and crm here
 */
function civicrm_exit($destination = null)
{
    // if the user edited the user record stuff might have changed so do a resync
    if (arg(0) == 'user' && arg(2) == 'edit' && ! empty($_POST)) {
        global $user;
        CRM_Core_BAO_Drupal::synchronize($user, true);
    }
        
}

/**
 * Menu callbacks; dispatches control to the appropriate handler
 */
function civicrm_admin_invoke()
{
    // Admin classes
    if (arg(1) == 'admin') {
        $view = null;
        
        if (arg(2) == 'locationType') {
            $view = new CRM_Admin_Page_LocationType(ts('View Location Types'));
        } else if (arg(2) == 'IMProvider') {
            // IM provider
            $view = new CRM_Admin_Page_IMProvider(ts('View Instant Messenger Providers'));
        } else if (arg(2) == 'mobileProvider') {
            // Mobile provider
            $view = new CRM_Admin_Page_MobileProvider(ts('View Mobile Providers'));
        } else if (arg(2) == 'reltype') {
            // relationship type
            $view = new CRM_Admin_Page_RelationshipType(ts('View Relationship Types'));
        } else if (arg(2) == 'tag') {
            // tag
            $view = new CRM_Admin_Page_Tag(ts('View Tags'));
        } else if (arg(2) == 'custom') {
            // handle external properties
            if (arg(3) == 'group') {
                if (arg(4) != 'field') {
                    $view = new CRM_Custom_Page_Group(ts('Custom Data Group'));
                } else {
                    $breadcrumb = drupal_get_breadcrumb();
                    $breadcrumb[] = ts('<a href="%1">Custom Data</a>', array(1 => 'civicrm/admin/custom/group'));
                    drupal_set_breadcrumb($breadcrumb);
                    $view = new CRM_Custom_Page_Field(ts('Custom Data Field'));
                }
            }
        } else {
            // Categories is default local task
            $view = new CRM_Admin_Page_Tag(ts('View Tags'));
        }
        
        if ($view) {
            $view->run();
        }
    }
}

function civicrm_invoke()
{
    $session = CRM_Core_Session::singleton();

    // synchronize the drupal uid with the contacts db
    global $user;
    CRM_Core_BAO_Drupal::synchronize($user, false);
    // contact related functionality
    if (arg(1) == 'contact') {
        return civicrm_contact_invoke();
    }

    // admin related functionality
    if (arg(1) == 'admin') {
        return civicrm_admin_invoke();
    }

    // browse groups
    if (arg(1) == 'group') {
        if (arg(2) == 'add') {
            $controller = new CRM_Group_Controller(ts('Groups'), CRM_CORE_ACTION_ADD);
            $session->pushUserContext(CRM_Utils_System::url('civicrm/group', 'reset=1'));
            $controller->run();
        } else if (arg(2) == 'search') {
            // this is really a search, including here to get drupal customizations
            civicrm_search();
        } else {
            $view = new CRM_Group_Page_Group(ts('View Groups'));
            $view->run();
        }
        return;
    }

    //  import wizard
    if (arg(1) == 'import') {
        $controller = new CRM_Import_Controller(ts('Import Contacts'));
        return $controller->run();
    }

    // Default landing page is basic search (mostly for CiviCRM link in breadcrumb)
    drupal_goto(CRM_Utils_System::url('civicrm/contact/search', 'reset=1', false));
}

function civicrm_contact_invoke()
{
    // make sure we are in contact menu
    if (arg(1) != 'contact') {
        return;
    }

    if (substr(arg(2),0,3) == 'add') {
        return civicrm_form(CRM_CORE_ACTION_ADD);
    }

    // edit contact
    if (arg(2) == 'edit') {
        $breadcrumb = drupal_get_breadcrumb();
        $breadcrumb[] = ts('<a href="%1">Search Results</a>', array(1 => 'civicrm/contact/search?force=1'));
        drupal_set_breadcrumb( $breadcrumb );
        $c = civicrm_form(CRM_CORE_ACTION_UPDATE);
        return $c;
    }
 
    // view contact
    if (arg(2) == 'view') {
        $breadcrumb = drupal_get_breadcrumb();
        $breadcrumb[] = ts('<a href="%1">Search Results</a>', array(1 => 'civicrm/contact/search?force=1'));
        drupal_set_breadcrumb( $breadcrumb );
        if (arg(3) == '') {
            $view = new CRM_Contact_Page_View('',CRM_CONTACT_PAGE_VIEW_MODE_NONE);
            return $view->run();
        }

        // view contact notes
        if (arg(3) == 'note') {
            $view = new CRM_Contact_Page_View('',CRM_CONTACT_PAGE_VIEW_MODE_NOTE);
            return $view->run();
        }
        
        // view contact relationships
        if (arg(3) == 'rel') {
            $view = new CRM_Contact_Page_View('',CRM_CONTACT_PAGE_VIEW_MODE_REL);
            return $view->run();
        }
        
        // view contact groups
        if (arg(3) == 'group') {
            $view = new CRM_Contact_Page_View('',CRM_CONTACT_PAGE_VIEW_MODE_GROUP);
            return $view->run();
        }
        
        // view contact tags
        if (arg(3) == 'tag') {
            $view = new CRM_Contact_Page_View('',CRM_CONTACT_PAGE_VIEW_MODE_TAG );
            return $view->run();
        }

        // view custom data
        if (arg(3) == 'cd') {
            $view = new CRM_Contact_Page_View('',CRM_CONTACT_PAGE_VIEW_MODE_CD );
            return $view->run();
        }

        // view activities
        if (arg(3) == 'activity') {
            $view = new CRM_Contact_Page_View('',CRM_CONTACT_PAGE_VIEW_MODE_ACTIVITY);
            return $view->run();
        }
    }

    // search
    if (arg(2) == 'search') {
        return civicrm_search();
    }
    
    // delete
    if (arg(2) == 'delete') {
        if(is_numeric(arg(3))) {
            return civicrm_delete(arg(3));
        }
    }

    echo ts('We landed in a bad place, please fix<p>');
    return;
} // end of function civicrm_invoke


function civicrm_load($queryArgs)
{
}

/**
 * This function is used to load form for add/edit/view contacts (all types)
 *
 * @return this returns the form and or display content
 */
function civicrm_form($mode)
{
    CRM_Utils_System::setUserContext(array('civicrm/contact/search', 'civicrm/contact/view'));
    $wrapper = new CRM_Utils_Wrapper();
    $wrapper->run('CRM_Contact_Form_Edit', ts('Contact Page'), $mode);
}


/**
 * Temporary function to load static templates for prototyping
 */
function fetch_tpl($tpl)
{
    $baseDir = 'CRM' . DIRECTORY_SEPARATOR;
    
    $fileName = $baseDir . $tpl;
    
    $config  = CRM_Core_Config::singleton ();
    $template = SmartyTemplate::singleton($config->templateDir, $config->templateCompileDir);
    echo CRM_Utils_System::theme('page', $template->fetch($fileName));
    
}

function civicrm_assign($var, $value = null)
{
    static $template = null;
    
    if (! isset($template)) {
        $config  = CRM_Core_Config::singleton ();
        $template = SmartyTemplate::singleton($config->templateDir, $config->templateCompileDir);
    }
    
    $template->assign($var, $value);
}

/*
 *
 * queries contacts from db.
 * the query string can use the "%" pattern recognition
 * character of sql
 *
 * @return this returns the display content
 */
function civicrm_search()
{
    $session = CRM_Core_Session::singleton();

    if (arg(3) == 'saved') {
        // browse saved searches
        $savedSearchPage = new CRM_Contact_Page_SavedSearch('', CRM_CONTACT_PAGE_VIEW_MODE_NONE);
        $savedSearchPage->run();
        return;
    } else {
        if(arg(3) == 'advanced') {
            // advanced search
            $mode  = CRM_CORE_ACTION_ADVANCED;
            $title = ts('Advanced Search');
            $url   = 'civicrm/contact/search/advanced';
        } else {
            $mode  = CRM_CORE_ACTION_BASIC;
            $title = ts('Search');
            $url   = 'civicrm/contact/search';
        }
        $controller = new CRM_Contact_Controller_Search($title, $mode);
        $session->pushUserContext(CRM_Utils_System::url($url, 'force=1'));
        $controller->run();
    }

} // end of function civicrm_search

/**
 *
 * civicrm_delete()
 *
 * lists all contacts from db.
 *
 */
function civicrm_delete($id)
{
} // end of function civicrm_delete

?>
