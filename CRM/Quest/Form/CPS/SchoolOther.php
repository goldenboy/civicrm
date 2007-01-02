<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                  |
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
 | http://www.civicrm.org/licensing/                                 |
 +--------------------------------------------------------------------+
*/


/**
 * This page is for entering other school information
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2006
 * $Id$
 *
 */

require_once 'CRM/Quest/Form/App.php';
require_once 'CRM/Core/OptionGroup.php';
require_once 'CRM/Quest/BAO/Essay.php';

/**
 * This class generates form components for relationship
 * 
 */
class CRM_Quest_Form_CPS_SchoolOther extends CRM_Quest_Form_App
{
    protected $_orgIDsOther;
    protected $_relIDsOther;
    
    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        parent::preProcess( );
        $this->_grouping = 'cm_school_desc';
        $this->_essays = CRM_Quest_BAO_Essay::getFields( $this->_grouping, $this->_contactID, $this->_contactID );
        require_once 'CRM/Contact/DAO/RelationshipType.php';
        $dao = & new CRM_Contact_DAO_RelationshipType();
        $dao->name_a_b = 'Student of';
        $dao->find(true);
        $relID  = $dao->id ;

        // to get  OrganizationId and Relationship ID's
        require_once 'CRM/Contact/DAO/Relationship.php';
        $dao = & new CRM_Contact_DAO_Relationship();
        $dao->relationship_type_id = $relID;
        $dao->contact_id_a   	   = $this->_contactID;
        $dao->find();
        $orgIds = array();
        while( $dao->fetch() ) {
            $orgIds[$dao->contact_id_b] = $dao->contact_id_b;
        }
        
        if ( ! empty( $orgIds ) ) {
            $orgIdsKey = implode( ',', $orgIds );

            $query = "
SELECT entity_id
  FROM civicrm_custom_value
 WHERE char_data = 'Other School'
   AND entity_id IN ( $orgIdsKey )
";
            $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
            while ( $dao->fetch( ) ) {
                $count = count( $this->_orgIDsOther)+1;
                $this->_orgIDsOther[$count] = $dao->entity_id;
            }
        }
      
        //get relationshipID
        require_once 'CRM/Contact/DAO/Relationship.php';
        if (is_array($this->_orgIDsOther)) {
            foreach ( $this->_orgIDsOther as $key => $value ) {
                $dao = & new CRM_Contact_DAO_Relationship();
                $dao->contact_id_b =$value;
                $dao->find(true);
                $this->_relIDsOther[$key] = $dao->id;
            }
        }
    }
    
    
    /**
     * This function sets the default values for the form. Relationship that in edit/view action
     * the default values are retrieved from the database
     * 
     * @access public
     * @return void
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        if (is_array($this->_orgIDsOther) ) {
            foreach ($this->_orgIDsOther as $key => $value ) {
                if ( $value  ) {
                    $ids         = array( );
                    $orgDefaults = array( );
                    $params  = array('contact_id' => $value ,'contact_type' => 'Organization'); 
                    require_once 'CRM/Contact/BAO/Contact.php';
                    $contact =& CRM_Contact_BAO_Contact::retrieve( $params, $orgDefaults, $ids );
                    
                    //set custom data defaults
                    require_once 'CRM/Core/BAO/CustomGroup.php';
                    $this->_groupTree =& CRM_Core_BAO_CustomGroup::getTree('Organization',$value , 0);
                    $viewMode = false;
                    $inactiveNeeded = false;
                    if( isset($this->_groupTree) ) {
                        CRM_Core_BAO_CustomGroup::setDefaults( $this->_groupTree,$orgDefaults, $viewMode, $inactiveNeeded );
                    }
                    
                    // set relationship defaults
                    require_once 'CRM/Utils/Date.php';
                    require_once 'CRM/Contact/DAO/Relationship.php';
                    $relDAO = & new CRM_Contact_DAO_Relationship();
                    $relDAO->id = $this->_relIDsOther[$key]; 
                    if ( $relDAO->find(true) ) {
                        $orgDefaults['date_of_entry'] =  CRM_Utils_Date::unformat( $relDAO->start_date , '-' );
                        $orgDefaults['date_of_exit'] =  CRM_Utils_Date::unformat( $relDAO->end_date , '-' );
                    }
                    
                    //format note for contact
                    if (is_array ($orgDefaults['note'])) {
                        foreach( $orgDefaults['note'] as $k1 => $v1) {
                            $orgDefaults['note'] = $v1['note'];
                            break;
                        }
                    }
                }
               
                foreach ($orgDefaults as $k => $v ) {
                    $defaults[$k."_".$key] = $v;
                }
            }
        }

        // fix for note field 
        require_once 'CRM/Core/DAO.php';
          
        // Assign show and hide blocks lists to the template for optional test blocks (SATII and AP)
        if ( ! ( $this->_action & CRM_Core_Action::VIEW ) ) {
            $this->_showHide =& new CRM_Core_ShowHideBlocks( );
            for ( $i = 2; $i <= 5; $i++ ) {
                if ( CRM_Utils_Array::value( "organization_name_$i", $defaults )) {
                    $this->_showHide->addShow( "id_otherSchool_info_$i" );
                    $this->_showHide->addHide( 'id_otherSchool_info_' . $i . '_show' );
                } else {
                    $this->_showHide->addHide( "id_otherSchool_info_$i" );
                }
            }
            $this->_showHide->addToTemplate( );
        }
        
        return $defaults;
    }
    

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Organization' );
        
        require_once 'CRM/Core/ShowHideBlocks.php';

        $otherSchool_info = array( );
        for ( $i = 1; $i <= 5; $i++ ) {
            $this->addElement('text', 'organization_name_'.$i, ts( 'Name of Institution' ), $attributes['organization_name'] );
            $this->addElement('date', 'date_of_entry_'.$i, ts( 'Dates Attended' ), 
                              CRM_Core_SelectValues::date( 'custom', 7, 0, "M\001Y" ) );
            $this->addElement('date', 'date_of_exit_'.$i, ts( 'Dates Attended' ), 
                              CRM_Core_SelectValues::date( 'custom', 7, 2, "M\001Y" ) );
            $this->buildAddressBlock( 1, ts( 'Location' ), null, null, null, null, null, "location_$i" );
            // $this->addElement('textarea', "note_{$i}", ts( 'School Description' ), array("rows"=>5,"cols"=>60));
            CRM_Quest_BAO_Essay::buildForm( $this, $this->_essays );
            if ( ! ( $this->_action & CRM_Core_Action::VIEW ) ) {
                $otherSchool_info[$i] = CRM_Core_ShowHideBlocks::links( $this,"otherSchool_info_$i",
                                                                        ts('add another School'),
                                                                        ts('hide this School'),
                                                                        false );
            }
        }
        $maxOtherSchool = 5;
        if ( $this->_action & CRM_Core_Action::VIEW ) {
            $defaults = $this->setDefaultValues( );
            $maxOtherSchool = 0;
            for ( $i = 1; $i <= 5; $i++ ) {
                if ( CRM_Utils_Array::value( "organization_name_$i", $defaults )) {
                    $maxOtherSchool++;
                }
            }
        }
        
        $this->assign( 'maxOtherSchool', $maxOtherSchool + 1 );
        $this->assign( 'otherSchool_info', $otherSchool_info );
        
        parent::buildQuickForm( );
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return void
     */
    public function postProcess() 
    {
        if ( ! ( $this->_action &  CRM_Core_Action::VIEW ) ) {
            $params = $this->controller->exportValues( $this->_name );
            CRM_Quest_BAO_Essay::create( $this->_essays, $params["essay"], $this->_contactID, $this->_contactID );
            //delete all contact entries
            require_once 'CRM/Contact/BAO/Contact.php';
            if ( ! empty( $this->_orgIDsOther ) ) {
                foreach( $this->_orgIDsOther as $orgID ) {
                    CRM_Contact_BAO_Contact::deleteContact( $orgID );
                }
            }
            $this->_orgIDsOther = null;
            $this->relIDsOther  = null;

            foreach( $params as $key => $value ) {
                $keyArray = explode( '_', $key );
                $orgnizationParams[$keyArray[count($keyArray)-1]][substr($key, 0, -2)] = $value ;
            }
            
            foreach( $orgnizationParams as $key => $orgParams) {
                if (! $orgParams['organization_name']) {
                    continue;
                }
                $orgParams['location'][1]['location_type_id'] = 1;
                $orgParams['location'][1]['is_primary'] = 1;
                
                $orgParams['contact_type'] = 'Organization';
                $orgParams['custom_4']     = 'Other School';
                
                $ids = array();
                if ( $this->_orgIDsOther[$key] ) {
                    $idParams = array( 'id' => $this->_orgIDsOther[$key], 'contact_id' => $this->_orgIDsOther[$key] );
                    CRM_Contact_BAO_Contact::retrieve( $idParams, $defaults, $ids );
                }
                
                $org = CRM_Contact_BAO_Contact::create($orgParams, $ids, 2);
                $this->_orgIDsOther[$key] = $org->id; 
                $this->set('orgIDsOther' , $this->_orgIDsOther );
                
                // add data for custom fields 
                require_once 'CRM/Core/BAO/CustomGroup.php';
                $this->_groupTree = & CRM_Core_BAO_CustomGroup::getTree('Organization',$org->id, 0 );
                
                CRM_Core_BAO_CustomGroup::postProcess( $this->_groupTree, $orgParams );
                
                CRM_Core_BAO_CustomGroup::updateCustomData($this->_groupTree,'Organization',$org->id); 
                
                //create a realtionship
                require_once 'CRM/Utils/Date.php';
                $relationshipParams = array();

                $relID  = 8;
                
                $relationshipParams['relationship_type_id'] = $relID.'_a_b';
                $relationshipParams['start_date']           = $orgParams['date_of_entry'];
                $relationshipParams['end_date']            =  $orgParams['date_of_exit'];
                $relationshipParams['contact_check']        = array("$org->id" => 1 ); 
                
                if ( $this->relIDsOther[$key] ) {
                    $ids = array('contact' =>$this->_contactID,'relationship' => $this->relIDsOther[$key] ,'contactTarget' =>$organizationID);
                } else {
                    $ids = array('contact' =>$this->_contactID);
                }
                
                $organizationID = $org->id;
                
                require_once 'CRM/Contact/BAO/Relationship.php';
                $relationship= CRM_Contact_BAO_Relationship::add($relationshipParams,$ids,$organizationID);
                $this->relIDsOther[$key] = $relationship->id;
            }
            
        }
        
        parent::postProcess( );
    }//end of function

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return ts('Other School Information');
    }
}

?>
