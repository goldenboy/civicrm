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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */
require_once 'CRM/Core/Form.php';
/**
 * This class generates form components for processing a ontribution 
 * 
 */
class CRM_Contribute_Form_PCP_PCPAccount extends CRM_Core_Form
{
    /**
     *Variable defined for Contribution Page Id
     *
     */

    public  $_pageId = null;
    public  $_id     = null;

    /** 
     * are we in single form mode or wizard mode?
     * 
     * @var boolean
     * @access protected 
     */ 
    public $_single;

    public function preProcess()  
    {
        $session =& CRM_Core_Session::singleton( );
        $this->_contactID = $session->get( 'userID' );
        $this->_action = CRM_Utils_Request::retrieve( 'action', 'String', $this, false );
        $this->_pageId = CRM_Utils_Request::retrieve( 'pageId', 'Positive', $this );
        $this->_id     = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
     
        if ( ! $this->_pageId ) {
            $this->_pageId = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_PCP', $this->_id, 'contribution_page_id' );
        }

        $this->_single = $this->get( 'single' );
        
        if ( !$this->_single ) {
            $this->_single = $session->get('singleForm');
        }

        $this->set( 'action'              , $this->_action );
        $this->set( 'page_id'             , $this->_id );
        $this->set( 'contribution_page_id', $this->_pageId );

        // we do not want to display recently viewed items, so turn off
        $this->assign('displayRecent' , false );

        if( $this->_single ) {
            $title = 'Update Contact Information';
            CRM_Utils_System::setTitle(ts($title));
        }
    }

    function setDefaultValues( ) 
    {   
        if (!$this->_contactID) {
            return;
        }
        foreach ( $this->_fields as $name => $dontcare) {
            $fields[$name] = 1;
        }
        
        require_once "CRM/Core/BAO/UFGroup.php";
        CRM_Core_BAO_UFGroup::setProfileDefaults( $this->_contactID, $fields, $this->_defaults );
        
        //set custom field defaults
        require_once "CRM/Core/BAO/CustomField.php";
        foreach ( $this->_fields as $name => $field ) {
            if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID($name) ) {
                if ( !isset( $this->_defaults[$name] ) ) {
                    CRM_Core_BAO_CustomField::setProfileDefaults( $customFieldID, $name, $this->_defaults,
                                                                  null, CRM_Profile_Form::MODE_REGISTER );
                }
            }
        }
        
        return $this->_defaults;
    }
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    {
        $id = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_PCPBlock', $this->_pageId, 'supporter_profile_id', 'entity_id' );
        require_once 'CRM/Contribute/BAO/PCP.php';
        if ( CRM_Contribute_BAO_PCP::checkEmailProfile( $id ) ){
            $this->assign('profileDisplay', true);
        }
        $fields = null;
        require_once "CRM/Core/BAO/UFGroup.php";
        if ( $this->_contactID ) {
            if ( CRM_Core_BAO_UFGroup::filterUFGroups($id, $this->_contactID)  ) {
                $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD );
            }
            $this->addFormRule( array( 'CRM_Contribute_Form_PCP_PCPAccount', 'formRule' ), $this ); 
        } else {
            require_once 'CRM/Core/BAO/CMSUser.php';
            CRM_Core_BAO_CMSUser::buildForm( $this, $id , true );

            $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD );
        }
        
        if ( $fields ) {
            $this->assign( 'fields', $fields );
            $addCaptcha = false;
            foreach($fields as $key => $field) {
                if ( isset( $field['data_type'] ) && $field['data_type'] == 'File' ) {
                    // ignore file upload fields
                    continue;
                }
                require_once "CRM/Core/BAO/UFGroup.php";
                require_once "CRM/Profile/Form.php";
                CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE);
                $this->_fields[$key] = $field;
                if ( $field['add_captcha'] ) {
                    $addCaptcha = true;
                }
            }
            
            if ( $addCaptcha ) {
                require_once 'CRM/Utils/ReCAPTCHA.php';
                $captcha =& CRM_Utils_ReCAPTCHA::singleton( );
                $captcha->add( $this );
                $this->assign( "isCaptcha" , true );
            }
        }

        require_once "CRM/Contribute/PseudoConstant.php";
        $this->assign( 'campaignName', CRM_Contribute_PseudoConstant::contributionPage( $this->_pageId ) );
        
        if ( $this->_single ) {
            $button = array ( array ( 'type'      => 'next',
                                      'name'      => ts('Save'), 
                                      'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                      'isDefault' => true   ),
                              array ( 'type' => 'cancel',
                                      'name' => ts('Cancel'))
                              );
        }else {
            $button[] = array ( 'type'      => 'next',
                                'name'      => ts('Continue >>'), 
                                'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                'isDefault' => true   );
        }
        
        $this->addButtons( $button );
    }
    
    /**  
     * global form rule  
     *  
     * @param array $fields  the input form values  
     * @param array $files   the uploaded files if any  
     * @param array $options additional user data  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * @static  
     */  
    static function formRule( &$fields, &$files, $self ) 
    {
        $errors = array( );
        require_once "CRM/Utils/Rule.php";
        foreach( $fields as $key => $value ) {
            if ( strpos($key, 'email-') !== false ) {
                $UFMatchId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFMatch', $self->_contactID, 'id', 'contact_id' );
                if ( !CRM_Utils_Rule::objectExists($value, array('CRM_Core_DAO_UFMatch', $UFMatchId, 'uf_name')) ) {
                    $errors[$key] = ts( 'There is already an user associated with this email address. Please enter different email address.' );   
                }
            }
        }
        return empty($errors) ? true : $errors;
    }
    
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess( )  
    {
        $params  = $this->controller->exportValues( $this->getName() );
        if ( ! $this->_contactID && isset( $params['cms_create_account'] ) ) {
            foreach( $params as $key => $value ) {
                if ( substr( $key , 0,5 ) == 'email' && ! empty( $value ) )  {
                    $params['email'] = $value;
                }
            }
        }
        $contactID =& CRM_Contact_BAO_Contact::createProfileContact( $params, $this->_fields, $this->_contactID, $addToGroups );
        $this->set('contactID', $contactID);
        require_once "CRM/Contribute/BAO/Contribution/Utils.php";
        CRM_Contribute_BAO_Contribution_Utils::createCMSUser( $params, $contactID, 'email' );
        if ( $this->_single ) {
            $session =& CRM_Core_Session::singleton( );
            CRM_Core_Session::setStatus( ts( "Your contact information has been updated.") );
            $url = CRM_Utils_System::url( 'civicrm/contribute/pcp/info', 'reset=1&id='.$this->_id );
            $session->pushUserContext( $url );
        }
    }
}
?>