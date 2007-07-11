<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.8                                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Event/Form/ManageEvent.php';
require_once 'CRM/Core/SelectValues.php';

/**
 * This class generates form components for processing Event Location 
 * civicrm_event_page. 
 */
class CRM_Event_Form_ManageEvent_Location extends CRM_Event_Form_ManageEvent
{

    /**
     * how many locationBlocks should we display?
     *
     * @var int
     * @const
     */
    const LOCATION_BLOCKS = 1;
    
    /**
     * the variable, for storing the location array
     *
     * @var array
     */
    protected $_locationIds;

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
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $eventId = $this->_id;

        $defaults = array( );
        $params   = array( );
        if ( isset( $eventId ) ) {
            $params = array( 'entity_id' => $eventId ,'entity_table' => 'civicrm_event');
            require_once 'CRM/Core/BAO/Location.php';
            $location = CRM_Core_BAO_Location::getValues($params, $defaults, $ids, self::LOCATION_BLOCKS);
            $this->_locationIds = $ids;
            $isShowLocation = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                         $eventId,
                                                         'is_show_location',
                                                         'id' );
            
        }
        
        $defaults['is_show_location'] = $isShowLocation;
        if ( ! empty( $params ) ) {
            $this->setShowHide( $params, true );
        } else {
            $this->setShowHide( $defaults, false );
        }

        //set defaults for country-state dojo widget
        if ( ! empty ( $defaults['location'] ) ) {
            $countries      =& CRM_Core_PseudoConstant::country( );
            $stateProvinces =& CRM_Core_PseudoConstant::stateProvince( false, false );
            
            foreach ( $defaults['location'] as $key => $value ) {
                $countryValue = null;
                $countryValue = $this->getElementValue( "location[$key][address][country_id]" );
                
                if ( $countryValue ) {
                    if ( ! is_numeric( $countryValue ) ) {
                        $this->assign( "country_{$key}_value", 
                                       $this->getElementValue( "location[$key][address][country_id]" ) );
                        $this->assign( "country_{$key}_id", 
                                       $this->getElementValue( "location[$key][address][country_id]" ) );
                    } else {
                        $this->assign( "country_{$key}_value",  $countries[$countryValue] );
                        $this->assign( "country_{$key}_id"   ,  $countryValue );
                    }
                } else if ( isset($value['address']['country_id']) ) {
                    $countryId = $value['address']['country_id'];
                    if ( $countryId ) {
                        $this->assign( "country_{$key}_value",  $countries[$countryId] );
                        $this->assign( "country_{$key}_id"   ,  $countryId );
                    }
                }
                
                $stateValue = null;
                $stateValue = $this->getElementValue( "location[$key][address][state_province_id]" );
                
                if ( $stateValue ) {
                    if ( ! is_numeric( $stateValue ) ) {
                        $this->assign( "state_province_{$key}_value", 
                                       $this->getElementValue( "location[$key][address][state_province_id]" ) );
                        $this->assign( "state_province_{$key}_id", 
                                       $this->getElementValue( "location[$key][address][state_province_id]" ) );
                    } else {
                        $this->assign( "state_province_{$key}_value",  $stateProvinces[$stateValue] );
                        $this->assign( "state_province_{$key}_id"   ,  $stateValue );
                    }
                } else  if ( isset($value['address']['state_province_id']) ) {
                    $stateProvinceId = $value['address']['state_province_id'];
                    if ( $stateProvinceId ) {
                        $this->assign( "state_province_{$key}_value",  $stateProvinces[$stateProvinceId] );
                        $this->assign( "state_province_{$key}_id"   ,  $stateProvinceId );
                    }
                }
            }
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
    function setShowHide( &$defaults, $force ) 
    {
        $this->_showHide =& new CRM_Core_ShowHideBlocks( array(),'') ;
        $prefix =  array( 'phone','email' );
        CRM_Contact_Form_Location::setShowHideDefaults( $this->_showHide, self::LOCATION_BLOCKS , $prefix, false);
        
        if ( $force ) {
            $locationDefaults = CRM_Utils_Array::value( 'location', $defaults );
            $config =& CRM_Core_Config::singleton( );
            
            CRM_Contact_Form_Location::updateShowHide( $this->_showHide,
                                                       $locationDefaults,
                                                       $config->maxLocationBlocks, $prefix );
        }
        
        $this->_showHide->addToTemplate( );
    }

    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        $this->addFormRule( array( 'CRM_Event_Form_ManageEvent_Location', 'formRule' ) );
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
    static function formRule( &$fields ) 
    {
        $errors = array( );
        // check for state/country mapping
        CRM_Contact_Form_Address::formRule($fields, $errors);

        return empty($errors) ? true : $errors;
    }    

    /** 
     *  function to build location block 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    { 
        $this->assign( 'locationCount', self::LOCATION_BLOCKS + 1);
        
        //hack the address sequence so that state province always comes after country
        $config =& CRM_Core_Config::singleton( );
        $addressSequence = $config->addressSequence();
        $key = array_search( 'country', $addressSequence);
        unset($addressSequence[$key]);

        $key = array_search( 'state_province', $addressSequence);
        unset($addressSequence[$key]);

        $addressSequence = array_merge( $addressSequence, array ( 'country', 'state_province' ) );
        $this->assign( 'addressSequence', $addressSequence );

        require_once 'CRM/Contact/Form/Location.php';

        //blocks to be displayed
        $locationCompoments = array('Phone', 'Email');
        CRM_Contact_Form_Location::buildLocationBlock( $this, self::LOCATION_BLOCKS ,$locationCompoments);
        $this->addElement('advcheckbox', 'is_show_location', ts('Show Location?') );
        $this->assign( 'index' , 1 );
        $this->assign( 'blockCount'   , CRM_Contact_Form_Location::BLOCKS + 1 );
    
        parent::buildQuickForm();
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess( ) 
    {
        $params = $ids = array( );
        $params = $this->exportValues( );

        $params['entity_table'] = 'civicrm_event';
        $ids = $this->_locationIds;
        $eventId = $this->_id;
        
        $params['entity_id'] = $eventId; 
        //set the location type to default location type
        require_once 'CRM/Core/BAO/LocationType.php';
        $defaultLocationType =& CRM_Core_BAO_LocationType::getDefault();
        $params['location'][1]['location_type_id'] = $defaultLocationType->id;

        require_once 'CRM/Core/BAO/Location.php';
        CRM_Core_BAO_Location::add($params, $ids, self::LOCATION_BLOCKS);

        $ids['event_id']  = $eventId;
        require_once 'CRM/Event/BAO/Event.php';
        CRM_Event_BAO_Event::add($params, $ids);
        
    }//end of function

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Event Location');
    }
}
?>
