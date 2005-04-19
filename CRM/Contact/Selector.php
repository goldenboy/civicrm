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
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

require_once 'CRM/Pager.php';
require_once 'CRM/Sort.php';
require_once 'CRM/Selector/Base.php';
require_once 'CRM/Selector/API.php';
require_once 'CRM/Form.php';
require_once 'CRM/Contact/BAO/Contact.php';


/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Contact_Selector extends CRM_Selector_Base implements CRM_Selector_API 
{
    
    /**
     * Constants are used for assigning a type of selector.
     * 
     * Selectors can be for Basic or Advanced search.
     *
     * @var const int
     */
    const
        TYPE_BASIC = 0,
        TYPE_ADVANCED = 1;


    /**
     * This defines two actions- View and Edit.
     *
     * @var array
     */
    static $_links = array(
                           CRM_Action::VIEW => array(
                                                     'name'     => 'View Contact',
                                                     'link'     => '/crm/contact?action=view&id=%%id%%',
                                                     'linkName' => 'View Contact',
                                                     'menuName' => 'View Contact Details'
                                                     ),
                           CRM_Action::EDIT => array(
                                                     'name'     => 'Edit Contact',
                                                     'link'     => '/crm/contact?action=edit&id=%%id%%',
                                                     'linkName' => 'Edit Contact',
                                                     'menuName' => 'Edit Contact Details'
                                                     ),
                           );

    static $_columnHeaders = array(
                                   array('name' => ''),
                                   array('name' => ''),
                                   array(
                                         'name'      => 'Name',
                                             'sort'      => 'sort_name',
                                             'direction' => CRM_Sort::ASCENDING,
                                             ),
                                       array('name' => 'Address'),
                                       array(
                                             'name'      => 'City',
                                             'sort'      => 'city',
                                             'direction' => CRM_Sort::DONTCARE,
                                             ),
                                       array(
                                             'name'      => 'State',
                                             'sort'      => 'state',
                                             'direction' => CRM_Sort::DONTCARE,
                                             ),
                                       array(
                                             'name'      => 'Postal',
                                             'sort'      => 'postal_code',
                                             'direction' => CRM_Sort::DONTCARE,
                                             ),
                                       array(
                                             'name'      => 'Country',
                                             'sort'      => 'country',
                                             'direction' => CRM_Sort::DONTCARE,
                                             ),
                                       array(
                                             'name'      => 'Email',
                                             'sort'      => 'email',
                                             'direction' => CRM_Sort::DONTCARE,
                                             ),
                                       array('name' => 'Phone'),
                                       array('name' => ''),
                                       );
    


    /**
     * This caches the content for the display system.
     *
     * @var string
     * @access protected
     */
    protected $_contact;

    /**
     * formValues is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    protected $_formValues;

    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_type;


    /**
     * Class constructor
     *
     * @param array $formValues array of parameters for query
     * @param int   $type - type of search basic or advanced.
     *
     * @return CRM_Contact_AdvancedSelector
     * @access public
     */
    function __construct(&$formValues, $type=self::TYPE_BASIC) 
    {
        //object of BAO_Contact_Individual for fetching the records from db
        $this->_contact = new CRM_Contact_BAO_Contact();

        // submitted form values
        $this->_formValues = $formValues;

        // type of selector
        $this->_type = $type;

    }//end of constructor


    /**
     * This method returns the links that are given for each search row.
     * currently the links added for each row are 
     * 
     * - View
     * - Edit
     *
     * @param none
     *
     * @return array
     * @access public
     *
     */
    function &getLinks() 
    {
        return CRM_Contact_Selector::$_links;
    } //end of function

    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['status']       = "Contact %%StatusMessage%%";
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Pager::ROWCOUNT;

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }//end of function

    /**
     * getter for headers for each column of the displayed form.
     *
     * @param 
     * @return array (reference)
     * @access public
     */
    function &getColumnHeaders($action) 
    {
        return self::$_columnHeaders;
    }


    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        switch ($this->_type) {
        case self::TYPE_BASIC:
            $v1 = $this->_contact->basicSearchQuery($this->_formValues, $offset, $rowCount, $sort, TRUE);
            break;
        case self::TYPE_ADVANCED:
            $v1 = $this->_contact->advancedSearchQuery($this->_formValues, $offset, $rowCount, $sort, TRUE);
            break;
        }
        $v2 = $v1->getDatabaseResult();
        $v3 = $v2->fetchRow();
        $count = $v3[0];
        return $count;
    }


    /**
     * getter for all the database values to be displayed on the form while listing
     *
     * @param int      $action   the type of action links
     * @param int      $offset   the offset for the query
     * @param int      $rowCount the number of rows to return
     * @param CRM_Sort $sort     the sort object
     *
     * @return array (reference)
     * @access public
     */
    function &getRows($action, $offset, $rowCount, $sort)
    {
        $config = CRM_Config::singleton( );

        // note the formvalues were given by CRM_Contact_Form_Search to us 
        // and contain the search criteria (parameters)
        switch ($this->_type) {
        case self::TYPE_BASIC:
            $result = $this->_contact->basicSearchQuery($this->_formValues, $offset, $rowCount, $sort);
            break;
        case self::TYPE_ADVANCED:
            $result = $this->_contact->advancedSearchQuery($this->_formValues, $offset, $rowCount, $sort);
            break;
        }


        // process the result of the query
        $rows = array( );

        while ($result->fetch()) {
            $row = array();

            // the columns we are interested in
            static $properties = array( 'contact_id', 'sort_name', 'street_address',
                                        'city', 'state', 'country', 'postal_code',
                                        'email', 'phone' );
            foreach ($properties as $property) {
                $row[$property] = $result->$property;
            }
            $row['edit'] = CRM_System::url( 'civicrm/contact/edit', 'reset=1&cid=' . $result->contact_id );
            $row['view'] = CRM_System::url( 'civicrm/contact/view', 'reset=1&cid=' . $result->contact_id );
            $contact_type = '<img src="' . $config->resourceBase . 'i/contact_';
            switch ($result->contact_type) {
            case 'Individual' :
                $contact_type .= 'ind.png" alt="Individual">';
                break;
            case 'Household' :
                $contact_type .= 'house.png" alt="Household" height="16" width="16">';
                break;
            case 'Organization' :
                $contact_type .= 'org.gif" alt="Organization" height="16" width="18">';
                break;

            }
            $row['contact_type'] = $contact_type;
            $rows[] = $row;
        }
        return $rows;
    }
    

    function getExportColumnHeaders($action, $type = 'csv')
    {
    }

    function getExportRows($action, $type = 'csv')
    {
    }

    function getExportFileName($action, $type = 'csv')
    {
    }


}//end of class

?>