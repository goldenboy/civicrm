<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
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
 * This file contains the various menus of the CiviCRM module
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

require_once 'CRM/Core/I18n.php';

class CRM_Core_Menu 
{
    /**
     * the list of menu items
     * 
     * @var array
     * @static
     */
    static $_items = null;

    /**
     * the list of permissioned menu items
     * 
     * @var array
     * @static
     */
    static $_permissionedItems = null;

    static $_serializedElements = array( 'access_arguments',
                                         'access_callback' ,
                                         'page_arguments'  ,
                                         'page_callback'   ,
                                         'breadcrumb'      );

    static $_menuCache = null;
    
    static $_navigationCache = null;

    const
        MENU_ITEM  = 1;

    static function &xmlItems( ) {
        if ( ! self::$_items ) {
            $config =& CRM_Core_Config::singleton( );

            // We needs this until Core becomes a component
            $coreMenuFilesNamespace = 'CRM_Core_xml_Menu';
            $coreMenuFilesPath = str_replace('_', DIRECTORY_SEPARATOR, $coreMenuFilesNamespace );
            global $civicrm_root;
            $files = CRM_Utils_File::getFilesByExtension( $civicrm_root . DIRECTORY_SEPARATOR . $coreMenuFilesPath, 'xml' );

            // Grab component menu files
            $files = array_merge( $files,
                                  CRM_Core_Component::xmlMenu( ) );
                                  
            // lets call a hook and get any additional files if needed
            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::xmlMenu( $files );

            self::$_items = array( );
            foreach ( $files as $file ) {
                self::read( $file, self::$_items );
            }
        }

        return self::$_items;
    }
    
    static function read( $name, &$menu ) {

        $config =& CRM_Core_Config::singleton( );

        $xml = simplexml_load_file( $name );
        foreach ( $xml->item as $item ) {
            
            //do not expose 'Access Control' link for Joomla, CRM-3550
            if ( $item->path == 'civicrm/admin/access' &&
                 $config->userFramework == 'Joomla' ) {
                continue;
            }
            
            if ( ! (string ) $item->path ) {
                CRM_Core_Error::debug( 'i', $item );
                CRM_Core_Error::fatal( );
            }
            $path = (string ) $item->path;
            $menu[$path] = array( );
            unset( $item->path );
            foreach ( $item as $key => $value ) {
                $key   = (string ) $key;
                $value = (string ) $value;
                if ( strpos( $key, '_callback' ) &&
                     strpos( $value, '::' ) ) {
                    $value = explode( '::', $value );
                } else if ( $key == 'access_arguments' ) {
                    if ( strpos( $value, ',' ) ||
                         strpos( $value, ';' ) ) {
                        if ( strpos( $value, ',' ) ) {
                            $elements = explode( ',', $value );
                            $op = 'and';
                        } else {
                            $elements = explode( ';', $element );
                            $op = 'or';
                        }
                        $items = array( );
                        foreach ( $elements as $element ) {
                            $items[] = $element;
                        }
                        $value = array( $items, $op );
                    } else {
                        $value = array( array( $value ), 'and' );
                    }
                } else if ( $key == 'is_public' || $key == 'is_ssl' ) {
                    $value = ( $value == 'true' || $value == 1 ) ? 1 : 0;
                }
                $menu[$path][$key] = $value;
            }
        }
    }

    /**
     * This function defines information for various menu items
     *
     * @static
     * @access public
     */
    static function &items( ) 
    {
        return self::xmlItems( );
    }

    static function isArrayTrue( &$values ) {
        foreach ( $values as $name => $value ) {
            if ( ! $value ) {
                return false;
            }
        }
        return true;
    }

    static function fillMenuValues( &$menu, $path ) {
        $fieldsToPropagate = array( 'access_callback',
                                    'access_arguments',
                                    'page_callback',
                                    'page_arguments',
                                    'is_ssl' );
        $fieldsPresent = array( );
        foreach ( $fieldsToPropagate as $field ) {
            $fieldsPresent[$field] = CRM_Utils_Array::value( $field, $menu[$path] ) !== null ?
                true : false;
        }

        $args = explode( '/', $path );
        while ( ! self::isArrayTrue( $fieldsPresent ) &&
                ! empty( $args ) ) {

            array_pop( $args );
            $parentPath = implode( '/', $args );

            foreach ( $fieldsToPropagate as $field ) {
                if ( ! $fieldsPresent[$field] ) {
                    if ( CRM_Utils_Array::value( $field, $menu[$parentPath] ) !== null ) {
                        $fieldsPresent[$field] = true;
                        $menu[$path][$field] = $menu[$parentPath][$field];
                    }
                }
            }
        }

        if ( self::isArrayTrue( $fieldsPresent ) ) {
            return;
        }

        $messages = array( );
        foreach ( $fieldsToPropagate as $field ) {
            if ( ! $fieldsPresent[$field] ) {
                $messages[] = ts( "Could not find %1 in path tree",
                                  array( 1 => $field ) );
            }
        }
        CRM_Core_Error::fatal( "'$path': " . implode( ', ', $messages ) );
    }

    /**
     * We use this function to
     * 
     * 1. Compute the breadcrumb
     * 2. Compute local tasks value if any
     * 3. Propagate access argument, access callback, page callback to the menu item
     * 4. Build the global navigation block
     * 
     */
    static function build( &$menu ) {
        foreach ( $menu as $path => $menuItems ) {
            self::buildBreadcrumb ( $menu, $path );
            self::fillMenuValues  ( $menu, $path );
            self::fillComponentIds( $menu, $path );
            self::buildReturnUrl  ( $menu, $path );

            // add add page_type if not present
            if ( ! isset( $menu[$path]['page_type'] ) ) {
                $menu[$path]['page_type'] = 0;
            }

        }

        self::buildNavigation( $menu );

        self::buildAdminLinks( $menu );
    }

    static function store( ) {
        // first clean up the db
        $query = 'TRUNCATE civicrm_menu';
        CRM_Core_DAO::executeQuery( $query );

        $menu =& self::items( );

        self::build( $menu );

        require_once "CRM/Core/DAO/Menu.php";

        foreach ( $menu as $path => $item ) {
            $menu  =& new CRM_Core_DAO_Menu( );
            $menu->path      = $path;

            $menu->find( true );
            
            $menu->copyValues( $item );

            foreach ( self::$_serializedElements as $element ) {
                if ( ! isset( $item[$element] ) ||
                     $item[$element] == 'null' ) {
                    $menu->$element = null;
                } else {
                    $menu->$element = serialize( $item[$element] );
                }
            }

            $menu->save( );
        }
    }

    static function buildNavigation( &$menu ) {

        $compNames = CRM_Core_Component::getNames( true );
        foreach( $compNames as $donCare => $name ) {
            $elements[$name] = 1;
        }
        // supplement the list with additional non-component positions
        $elements[ts('Logout')] = 1;
        $elements[ts('Import')] = 1;

        $values = array( );
        foreach ( $menu as $path => $item ) {
            if ( ! CRM_Utils_Array::value( 'page_type', $item ) ) {
                continue;
            }

            if ( $item['page_type'] ==  CRM_Core_Menu::MENU_ITEM ) {
                $query = CRM_Utils_Array::value( 'path_arguments', $item ) 
                    ? str_replace(',', '&', $item['path_arguments']) . '&reset=1' : 'reset=1';
                
                $value = array( );
                $value['url'  ]  = CRM_Utils_System::url( $path, $query, false );
                $value['title']  = $item['title'];
                $value['path']   = $path;
                $value['access_callback' ] = $item['access_callback' ];
                $value['access_arguments'] = $item['access_arguments'];
                $value['component_id'    ] = $item['component_id'    ];
                
                if ( array_key_exists( $item['title'], $elements ) ) {
                    $value['class']  = 'collapsed';
                } else {
                    $value['class']  = 'leaf';
                }
                $value['parent'] = null;
                $value['start']  = $value['end'] = null;
                $value['active'] = '';

                // check if there is a parent
                foreach ( $values as $weight => $v ) {
                    if ( strpos( $path, $v['path'] ) !== false) {
                        $value['parent'] = $weight;

                        // only reset if still a leaf
                        if ( $values[$weight]['class'] == 'leaf' ) {
                            $values[$weight]['class'] = 'collapsed';
                        }
                    }
                }
                
                $values[$item['weight'] . '.' . $item['title']] = $value;
            }
        }

        $menu['navigation'] = array( 'breadcrumb' => $values );
    }

    static function buildAdminLinks( &$menu ) {
        $values = array( );

        foreach ( $menu as $path => $item ) {
            if ( ! CRM_Utils_Array::value( 'adminGroup', $item ) ) {
                continue;
            }

            $query = CRM_Utils_Array::value( 'path_arguments', $item ) 
                ? str_replace(',', '&', $item['path_arguments']) . '&reset=1' : 'reset=1';
            
            $value = array( 'title' => $item['title'],
                            'desc'  => $item['desc'],
                            'id'    => strtr($item['title'], array('('=>'_', ')'=>'', ' '=>'',
                                                                   ','=>'_', '/'=>'_' 
                                                                   )
                                             ),
                            'url'   => CRM_Utils_System::url( $path, $query, false ), 
                            'icon'  => $item['icon'],
                            'extra' => CRM_Utils_Array::value( 'extra', $item ) );
            if ( ! array_key_exists( $item['adminGroup'], $values ) ) {
                $values[$item['adminGroup']] = array( );
                $values[$item['adminGroup']]['fields'] = array( );
            }
            $values[$item['adminGroup']]['fields'][$item['weight'] . '.' . $item['title']] = $value;
            $values[$item['adminGroup']]['component_id'] = $item['component_id'];
        }

        foreach( $values as $group => $dontCare ) {
            $values[$group]['perColumn'] = round( count( $values[$group]['fields'] ) / 2 );
            ksort( $values[$group] );
        }

        $menu['admin'] = array( 'breadcrumb' => $values );
    }

    static function &getNavigation( $all = false ) {
        if ( ! self::$_menuCache ) {
            self::get( 'navigation' );
        }
        
        $config =& CRM_Core_Config::singleton( );
        if ( CRM_Utils_Array::value( $config->userFrameworkURLVar, $_GET ) == 'civicrm/upgrade' ) {
            return array( );
        }
        
        if ( ! array_key_exists( 'navigation', self::$_menuCache ) ) {
            // problem could be due to menu table empty. Just do a
            // menu store and try again
            self::store( );            

	        // here we goo 
            self::get( 'navigation' );
	        if ( ! array_key_exists( 'navigation', self::$_menuCache ) ) {
	            CRM_Core_Error::fatal( );
	        }
        }
        $nav =& self::$_menuCache['navigation'];

        if ( ! $nav ||
             ! isset( $nav['breadcrumb'] ) ) {
            return null;
        }

        $values =& $nav['breadcrumb'];
        $config =& CRM_Core_Config::singleton( );
        foreach ( $values as $index => $item ) {
            if ( strpos( CRM_Utils_Array::value( $config->userFrameworkURLVar, $_REQUEST ),
                         $item['path'] ) === 0 ) {
                $values[$index]['active'] = 'class="active"';
            } else {
                $values[$index]['active'] = '';
            }

            if ( $values[$index]['parent'] ) {
                $parent = $values[$index]['parent'];

                // only reset if still a leaf
                if ( $values[$parent]['class'] == 'leaf' ) {
                    $values[$parent]['class'] = 'collapsed';
                }

                // if a child or the parent is active, expand the menu
                if ( $values[$index ]['active'] ||
                     $values[$parent]['active'] ) {
                    $values[$parent]['class'] = 'expanded';
                }
                    
                // make the parent inactive if the child is active
                if ( $values[$index ]['active'] &&
                     $values[$parent]['active'] ) { 
                    $values[$parent]['active'] = '';
                }
            }
        }


        if ( ! $all ) {
            // remove all collapsed menu items from the array
            foreach ( $values as $weight => $v ) {
                if ( $v['parent'] &&
                     $values[$v['parent']]['class'] == 'collapsed' ) {
                    unset( $values[$weight] );
                }
            }
        }

        // check permissions for the rest
        require_once 'CRM/Core/Permission.php';
        $activeChildren = array( );
            
        foreach ( $values as $weight => $v ) {
            if ( CRM_Core_Permission::checkMenuItem( $v ) ) {
                if ( $v['parent'] ) {
                    $activeChildren[] = $weight;
                }
            } else {
                unset( $values[$weight] );
            }
        }

        // add the start / end tags
        $len = count($activeChildren) - 1;
        if ( $len >= 0 ) {
            $values[$activeChildren[0   ]]['start'] = true;
            $values[$activeChildren[$len]]['end'  ] = true;
        }

        ksort($values, SORT_NUMERIC );
        $i18n =& CRM_Core_I18n::singleton();
        $i18n->localizeTitles($values);
        
        return $values;
    }

    static function &getAdminLinks( ) {
        $links =& self::get( 'admin' );

        if ( ! $links ||
             ! isset( $links['breadcrumb'] ) ) {
            return null;
        }

        $values =& $links['breadcrumb'];
        return $values;
    }

    /**
     * Get the breadcrumb for a given path.
     *
     * @param  array   $menu   An array of all the menu items.
     * @param  string  $path   Path for which breadcrumb is to be build.
     *
     * @return array  The breadcrumb for this path
     *
     * @static
     * @access public
     */
    static function buildBreadcrumb( &$menu, $path ) {
        $crumbs       = array( );

        $pathElements = explode('/', $path);
        array_pop( $pathElements );

        $currentPath = null;
        while ( $newPath = array_shift($pathElements) ) {
            $currentPath = $currentPath ? ($currentPath . '/' . $newPath) : $newPath;
            
            // when we come accross breadcrumb which involves ids,
            // we should skip now and later on append dynamically.
            if ( isset( $menu[$currentPath]['skipBreadcrumb'] ) ) {
                continue;
            }
            
            // add to crumb, if current-path exists in params.
            if ( array_key_exists( $currentPath, $menu ) &&
                 isset( $menu[$currentPath]['title'] ) ) {
                $urlVar = CRM_Utils_Array::value('path_arguments', $menu[$currentPath]) ? 
                    '&' . $menu[$currentPath]['path_arguments'] : '';
                $crumbs[] = array('title' => $menu[$currentPath]['title'], 
                                  'url'   => CRM_Utils_System::url( $currentPath, 
                                                                    'reset=1' . $urlVar, false ));
            }
        }
        $menu[$path]['breadcrumb'] = $crumbs;

        return $crumbs;
    }

    static function buildReturnUrl( &$menu, $path ) {
        if ( ! isset($menu[$path]['return_url']) ) {
            list( $menu[$path]['return_url'], $menu[$path]['return_url_args'] ) = 
                self::getReturnUrl( $menu, $path );
        }
    }
    
    static function getReturnUrl( &$menu, $path ) {
        if ( ! isset($menu[$path]['return_url']) ) {
            $pathElements   = explode('/', $path);
            array_pop( $pathElements );
            
            if ( empty($pathElements) ) {
                return array( null, null );
            }
            $newPath = implode( '/', $pathElements );

            return self::getReturnUrl( $menu, $newPath );
        } else {
            return array( CRM_Utils_Array::value( 'return_url',
                                                  $menu[$path] ),
                          CRM_Utils_Array::value( 'return_url_args',
                                                  $menu[$path] ) );
        }
    }

    static function fillComponentIds( &$menu, $path ) {
        static $cache = array( );

        if (array_key_exists('component_id', $menu[$path])) {
            return;
        }
        
        $args = explode('/', $path);

        if ( count($args) > 1 ) {
            $compPath  = $args[0] . '/' . $args[1];
        } else {
            $compPath  = $args[0];
        }    
        
        $componentId = null;

        if ( array_key_exists($compPath, $cache) ) {
            $menu[$path]['component_id'] = $cache[$compPath];
        } else {
            if ( CRM_Utils_Array::value( 'component', $menu[$compPath] ) ) {
                $componentId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Component', 
                                                            $menu[$compPath]['component'], 
                                                            'id', 'name' );
            }
            $menu[$path]['component_id'] = $componentId ? $componentId : null;
            $cache[$compPath] = $menu[$path]['component_id'];
        }
    }

    static function get( $path )
    {
        // return null if menu rebuild
        $config =& CRM_Core_Config::singleton( );

        $params = array( );

        $args = explode( '/', $path );

        $elements = array( );
        while ( ! empty( $args ) ) {
            $elements[] = "'" . implode( '/', $args ) . "'";
            array_pop( $args );
        }

        $queryString = implode( ', ', $elements );
        
        $query = "
( 
  SELECT * 
  FROM     civicrm_menu 
  WHERE    path in ( $queryString )
  ORDER BY length(path) DESC
  LIMIT    1 
)
";

        if ( $path != 'navigation' ) {
            $query .= "
UNION ( 
  SELECT *
  FROM   civicrm_menu 
  WHERE   path IN ( 'navigation' )
)
";
        }
        
        require_once "CRM/Core/DAO/Menu.php";
        $menu  =& new CRM_Core_DAO_Menu( );
        $menu->query( $query );

        self::$_menuCache = array( );
        $menuPath = null;
        while ( $menu->fetch( ) ) {
            self::$_menuCache[$menu->path] = array( );
            CRM_Core_DAO::storeValues( $menu, self::$_menuCache[$menu->path] );

            foreach ( self::$_serializedElements as $element ) {
                self::$_menuCache[$menu->path][$element] = unserialize( $menu->$element );
                
                if ( strpos( $path, $menu->path ) !== false ) {
                    $menuPath =& self::$_menuCache[$menu->path];
                }
            }
        }
        
        // *FIXME* : hack for 2.1 -> 2.2 upgrades. The below block of code 
        // can be safely removed for v2.3.
        if ( $path == 'civicrm/upgrade' ) {
            $menuPath['page_callback']         = 'CRM_Upgrade_Page_Upgrade';
            $menuPath['access_arguments'][0][] = 'administer CiviCRM';
            $menuPath['access_callback']       = array('CRM_Core_Permission', 'checkMenu');
        }

        $i18n =& CRM_Core_I18n::singleton();
        $i18n->localizeTitles($menuPath);
        return $menuPath;
    }

    static function getArrayForPathArgs( $pathArgs )
    {
        if (! is_string($pathArgs)) {
            return;
        }
        $args = array();

        $elements = explode( ',', $pathArgs );
        //CRM_Core_Error::debug( 'e', $elements );
        foreach ( $elements as $keyVal ) {
            list($key, $val) = explode( '=', $keyVal );
            $arr[$key] = $val;
        }

        if (array_key_exists('urlToSession', $arr)) {
            $urlToSession = array( );

            $params = explode( ';', $arr['urlToSession'] );
            $count  = 0;
            foreach ( $params as $keyVal ) {
                list($urlToSession[$count]['urlVar'], 
                     $urlToSession[$count]['sessionVar'], 
                     $urlToSession[$count]['type'], 
                     $urlToSession[$count]['default'] ) = explode( ':', $keyVal );
                $count++;
            }
            $arr['urlToSession'] = $urlToSession; 
        }
        return $arr;
    }
    
    /**
     * Function to get existing / build navigation for CiviCRM Admin Menu
     */
    static function retrieveNavigation(  ) {
        if ( ! self::$_navigationCache ) {
            $navigationArray = self::parseNavigation( true );
            $titleClause = implode( ',', array_keys($navigationArray) );
        
            $query = "
              SELECT * 
              FROM     civicrm_menu 
              WHERE    title in ( $titleClause )";

            require_once "CRM/Core/DAO/Menu.php";
            $menu  =& new CRM_Core_DAO_Menu( );
            $menu->query( $query );

            $validMenus = array();
            while ( $menu->fetch() ) {
                $path = $menu->path;
                $query = $menu->path_arguments 
                     ? str_replace(',', '&', $menu->path_arguments) . '&reset=1' : 'reset=1';
            
                $value = array( );
                $value['url'  ]  = CRM_Utils_System::url( $path, $query, false );
                $value['title']  = $menu->title;
                $value['path']   = $path;
                $value['access_callback' ] = unserialize($menu->access_callback);
                $value['access_arguments'] = unserialize($menu->access_arguments);
                $value['component_id'    ] = $menu->component_id;
               
                // check permission
                if ( CRM_Core_Permission::checkMenuItem( $value ) ) {
                    $validMenus[$value['title']] = $value;
                }
            }    
            self::$_navigationCache = $validMenus;        
        }
             
        return self::$_navigationCache;
    }
    
    /**
     * Function to create navigation for CiviCRM Admin Menu
     */
    static function createNavigation(  ) {
        //retrieveNavigation       
        $menuString = self::parseNavigation( );
        return $menuString;
    }
    
    static function parseNavigation( $flatList = false ) {
        $config =& CRM_Core_Config::singleton( );
        $navigationXML = "{$config->userFrameworkResourceURL}/templates/CRM/xml/Navigation.xml";
        $dom = DomDocument::load( $navigationXML );
        $dom->xinclude( );
        $menuXML = simplexml_import_dom( $dom );
        
        $object = null;
        foreach($menuXML->children() as $children) {
            if ( !$flatList ) {
                $name = self::getMenuName( $children );
                if ( $name ) { 
                    $object .= '<li>' . $name;
                    self::recurseNavigation( $children, $object  );
                }
            } else {
                if ( !isset( $children['group'] ) ) {
                    $object["'{$children['key']}'"] = 1;
                }
                self::recurseNavigation( $children, $object, true );
            }
        }
        
        return $object;
    }

    /**
     * Recursively check child menus
     */
    function recurseNavigation(&$child, &$object, $flatList = false ) {
        if ( !$flatList ) {
            if ( count( $child->children() ) > 0 ) {
                $object .= '<ul>';  
            } else {
                $object .= '</li>'; 
            }

            foreach($child->children() as $children) {
                $name = self::getMenuName( $children );
                if ( $name ) { 
                    $object .= '<li>' . $name;
                    self::recurseNavigation($children, $object );
                }
            }

            if ( count( $child->children() ) > 0 ) {
                $object .= '</ul></li>';
            }
        } else {
            foreach($child->children() as $children) {
                if ( !isset( $children['group'] ) ) {
                    $object["'{$children['key']}'"] = 1;
                }
                self::recurseNavigation( $children, $object, true );
            }
        }
        return $object;
     }
     
     /**
      *  Get Menu name
      */
     function getMenuName( &$children ) {
         $name = $children['key'];
         
         //localize the label     
         $i18n =& CRM_Core_I18n::singleton();
         $menuTitleArray = array( 'title' => $name );
         $i18n->localizeTitles($menuTitleArray);
         $name = $menuTitleArray['title'];
         
         if ( isset( $children['label'] ) ) {
             $name = $children['label'];
         }

         if ( !isset( $children['group'] ) ) {
             if ( isset( $children['url'] ) && substr( $children['url'], 0, 4 ) === 'http' ) {
                 $url = $children['url'];
             } else {
                 // get url from civicrm based on permission                
                 $validMenus = self::retrieveNavigation( );
                 
                 // get the url for menus
                 $urlFound = false;
                 foreach ( $validMenus as $key => $values ) {
                     if ( $values['title'] == $children['key'] ) {
                         $url  = $values['url'];
                         $urlFound = true;
                         break;
                     }
                 }
                 
                 if ( !$urlFound ) {
                     return false;
                 }
             }
            $name = '<a href=' . $url . '>'. $name .'</a>';
         }
         
         return $name;
     }
}


