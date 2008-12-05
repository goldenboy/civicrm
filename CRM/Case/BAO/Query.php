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
        require_once 'CRM/Case/BAO/Case.php';
        $fields = array( );
        $fields = CRM_Case_BAO_Case::exportableFields( );
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
        //field which are used in export, not in find case
        $activityElements = array( 'case_subject', 'case_source_contact_id', 'case_activity_status_id',
                                   'case_activity_duration', 'case_activity_medium_id', 'case_activity_details',
                                   'case_activity_is_auto' );

        //this block is used to figure out the whether the mode is
        //just find case or find case with export
        //here we change the table, to avoid the confict,
        //recent_activity is used in find case and all_activity is
        //used in export, since recent_activity contain code which are
        //only used in find case

        $tableName = "recent_activity";
        foreach( $activityElements as $val ) {
            if ( CRM_Utils_Array::value( $val, $query->_returnProperties) ) {
                $tableName = "all_activity";
                unset($query->_returnProperties['case_role']);
                break;
            }
        }

        if ( ( $query->_mode & CRM_Contact_BAO_Query::MODE_CASE ) ||
             CRM_Utils_Array::value( 'case_id', $query->_returnProperties ) ) {
            $query->_select['case_id'] = "civicrm_case.id as case_id";
            $query->_element['case_id'] = 1;
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
            $query->_tables['civicrm_case_contact'] = $query->_whereTables['civicrm_case_contact'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'case_type_id', $query->_returnProperties ) ) {
            $query->_select['case_type']  = "case_type.label as case_type_id";
            $query->_element['case_type'] = 1;
            $query->_tables['case_type']  = $query->_whereTables['case_type'] = 1;
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_start_date', $query->_returnProperties ) ) {
            $query->_select['case_start_date']  = "civicrm_case.start_date as case_start_date";
            $query->_element['case_start_date'] = 1;
            $query->_tables['civicrm_case']     = 1;
        }

        if ( CRM_Utils_Array::value( 'case_end_date', $query->_returnProperties ) ) {
            $query->_select['case_end_date']  = "civicrm_case.end_date as case_end_date";
            $query->_element['case_end_date'] = 1;
            $query->_tables['civicrm_case']   = 1;
        }
        
        if ( CRM_Utils_Array::value( 'case_status_id', $query->_returnProperties ) ) {
            $query->_select['case_status']  = "case_status.label as case_status_id";
            $query->_element['case_status'] = 1;
            $query->_tables['case_status']  = $query->_whereTables['case_status'] = 1;
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'case_deleted', $query->_returnProperties ) ) {
            $query->_select['case_deleted']  = "civicrm_case.is_deleted as case_deleted";
            $query->_element['case_deleted'] = 1;
            $query->_tables['civicrm_case']  = $query->_whereTables['civicrm_case'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_role', $query->_returnProperties ) ) {
            $query->_select['case_role']          = "case_relation_type.name_b_a as case_role";
            $query->_element['case_role']         = 1;
            $query->_tables['case_relationship']  = $query->_whereTables['case_relationship'] = 1;
            $query->_tables['case_relation_type'] = $query->_whereTables['case_relation_type'] = 1;
        }

        if ( CRM_Utils_Array::value( 'case_recent_activity_date', $query->_returnProperties ) ) {
            $query->_select['case_recent_activity_date']  = "recent_activity.activity_date_time as case_recent_activity_date";
            $query->_element['case_recent_activity_date'] = 1;
            $query->_tables[$tableName]                   = $query->_whereTables[$tableName] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'case_subject', $query->_returnProperties ) ) {
            $query->_select['case_subject']    = "recent_activity.subject as case_subject";
            $query->_element['case_subject']   = 1;
            $query->_tables['all_activity']    = 1;
        }

        if ( CRM_Utils_Array::value( 'case_source_contact_id', $query->_returnProperties ) ) {
            $query->_select['case_source_contact_id']  = "civicrm_case_reporter.sort_name as case_source_contact_id";
            $query->_element['case_source_contact_id'] = 1;
            $query->_tables['civicrm_case_reporter']   = 1;
            $query->_tables['all_activity']            = 1;
        }

        if ( CRM_Utils_Array::value( 'case_activity_status_id', $query->_returnProperties ) ) {
            $query->_select['case_activity_status_id']  = "rec_activity_status.label as case_activity_status_id";
            $query->_element['case_activity_status_id'] = 1;
            $query->_tables['all_activity']             = 1;
            $query->_tables['recent_activity_status']   = 1;
        }

        if ( CRM_Utils_Array::value( 'case_activity_duration', $query->_returnProperties ) ) {
            $query->_select['case_activity_duration']  = "recent_activity.duration as case_activity_duration";
            $query->_element['case_activity_duration'] = 1;
            $query->_tables['all_activity']            = 1;
        }

        if ( CRM_Utils_Array::value( 'case_activity_medium_id', $query->_returnProperties ) ) {
            $query->_select['case_activity_medium_id']  = "recent_activity_medium.label as case_activity_medium_id";
            $query->_element['case_activity_medium_id'] = 1;
            $query->_tables['all_activity']             = 1;
            $query->_tables['case_activity_medium']     = 1;
        }  

        if ( CRM_Utils_Array::value( 'case_activity_details', $query->_returnProperties ) ) {
            $query->_select['case_activity_details']  = "recent_activity.details as case_activity_details";
            $query->_element['case_activity_details'] = 1;
            $query->_tables['all_activity']           = 1;
        }

        if ( CRM_Utils_Array::value( 'case_activity_is_auto', $query->_returnProperties ) ) {
            $query->_select['case_activity_is_auto']  = "recent_activity.is_auto as case_activity_is_auto";
            $query->_element['case_activity_is_auto'] = 1;
            $query->_tables['all_activity']           = 1;

        }

        if ( CRM_Utils_Array::value( 'case_scheduled_activity_date', $query->_returnProperties ) ) {
            $query->_select['case_scheduled_activity_date']  = "recent_activity.due_date_time as case_scheduled_activity_date";
            $query->_element['case_scheduled_activity_date'] = 1;
            $query->_tables[$tableName]                      = 1;
        }
        if ( CRM_Utils_Array::value( 'case_recent_activity_type', $query->_returnProperties ) ) {
            $query->_select['case_recent_activity_type']  = "rec_activity_type.label as case_recent_activity_type";
            $query->_element['case_recent_activity_type'] = 1;
            $query->_tables[$tableName]                   = 1;
            if ( $tableName == 'all_activity' ) {
                $query->_tables['case_activity_type']        =  1;
            } else {
                $query->_tables['recent_activity_type'] = $query->_whereTables['recent_activity_type'] = 1;
            }           
        }
        if ( $query->_tables['all_activity'] && $query->_tables['recent_activity'] ) {
            unset($query->_tables['recent_activity']);
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
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 5) == 'case_' ) {
                if ( $query->_mode == CRM_Contact_BAO_Query::MODE_CONTACTS ) {
                    $query->_useDistinct = true;
                }
				$grouping = $query->_params[$id][3];
				self::whereClauseSingle( $query->_params[$id], $query );
            }
        }
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
            require_once "CRM/Case/PseudoConstant.php";
            $statuses  = CRM_Case_PseudoConstant::caseStatus( );

            $query->_where[$grouping][] = "civicrm_case.status_id {$op} $value ";

            $value = $statuses[$value];
            $query->_qill[$grouping ][] = ts( 'Case Status %2 %1', array( 1 => $value, 2 => $op) );
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
            return;
            
        case 'case_type_id':
            require_once "CRM/Case/PseudoConstant.php";
            $caseTypes = CRM_Case_PseudoConstant::caseType( );            
            
            if ( is_array( $value ) ) {
                foreach ($value as $k => $v) {
                    if ($v) {
                        $val[$k] = $k;
                    }
                } 
            }

            $names = array( );
            foreach ( $value as $id => $dontCare ) {
                $names[] = $caseTypes[$id];
            }
            require_once 'CRM/Case/BAO/Case.php';
            $value = CRM_Case_BAO_Case::VALUE_SEPERATOR . 
                implode( CRM_Case_BAO_Case::VALUE_SEPERATOR . "%' OR civicrm_case.case_type_id LIKE '%" .
                         CRM_Case_BAO_Case::VALUE_SEPERATOR, $val) . 
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

        case 'case_owner':
            $query->_where[$grouping][] = "civicrm_case_contact.contact_id $op $value";
            $query->_qill[$grouping ][] = ts( 'Case %1 My Cases', array( 1 => $op ) );
            $query->_tables['civicrm_case_contact'] = $query->_whereTables['civicrm_case_contact'] = 1;
            return;
	
		case 'case_recent_activity_type':
			$query->_where[$grouping][] = " rec_activity_type.name != '$value' AND  ca2.id IS NULL";
 			return;

        case 'case_deleted':
            $queryDelete = " civicrm_case.is_deleted $op $value ";
            if ( $query->_mode &  CRM_Contact_BAO_Query::MODE_CASE  ) {
                $queryDelete .= " AND recent_activity.is_deleted $op $value ";
            }
            $query->_where[$grouping][] = $queryDelete;
            if ( $value ) {
                $query->_qill[$grouping][]  = "Find Deleted Cases";
            }
            $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;
            return;
        }
    }

    static function from( $name, $mode, $side ) 
    {
        $from = "";
                                 
        switch ( $name ) {
            
        case 'civicrm_case_contact':
            $from .= " $side JOIN civicrm_case_contact ON civicrm_case_contact.contact_id = contact_a.id ";
            break;

        case 'civicrm_case_reporter':
            $from .= " $side JOIN civicrm_contact as civicrm_case_reporter ON civicrm_case_contact.contact_id = civicrm_case_reporter.id ";
            break;
            
        case 'civicrm_case':
            $from .= " INNER JOIN civicrm_case ON civicrm_case_contact.case_id = civicrm_case.id";
            break;

        case 'case_status':
            $from .= " $side JOIN civicrm_option_group option_group_case_status ON (option_group_case_status.name = 'case_status')";
            $from .= " $side JOIN civicrm_option_value case_status ON (civicrm_case.status_id = case_status.value AND option_group_case_status.id = case_status.option_group_id ) ";
            break;

        case 'case_type':
            $from .= " $side JOIN civicrm_option_group option_group_case_type ON (option_group_case_type.name = 'case_type')";
            $from .= " $side JOIN civicrm_option_value case_type ON (civicrm_case.case_type_id = case_type.value AND option_group_case_type.id = case_type.option_group_id ) ";
            break;
            
        case 'recent_activity_type':
            $from .= " $side JOIN civicrm_option_group option_group_activity_type ON (option_group_activity_type.name = 'activity_type')";
            $from .= " $side JOIN civicrm_option_value rec_activity_type ON (recent_activity.activity_type_id = rec_activity_type.value AND option_group_activity_type.id = rec_activity_type.option_group_id ) ";
            break;

        case 'case_activity_type':
            $from .= " $side JOIN civicrm_option_group option_group_activity_type ON (option_group_activity_type.name = 'activity_type')";
            $from .= " $side JOIN civicrm_option_value rec_activity_type ON (recent_activity.activity_type_id = rec_activity_type.value AND option_group_activity_type.id = rec_activity_type.option_group_id ) ";
            break;

        case 'recent_activity_status':
            $from .= " $side JOIN civicrm_option_group option_group_activity_status ON (option_group_activity_status.name = 'activity_status')";
            $from .= " $side JOIN civicrm_option_value rec_activity_status ON (recent_activity.status_id = rec_activity_status.value AND option_group_activity_status.id = rec_activity_status.option_group_id ) ";
            break;

        case 'recent_activity':
            $from .= " INNER JOIN civicrm_case_activity ON civicrm_case_activity.case_id = civicrm_case.id ";
			$from .= " INNER JOIN civicrm_activity recent_activity ON ( civicrm_case_activity.activity_id = recent_activity.id
				AND recent_activity.is_current_revision = 1
				AND recent_activity.activity_date_time <= NOW() 
				AND recent_activity.activity_date_time >= DATE_SUB( NOW(), INTERVAL 20 DAY )) ";

            $from .= " LEFT JOIN civicrm_activity ca2
                              ON ( ca2.id IN ( SELECT cca.activity_id FROM civicrm_case_activity cca 
                                               WHERE cca.case_id = civicrm_case.id )
                                   AND ca2.is_current_revision = 1
                                          AND ca2.activity_date_time <= NOW() 
                                   AND ca2.activity_date_time >= DATE_SUB( NOW(), INTERVAL 14 DAY )
                                   AND recent_activity.activity_date_time < ca2.activity_date_time  )";
            break;            

        case 'case_relationship':
            $session = & CRM_Core_Session::singleton();
            $userID  = $session->get('userID');
            $from .=" $side JOIN civicrm_relationship case_relationship ON ( case_relationship.contact_id_a = civicrm_case_contact.contact_id AND case_relationship.contact_id_b = {$userID} )";
            break;

        case 'case_relation_type':
            $from .=" $side JOIN civicrm_relationship_type case_relation_type ON ( case_relation_type.id = case_relationship.relationship_type_id AND
case_relation_type.id = case_relationship.relationship_type_id )";
            break;

        case 'case_activity_medium':
            $from .= " $side JOIN civicrm_option_group option_group_activity_medium ON (option_group_activity_medium.name = 'encounter_medium')";
            $from .= " $side JOIN civicrm_option_value recent_activity_medium ON (recent_activity.medium_id = recent_activity_medium.value AND option_group_activity_medium.id = recent_activity_medium.option_group_id ) ";
            break;

        case 'all_activity':
            $from .= " INNER JOIN civicrm_case_activity ON civicrm_case_activity.case_id = civicrm_case.id ";
			$from .= " INNER JOIN civicrm_activity recent_activity ON ( civicrm_case_activity.activity_id = recent_activity.id
				                                                        AND recent_activity.is_current_revision = 1 )";
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
                                'contact_type'                =>      1,
                                'contact_id'                  =>      1,
                                'sort_name'                   =>      1,   
                                'display_name'                =>      1,
                                'case_id'                     =>      1,   
                                'case_status_id'              =>      1, 
                                'case_type_id'                =>      1,
                                'case_role'                   =>      1,
                                'case_deleted'                =>      1, 
                                'case_recent_activity_date'   =>      1,
                                'case_recent_activity_type'   =>      1, 
                                'case_scheduled_activity_date'=>      1
                                // 'case_scheduled_activity_type'=>      1
                                );
        }
        return $properties;
    }

 
    /**
     * This includes any extra fields that might need for export etc
     */
    static function extraReturnProperties( $mode ) 
    {
        $properties = null;
     
        if ( $mode & CRM_Contact_BAO_Query::MODE_CASE ) {
            $properties = array( 
                                'case_start_date'         => 1,
                                'case_end_date'           => 1,
                                'case_subject'            => 1,
                                'case_source_contact_id'  => 1,
                                'case_activity_status_id' => 1,
                                'case_activity_duration'  => 1,
                                'case_activity_medium_id' => 1,
                                'case_activity_details'   => 1,
                                'case_activity_is_auto'   => 1
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

        require_once "CRM/Case/PseudoConstant.php";
        $caseTypes = CRM_Case_PseudoConstant::caseType( );
        foreach ( $caseTypes as $id => $Name) {
            $form->addElement('checkbox', "case_type_id[$id]", null,$Name);
        }
      
        $statuses  = CRM_Case_PseudoConstant::caseStatus( );
        $form->add('select', 'case_status_id',  ts( 'Case Status' ),  
                   array( '' => ts( '- any status -' ) ) + $statuses );
        
        $form->assign( 'validCiviCase', true );
    
        $caseOwner = array( ts('My Cases'), ts('All Cases') );
        $form->addRadio( 'case_owner', ts( 'Cases' ), $caseOwner );
        $form->setDefaults(array('case_owner' => 1));

        require_once"CRM/Core/Permission.php";
        if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ) { 
            $form->addElement( 'checkbox', 'case_deleted' , ts( 'Deleted Cases' ) );
        }
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


