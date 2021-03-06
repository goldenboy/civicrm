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

require_once 'CRM/Admin/Form.php';

/**
 * This class generates form components for Option Value
 * 
 */
class CRM_Admin_Form_OptionValue extends CRM_Admin_Form
{
    static $_gid = null;

    /**
     * The option group name
     *
     * @var string
     * @static
     */
    static $_gName = null;

    /**
     * Function to for pre-processing
     *
     * @return None
     * @access public
     */
    public function preProcess( ) 
    {
        parent::preProcess( );
        require_once 'CRM/Utils/Request.php';
        $this->_gid = CRM_Utils_Request::retrieve('gid', 'Positive',
                                                  $this, false, 0);
        //get optionGroup name in case of email/postal greeting or addressee, CRM-4575
        if( !empty( $this->_gid ) ) {
            $this->_gName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', $this->_gid, 'name');
        }
        $session =& CRM_Core_Session::singleton();
        $url = CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&action=browse&gid='.$this->_gid); 
        $session->pushUserContext( $url );
        $this->assign('id', $this->_id);
    }

    /**
     * This function sets the default values for the form. 
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) {
        $defaults = array( );
        $defaults = parent::setDefaultValues( );
        if (! CRM_Utils_Array::value( 'weight', $defaults ) ) {
            $query = "SELECT max( `weight` ) as weight FROM `civicrm_option_value` where option_group_id=" . $this->_gid;
            $dao =& new CRM_Core_DAO( );
            $dao->query( $query );
            $dao->fetch();
            $defaults['weight'] = ($dao->weight + 1);   
        }
        //setDefault of contact types for email greeting, postal greeting, addressee, CRM-4575
        if ( in_array( $this->_gName, array( 'email_greeting', 'postal_greeting', 'addressee' ) ) ) {
            $defaults['contactOptions'] = CRM_Utils_Array::value( 'filter', $defaults );
        }
        return $defaults;
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        //CRM-4575
        $isReserved = false;
        if ( $this->_id ) {
            $isReserved = (bool) CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $this->_id, 'is_reserved' );     
        } 
        parent::buildQuickForm( );
        if ($this->_action & CRM_Core_Action::DELETE ) { 
            return;
        }

        $this->applyFilter('__ALL__', 'trim');
        $this->add('text', 'label', ts('Title'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue', 'label' ),true );
        $this->add('text', 'value', ts('Value'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue', 'value' ),true );
        $this->add('text', 'name', ts('Name'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue', 'name' ) );
        if ( $this->_gName == 'custom_search') {
            $this->add('text', 
                       'description', 
                       ts('Description'), 
                       CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue', 'description' ) );
            
        } else {
            $this->addWysiwyg( 'description', 
                               ts('Description'), 
                               CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue', 'description' ) );
        }
       
        $this->add('text', 'grouping', ts('Option Grouping Name'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue', 'grouping' ) );
        $this->add('text', 'weight', ts('Weight'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue', 'weight' ),true );
        $this->add('checkbox', 'is_active', ts('Enabled?'));
        $this->add('checkbox', 'is_default', ts('Default Option?') );
        $this->add('checkbox', 'is_optgroup',ts('Option Group?'));
        
        if ($this->_action & CRM_Core_Action::UPDATE && $isReserved ) { 
            $this->freeze(array('name', 'description', 'is_active' ));
        }
        //get contact type for which user want to create a new greeting/addressee type, CRM-4575
        if ( in_array( $this->_gName, array( 'email_greeting', 'postal_greeting', 'addressee' ) ) && ! $isReserved ) {
            $values = array( 1 => ts('Individual'), 2 => ts('Household') );
            if ( $this->_gName == 'addressee' ) {
                $values[] =  ts('Organization'); 
            }
            $this->add( 'select', 'contactOptions', ts('Contact Type'),array('' => '-select-' ) + $values, true );
        }  
    }

       
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $this->exportValues();
        require_once 'CRM/Core/BAO/OptionValue.php';
        if($this->_action & CRM_Core_Action::DELETE) {
            CRM_Core_BAO_OptionValue::del($this->_id);
            CRM_Core_Session::setStatus( ts('Selected option value has been deleted.') );
        } else { 

            $params = $ids = array( );
            // store the submitted values in an array
            $params = $this->exportValues();
            $params['option_group_id'] = $this->_gid;

            if ($this->_action & CRM_Core_Action::UPDATE ) {
                $ids['optionValue'] = $this->_id;
            }
            
            //set defaultGreeting option in params to save default value as per contactOption-defaultValue mapping
            if ( CRM_Utils_Array::value( 'contactOptions', $params ) ) {
                $params['filter']          = CRM_Utils_Array::value( 'contactOptions', $params );
                $params['defaultGreeting'] = 1;
            }
            
            $optionValue = CRM_Core_BAO_OptionValue::add($params, $ids);
            CRM_Core_Session::setStatus( ts('The Option Value \'%1\' has been saved.', array( 1 => $optionValue->label )) );
        }
        
    }
}


