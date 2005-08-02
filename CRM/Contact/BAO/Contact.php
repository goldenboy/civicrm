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

require_once 'CRM/Core/DAO/Note.php';
require_once 'CRM/Core/Form.php';

require_once 'CRM/Contact/DAO/Contact.php';

require_once 'CRM/Core/DAO/Location.php';
require_once 'CRM/Core/DAO/Address.php';
require_once 'CRM/Core/DAO/Phone.php';
require_once 'CRM/Core/DAO/Email.php';



/**
 * rare case where because of inheritance etc, we actually store a reference
 * to the dao object rather than inherit from it
 */

class CRM_Contact_BAO_Contact extends CRM_Contact_DAO_Contact 
{
    /**
     * the types of communication preferences
     *
     * @var array
     */
    static $_commPrefs = array( 'do_not_phone', 'do_not_email', 'do_not_mail', 'do_not_trade' );

    /**
     * static field for all the contact information that we can potentially import
     *
     * @var array
     * @static
     */
    static $_importableFields = null;

    function __construct()
    {
        parent::__construct();
    }

    static function permissionedContact( $id, $type = CRM_Core_Permission::VIEW ) {
        $tables     = array( );
        $permission = CRM_Core_Permission::whereClause( $type, $tables );
        $from       = self::fromClause( $tables );
        $query = "
SELECT count(DISTINCT civicrm_contact.id) 
       $from
WHERE civicrm_contact.id = $id AND $permission
";

        $dao =& new CRM_Core_DAO( );
        $dao->query($query);
        
        $result = $dao->getDatabaseResult();
        $row    = $result->fetchRow();
        return ( $row[0] > 0 ) ? true : false;
    }

    /**
     * given an id return the relevant contact details
     *
     * @param int $id contact id
     *
     * @return the contact object
     * @static
     * @access public
     */
    static function contactDetails( $id ) {
        if ( ! $id ) {
            return null;
        }

        $query = "
SELECT DISTINCT
  civicrm_contact.id as contact_id,
  civicrm_individual.id               as individual_id ,
  civicrm_location.id                 as location_id   ,
  civicrm_address.id                  as address_id    ,
  civicrm_email.id                    as email_id      ,
  civicrm_phone.id                    as phone_id      ,
  civicrm_individual.first_name       as first_name    ,
  civicrm_individual.middle_name      as middle_name   ,
  civicrm_individual.last_name        as last_name     ,
  civicrm_individual.prefix           as prefix        ,
  civicrm_individual.suffix           as suffix        ,
  civicrm_address.street_address      as street_address,
  civicrm_address.city                as city          ,
  civicrm_address.postal_code         as postal_code   ,
  civicrm_state_province.name         as state         ,
  civicrm_country.name                as country       ,
  civicrm_email.email                 as email         ,
  civicrm_phone.phone                 as phone         ";

        $tables = array( 'civicrm_individual'     => 1,
                         'civicrm_location'       => 1,
                         'civicrm_address'        => 1,
                         'civicrm_email'          => 1,
                         'civicrm_phone'          => 1,
                         'civicrm_state_province' => 1,
                         'civicrm_country'        => 1,
                         'civicrm_custom_value'   => 1 );
        $query .= self::fromClause( $tables );

        $query .= " WHERE civicrm_contact.id = $id";

        $dao =& new CRM_Core_DAO( );
        $dao->query($query);
        if ( $dao->fetch( ) ) {
            return $dao;
        }
        return null;
    }

    /**
     * Find contacts which match the criteria
     *
     * @param string $matchClause the matching clause
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     * @param int    $id          the current contact id (hence excluded from matching)
     *
     * @return string                contact ids if match found, else null
     * @static
     * @access public
     */
    static function matchContact( $matchClause, &$tables, $id = null ) {
        $config =& CRM_Core_Config::singleton( );
        if ( $config->mysqlVersion >= 4.1 ) {
            $query  = "SELECT GROUP_CONCAT(DISTINCT civicrm_contact.id)";
        } else {
            $query  = "SELECT DISTINCT civicrm_contact.id as id";
        }
        $query .= self::fromClause( $tables );
        $query .= " WHERE $matchClause ";
        if ( $id ) {
            $query .= " AND civicrm_contact.id != $id ";
        }

        $dao =& new CRM_Core_DAO( );
        $dao->query($query);
        if ( $config->mysqlVersion >= 4.1 ) {
            $result = $dao->getDatabaseResult();
            if ( $result ) {
                $row = $result->fetchRow();
                if ( $row ) {
                    return $row[0];
                }
            }
        } else {
            $ids = array( );
            while ( $dao->fetch( ) ) {
                $ids[] = $dao->id;
            }
            return implode( ',', $ids );
        }
        return null;
    }

    /**
     * Get all the emails for a specified contact_id, with the primary email being first
     *
     * @param int $id the contact id
     *
     * @return array  the array of email id's
     * @access public
     * @static
     */
    static function allEmails( $id ) {
        if ( ! $id ) {
            return null;
        }

        $query = "
SELECT email, civicrm_location_type.name as locationType, civicrm_email.is_primary as is_primary
FROM    civicrm_contact
LEFT JOIN civicrm_location ON ( civicrm_location.entity_table = 'civicrm_contact' AND
                                civicrm_contact.id = civicrm_location.entity_id )
LEFT JOIN civicrm_location_type ON ( civicrm_location.location_type_id = civicrm_location_type.id )
LEFT JOIN civicrm_email ON ( civicrm_location.id = civicrm_email.location_id )
WHERE
  civicrm_contact.id = $id
ORDER BY
  civicrm_location.is_primary DESC, civicrm_email.is_primary DESC";
        
        $dao =& new CRM_Core_DAO( );
        $dao->query($query);
        $emails = array( );
        while ( $dao->fetch( ) ) {
            $emails[$dao->email] = array( 'locationType' => $dao->locationType,
                                          'primary'      => $dao->primary );
        }
        return $emails;
    }

    /**
     * create and query the db for an contact search
     *
     * @param array    $formValues array of reference of the form values submitted
     * @param int      $action   the type of action links
     * @param int      $offset   the offset for the query
     * @param int      $rowCount the number of rows to return
     * @param boolean  $count    is this a count only query ?
     * @param boolean  $includeContactIds should we include contact ids?
     * @param boolean  $sortByChar if true returns the distinct array of first characters for search results
     * @param boolean  $groupContacts if true, use a single mysql group_contact statement to get the contact ids
     *
     * @return CRM_Contact_DAO_Contact 
     * @access public
     */
    function searchQuery(&$fv, $offset, $rowCount, $sort, 
                         $count = false, $includeContactIds = false, $sortByChar = false,
                         $groupContacts = false )
    {
        $config =& CRM_Core_Config::singleton( );

        $select = $from = $where = $order = $limit = '';

        $tables = array( );
        if( $count ) {
            $select = "SELECT count(DISTINCT civicrm_contact.id) ";
        } else if ( $sortByChar ) {
            $select = "SELECT DISTINCT UPPER(LEFT(civicrm_contact.sort_name, 1)) as sort_name";
        } else if ( $groupContacts && $config->mysqlVersion < 4.1 ) {
            $select  = "SELECT DISTINCT civicrm_contact.id as id";
        } else if ( $groupContacts ) {
            $select  = "SELECT GROUP_CONCAT(DISTINCT civicrm_contact.id)";
        } else {
            $select = self::selectClause( $tables );
        }

        $where      = self::whereClause( $fv, $includeContactIds, $tables );
        $permission = CRM_Core_Permission::whereClause( CRM_Core_Permission::VIEW, $tables );
        if ( empty( $where ) ) {
            $where = " WHERE $permission ";
        } else {
            $where = " WHERE $where AND $permission ";
        }

        $from = self::fromClause( $tables );

        if (!$count) {
            if ($sort) {
                $order = " ORDER BY " . $sort->orderBy(); 
            } else if ($sortByChar) { 
                $order = " ORDER BY LEFT(civicrm_contact.sort_name, 1) ";
            }
            if ( $rowCount > 0 ) {
                $limit = " LIMIT $offset, $rowCount ";
            }
        }

        // building the query string
        $queryString = $select . $from . $where . $order . $limit;
        $crmDAO = new CRM_Core_DAO();

        $crmDAO->query($queryString);

        if ($count || $groupContacts) {
            if ( $groupContacts && $config->mysqlVersion < 4.1 ) {
                $ids = array( );
                while ( $crmDAO->fetch( ) ) {
                    $ids[] = $crmDAO->id;
                }
                return implode( ',', $ids );
            } else {
                $result = $crmDAO->getDatabaseResult();
                $row    = $result->fetchRow();
                return $row[0];
            }
        }

        return $crmDAO;
    }

    /**
     * create the default select clause
     *
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     *
     * @return string the select clause
     * @access public
     * @static
     */
    static function selectClause( &$tables ) {
        $tables['civicrm_location']       = 1;
        $tables['civicrm_address']        = 1;
        $tables['civicrm_phone']          = 1;
        $tables['civicrm_email']          = 1;
        $tables['civicrm_state_province'] = 1;
        $tables['civicrm_country']        = 1;
        $tables['civicrm_custom_value']    = 1;

        return "
SELECT DISTINCT civicrm_contact.id as contact_id,
  civicrm_contact.sort_name as sort_name,
  civicrm_contact.display_name as display_name,
  civicrm_address.street_address as street_address,
  civicrm_address.city as city,
  civicrm_address.postal_code as postal_code,
  civicrm_address.geo_code_1 as latitude,
  civicrm_address.geo_code_2 as longitude,
  civicrm_state_province.abbreviation as state,
  civicrm_country.name as country,
  civicrm_email.email as email,
  civicrm_phone.phone as phone,
  civicrm_contact.contact_type as contact_type
";
    }

    /**
     * create the from clause
     *
     * @param array $tables tables that need to be included in this from clause
     *                      if null, return mimimal from clause (i.e. civicrm_contact)
     * @return string the from clause
     * @access public
     * @static
     */
    static function fromClause( &$tables ) {
        $from = ' FROM civicrm_contact ';
        if ( empty( $tables ) ) {
            return $from;
        }

        // add location table if address / phone / email is set
        if ( CRM_Utils_Array::value( 'civicrm_address', $tables ) ||
             CRM_Utils_Array::value( 'civicrm_phone'  , $tables ) ||
             CRM_Utils_Array::value( 'civicrm_email'  , $tables ) ) {
            $tables['civicrm_location'] = 1;
        }

        // add group_contact table if group table is present
        if ( CRM_Utils_Array::value( 'civicrm_group', $tables ) ) {
            $tables['civicrm_group_contact'] = 1;
        }

        foreach ( $tables as $name => $value ) {
            if ( ! $value ) {
                continue;
            }

            switch ( $name ) {
            case 'civicrm_individual':
                $from .= ' LEFT JOIN civicrm_individual ON (civicrm_contact.id = civicrm_individual.contact_id) ';
                continue;

            case 'civicrm_location':
                $from .= " LEFT JOIN civicrm_location ON (civicrm_location.entity_table = 'civicrm_contact' AND
                                                          civicrm_contact.id = civicrm_location.entity_id  AND
                                                          civicrm_location.is_primary = 1)";
                continue;

            case 'civicrm_address':
                $from .= ' LEFT JOIN civicrm_address ON civicrm_location.id = civicrm_address.location_id ';
                continue;

            case 'civicrm_phone':
                $from .= ' LEFT JOIN civicrm_phone ON (civicrm_location.id = civicrm_phone.location_id AND civicrm_phone.is_primary = 1) ';
                continue;

            case 'civicrm_email':
                $from .= ' LEFT JOIN civicrm_email ON (civicrm_location.id = civicrm_email.location_id AND civicrm_email.is_primary = 1) ';
                continue;

            case 'civicrm_state_province':
                $from .= ' LEFT JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id ';
                continue;

            case 'civicrm_country':
                $from .= ' LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id ';
                continue;

            case 'civicrm_group':
                $from .= ' LEFT JOIN civicrm_group ON civicrm_group.id =  civicrm_group_contact.group_id ';
                continue;

            case 'civicrm_group_contact':
                $from .= ' LEFT JOIN civicrm_group_contact ON civicrm_contact.id = civicrm_group_contact.contact_id ';
                continue;

            case 'civicrm_entity_tag':
                $from .= " LEFT JOIN civicrm_entity_tag ON ( civicrm_entity_tag.entity_table = 'civicrm_contact' AND
                                                             civicrm_contact.id = civicrm_entity_tag.entity_id ) ";
                continue;

            case 'civicrm_activity_history':
                $from .= " LEFT JOIN civicrm_activity_history ON ( civicrm_activity_history.entity_table = 'civicrm_contact' AND  
                                                               civicrm_contact.id = civicrm_activity_history.entity_id ) ";
                continue;

            case 'civicrm_custom_value':
                $from .= " LEFT JOIN civicrm_custom_value ON ( civicrm_custom_value.entity_table = 'civicrm_contact' AND
                                                           civicrm_contact.id = civicrm_custom_value.entity_id ) ";
                continue;
            }
        }

        return $from;
    }

    /**
     * create the where clause for a contact search
     *
     * @param array    $formValues array of reference of the form values submitted
     * @param boolean  $includeContactIds should we include contact ids?
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     *
     * @return string  the where clause without the permissions hook (important)
     * @access public
     * @static
     */
    static function whereClause( &$fv, $includeContactIds = false, &$tables)
    {
        $where = '';

        /*
         * sample formValues for query 
         *
         * Get me all contacts of type individual or organization who are members of group 1 "Newsletter Subscribers"
         * and are categorized as "Non Profit" (catid 1) or "Volunteer" (catid 5) 

        $fv = Array
            (
             [cb_contact_type] => Array
             (
              [Individual] => 1
              [Organization] => 1
              )
             
             [cb_group] => Array
             (
              [1] => 1
              )
             
             [cb_group_contact_status] => Array
             (
              [In] => 1
              [Out] => 0
              [Pending] => 0
              )
             
             [cb_tag] => Array
             (
              [1] => 1
              [5] => 1
              )
             
             [last_name] => 
             [first_name] => 
             [street_name] => 
             [city] => 
             [state_province] => 
             [country] => 
             [postal_code] => 
             [postal_code_low] => 
             [postal_code_high] => 
             )

        */


        // stores all the "AND" clauses
        $andArray = array();

        $config =& CRM_Core_Config::singleton( );
        $andArray['domain'] = 'civicrm_contact.domain_id = ' . $config->domainID( ) . ' ';

        // check for contact type restriction
        if ( CRM_Utils_Array::value( 'cb_contact_type', $fv ) ) {
            $andArray['contact_type'] = "(contact_type IN (";
            foreach ($fv['cb_contact_type']  as $k => $v) {
                $andArray['contact_type'] .= "'$k',"; 
            }            
            // replace the last comma with the parentheses.
            $andArray['contact_type'] = rtrim($andArray['contact_type'], ",");
            $andArray['contact_type'] .= "))";
        }
        
        // check for group restriction
        if ( CRM_Utils_Array::value( 'cb_group', $fv ) ) {
            $andArray['group'] = "(civicrm_group_contact.group_id IN (" . implode( ',', array_keys($fv['cb_group']) ) . '))';

            $statii = array();
            if (is_array($fv['cb_group_contact_status'])) {
                foreach ($fv['cb_group_contact_status'] as $key => $value) {
                    if ($value) {
                        $statii[] = "\"$key\"";
                    }
                }
                $andArray['groupStatus']    
                        = '(civicrm_group_contact.status IN (' 
                        . implode( ',', $statii) . '))';
            } else {
                $andArray['groupStatus'] = '(civicrm_group_contact.status = "In")';
            }
            $tables['civicrm_group_contact'] = 1;
        }
        
        // check for tag restriction
        if ( CRM_Utils_Array::value( 'cb_tag', $fv ) ) {
            $andArray['tag'] .= "(tag_id IN (" . implode( ',', array_keys($fv['cb_tag']) ) . '))';

            $tables['civicrm_entity_tag'] = 1;
        }
        
        // check for last name, as of now only working with sort name
        if ( CRM_Utils_Array::value( 'sort_name', $fv ) ) {
            $name = trim($fv['sort_name']);
            $sub  = array( );
            // if we have a comma in the string, search for the entire string
            if ( strpos( $name, ',' ) === false ) {
                $sub[] = " ( LOWER(civicrm_contact.sort_name) LIKE '%" . strtolower(addslashes($name)) . "%' )";
                $sub[] = " ( LOWER(civicrm_email.email)       LIKE '%" . strtolower(addslashes($name)) . "%' )";
                $tables['civicrm_location'] = 1;
                $tables['civicrm_email']    = 1;
            } else {
                // split the string into pieces
                $pieces =  explode( ' ', $name );
                foreach ( $pieces as $piece ) {
                    $sub[] = " ( LOWER(civicrm_contact.sort_name) LIKE '%" . strtolower(addslashes(trim($piece))) . "%' ) ";
                }
            }
            $andArray['sort_name'] = ' ( ' . implode( '  OR ', $sub ) . ' ) ';
        }

        // sortByCharacter
        if ( CRM_Utils_Array::value( 'sortByCharacter', $fv ) ) {
            $name = trim($fv['sortByCharacter']);

            $cond = " LOWER(civicrm_contact.sort_name) LIKE '" . strtolower(addslashes($name)) . "%'";
            if ( CRM_Utils_Array::value( 'sort_name', $andArray ) ) {
                $andArray['sort_name'] = '(' . $andArray['sort_name'] . "AND ( $cond ))";
            } else {
                $andArray['sort_name'] = "( $cond )";
            }
        }

        if ( $includeContactIds ) {
            $contactIds = array( );
            foreach ( $fv as $name => $value ) {
                if ( substr( $name, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
                    $contactIds[] = substr( $name, CRM_Core_Form::CB_PREFIX_LEN );
                }
            }
            if ( ! empty( $contactIds ) ) {
                $andArray['cid'] = " ( civicrm_contact.id in (" . implode( ',', $contactIds ) . " ) ) ";
            }
        }
        
        $fields = array( 'street_name'=> 1, 'city' => 1, 'state_province' => 2, 'country' => 3 );
        foreach ( $fields as $field => $value ) {
            if ( CRM_Utils_Array::value( $field, $fv ) ) {
                $tables['civicrm_location'] = 1;
                $tables['civicrm_address']  = 1;

                if ( $value == 1 ) {
                    $andArray[$field] = " ( LOWER(civicrm_address." . $field .  ") LIKE '%" . strtolower( addslashes( $fv[$field] ) ) . "%' )";
                } else { 
                    $andArray[$field] = ' ( civicrm_address.' . $field .  '_id = ' . $fv[$field] . ') ';
                    if ( $value == 2 ) {
                        $tables['civicrm_state_province'] = 1;
                    } else {
                        $tables['civicrm_country'] = 1;
                    }
                }
            }
        }

        // postal code processing
        if ( CRM_Utils_Array::value( 'postal_code'     , $fv ) ||
             CRM_Utils_Array::value( 'postal_code_low' , $fv ) ||
             CRM_Utils_Array::value( 'postal_code_high', $fv ) ) {
            $tables['civicrm_location'] = 1;
            $tables['civicrm_address']   = 1;

            // we need to do postal code processing
            $pcORArray   = array();
            $pcANDArray  = array();

            if ($fv['postal_code']) {
                $pcORArray[] = ' ( civicrm_address.postal_code = ' . $fv['postal_code'] . ' ) ';
            }
            if ($fv['postal_code_low']) {
                $pcANDArray[] = ' ( civicrm_address.postal_code >= ' . $fv['postal_code_low'] . ' ) ';
            }
            if ($fv['postal_code_high']) {
                $pcANDArray[] = ' ( civicrm_address.postal_code <= ' . $fv['postal_code_high'] . ' ) ';
            }            

            if ( ! empty( $pcANDArray ) ) {
                $pcORArray[] = ' ( ' . implode( ' AND ', $pcANDArray ) . ' ) ';
            }

            $andArray['postal_code'] = ' ( ' . implode( ' OR ', $pcORArray ) . ' ) ';
        }

        if ( CRM_Utils_Array::value( 'cb_location_type', $fv ) ) {
            
            // processing for location type - check if any locations checked
            $andArray['location_type'] = "(civicrm_location.location_type_id IN (" . implode( ',', array_keys($fv['cb_location_type']) ) . '))';
        }
        
        // processing for primary location
        if ( CRM_Utils_Array::value( 'cb_primary_location', $fv ) ) {
            $andArray['cb_primary_location'] = ' ( civicrm_location.is_primary = 1 ) ';
            
            $tables['civicrm_location'] = 1;
        }


        // processing activity type, from and to date
        // check for activity type
        if ( CRM_Utils_Array::value( 'activity_type', $fv ) ) {
            $name = trim($fv['activity_type']);
            // split the string into pieces
            $pieces =  explode( ' ', $name );
            $first = true;
            $cond  = ' ( ';
            foreach ( $pieces as $piece ) {
                if ( ! $first ) {
                    $cond .= ' OR';
                } else {
                    $first = false;
                }
                $cond .= " LOWER(civicrm_activity_history.activity_type) LIKE '%" . strtolower(addslashes(trim($piece))) . "%'";
            }
            $cond .= ' ) ';
            $andArray['activity_type'] = "( $cond )";

            $tables['civicrm_activity_history'] = 1;
        }

        // from date

        if ( isset($fv['activity_from_date']) &&
             ( $activityFromDate = CRM_Utils_Date::format(array_reverse(CRM_Utils_Array::value('activity_from_date', $fv))))) {
            $andArray['activity_from_date'] = " ( civicrm_activity_history.activity_date >= '$activityFromDate' ) ";
            $tables['civicrm_activity_history'] = 1;
        }
        if (isset($fv['activity_to_date']) &&
            ($activityToDate = (CRM_Utils_Date::format(array_reverse(CRM_Utils_Array::value('activity_to_date', $fv)))))) {            
            $andArray['activity_to_date'] = " ( civicrm_activity_history.activity_date <= '$activityToDate' ) ";
            $tables['civicrm_activity_history'] = 1;
        }
        
        //Start Custom data Processing 
        
        $cdANDArray = array();
        if ( ! empty( $fv ) ) {
            foreach ($fv as $k => $v) {
                if ( substr( $k, 0, 10 ) != 'customData' ) {
                    continue;
                }
                $tables['civicrm_custom_value'] = 1;
                
                list($str, $groupId, $fieldId, $elementName) = explode('_', $k, 4);
                
                if ( $str == 'customData' && $v != '') {
                    
                    $strSelect = $strFrom = $strWhere = $orderBy = ''; 
                    
                    $tableData = array();
                    
                    // using tableData to build the queryString 
                    $tableData = array(
                                       'civicrm_custom_value' => array('id', 'int_data', 'float_data', 'char_data', 'date_data', 'memo_data'),
                                       'civicrm_custom_field' => array('id', 'name', 'label', 'data_type', 'html_type'),
                                       );
                    
                    // create select
                    $strSelect = "SELECT"; 
                    foreach ($tableData as $tableName => $tableColumn) {
                        foreach ($tableColumn as $columnName) {
                            $alias = $tableName . '_' . $columnName;
                            $strSelect .= " $tableName.$columnName as $alias,";
                        }
                    }
                    $strSelect = rtrim($strSelect, ',');
                    
                    // from, where, order by
                    $strFrom = " FROM civicrm_custom_value, civicrm_custom_field ";
                    $strWhere = " WHERE civicrm_custom_value.custom_field_id = $fieldId
                              AND civicrm_custom_value.custom_field_id = civicrm_custom_field.id
                              AND civicrm_custom_field.is_active = 1";
                    $orderBy = " ORDER BY civicrm_custom_field.weight";
                    
                    // final query string
                    $queryString = $strSelect . $strFrom . $strWhere . $orderBy;
                    
                    // dummy dao needed
                    $crmDAO =& new CRM_Core_DAO();
                    $crmDAO->query($queryString);
                    
                    // process records
                    while($crmDAO->fetch()) {
                        $dataType = $crmDAO->civicrm_custom_field_data_type;
                        $htmlType = $crmDAO->civicrm_custom_field_html_type;
                        switch ($dataType) {
                        case 'String':
                            if ( $htmlType == 'CheckBox' ) {
                                
                                $strChkBox = implode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, array_keys($v));
                                
                                $cdANDArray[] = " ( civicrm_custom_value.char_data LIKE '". $strChkBox ."' )";                            
                            } else {
                                $cdANDArray[] = " ( civicrm_custom_value.char_data LIKE '%". $v ."%' )";
                            }
                            break;
                        case 'Int':
                        case 'Boolean':
                            $cdANDArray[] = " ( civicrm_custom_value.int_data = '". $v . "' )";;
                            break;
                        case 'Float':
                        case 'Money':
                            $cdANDArray[] = " ( civicrm_custom_value.float_data = '". $v . "' ) ";
                            break;
                        case 'Memo':
                            $cdANDArray[] = " ( civicrm_custom_value.memo_data LIKE '%". $v . "%' )";
                            break;
                        case 'Date':
                            if ( !empty($v['d']) ) {
                                $date = CRM_Utils_Date::format( $v );
                                if ( ! $date ) {
                                    $date = '';
                                }
                                $cdANDArray[] = " ( civicrm_custom_value.date_data = '". $v . "' )";
                            }
                            
                            if ( !empty($v['M']) ) {
                                $cdANDArray[] = " ( MONTH(civicrm_custom_value.date_data) = '". $v['M'] . "' AND YEAR(civicrm_custom_value.date_data) = '". $v['Y'] . "' )";
                            } else {
                                $cdANDArray[] = " ( YEAR(civicrm_custom_value.date_data) = '". $v['Y'] . "' )";
                            }
                            /*$date = CRM_Utils_Date::format( $v );
                            if ( ! $date ) {
                                $date = '';
                            }
                            $cdANDArray[] = " ( civicrm_custom_value.date_data = '". $v . "' )";*/
                            break;
                        case 'StateProvince':
                            $cdANDArray[] = " ( civicrm_custom_value.int_data = '". $v . "' )";
                            break;
                        case 'Country':
                            $cdANDArray[] = " ( civicrm_custom_value.int_data = '". $v . "' )";
                            break;
                        }
                    }
                }
            }
        }
        
        if( !empty( $cdANDArray )) {
            $andArray['custom_data'] = ' ( ' . implode( ' OR ', $cdANDArray ) . ' ) ';
        }
        
        // final AND ing of the entire query.
        if ( !empty( $andArray ) ) {
            $where = ' ( ' . implode( ' AND ', $andArray ) . ' ) ';
        }

        return $where;
    }

    /**
     * takes an associative array and creates a contact object
     *
     * the function extract all the params it needs to initialize the create a
     * contact object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Contact_BAO_Contact object
     * @access public
     * @static
     */
    static function add(&$params, &$ids)
    {
        $contact =& new CRM_Contact_BAO_Contact();
        
        $contact->copyValues($params);
        
        $contact->domain_id = CRM_Utils_Array::value( 'domain' , $ids, CRM_Core_Config::domainID( ) );
        $contact->id        = CRM_Utils_Array::value( 'contact', $ids );
        
        if ($contact->contact_type == 'Individual') {
            $sortName = "";
            $firstName  = CRM_Utils_Array::value('first_name', $params, '');
            $middleName = CRM_Utils_Array::value('middle_name', $params, '');
            $lastName   = CRM_Utils_Array::value('last_name' , $params, '');
            $prefix     = CRM_Utils_Array::value('prefix'    , $params, '');
            $suffix     = CRM_Utils_Array::value('suffix'    , $params, '');
            
            // a comma should only be present if both first_name and last name are present.
            if ($firstName && $lastName) {
                $sortName = "$lastName, $firstName";
            } else {
                if (empty($firstName) || empty($lastName)) {
                    $sortName = $lastName . $firstName;
                } else {
                    $individual =& new CRM_Contact_BAO_Individual();
                    $individual->contact_id = $contact->id;
                    $individual->find();
                    while($individual->fetch()) {
                        $individualLastName = $individual->last_name;
                        $individualFirstName = $individual->first_name;
                        $individualPrefix = $individual->prefix;
                        $individualSuffix = $individual->suffix;
                        $individualMiddleName = $individual->middle_name;
                    }
                    
                    if (empty($lastName) && !empty($individualLastName)) {
                        $lastName = $individualLastName;
                    } 
                    
                    if (empty($firstName) && !empty($individualFirstName)) {
                        $firstName = $individualFirstName;
                    }
                                                            
                    if (empty($prefix) && !empty($individualPrefix)) {
                        $prefix = $individualPrefix;
                    }
                    
                    if (empty($middleName) && !empty($individualMiddleName)) {
                        $middleName = $individualMiddleName;
                    }
                    
                    if (empty($suffix) && !empty($individualSuffix)) {
                        $suffix = $individualSuffix;
                    }
                    
                    $sortName = "$lastName, $firstName";
                }
            }
            $contact->sort_name    = trim($sortName);
            $contact->display_name =
                trim( $prefix . ' ' . $firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix );
        } else if ($contact->contact_type == 'Household') {
            $contact->display_name = $contact->sort_name = CRM_Utils_Array::value('household_name', $params, '');
        } else {
            $contact->display_name = $contact->sort_name = CRM_Utils_Array::value('organization_name', $params, '') ;
        }

        // preferred communication block
        $privacy = CRM_Utils_Array::value('privacy', $params);
        if ($privacy && is_array($privacy)) {
            foreach (self::$_commPrefs as $name) {
                $contact->$name = CRM_Utils_Array::value($name, $privacy, false);
            }
        }
        
        return $contact->save();
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params input parameters to find object
     * @param array $values output values of the object
     * @param array $ids    the array that holds all the db ids
     *
     * @return CRM_Contact_BAO_Contact|null the found object or null
     * @access public
     * @static
     */
    static function getValues( &$params, &$values, &$ids ) {

        $contact =& new CRM_Contact_BAO_Contact( );

        $contact->copyValues( $params );

        if ( $contact->find(true) ) {
            $ids['contact'] = $contact->id;
            $ids['domain' ] = $contact->domain_id;

            CRM_Core_DAO::storeValues( $contact, $values );

            $privacy = array( );
            foreach ( self::$_commPrefs as $name ) {
                if ( isset( $contact->$name ) ) {
                    $privacy[$name] = $contact->$name;
                }
            }
            if ( !empty($privacy) ) {
                $values['privacy'] = $privacy;
            }

            CRM_Contact_DAO_Contact::addDisplayEnums($values);

            return $contact;
        }
        return null;
    }

    /**
     * takes an associative array and creates a contact object and all the associated
     * derived objects (i.e. individual, location, email, phone etc)
     *
     * This function is invoked from within the web form layer and also from the api layer
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     * @param int   $maxLocationBlocks the maximum number of location blocks to process
     *
     * @return object CRM_Contact_BAO_Contact object 
     * @access public
     * @static
     */
    static function create(&$params, &$ids, $maxLocationBlocks)
    {
        // we need a few ids resolved, so lets resolve the defaults
        self::resolveDefaults( $params );

        CRM_Core_DAO::transaction('BEGIN');
        
        $contact = self::add($params, $ids);
        
        $params['contact_id'] = $contact->id;

        // invoke the add operator on the contact_type class
        require_once(str_replace('_', DIRECTORY_SEPARATOR, "CRM_Contact_BAO_" . $params['contact_type']) . ".php");
        eval('$contact->contact_type_object =& CRM_Contact_BAO_' . $params['contact_type'] . '::add($params, $ids);');

        $location = array();
        for ($locationId = 1; $locationId <= $maxLocationBlocks; $locationId++) { // start of for loop for location
            $location[$locationId] = CRM_Core_BAO_Location::add($params, $ids, $locationId);
        }
        $contact->location = $location;

        // add notes
        if (is_array($params['note'])) {
            foreach ($params['note'] as $note) {
                $noteParams = array(
                    'entity_id'     => $contact->id,
                    'entity_table'  => 'civicrm_contact',
                    'note'          => $note['note']
                    );
                CRM_Core_BAO_Note::add($noteParams);
            }
        }
        // update the UF email if that has changed
        CRM_Core_BAO_UFMatch::updateUFEmail( $contact->id );


        // add custom field values
        if (is_array($params['custom'])) {  
            foreach ($params['custom'] as $customValue) {
                $cvParams = array(
                    'entity_table' => 'civicrm_contact',
                    'entity_id' => $contact->id,
                    'value' => $customValue['value'],
                    'type' => $customValue['type'],
                    'custom_field_id' => $customValue['custom_field_id'],
                );
                
                CRM_Core_BAO_CustomValue::create($cvParams);
            }
        }
        
        $subscriptionParams = array('contact_id' => $contact->id,
                                    'status' => 'In',
                                    'method' => 'Admin');
        CRM_Contact_BAO_SubscriptionHistory::create($subscriptionParams);

        CRM_Core_DAO::transaction('COMMIT');
        
        $contact->contact_type_display = CRM_Contact_DAO_Contact::tsEnum('contact_type', $contact->contact_type);

        return $contact;
    }

    /**
     * Get the display name and image of a contact
     *
     * @param int $id the contactId
     *
     * @return array the displayName and contactImage for this contact
     * @access public
     * @static
     */
    static function getDisplayAndImage( $id ) {
        $sql = "
SELECT civicrm_contact.display_name as display_name, civicrm_contact.contact_type as contact_type
FROM   civicrm_contact
WHERE  civicrm_contact.id = $id
";
        $dao =& new CRM_Core_DAO( );
        $dao->query( $sql );
        if ( $dao->fetch( ) ) {
            $config =& CRM_Core_Config::singleton( );
            $image  =  '<img src="' . $config->resourceBase . 'i/contact_';
            switch ( $dao->contact_type ) {
            case 'Individual' :
                $image .= 'ind.gif" alt="' . ts('Individual') . '">';
                break;
            case 'Household' :
                $image .= 'house.png" alt="' . ts('Household') . '" height="16" width="16">';
                break;
            case 'Organization' :
                $image .= 'org.gif" alt="' . ts('Organization') . '" height="16" width="18">';
                break;
            }
            return array( $dao->display_name, $image );
        }
        return null;
    }

    /**
     *
     * Get the values for pseudoconstants for name->value and reverse.
     *
     * @param array   $defaults (reference) the default values, some of which need to be resolved.
     * @param boolean $reverse  true if we want to resolve the values in the reverse direction (value -> name)
     *
     * @return none
     * @access public
     * @static
     */
    static function resolveDefaults( &$defaults, $reverse = false ) {
        // hack for birth_date
        if ( CRM_Utils_Array::value( 'birth_date', $defaults ) ) {
            $defaults['birth_date'] = CRM_Utils_Date::format( $defaults['birth_date'], '-' );
        }

        if ( array_key_exists( 'location', $defaults ) ) {
            $locations =& $defaults['location'];

            foreach ($locations as $index => $location) {                
                $location =& $locations[$index];
                self::lookupValue( $location, 'location_type', CRM_Core_PseudoConstant::locationType(), $reverse );

                if (array_key_exists( 'address', $location ) ) {
                    if ( ! self::lookupValue( $location['address'], 'state_province',
                                              CRM_Core_PseudoConstant::stateProvince(), $reverse ) &&
                         $reverse ) {
                        self::lookupValue( $location['address'], 'state_province', 
                                           CRM_Core_PseudoConstant::stateProvinceAbbreviation(), $reverse );
                    }
                    
                    if ( ! self::lookupValue( $location['address'], 'country',
                                              CRM_Core_PseudoConstant::country(), $reverse ) &&
                         $reverse ) {
                        self::lookupValue( $location['address'], 'country', 
                                           CRM_Core_PseudoConstant::countryIsoCode(), $reverse );
                    }
                    self::lookupValue( $location['address'], 'county'        , CRM_Core_SelectValues::county()         , $reverse );
                }

                if (array_key_exists('im', $location)) {
                    $ims =& $location['im'];
                    foreach ($ims as $innerIndex => $im) {
                        $im =& $ims[$innerIndex];
                        self::lookupValue( $im, 'provider', CRM_Core_PseudoConstant::IMProvider(), $reverse );
                        unset($im);
                    }
                }
                unset($location);
            }
        }
    }

    /**
     * This function is used to convert associative array names to values
     * and vice-versa.
     *
     * This function is used by both the web form layer and the api. Note that
     * the api needs the name => value conversion, also the view layer typically
     * requires value => name conversion
     */
    static function lookupValue( &$defaults, $property, &$lookup, $reverse ) {
        $id = $property . '_id';

        $src = $reverse ? $property : $id;
        $dst = $reverse ? $id       : $property;

        if ( ! array_key_exists( $src, $defaults ) ) {
            return false;
        }

        $look = $reverse ? array_flip( $lookup ) : $lookup;
        
        if(is_array($look)) {
            if ( ! array_key_exists( $defaults[$src], $look ) ) {
                return false;
            }
        }
        $defaults[$dst] = $look[$defaults[$src]];
        return true;
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the name / value pairs
     *                        in a hierarchical manner
     * @param array $ids      (reference) the array that holds all the db ids
     *
     * @return object CRM_Contact_BAO_Contact object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults, &$ids ) {
        $contact = CRM_Contact_BAO_Contact::getValues( $params, $defaults, $ids );
        unset($params['id']);
        require_once(str_replace('_', DIRECTORY_SEPARATOR, "CRM_Contact_BAO_" . $contact->contact_type) . ".php");
        eval( '$contact->contact_type_object =& CRM_Contact_BAO_' . $contact->contact_type . '::getValues( $params, $defaults, $ids );' );
        $locParams = $params + array('entity_id' => $params['contact_id'],
                'entity_table' => self::getTableName());
        $contact->location     =& CRM_Core_BAO_Location::getValues( $locParams, $defaults, $ids, 3 );
        $contact->notes        =& CRM_Core_BAO_Note::getValues( $params, $defaults, $ids );
        $contact->relationship =& CRM_Contact_BAO_Relationship::getValues( $params, $defaults, $ids );
        $contact->groupContact =& CRM_Contact_BAO_GroupContact::getValues( $params, $defaults, $ids );

        $activityParam         =  array('entity_id' => $params['contact_id']);
        $contact->activity     =& CRM_Core_BAO_History::getValues($activityParam, $defaults, 'Activity');

        $activityParam            =  array('contact_id' => $params['contact_id']);
        $defaults['openActivity'] = array(
                                          'data'       => self::getOpenActivities( $activityParam, 0, 3 ),
                                          'totalCount' => self::getNumOpenActivity( $params['contact_id'] ),
                                          );
        return $contact;
    }

    /**
     * Given a parameter array from CRM_Contact_BAO_Contact::retrieve() and a
     * key to search for, search recursively for that key's value.
     *
     * @param array $values     The parameter array
     * @param string $key       The key to search for
     * @return mixed            The value of the key, or null.
     * @access public
     * @static
     */
    static function retrieveValue(&$params, $key) {
        if (! is_array($params)) {
            return null;
        } else if ($value = CRM_Core_Utils_Array::value($key, $params)) {
            return $value;
        } else {
            foreach ($params as $subParam) {
                if ($value = self::retrieveValue($subParam, $key)) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * function to get the display name of a contact
     *
     * @param  int    $id id of the contact
     *
     * @return null|string     display name of the contact if found
     * @static
     * @access public
     */
    static function displayName( $id ) {
        return CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $id, 'display_name' );
    }

    /**
     * function to get the email and display name of a contact
     *
     * @param  int    $id id of the contact
     *
     * @return null|string     display name of the contact if found
     * @static
     * @access public
     */
    static function getEmailDetails( $id ) {
        $sql = " SELECT    civicrm_contact.display_name, civicrm_email.email
                 FROM      civicrm_contact
                 LEFT JOIN civicrm_location ON (civicrm_location.entity_table = 'civicrm_contact' AND
                                                civicrm_contact.id = civicrm_location.entity_id AND
                                                civicrm_location.is_primary = 1)
                 LEFT JOIN civicrm_email ON (civicrm_location.id = civicrm_email.location_id AND civicrm_email.is_primary = 1)
                 WHERE     civicrm_contact.id = $id";
        $dao =& new CRM_Core_DAO( );
        $dao->query( $sql );
        $result = $dao->getDatabaseResult();
        if ( $result ) {
            $row    = $result->fetchRow();
            if ( $row ) {
                return array( $row[0], $row[1] );
            }
        }
        return array( null, null );
    }

    /**
     * function to get the information to map a contact
     *
     * @param  array    $ids   the list of ids for which we want map info
     *
     * @return null|string     display name of the contact if found
     * @static
     * @access public
     */
    static function &getMapInfo( $ids ) {
        $idString = ' ( ' . implode( ',', $ids ) . ' ) ';
        $sql = "
SELECT
  civicrm_contact.id as contact_id,
  civicrm_contact.display_name as display_name,
  civicrm_address.street_address as street_address,
  civicrm_address.city as city,
  civicrm_address.postal_code as postal_code,
  civicrm_address.geo_code_1 as latitude,
  civicrm_address.geo_code_2 as longitude,
  civicrm_state_province.abbreviation as state,
  civicrm_country.name as country
FROM      civicrm_contact
LEFT JOIN civicrm_location ON (civicrm_location.entity_table = 'civicrm_contact' AND
                               civicrm_contact.id = civicrm_location.entity_id AND
                               civicrm_location.is_primary = 1)
LEFT JOIN civicrm_address ON civicrm_location.id = civicrm_address.location_id
LEFT JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id
LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
WHERE     civicrm_contact.id IN $idString AND civicrm_country.id = 1228 AND civicrm_address.geo_code_1 is not null";

        $dao =& new CRM_Core_DAO( );
        $dao->query( $sql );

        $locations = array( );
        while ( $dao->fetch( ) ) {
            $location = array( );
            $location['displayName'] = $dao->display_name;
            $location['lat'        ] = $dao->latitude;
            $location['lng'        ] = $dao->longitude;
            $address = '';
            CRM_Utils_String::append( $address, ', ',
                                      array( $dao->street_address, $dao->city, $dao->state, $dao->postal_code, $dao->country ) );
            $location['address'    ] = $address;
            $location['url'        ] = CRM_Utils_System::url( 'civicrm/contact/view', 'reset=1&cid=' . $dao->contact_id );
            $locations[] = $location;
        }
        return $locations;
    }

    /**
     * Delete a contact and all its associated records
     * 
     * @param  int  $id id of the contact to delete
     *
     * @return void
     * @access public
     * @static
     */
    function deleteContact( $id ) {
        CRM_Core_DAO::transaction( 'BEGIN' );

        // do a top down deletion
        CRM_Contact_BAO_GroupContact::deleteContact( $id );
        CRM_Contact_BAO_SubscriptionHistory::deleteContact($id);
        
        CRM_Contact_BAO_Relationship::deleteContact( $id );

        // cannot use this one since we need to also delete note creator contact_id
        //CRM_Core_DAO::deleteEntityContact( 'CRM_Core_DAO_Note', $id );
        CRM_Core_BAO_Note::deleteContact($id);

        CRM_Core_DAO::deleteEntityContact( 'CRM_Core_DAO_CustomValue', $id );

        CRM_Core_DAO::deleteEntityContact( 'CRM_Core_DAO_ActivityHistory', $id );

        CRM_Core_BAO_UFMatch::deleteContact( $id );
        
        // need to remove them from email, meeting and phonecall
        CRM_Core_BAO_EmailHistory::deleteContact($id);
        CRM_Core_BAO_Meeting::deleteContact($id);
        CRM_Core_BAO_Phonecall::deleteContact($id);

        // location shld be deleted after phonecall, since fields in phonecall are
        // fkeyed into location/phone.
        CRM_Core_BAO_Location::deleteContact( $id );

        // fix household and org primary contact ids
        static $misc = array( 'Household', 'Organization' );
        foreach ( $misc as $name ) {
            require_once(str_replace('_', DIRECTORY_SEPARATOR, "CRM_Contact_DAO_" . $name) . ".php");
            eval( '$object =& new CRM_Contact_DAO_' . $name . '( );' );
            $object->primary_contact_id = $id;
            $object->find( );
            while ( $object->fetch( ) ) {
                // we need to set this to null explicitly
                $object->primary_contact_id = 'null';
                $object->save( );
            }
        }

        // get the contact type
        $contact =& new CRM_Contact_DAO_Contact();
        $contact->id = $id;
        if ($contact->find(true)) {
            require_once(str_replace('_', DIRECTORY_SEPARATOR, "CRM_Contact_BAO_" . $contact->contact_type) . ".php");
            eval( '$object =& new CRM_Contact_BAO_' . $contact->contact_type . '( );' );
            $object->contact_id = $contact->id;
            $object->delete( );
            $contact->delete( );
        }

        //delete the contact id from recently view
        CRM_Utils_Recent::del($id);

        CRM_Core_DAO::transaction( 'COMMIT' );
    }


    /**
     * Get contact type for a contact.
     *
     * @param int $id - id of the contact whose contact type is needed
     *
     * @return string contact_type if $id found else null ""
     *
     * @access public
     *
     * @static
     *
     */
    public static function getContactType($id)
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $id, 'contact_type' );
    }


    /**
     * combine all the importable fields from the lower levels object
     *
     * The ordering is important, since currently we do not have a weight
     * scheme. Adding weight is super important and should be done in the
     * next week or so, before this can be called complete.
     *
     * @return array array of importable Fields
     * @access public
     */
    function &importableFields( ) {
        if ( ! self::$_importableFields ) {
            self::$_importableFields = array();
            
            self::$_importableFields = array_merge(self::$_importableFields,
                                                   array('' => array( 'title' => ts('-do not import-'))) );
            
            self::$_importableFields = array_merge(self::$_importableFields,
                                                   CRM_Contact_DAO_Individual::import( ) );

            $locationFields = array_merge(  CRM_Core_DAO_Address::import( ),
                                            CRM_Core_DAO_Phone::import( ),
                                            CRM_Core_DAO_Email::import( ),
                                            CRM_Core_DAO_IM::import( true ));
            foreach ($locationFields as $key => $field) {
                $locationFields[$key]['hasLocationType'] = true;
            }

            self::$_importableFields = array_merge(self::$_importableFields, $locationFields);

            self::$_importableFields = array_merge(self::$_importableFields,
                                                   CRM_Contact_DAO_Contact::import( ) );
            self::$_importableFields = array_merge(self::$_importableFields,
                                                   CRM_Core_DAO_Note::import());
            self::$_importableFields = array_merge(self::$_importableFields,
                                                   CRM_Core_BAO_CustomField::getFieldsForImport() );
        }
        return self::$_importableFields;
    }

    /**
     * Get total number of open activities
     *
     * @param  int $id id of the contact
     * @return int $numRow - total number of open activities    
     *
     * @static
     * @access public
     */
    static function getNumOpenActivity($id)
    {

        // this is not sufficient way to do.

        $query1 = "SELECT count(*) FROM civicrm_meeting WHERE (civicrm_meeting.target_entity_table = 'civicrm_contact' AND target_entity_id = $id OR source_contact_id = $id) AND status != 'Completed'";
        $dao =& new CRM_Core_DAO();
        $dao->query($query1);
        $result = $dao->getDatabaseResult();
        $row    = $result->fetchRow();
        
        $rowMeeting = $row[0];
        
        $query2 = "SELECT count(*) FROM civicrm_phonecall WHERE (civicrm_phonecall.target_entity_table = 'civicrm_contact' AND target_entity_id = $id OR source_contact_id = $id) AND status != 'Completed'";
        $dao->query($query2);
        $result = $dao->getDatabaseResult();
        $row    = $result->fetchRow();
        $rowPhonecall = $row[0];

        return  $rowMeeting + $rowPhonecall;
    }

    /**
     * function to get the list of open Actvities
     *
     * @param array reference $params  array of parameters 
     * @param int     $offset          which row to start from ?
     * @param int     $rowCount        how many rows to fetch
     * @param object|array  $sort      object or array describing sort order for sql query.
     * @param type    $type            type of history we're interested in
     *
     * @return array (reference)      $values the relevant data object values of open activitie
     *
     * @access public
     * @static
     */
    static function &getOpenActivities(&$params, $offset=null, $rowCount=null, $sort=null, $type='Activity')
    {
        
        $dao =& new CRM_Core_DAO();
        $contactId = $params['contact_id'];
        
        $query = "
( SELECT
    civicrm_phonecall.id as id,
    civicrm_phonecall.subject as subject,
    civicrm_phonecall.scheduled_date_time as date,
    civicrm_phonecall.status as status,
    source.display_name as sourceName,
    target.display_name as targetName,
    1 as activity_type
  FROM civicrm_phonecall, civicrm_contact source, civicrm_contact target
  WHERE
    civicrm_phonecall.source_contact_id = source.id AND
    civicrm_phonecall.target_entity_table = 'civicrm_contact' AND
    civicrm_phonecall.target_entity_id = target.id AND
    ( civicrm_phonecall.source_contact_id = $contactId OR civicrm_phonecall.target_entity_id = $contactId ) AND
    civicrm_phonecall.status != 'Completed'
) UNION
( SELECT   
    civicrm_meeting.id as id,
    civicrm_meeting.subject as subject,
    civicrm_meeting.scheduled_date_time as date,
    civicrm_meeting.status as status,
    source.display_name as sourceName,
    target.display_name as targetName,
    0 as activity_type
  FROM civicrm_meeting, civicrm_contact source, civicrm_contact target
  WHERE
    civicrm_meeting.source_contact_id = source.id AND
    civicrm_meeting.target_entity_table = 'civicrm_contact' AND
    civicrm_meeting.target_entity_id = target.id AND
    ( civicrm_meeting.source_contact_id = $contactId OR civicrm_meeting.target_entity_id = $contactId ) AND
    civicrm_meeting.status != 'Completed'
)";
        if ($sort) {
            $order = " ORDER BY " . $sort->orderBy(); 
        } else {
            $order = " ORDER BY date desc ";
        }
        
        if ( $rowCount > 0 ) {
            $limit = " LIMIT $offset, $rowCount ";
        }
        

        $queryString = $query . $order . $limit;

        $dao->query( $queryString );
        $values =array();
        $rowCnt = 0;
        while($dao->fetch()) {
            if ($dao->activity_type == 1) {
                $values[$rowCnt]['activity_type'] = 'Phone Call';        
            } else {
                $values[$rowCnt]['activity_type'] = 'Meeting';        
            }
            $values[$rowCnt]['id']      = $dao->id;
            $values[$rowCnt]['subject'] = $dao->subject;
            $values[$rowCnt]['date']    = $dao->date;
            $values[$rowCnt]['status']  = $dao->status;
            $values[$rowCnt]['sourceName'] = $dao->sourceName;
            $values[$rowCnt]['targetName'] = $dao->targetName;
            $rowCnt++;
        }
        foreach ($values as $key => $array) {
            CRM_Core_DAO_Meeting::addDisplayEnums($values[$key]);
            CRM_Core_DAO_Phonecall::addDisplayEnums($values[$key]);
        }
        return $values;

    }

    /**
     * Get unique contact id for input parameters.
     * Currently the parameters allowed are
     *
     * 1 - email
     * 2 - phone number
     * 3 - city
     *
     * @param array $param - array of input parameters
     *
     * @return $contactId|CRM_Error if unique id available
     *
     * @access public
     *
     */
    function _crm_get_contact_id($params)
    {
        if (!isset($params['email']) && !isset($params['phone']) && !isset($params['city'])) {
            //CRM_Core_Error::debug_log_message('$params must contain either email, phone or city to obtain contact id');
            //CRM_Core_Error::ll_function();
            return _crm_error( '$params must contain either email, phone or city to obtain contact id' );
        }

        
        $queryString = $select = $from = $where = '';
        
        $select = 'SELECT civicrm_contact.id';
        $from   = ' FROM civicrm_contact, civicrm_location';
        $andArray = array();
        
        $andArray[] = "civicrm_location.entity_table = 'civicrm_contact'";
        $andArray[] = "civicrm_contact.id = civicrm_location.entity_id";
        

        if (isset($params['email'])) {// is email present ?
            $from .= ', civicrm_email';
            $andArray[] = "civicrm_location.id = civicrm_email.location_id";
            $andArray[] = "civicrm_email.email = '" . $params['email'] . "'";
        }

        if (isset($params['phone'])) { // is phone present ?
            $from .= ', civicrm_phone';
            $andArray[] = 'civicrm_location.id = civicrm_phone.location_id';
            $andArray[] = "civicrm_phone.phone = '" . $params['phone'] . "'";
        }
        
        if (isset($params['city'])) { // is city present ?
            $from .= ', civicrm_address';
            $andArray[] = 'civicrm_location.id = civicrm_address.location_id';
            $andArray[] = "civicrm_address.city = '" . $params['city'] . "'";
        }

        $where = " WHERE " . implode(" AND ", $andArray);
        
        $queryString = $select . $from . $where;
        //CRM_Core_Error::debug_var('queryString', $queryString);
        
        $dao = new CRM_Core_DAO();
        
        $dao->query($queryString);
        $count = 0;
        while($dao->fetch()) {
            $count++;
            if ($count > 1) {

                return _crm_error( 'more than one contact id matches $params' );
            }
            
        }
        //$result = $dao->getDatabaseResult();
        //$rows = $result->fetchRow();
    
        if ($count == 0) {
            //CRM_Core_Error::debug_log_message('more than one contact id matches $params  email, phone or city to obtain contact id');
            //CRM_Core_Error::ll_function();
            return _crm_error( 'No contact found for given $params ' );
        }
        
        //CRM_Core_Error::debug_var('contactId', $rows[0]);
        //CRM_Core_Error::ll_function();
        return $dao->id;
    }
    
    
}

?>
