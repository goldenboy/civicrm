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
 * This class acts as our base controller class and adds additional 
 * functionality and smarts to the base QFC. Specifically we create
 * our own action classes and handle the transitions ourselves by
 * simulating a state machine. We also create direct jump links to any
 * page that can be used universally.
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action/Direct.php';

require_once 'CRM/StateMachine.php';

class CRM_Controller extends HTML_QuickForm_Controller {

  /**
   * the state machine associated with this controller
   *
   * @var object
   */
  protected $_stateMachine;

  /**
   * This caches the content for the display system
   *
   * @var string
   */
  protected $_content;

  /**
   * All CRM single or multi page pages should inherit from this class. 
   *
   * @param string  name of the controller
   * @param boolean whether controller is modal
   *
   * @access public
   *   
   * @return void
   *
   */
  function __construct( $name, $modal ) {
    $this->HTML_QuickForm_Controller( $name, $modal );

    // if the request has a reset value, initialize the controller session
    if ( $_GET['reset'] ) {
      $this->container( true );
    }

  }

  /**
   * Process the request, overrides the default QFC run method
   * This routine actually checks if the QFC is modal and if it
   * is the first invalid page, if so it call the requested action
   * if not, it calls the display action on the first invalid page
   * avoids the issue of users hitting the back button and getting
   * a broken page
   *
   * This run is basically a composition of the original run and the
   * jump action
   *
   */
  function run( ) {
    // the names of the action and page should be saved
    // note that this is split into two, because some versions of
    // php 5.x core dump on the triple assignment :)
    $this->_actionName = $this->getActionName();
    list($pageName, $action) = $this->_actionName;
 
    if ( $this->isModal( ) ) {
      if ( ! $this->isValid( $pageName ) ) {
        $pageName = $this->findInvalid( );
        $action   = 'display';
      }
    }

    // note that based on action, control might not come back!!
    // e.g. if action is a valid JUMP, u basically do a redirect
    // to the appropriate place
    $this->_pages[$pageName]->handle($action);

    return $pageName;
  }

  /**
   * Helper function to add a jump to all the states as direct jumps. Helps
   * if we need to have links / submit buttons to goto one specific page.
   * Not needed for all forms, but does not hurt to have something like this 
   * standard for all forms
   *
   * @param array names of all the states that compose this wizard
   * @access private
   * @return void
   *
   */
  function addDirect( $stateNames ) {
    foreach ( $stateNames as $name ) {
      $this->addAction( $name, new HTML_QuickForm_Action_Direct( ) );
    }
  }

  /**
   * Helper function to add all the needed default actions. Note that the framework
   * redefines all of the default QFC actions
   *
   * @param string   directory to store all the uploaded files
   * @param array    names for the various upload buttons (note u can have more than 1 upload)
   *
   * @access private
   * @return void
   *
   */
  function addDefault( $uploadDirectory = null, $uploadNames = null ) {
    static $names = array(
                          'display'   => 'CRM_QuickForm_Action_Display',
                          'next'      => 'CRM_QuickForm_Action_Next'   ,
                          'back'      => 'CRM_QuickForm_Action_Back'   ,
                          'process'   => 'CRM_QuickForm_Action_Process',
                          'cancel'    => 'CRM_QuickForm_Action_Cancel' ,
                          'refresh'   => 'CRM_QuickForm_Action_Refresh',
                          'done'      => 'CRM_QuickForm_Action_Done'   ,
                          'jump'      => 'CRM_QuickForm_Action_Jump'   ,
                          'submit'    => 'CRM_QuickForm_Action_Submit' ,
                          );

    foreach ( $names as $name => $classPath ) {
      CRM_Utils::import( $classPath );
      $this->addAction( $name, new $classPath( $this->_stateMachine ) );
    }
    
    if ( ! empty( $uploadDirectory ) ) {
      require_once 'CRM/QuickForm/Action/Upload.php';

      $this->addAction('upload' ,
                       new CRM_QuickForm_Action_Upload ($this->_stateMachine,
                                                        $uploadDirectory,
                                                        $uploadNames));
    }
    
  }

  /**
   * getter method for stateMachine
   *
   * @return object
   * @access public
   */
  function getStateMachine( ) {
    return $this->_stateMachine;
  }

  /**
   * setter method for stateMachine
   *
   * @param object a stateMachineObject
   *
   * @return void
   * @access public
   */
 function setStateMachine( $stateMachine) {
    $this->_stateMachine = $stateMachine;
  }

  /**
   * add pages to the controller. Note that the controller does not really care
   * the order in which the pages are added
   *
   * @param object stateMachine  the state machine object
   * @param int    mode          the mode in which the state machine is operating
   *                             typicaly this will be add/view/edit
   *
   * @return void
   * @access public
   *
   */
  function addPages( $stateMachine, $mode = CRM_Form::MODE_NONE ) {
    $stateNames = array( );

    if ( ! $states ) {
      $states = $stateMachine->getStatesDescription( );
    }

    foreach ( $states as $classPath ) {
      $className  = CRM_String::getClassName( $classPath );
      $stateNames[] = $className;

      CRM_Utils::import( $classPath );

      $page = new $classPath( $className,
                              $stateMachine->find( $classPath ),
                              $mode );
      $this->addPage( $page );
    }

    $this->addDirect( $stateNames );

  }

  /**
   * QFC does not provide native support to have different 'submit' buttons.
   * We introduce this notion to QFC by using button specific data. Thus if
   * we have two submit buttons, we could have one displayed as a button and
   * the other as an image, both are of type 'submit'.
   *
   * @param string name of the button
   *
   * @return string the value of the button data (null if not present)
   * @access public
   *
   */
  function getButtonData( $buttonName ) {
    $data =& $this->container();
    
    $buttonStore =& $data['_qf_button_data'];

    return CRM_Array::value( $buttonName, $buttonStore );
  }

  /**
   * The above button data is actually stored in the session by QFC.
   * It is super important to reset this data once you have retrieved it.
   * We avoid doing it in the above routine in case the user calls the
   * getButtonData function multiple times
   *
   * @access public
   * @return void
   *
   */
  function resetButtonData( ) {
    $data =& $this->container();

    $data['_qf_button_data'] = array( );
  }

  /**
   * function to destroy all the session state of the controller.
   *
   * @access public
   * @return void
   */
  function reset( ) {
    $this->container( true );
  }

  /**
   * virtual function to do any processing of data.
   * Sometimes it is useful for the controller to actually process data.
   * This is typically used when we need the controller to figure out
   * what pages are potentially involved in this wizard. (this is dynamic
   * and can change based on the arguments
   *
   * @return void
   * @access public
   */
  function process( ) {
  }


  /**
   * setter for content
   *
   * @param string
   * @return void
   * @access public
   */
  function setContent(&$content) {
    $this->_content =& $content;
  }

  /**
   * getter for content
   *
   * @return void
   * @access public
   */
  function &getContent() {
    return $this->_content;
  }

}

?>