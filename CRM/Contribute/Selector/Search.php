<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.1                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Social Source Foundation                        |
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
 | at http://www.openngo.org/faqs/licensing.html                      |
 +--------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Social Source Foundation (c) 2005
 * $Id: Selector.php 3854 2005-11-23 13:28:15Z kurund $
 *
 */

require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Contact/BAO/Query.php';

/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Contribute_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions- View and Edit.
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    /**
     * Properties of contact we're interested in displaying
     * @var array
     * @static
     */
    static $_properties = array( 'contact_id', 'contribution_id',
                                 'contact_type',
                                 'sort_name',
                                 'total_amount',
                                 'contribution_type',
                                 'contribution_source',
                                 'receive_date',
                                 'thankyou_date',
                                 'cancel_date' );

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
    public $_formValues;

    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;

    protected $_query;

    /**
     * Class constructor
     *
     * @param array $formValues array of parameters for query
     * @param int   $action - action of search basic or advanced.
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct(&$formValues, $action = CRM_Core_Action::NONE) 
    {
        // submitted form values
        $this->_formValues =& $formValues;

        // type of selector
        $this->_action = $action;

        $this->_query =& new CRM_Contact_BAO_Query( $this->_formValues, null, null, false, false, true );

    }//end of constructor


    /**
     * This method returns the links that are given for each search row.
     * currently the links added for each row are 
     * 
     * - View
     * - Edit
     *
     * @return array
     * @access public
     *
     */
    static function &links()
    {

        if (!(self::$_links)) {
            self::$_links = array(
                                  CRM_Core_Action::VIEW   => array(
                                                                   'name'     => ts('View'),
                                                                   'url'      => 'civicrm/contribute',
                                                                   'qs'       => 'reset=1&id=%%id%%&action=view',
                                                                   'title'    => ts('View Contribution'),
                                                                  ),
                                  CRM_Core_Action::UPDATE => array(
                                                                   'name'     => ts('Edit'),
                                                                   'url'      => 'civicrm/contribute',
                                                                   'qs'       => 'reset=1&action=update&id=%%id%%',
                                                                   'title'    => ts('Edit Contribution'),
                                                                  ),
                                  CRM_Core_Action::DELETE => array(
                                                                   'name'     => ts('Delete'),
                                                                   'url'      => 'civicrm/contribute',
                                                                   'qs'       => 'reset=1&action=delete&id=%%id%%',
                                                                   'title'    => ts('Delete Contribution'),
                                                                  ),
                                  );
        }
        return self::$_links;
    } //end of function


    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['status']       = ts('Contribution') . ' %%StatusMessage%%';
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }//end of function


    /**
     * returns the column headers as an array of tuples:
     * (name, sortName (key to the sort array))
     *
     * @param string $action the action being performed
     * @param enum   $output what should the result set include (web/email/csv)
     *
     * @return array the column headers that need to be displayed
     * @access public
     */
    function &getColumnHeaders($action = null, $output = null) 
    {
        return self::_getColumnHeaders();
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
        return $this->_query->searchQuery( 0, 0, null, true );
    }


    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     */
    function &getRows($action, $offset, $rowCount, $sort, $output = null) {
        // note the formvalues were given by CRM_Contact_Form_Search to us 
        // and contain the search criteria (parameters)
        // note that the default action is basic
        $result = $this->_query->searchQuery( $offset, $rowCount, $sort );

        // process the result of the query
        $rows = array( );

        $mask = CRM_Core_Action::mask( CRM_Core_Permission::getPermission( ) );

        while ($result->fetch()) {
            $row = array();

            // the columns we are interested in
            foreach (self::$_properties as $property) {
                $row[$property] = $result->$property;
            }

            $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->contribute_id;
            $row['action']   = CRM_Core_Action::formLink( self::links(), $mask, array( 'id' => $result->contribution_id ) );
            $config =& CRM_Core_Config::singleton( );
            $contact_type    = '<img src="' . $config->resourceBase . 'i/contact_';
            switch ($result->contact_type) {
            case 'Individual' :
                $contact_type .= 'ind.gif" alt="' . ts('Individual') . '" />';
                break;
            case 'Household' :
                $contact_type .= 'house.png" alt="' . ts('Household') . '" height="16" width="16" />';
                break;
            case 'Organization' :
                $contact_type .= 'org.gif" alt="' . ts('Organization') . '" height="16" width="18" />';
                break;
            }
            $row['contact_type'] = $contact_type;

            $rows[] = $row;
        }
        return $rows;
    }
    
    
    /**
     * Given the current formValues, gets the query in local
     * language
     *
     * @param  array(reference)   $formValues   submitted formValues
     *
     * @return array              $qill         which contains an array of strings
     * @access public
     */
  
    // the current internationalisation is bad, but should more or less work
    // for most of "European" languages
    public function getQILL( )
    {
        return $this->_query->qill( );
    }

    /**
     * get colunmn headers for search selector
     *
     *
     * @return array $_columnHeaders
     * @access private
     */
    private static function &_getColumnHeaders() 
    {
        if ( ! isset( self::$_columnHeaders ) )
        {
            self::$_columnHeaders = array(
                                          array('desc' => ts('Contact Type') ),
                                          array(
                                                'name'      => ts('Name'),
                                                'sort'      => 'sort_name',
                                                'direction' => CRM_Utils_Sort::ASCENDING,
                                                ),
                                          array(
                                                'name'      => ts('Amount'),
                                                'sort'      => 'total_amount',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Type'),
                                                'sort'      => 'contribution_type_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Source'),
                                                'sort'      => 'contribution_source',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Received'),
                                                'sort'      => 'receive_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Thank-you Sent'),
                                                'sort'      => 'thankyou_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Cancelled'),
                                                'sort'      => 'cancel_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('desc' => ts('Actions') ),
                                          );
        }
        return self::$_columnHeaders;
    }
    
    function &getQuery( ) {
        return $this->_query;
    }

    /** 
     * name of export file. 
     * 
     * @param string $output type of output 
     * @return string name of the file 
     */ 
    function getExportFileName( $output = 'csv') { 
        return ts('CiviCRM Contribution Search'); 
    }

}//end of class

?>
