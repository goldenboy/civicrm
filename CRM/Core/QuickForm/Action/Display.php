<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
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
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 * Redefine the display action.
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Core/QuickForm/Action.php';

require_once 'CRM/Core/Config.php';

class CRM_Core_QuickForm_Action_Display extends CRM_Core_QuickForm_Action {

    /**
     * the template to display the required "red" asterick
     * @var string
     */
    static $_requiredTemplate = null;

    /**
     * the template to display error messages inline with the form element
     * @var string
     */
    static $_errorTemplate    = null;
    
    /**
     * class constructor
     *
     * @param object $stateMachine reference to state machine object
     *
     * @return object
     * @access public
     */
    function __construct( &$stateMachine ) {
        parent::__construct( $stateMachine );
    }

    /**
     * Processes the request.
     *
     * @param  object    $page       CRM_Core_Form the current form-page
     * @param  string    $actionName Current action name, as one Action object can serve multiple actions
     *
     * @return void
     * @access public
     */
    function perform(&$page, $actionName) {
        $pageName = $page->getAttribute('id');

        // If the original action was 'display' and we have values in container then we load them
        // BTW, if the page was invalid, we should later call validate() to get the errors
        list(, $oldName) = $page->controller->getActionName();
        if ('display' == $oldName) {
            // If the controller is "modal" we should not allow direct access to a page
            // unless all previous pages are valid (see also bug #2323)
            if ($page->controller->isModal() && !$page->controller->isValid($page->getAttribute('id'))) {
                $target =& $page->controller->getPage($page->controller->findInvalid());
                return $target->handle('jump');
            }
            $data =& $page->controller->container();
            if (!empty($data['values'][$pageName])) {
                $page->loadValues($data['values'][$pageName]);
                $validate = false === $data['valid'][$pageName];
            }
        }

        // set "common" defaults and constants
        $page->controller->applyDefaults($pageName);
        $page->isFormBuilt() or $page->buildForm();
        // if we had errors we should show them again
        if (isset($validate) && $validate) {
            $page->validate();
        }

        return $this->renderForm($page);
    }

    /**
     * render the page using a custom templating
     * system
     *
     * @param object  $page the CRM_Core_Form page
     * @param boolean $ret  should we echo or return output
     *
     * @return void
     * @access public
     */
    function renderForm(&$page, $ret = false) {
        $this->_setRenderTemplates($page);
        $template =& CRM_Core_Smarty::singleton( );
        $template->assign( 'form'   ,  $page->toSmarty());
        $template->assign( 'isForm' , 1 );

        $controller =& $page->controller;
        if ( $controller->getEmbedded( ) ) {
            return;
        }

        $template->assign( 'action' , $page->getAction( ) );
        $template->assign( 'tplFile', $page->getTemplateFileName() ); 

        $content = $template->fetch( $controller->getTemplateFile( ) );

        $html = CRM_Utils_System::theme( 'page', $content, null, $controller->getPrint( ) );
        if ( $ret ) {
            return $html;
        }

        echo $html;
        return;
    }

    /**
     * set the various rendering templates
     *
     * @param object  $page the CRM_Core_Form page
     *
     * @return void
     * @access public
     */
    function _setRenderTemplates(&$page) {
        if ( self::$_requiredTemplate === null ) {
            $this->initializeTemplates();
        }

        $renderer =& $page->getRenderer();
    
        $renderer->setRequiredTemplate( self::$_requiredTemplate );
        $renderer->setErrorTemplate   ( self::$_errorTemplate    );
    }

    /**
     * initialize the various templates
     *
     * @param object  $page the CRM_Core_Form page
     *
     * @return void
     * @access public
     */
    function initializeTemplates() {
        if ( self::$_requiredTemplate !== null ) {
            return;
        }

        $config =& CRM_Core_Config::singleton();
        self::$_requiredTemplate = file_get_contents( $config->templateDir . '/CRM/Form/label.tpl' );
        self::$_errorTemplate    = file_get_contents( $config->templateDir . '/CRM/Form/error.tpl' );
    }

}

?>