<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
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
class CRM_Case_Selector_Search extends CRM_Core_Selector_Base 
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
    static $_properties = array( 
                                'contact_id',
                                'sort_name',   
                                'case_id',   
                                'case_status', 
                                'case_type',
                                'relationshipType_id',
                                'case_recent_activity_date',
                                'case_recent_activity_type', 
                                'case_scheduled_activity_date',
                                'case_scheduled_activity_type'
                                 );

    /** 
     * are we restricting ourselves to a single contact 
     * 
     * @access protected   
     * @var boolean   
     */   
    protected $_single = false;

    /**  
     * are we restricting ourselves to a single contact  
     *  
     * @access protected    
     * @var boolean    
     */    
    protected $_limit = null;

    /**
     * what context are we being invoked from
     *   
     * @access protected     
     * @var string
     */     
    protected $_context = null;

    /**
     * queryParams is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    public $_queryParams;

    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;

    /** 
     * The additional clause that we restrict the search with 
     * 
     * @var string 
     */ 
    protected $_additionalClause = null;

    /** 
     * The query object
     * 
     * @var string 
     */ 
    protected $_query;

    /**
     * Class constructor
     *
     * @param array   $queryParams array of parameters for query
     * @param int     $action - action of search basic or advanced.
     * @param string  $additionalClause if the caller wants to further restrict the search (used in participations)
     * @param boolean $single are we dealing only with one contact?
     * @param int     $limit  how many signers do we want returned
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct( &$queryParams,
                          $action = CRM_Core_Action::NONE,
                          $additionalClause = null,
                          $single = false,
                          $limit = null,
                          $context = 'search' ) 
    {

        // submitted form values
        $this->_queryParams =& $queryParams;

        $this->_single  = $single;
        $this->_limit   = $limit;
        $this->_context = $context;

        $this->_additionalClause = $additionalClause;
        
        // type of selector
        $this->_action = $action;

        $this->_query =& new CRM_Contact_BAO_Query( $this->_queryParams, null, null, false, false,
                                                    CRM_Contact_BAO_Query::MODE_CASE);

        $this->_query->_distinctComponentClause = " DISTINCT civicrm_case.id ";
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
    static function &links( $hideOption )
    {
        $cancelExtra = ts('Do you want to continue?');
        self::$_links = array(
                              CRM_Core_Action::VIEW   => array(
                                                               'name'     => ts('View'),
                                                               'url'      => 'civicrm/contact/view/case',
                                                               'qs'       => 'reset=1&id=%%id%%&cid=%%cid%%&action=view&context=%%cxt%%&selectedChild=case',
                                                               'title'    => ts('View Case'),
                                                               ),
                              CRM_Core_Action::UPDATE => array(
                                                               'name'     => ts('Edit'),
                                                               'url'      => 'civicrm/contact/view/case',
                                                               'qs'       => 'reset=1&action=update&id=%%id%%&cid=%%cid%%&context=%%cxt%%',
                                                               'title'    => ts('Edit Case'),
                                                               ),
                              CRM_Core_Action::DETACH => array(
                                                               'name'     => ts('Cancel'),
                                                               'url'      => 'civicrm/contact/view/case',
                                                               'qs'       => 'reset=1&action=detach&id=%%id%%&cid=%%cid%%&context=%%cxt%%',
                                                               'extra'    => 'onclick = "return confirm(\'' . $cancelExtra . '\');"',
                                                               'title'    => ts('Cancel Case'),
                                                               ),
                              CRM_Core_Action::DELETE => array(
                                                               'name'     => ts('Delete'),
                                                               'url'      => 'civicrm/contact/view/Case',
                                                               'qs'       => 'reset=1&action=delete&id=%%id%%&cid=%%cid%%&context=%%cxt%%',
                                                               'title'    => ts('Delete Case'),
                                                               ),
                              ); 
        
        
        if ( in_array('Cancel', $hideOption ) ) {
            unset( self::$_links[CRM_Core_Action::DETACH] );
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
        $params['status']       = ts('Case') . ' %%StatusMessage%%';
        $params['csvString']    = null;
        if ( $this->_limit ) {
            $params['rowCount']     = $this->_limit;
        } else {
            $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;
        }

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    } //end of function

    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        return $this->_query->searchQuery( 0, 0, null,
                                           true, false, 
                                           false, false, 
                                           false, 
                                           $this->_additionalClause );
        
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
     function &getRows($action, $offset, $rowCount, $sort, $output = null) 
     {


    static $_properties = array( 
                                'contact_id',
                                'sort_name',   
                                'case_id',   
                                'case_status', 
                                'case_type',
                                'relationshipType_id',
                                'case_recent_activity_date',
                                'case_recent_activity_type', 
                                'case_scheduled_activity_date',
                                'case_scheduled_activity_type'
                                 );

         $query = "select 
         civicrm_contact.id as contact_id,          
         civicrm_contact.contact_type as contact_type,
         civicrm_contact.sort_name as sort_name,
         civicrm_case.id as case_id,
         cov1.label as case_status,
         civicrm_case.subject as subject,
         cov2.label as case_type,
         max(ca1.activity_date_time) as case_recent_activity_date,
         cat1.label as case_recent_activity_type,
         min(ca2.due_date_time) as case_scheduled_activity_date,
         cat2.label as case_scheduled_activity_type
         
         from civicrm_case 
         
         LEFT JOIN civicrm_case_contact ON civicrm_case.id = civicrm_case_contact.case_id 
         LEFT JOIN civicrm_contact ON civicrm_case_contact.contact_id = civicrm_contact.id
         LEFT JOIN civicrm_option_value as cov1 ON (civicrm_case.status_id=cov1.value AND cov1.option_group_id=28)
         LEFT JOIN civicrm_option_value as cov2 ON (civicrm_case.status_id=cov2.value AND cov2.option_group_id=27)
         LEFT JOIN civicrm_case_activity ON civicrm_case.id=civicrm_case_activity.case_id
         LEFT JOIN (civicrm_activity ca1, civicrm_category cat1)
         ON (civicrm_case_activity.activity_id=ca1.id AND cat1.id=ca1.activity_type_id)
         LEFT JOIN (civicrm_activity ca2, civicrm_category cat2)
         ON (civicrm_case_activity.activity_id=ca2.id AND cat2.id=ca2.activity_type_id)
         
         GROUP BY civicrm_case.id";

         $params = array();
         $result =& CRM_Core_DAO::executeQuery( $query, $params );

         // process the result of the query
         $rows = array( );
         
         // check is the user has view/edit signer permission
         $permission = CRM_Core_Permission::VIEW;
         if ( CRM_Core_Permission::check( 'edit cases' ) ) {
             $permission = CRM_Core_Permission::EDIT;
         }
         
         $mask = CRM_Core_Action::mask( $permission );
         while ( $result->fetch( ) ) {
             $row = array();
             // the columns we are interested in
             foreach (self::$_properties as $property) {
                 if ( isset( $result->$property ) ) {
                     $row[$property] = $result->$property;
                 }
             }

//         CRM_Core_Error::debug( 'rows', $result);
                                       
             $hideOption = array();
             if ( CRM_Utils_Array::key( 'Cancelled', $row ) ||
                  CRM_Utils_Array::key('Completed', $row ) ) {
                 $hideOption[] = 'Cancel';
             }
             
             $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->case_id;
             
             $row['action']   = CRM_Core_Action::formLink( self::links( $hideOption ), $mask,
                                                           array( 'id'  => $result->case_id,
                                                                  'cid' => $result->contact_id,
                                                                  'cxt' => $this->_context ) );
             
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
             
             $row['contact_type' ] = $contact_type;
             
             $rows[] = $row;
         }



         return $rows;
     }
     
     
     /**
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
      * returns the column headers as an array of tuples: 
     * (name, sortName (key to the sort array)) 
     * 
     * @param string $action the action being performed 
     * @param enum   $output what should the result set include (web/email/csv) 
     * 
     * @return array the column headers that need to be displayed 
     * @access public 
     */ 
    public function &getColumnHeaders( $action = null, $output = null ) 
    {
        if ( ! isset( self::$_columnHeaders ) ) {
            self::$_columnHeaders = array( 
                                          array(
                                                'name'      => ts('Client\'s Name'),
                                                'sort'      => 'sort_name',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Case Status'),
                                                'sort'      => 'case_status_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Case Type'),
                                                'sort'      => 'case_type_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Role'),
                                                'sort'      => 'relationshipType_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Date of Most Recent Activity'),
                                                'sort'      => 'case_recent_activity_date',
                                                'direction' => CRM_Utils_Sort::DESCENDING,
                                                ),
                                          array(
                                                'name'      => ts('Activity (most recent activity)'),
                                                'sort'      => 'case_recent_activity_type',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Date of next scheduled Activity'),
                                                'sort'      => 'case_scheduled_activity_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Activity (next scheduled activity)'),
                                                'sort'      => 'case_scheduled_activity_type',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Actions') ),
                                          );
            
//            if ( ! $this->_single ) {
//                $pre = array( 
//                             array('desc'      => ts('Contact Id') ), 
//                             array( 
//                                   'name'      => ts('Name'), 
//                                   'sort'      => 'sort_name', 
//                                   'direction' => CRM_Utils_Sort::DONTCARE,
//                                   )
//                             );
//                
//                self::$_columnHeaders = array_merge( $pre, self::$_columnHeaders );
//            }
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
         return ts('Case Search'); 
     } 

}//end of class


