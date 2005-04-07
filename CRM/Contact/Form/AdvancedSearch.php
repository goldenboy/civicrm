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

/**
 * Files required
 */
require_once 'CRM/Form.php';
require_once 'CRM/PseudoConstant.php';
require_once 'CRM/Selector/Controller.php';
require_once 'CRM/Contact/Selector.php';

/**
 * Advanced Search form for which provides more options
 * like Categories, Groups, Addresses and Locations.
 */
class CRM_Contact_Form_AdvancedSearch extends CRM_Form {

    /**
     * Class construtor
     *
     * @param string    $name  name of the form
     * @param CRM_State $state State object that is controlling this form
     * @param int       $mode  Mode of operation for this form
     *
     * @return CRM_Contact_Form_Search
     * @access public
     */
    function __construct($name, $state, $mode = self::MODE_NONE)
    {
        parent::__construct($name, $state, $mode);
    }

    /**
     * Build the form
     *
     * @access protected
     * @return void
     */
    function buildQuickForm( ) 
    {
        // add checkboxes for contact type
        $cb_contact_type = array( );
        foreach (CRM_PseudoConstant::$contactType as $key => $value) {
            CRM_Error::debug_var("key", $key);
            CRM_Error::debug_var("value", $value);
            $cb_contact_type[] = HTML_QuickForm::createElement('checkbox', $key, null, $value);
        }
        $this->addGroup($cb_contact_type, 'cb_contact_type', 'Show Me....', '<br />');
        
        // checkboxes for groups
        $cb_group = array();
        $group = CRM_PseudoConstant::getGroup();
        foreach ($group as $groupID => $groupName) {
            $cb_group[] = HTML_QuickForm::createElement('checkbox', $groupID, null, $groupName);
        }
        $this->addGroup($cb_group, 'cb_group', 'In Group (s)', '<br />');

        // checkboxes for categories
        $cb_category = array();
        $category = CRM_PseudoConstant::getCategory();
        foreach ($category as $categoryID => $categoryDetail) {
            $cb_category[] = HTML_QuickForm::createElement('checkbox', $categoryID, null, $categoryDetail['name']);
        }
        $this->addGroup($cb_category, 'cb_category', 'In Categorie (s)', '<br />');

        // add text box for last name, first name, street name, city
        $this->addElement('text', 'last_name', 'Contact Name', CRM_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name') );
        $this->addElement('text', 'first_name', 'First Name', CRM_DAO::getAttribute('CRM_Contact_DAO_Individual', 'first_name') );
        $this->addElement('text', 'street_name', 'Street Name:', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'street_name'));
        $this->addElement('text', 'city', 'City:',CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'city'));


        // select for state province
        $stateProvince = CRM_PseudoConstant::getStateProvince();
        //$firstElement = array('' => ' - any state/province - ');
        //$stateProvince = array_merge($firstElement, $stateProvince);
        //$stateProvince[''] = ' - any state/province - ';
        $stateProvince = array('' => ' - any state/province - ') + $stateProvince;
        $this->addElement('select', 'state_province', 'State/Province', $stateProvince);

        // select for country
        $country = CRM_PseudoConstant::getCountry();
        //$firstElement = array('' => ' - any country - ');
        //$country = array_merge($firstElement, $country)
        $country = array('' => ' - any country - ') + $country;
        $this->addElement('select', 'country', 'Country', $country);

        // add text box for postal code
        $this->addElement('text', 'postal_code', 'Postal Code', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'postal_code') );
        $this->addElement('text', 'postal_code_low', 'From', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'postal_code') );
        $this->addElement('text', 'postal_code_high', 'To', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'postal_code') );

        // checkboxes for location type
        $cb_location_type = array();
        $locationType = CRM_PseudoConstant::getLocationType();
        CRM_Error::debug_var("locationType", $locationType);
        $locationType[''] = 'Any Locations';
        CRM_Error::debug_var("locationType", $locationType);
        foreach ($locationType as $locationTypeID => $locationTypeName) {
            $cb_location_type[] = HTML_QuickForm::createElement('checkbox', $locationTypeID, null, $locationTypeName);
        }
        $this->addGroup($cb_location_type, 'cb_location_type', 'Include these locations', '<br />');


        // checkbox for primary location only
        $this->addElement('checkbox', 'cb_primary_location', null, 'Search for primary locations only');        
        
        // submit button
        $this->addElement('submit', 'submit', 'Search');
    }




    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues( ) {
        $defaults = array( );
        return $defaults;
    }



    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) {
    }




    function preProcess( ) {

        /*
         * since all output is generated by postProcess which will never be invoked by a GET call
         * we need to explicitly call it if we are invoked by a GET call
         *
         * Scenarios where we make a GET call include
         *  - pageID/sortID change
         *  - user hits reload
         *  - user clicks on menu
         */
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            $this->postProcess( );
        }

    }




    function postProcess() 
    {
    }

}

?>