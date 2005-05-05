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

class CRM_Core_ShowHideBlocks {

    protected $_show;

    protected $_hide;

    protected $_hideString;

    protected $_showString;

    function __construct( $show = null, $hide = null ) {
        if ( ! empty( $show ) ) {
            $this->_show = $show;
        } else {
            $this->_show = array( );
        }

        if ( ! empty( $hide ) ) {
            $this->_hide = $hide;
        } else {
            $this->_hide = array( );
        }

    }

    function addToTemplate( ) {
        $this->_hideString = '';

        $first = true;
        foreach ( array_keys( $this->_hide ) as $h ) {
            if ( ! $first ) {
                $this->_hideString .= ',';
            }
            $this->_hideString .= "'$h'";
            $first = false;
        }

        $first = true;
        foreach ( array_keys( $this->_show ) as $s ) {
            if ( ! $first ) {
                $this->_showString .= ',';
            }
            $this->_showString .= "'$s'";
            $first = false;
        }

        $template = CRM_Core_Smarty::singleton( );
        $template->assign_by_ref( 'hideBlocks', $this->_hideString );
        $template->assign_by_ref( 'showBlocks', $this->_showString );
    }

    function addShow( $name ) {
        $this->_show[$name] = 1;
        if ( array_key_exists( $name, $this->_hide ) ) {
            unset( $this->_hide[$name] );
        }
    }

    function addHide( $name ) {
        $this->_hide[$name] = 1;
        if ( array_key_exists( $name, $this->_show ) ) {
            unset( $this->_show[$name] );
        }
     }

    function linkHtml( $name, $href, $text, $js ) {
        return '<a name="' . $name . '" id="' . $name . '" href="' . $href . '" ' . $js . ">$text</a>";
    }

    function links( $form, $prefix, $showLinkText, $hideLinkText ) {
        $showCode = "show('${prefix}'); hide('${prefix}[show]'); return false;";
        $hideCode = "hide('${prefix}'); show('${prefix}[show]'); return false;";
        
        $values = array();
        $values['show'] = self::linkHtml("${prefix}[show]", "#${prefix}", $showLinkText, "onclick=\"$showCode\"");
        $values['hide'] = self::linkHtml("${prefix}[hide]", "#${prefix}", $hideLinkText, "onclick=\"$hideCode\"");
        $form->assign( $prefix, $values);

        /**
         * creating and using form elements seems quite expensive, and hence we bypass QFC for this.
         * note that we introduce a fair number of show/hide links and hence this is a decent savings.
         * we need to figure out how to handle nested arrays, defering for now
         *
         *        $form->addElement('link', "${prefix}[show]", null, "#${prefix}", $showLinkText,
         *                array( 'onclick' => "$showCode" ));
         *        $form->addElement('link', "${prefix}[hide]", null, "#${prefix}", $hideLinkText,
         *                          array('onclick' => "$hideCode" ));
         **/
    }

    function linksForArray( $form, $index, $maxIndex, $prefix, $showLinkText, $hideLinkText ) {
        if ( $index == $maxIndex ) {
            $showCode = $hideCode = "return false;";
        } else {
            $next = $index + 1;
            $showCode = "show('${prefix}[${next}][show]'); return false;";
            $hideCode = "hide('${prefix}[${next}][show]'); hide('${prefix}[${next}]'); return false;";
        }

        $form->addElement('link', "${prefix}[${index}][show]", null, "#${prefix}[${index}]", $showLinkText,
                          array( 'onclick' => "hide('${prefix}[${index}][show]'); show('${prefix}[${index}]');" . $showCode));
        $form->addElement('link', "${prefix}[${index}][hide]", null, "#${prefix}[${index}]", $hideLinkText,
                          array('onclick' => "hide('${prefix}[${index}]'); show('${prefix}[${index}][show]');" . $hideCode));
    }

}

?>