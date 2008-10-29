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

class CRM_Case_BAO_Query 
{
    
    static function &getFields( ) 
    {
        $fields = array( );
        require_once 'CRM/Case/DAO/Case.php';
        $fields = array_merge( $fields, CRM_Case_DAO_Case::import( ) );
        return $fields;  
    }

    /** 
     * build select for Case 
     * 
     * @return void  
     * @access public  
     */
    static function select( &$query ) 
    {
        if ( ( $query->_mode & CRM_Contact_BAO_Query::MODE_CASE ) ||
             CRM_Utils_Array::value( 'case_id', $query->_returnProperties ) ) {
            $query->_select['case_id'] = "civicrm_case.id as case_id";
            $query->_element['case_id'] = 1;
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
            $query->_tables['civicrm_case_contact'] = $query->_whereTables['civicrm_case_contact'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'case_type', $query->_returnProperties ) ) {
            $query->_select['case_type']  = "case_type.label as case_type";
            $query->_element['case_type'] = 1;
            $query->_tables['case_type']  = $query->_whereTables['case_type'] = 1;
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'case_role', $query->_returnProperties ) ) {
            $query->_select['case_role']  = "case_relation_type.name_a_b as case_role";
            $query->_element['case_role'] = 1;
            $query->_tables['case_relationship'] = $query->_whereTables['case_relationship'] = 1;
            $query->_tables['case_relation_type'] = $query->_whereTables['case_relation_type'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_status', $query->_returnProperties ) ) {
            $query->_select['case_status']  = "case_status.name as case_status";
            $query->_element['case_status'] = 1;
            $query->_tables['case_status']  = $query->_whereTables['case_status'] = 1;
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_recent_activity_date', $query->_returnProperties ) ) {
            $query->_select['case_recent_activity_date']  = "civicrm_activity.activity_date_time as case_recent_activity_date";
            $query->_element['case_recent_activity_date'] = 1;
            $query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_recent_activity_type', $query->_returnProperties ) ) {
            $query->_select['case_recent_activity_type']  = "civicrm_category.label as case_recent_activity_type";
            $query->_element['case_recent_activity_type'] = 1;
            $query->_tables['civicrm_category'] = $query->_whereTables['civicrm_category'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_scheduled_activity_date', $query->_returnProperties ) ) {
            $query->_select['case_scheduled_activity_date']  = "civicrm_activity.activity_date_time as case_scheduled_activity_date";
            $query->_element['case_scheduled_activity_date'] = 1;
            $query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_scheduled_activity_type', $query->_returnProperties ) ) {
            $query->_select['case_scheduled_activity_type']  = "civicrm_category.label as case_scheduled_activity_type";
            $query->_element['case_scheduled_activity_type'] = 1;
            $query->_tables['civicrm_category'] = $query->_whereTables['civicrm_category'] = 1;
        }
    }

     /** 
     * Given a list of conditions in query generate the required
     * where clause
     * 
     * @return void 
     * @access public 
     */ 
    static function where( &$query ) 
    {
        $isTest   = false;
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 5) == 'case_' ) {
                if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                    $query->_useDistinct = true;
                }
                $grouping = $query->_params[$id][3];
                self::whereClauseSingle( $query->_params[$id], $query );
            }
        }
        
        //  foreach ( array_keys( $query->_params ) as $id ) {
        //             if ( substr( $query->_params[$id][0], 0, 5) == 'case_' ) {
        //                 self::whereClauseSingle( $query->_params[$id], $query );
        //             }
        //         }
    }
    
    /** 
     * where clause for a single field
     * 
     * @return void 
     * @access public 
     */ 
    static function whereClauseSingle( &$values, &$query ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        switch( $name ) {
            
        case 'case_status_id':
            require_once 'CRM/Core/OptionGroup.php' ;
            $caseStatus = CRM_Core_OptionGroup::values('case_status');

            $query->_where[$grouping][] = "civicrm_case.status_id {$op} $value ";

            $value = $caseStatus[$value];
            $query->_qill[$grouping ][] = ts( 'Case Status %2 %1', array( 1 => $value, 2 => $op) );
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
            return;
            
        case 'case_type_id':
            require_once 'CRM/Core/OptionGroup.php' ;
            $caseType = CRM_Core_OptionGroup::values('case_type');
            $names = array( );
            foreach ( $value as $id => $val ) {
                $names[] = $caseType[$val];
            }
            require_once 'CRM/Case/BAO/Case.php';
            $value = CRM_Case_BAO_Case::VALUE_SEPERATOR . 
                implode( CRM_Case_BAO_Case::VALUE_SEPERATOR . "%' OR civicrm_case.case_type_id LIKE '%" .
                         CRM_Case_BAO_Case::VALUE_SEPERATOR, $value) . 
                CRM_Case_BAO_Case::VALUE_SEPERATOR;
            $query->_where[$grouping][] = "(civicrm_case.case_type_id LIKE '%{$value}%')";

            $value = $caseType[$value];
            $query->_qill[$grouping ][] = ts( 'Case Type %1', array( 1 => $op))  . ' ' . implode( ' ' . ts('or') . ' ', $names );
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
            return;

        case 'case_id':
            $query->_where[$grouping][] = "civicrm_case.id $op $value";
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
            return;
        }
    }

    static function from( $name, $mode, $side ) 
    {
        $from = null;
                   
        switch ( $name ) {
            
        case 'civicrm_case_contact':
            $from = " $side JOIN civicrm_case_contact ON civicrm_case_contact.contact_id = contact_a.id ";
            break;

        case 'civicrm_case':
            $from .= " INNER JOIN civicrm_case ON civicrm_case_contact.case_id = civicrm_case.id ";
            break;

        case 'case_status':
            $from .= " $side JOIN civicrm_option_group option_group_case_status ON (option_group_case_status.name = 'case_status')";
            $from .= " $side JOIN civicrm_option_value case_status ON (civicrm_case.status_id = case_status.value AND option_group_case_status.id = case_status.option_group_id ) ";
            break;

        case 'case_type':
            $from .= " $side JOIN civicrm_option_group option_group_case_type ON (option_group_case_type.name = 'case_type')";
            $from .= " $side JOIN civicrm_option_value case_type ON (civicrm_case.case_type_id = case_type.value AND option_group_case_type.id = case_type.option_group_id ) ";
            break;
            
        case 'civicrm_category':
            $from .= " $side JOIN civicrm_category ON civicrm_category.id = civicrm_activity.activity_type_id LEFT JOIN civicrm_case_activity ON civicrm_case_activity.activity_id = civicrm_activity.id";
            break;

        case 'case_relationship':
            $from .=" $side JOIN civicrm_relationship case_relationship ON case_relationship.contact_id_b = civicrm_case_contact.contact_id ";
            break;

        case 'case_relation_type':
            $from .=" $side JOIN civicrm_relationship_type case_relation_type ON ( case_relation_type.id = case_relationship.relationship_type_id AND
case_relation_type.id = case_relationship.relationship_type_id )";
            break;

        }
        return $from;
        
    }
    
    /**
     * getter for the qill object
     *
     * @return string
     * @access public
     */
    function qill( ) {
        return (isset($this->_qill)) ? $this->_qill : "";
    }
    
    static function defaultReturnProperties( $mode ) 
    {

        $properties = null;
        
        if ( $mode & CRM_Contact_BAO_Query::MODE_CASE ) {
            $properties = array(  
                                'contact_id'                  =>      1,
                                'sort_name'                   =>      1,   
                                'display_name'                =>      1,
                                'case_id'                     =>      1,   
                                'case_status'                 =>      1, 
                                'case_type'                   =>      1,
                                'case_role'                   =>      1,
                                'case_recent_activity_date'   =>      1,
                                'case_recent_activity_type'   =>      1, 
                                'case_scheduled_activity_date'=>      1,
                                'case_scheduled_activity_type'=>      1

                            );
        }
        return $properties;
    }
    
    static function tableNames( &$tables ) 
    {
        if ( CRM_Utils_Array::value( 'civicrm_case', $tables ) ) {
            $tables = array_merge( array( 'civicrm_case_contact' => 1), $tables );
        }

        if ( CRM_Utils_Array::value( 'case_relation_type', $tables ) ) {
            $tables = array_merge( array( 'case_relationship' => 1), $tables );
        }
    }
    
    /**
     * add all the elements shared between case search and advanaced search
     *
     * @access public 
     * @return void
     * @static
     */  
    static function buildSearchForm( &$form ) 
    {
        $config =& CRM_Core_Config::singleton( );
        require_once 'CRM/Core/OptionGroup.php';
        $caseType = CRM_Core_OptionGroup::values('case_type');
        $form->addElement('select', 'case_type_id',  ts( 'Case Type' ),  
                          $caseType, array("size"=>"5",  "multiple"));
        
        $caseStatus = CRM_Core_OptionGroup::values('case_status'); 
        $form->add('select', 'case_status_id',  ts( 'Case Status' ),  
                   array( '' => ts( '- select -' ) ) + $caseStatus );
        
        $form->assign( 'validCiviCase', true );
    }

    static function searchAction( &$row, $id ) 
    {
    }

    static function addShowHide( &$showHide ) 
    {
        $showHide->addHide( 'caseForm' );
        $showHide->addShow( 'caseForm_show' );
    }

}


