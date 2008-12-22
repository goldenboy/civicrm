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

require_once 'CRM/Contribute/Form/ContributionPage.php';

class CRM_Contribute_Form_ContributionPage_PCP extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * Function to pre process the form
     *
     * @access public
     * @return None
     */
    function preProcess( ) 
    {
        parent::preProcess( );
    }
    
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return void
     */
    function setDefaultValues( ) 
    {
        $defaults = array();
        if ( isset($this->_id ) ) {
            $params = array( 'entity_id' => $this->_id, 'entity_table' => 'civicrm_contribution_page' );
            CRM_Core_DAO::commonRetrieve( 'CRM_Contribute_DAO_PCPBlock', $params, $defaults );
        }

        if ( !$defaults['id'] ) { 
            $defaults['is_approval_needed']    = 1;
            $defaults['is_tellfriend_enabled'] = 1;
            $defaults['tellfriend_limit']      = 5;
            $defaults['link_text']             = ts('Become a Supporter!');
        } 
        return $defaults;
    }
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    function buildQuickForm( ) 
    {
        $this->addElement( 'checkbox', 'is_active', ts('Enable Personal Campaign Pages?'), null, array('onclick' => "pcpBlock(this)") );
	
        $this->addElement( 'checkbox', 'is_approval_needed', ts('Approval required') );
        
        $profile = array( );
        CRM_Core_DAO::commonRetrieveAll('CRM_Core_DAO_UFGroup', 'is_cms_user', 2, $profiles, array ( 'title', 'is_active' ) );
        if ( !empty( $profiles ) ) {
            foreach ( $profiles as $key => $value ) {
                if ( $value['is_active'] ) $profile[$key] = $value['title'];
            }
            $this->assign('profile',$profile);
        } 

        $this->add('select', 'supporter_profile_id', ts( 'Supporter profile' ), array( '' => ts( '- select -' ) ) + $profile ); 
        
        $this->addElement( 'checkbox', 'is_tellfriend_enabled', ts("Allow 'Tell a friend' functionality") );
        
        $this->add( 'text', 
                    'tellfriend_limit', 
                    ts("'Tell a friend' maximum recipients limit"), 
                    CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_ContributionPage' , 'pcp_tellfriend_limit') );
        $this->addRule( 'tellfriend_limit', ts( 'Please enter a valid limit.' ), 'integer' );
        
        $this->add( 'text', 
                    'link_text', 
                    ts("'Create Personal Campaign Page' link text"), 
                    CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_ContributionPage' , 'pcp_link_text') );
        
        parent::buildQuickForm( );
        $this->addFormRule(array('CRM_Contribute_Form_ContributionPage_PCP', 'formRule') , $this );
    }

    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    public static function formRule( &$params, &$files, $self ) 
    { 
        $errors = array( );
        if ( CRM_Utils_Array::value( 'is_active', $params ) ) {
            
            if ( CRM_Utils_Array::value( 'is_tellfriend_enabled', $params ) && 
                 ( CRM_Utils_Array::value( 'tellfriend_limit', $params ) <= 0 ) ) {
                $errors['tellfriend_limit'] = ts('if Tell Friend is enable, Maximum recipients limit should be greater than zero.');
            }
            if ( ! CRM_Utils_Array::value( 'supporter_profile_id', $params ) ) {
                $errors['supporter_profile_id'] = ts('Supporter profile is a required field.');
            } else {
                require_once 'CRM/Contribute/BAO/PCP.php';
                if ( CRM_Contribute_BAO_PCP::checkEmailProfile( $params['supporter_profile_id'] ) ){
                    $errors['supporter_profile_id'] = ts('Profile is not configured with Email address.');
                }
            } 
        }
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    function postProcess( ) 
    {
        // get the submitted form values.
        $params = $this->controller->exportValues( $this->_name );
        
        $params['entity_id'] = $this->_id;
        $params['entity_table'] = 'civicrm_contribution_page';
       
        $dao =& new CRM_Contribute_DAO_PCPBlock();
        $dao->entity_table = 'civicrm_contribution_page';
        $dao->entity_id = $this->_id; 
        $dao->find(true);
        $params['id'] = $dao->id;
        $params['is_active']             =  CRM_Utils_Array::value( 'is_active', $params, false );
        $params['is_approval_needed']    =  CRM_Utils_Array::value( 'is_approval_needed', $params, false );
        $params['is_tellfriend_enabled'] =  CRM_Utils_Array::value( 'is_tellfriend_enabled', $params, false );

        require_once 'CRM/Contribute/BAO/PCP.php'; 
        $dao = CRM_Contribute_BAO_PCP::add( $params ); 
    }

    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) 
    {
        return ts( 'Enable Personal Campaign Pages' );
    }

}


