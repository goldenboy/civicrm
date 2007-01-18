<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | Foundation at info[AT]civicrm[DOT]org. If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Event/Form/ManageEvent.php';
require_once 'CRM/Event/BAO/EventPage.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_ManageEvent_Registration extends CRM_Event_Form_ManageEvent
{
    /**
     * what blocks should we show and hide.
     *
     * @var CRM_Core_ShowHideBlocks
     */
    protected $_showHide;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) {
        parent::preProcess( );
    }

    /**
     * This function sets the default values for the form. 
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $eventId = $this->_id;

        $defaults = parent::setDefaultValues( );
        $this->setShowHide( $defaults );
        if ( isset( $eventId ) ) {
            $params = array( 'event_id' => $eventId );
            CRM_Event_BAO_EventPage::retrieve( $params, $defaults );
            
            require_once 'CRM/Core/BAO/UFJoin.php';
            $ufJoinParams = array( 'entity_table' => 'civicrm_event',
                                   'entity_id'    => $eventId,
                                   'weight'       => 1 );

            $defaults['custom_pre_id'] = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );
            
            $ufJoinParams['weight'] = 2;
            $defaults['custom_post_id'] = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );
        }
        return $defaults;
    }   
    
    /**
     * Fix what blocks to show/hide based on the default values set
     *
     * @param array   $defaults the array of default values
     * @param boolean $force    should we set show hide based on input defaults
     *
     * @return void
     */
    function setShowHide( &$defaults) 
    {
        require_once 'CRM/Core/ShowHideBlocks.php';
        $this->_showHide =& new CRM_Core_ShowHideBlocks( array('registration_show' => 1 ),
                                                         '') ;
        if ( empty($defaults)) {
            $this->_showHide->addShow( 'registration_show' );
            $this->_showHide->addShow( 'confirm_show' );
            $this->_showHide->addShow( 'mail_show' );
            $this->_showHide->addHide( 'registration' );
            $this->_showHide->addHide( 'confirm' );
            $this->_showHide->addHide( 'mail' );
        } else {
            $this->_showHide->addShow( 'registration' );
            $this->_showHide->addShow( 'confirm' );
            $this->_showHide->addShow( 'mail' );
            $this->_showHide->addHide( 'registration_show' );
            $this->_showHide->addHide( 'confirm_show' );            
            $this->_showHide->addHide( 'mail_show' );
        }
        $this->_showHide->addToTemplate( );
    }

    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    { 
        $this->applyFilter('__ALL__', 'trim');

        $this->addElement('checkbox', 'is_online_registration', ts('Allow Online Registration?'),null,array('onclick' =>"return showHideByValue('is_online_registration','','registrationLink|registration','block','radio',false);")); 
        
        $this->add('text','registration_link_text',ts('Registration Link Text'));
       
        self::buildRegistrationBlock( $this );
        self::buildConfirmationBlock( $this );
        self::buildMailBlock( $this );

        parent::buildQuickForm();
    }
    
    /**
     * Function to build Registration Block  
     * 
     * @param int $pageId 
     * @static
     */
    function buildRegistrationBlock( $form ) 
    {
        $form->add('textarea','intro_text',ts('Intro Text'), array("rows"=>6,"cols"=>80));
        $form->add('textarea','footer_text',ts('Footer Text'), array("rows"=>6,"cols"=>80));
        $form->add('select', 'custom_pre_id', ts('Custom Fields'),array(''=>'-select-') + CRM_Core_PseudoConstant::ufGroup( ));
        $form->add('select', 'custom_post_id', ts('Custom Fields'),array(''=>'-select-')+ CRM_Core_PseudoConstant::ufGroup( ));
    }

    /**
     * Function to build Confirmation Block  
     * 
     * @param int $pageId 
     * @static
     */
    function buildConfirmationBlock( $form) 
    {
        $form->add('text','confirm_title',ts('Title '));   
        $form->add('textarea','confirm_text',ts('Intro Text'), array("rows"=>6,"cols"=>80));
        $form->add('textarea','confirm_footer_text',ts('Footer Text'), array("rows"=>6,"cols"=>80));
    }

    /**
     * Function to build Email Block  
     * 
     * @param int $pageId 
     * @static
     */
    function buildMailBlock( $form ) 
    {
        $form->addYesNo( 'is_email_confirm', ts( 'Send Confirmation Email?' ) , null, null, array('onclick' =>"return showHideByValue('is_email_confirm','','confirmEmail','block','radio',false);"));
        $form->add('textarea','confirm_email_text',ts('Text'), array("rows"=>2,"cols"=>60));
        $form->add('text','cc_confirm',ts('CC Confirmation To '));
        $form->addRule( "cc_confirm", ts('Email is not valid.'), 'email' );  
        $form->add('text','bcc_confirm',ts('BCC Confirmation To '));  
        $form->addRule( "bcc_confirm", ts('Email is not valid.'), 'email' );  
    }


    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        $this->addFormRule( array( 'CRM_Event_Form_ManageEvent_Registration', 'formRule' ) );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( &$values ) 
    {
        if ( $values['is_online_registration'] ) {
            //intro text
            if ( !$values['intro_text'] ) {
                $errorMsg['intro_text'] = "Please enter Introduction text.";;
            }

            if ( !$values['confirm_title'] ) {
                $errorMsg['confirm_title'] = "Please enter Confirmation text.";;
            }

            if ( !$values['confirm_text'] ) {
                $errorMsg['confirm_text'] = "Please enter Confirmation text.";;
            }

            if ( $values['is_email_confirm'] && !$values['confirm_email_text'] ) {
                $errorMsg['confirm_email_text'] = "Please enter Email Confirmation text.";;
            }
        }
        
        if ( !empty($errorMsg) ) {
            return $errorMsg;
        }

        return true;
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $ids = array();
        $params = $this->exportValues();

        if ( $this->_action & CRM_Core_Action::COPY ) {
            $eventId = $this->get('eventId');
        } else {
            $eventId = $this->_id;
        }

        $params['event_id'] = $ids['event_id'] = $eventId;

        //format params
        $params['is_online_registration'] = CRM_Utils_Array::value('is_online_registration', $params, false);

        require_once 'CRM/Event/BAO/Event.php';
        CRM_Event_BAO_Event::add($params ,$ids);

        CRM_Event_BAO_EventPage::add( $params );

        // also update the ProfileModule tables 
        $ufJoinParams = array( 'is_active'    => 1, 
                               'module'       => 'CiviEvent',
                               'entity_table' => 'civicrm_event', 
                               'entity_id'    => $eventId, 
                               'weight'       => 1, 
                               'uf_group_id'  => $params['custom_pre_id'] ); 
        
        require_once 'CRM/Core/BAO/UFJoin.php';
        CRM_Core_BAO_UFJoin::create( $ufJoinParams ); 

        $ufJoinParams['weight'     ] = 2; 
        $ufJoinParams['uf_group_id'] = $params['custom_post_id'];  
        CRM_Core_BAO_UFJoin::create( $ufJoinParams ); 

        CRM_Core_Session::setStatus( ts('Online Registration details has been saved.') );
        
    }//end of function
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Online Registration');
    }

    
    
}
?>
