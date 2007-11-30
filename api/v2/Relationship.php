<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 * new version of civicrm apis. See blog post at
 * http://civicrm.org/node/131
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id: Contribute.php 10015 2007-06-17 22:00:12Z lobo $
 *
 */

require_once 'api/v2/utils.php';
require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Contact/BAO/RelationshipType.php';


/**
 * Add or update a relationship
 *
 * @param  array   $params   (reference ) input parameters
 *
 * @return array (reference) id of created or updated record
 * @static void
 * @access public
 */
function &civicrm_relationship_add( &$params ) {
    _civicrm_initialize( );

    if ( empty( $params ) ) {
        return civicrm_create_error('No input parameter present' );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameter is not an array' ) );
    }

    if( ! isset( $params['contact_id_a'] ) &&
        ! isset( $params['contact_id_b'] ) &&
        ! isset( $params['relationship_type_id'] )) {
        return civicrm_create_error( ts('Missing required parameters'));
    }

    $ids = array( );
    if( CRM_Utils_Array::value( 'id', $params ) ){
        $ids['relationship'] = $params['id'];
    }
    
    $relationshipBAO = CRM_Contact_BAO_Relationship::create( $params, $ids );
 
    if ( is_a( $relationshipBAO, 'CRM_Core_Error' ) ) {
        return civicrm_create_error( "Relationship can not be created" );
    } 
    $relation = array( );
    
    _civicrm_object_to_array( $relationshipBAO, $relation );
    
    return $relation;
    
}

/**
 * Function to create relationship type
 *
 * @param  array $params   Associative array of property name/value pairs to insert in new relationship type.
 *
 * @return Newly created Relationship_type object
 *
 * @access public
 *
 */
function civicrm_relationship_type_add( $params ) {
   
    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Parameter is not an array' ) );
    }

    if(! isset($params['name_a_b']) &&
       ! isset($params['name_b_a']) || $params['name_a_b'] == null) {
        return civicrm_create_error('Missing required parameters');
    }

    require_once 'CRM/Utils/Rule.php';

    $ids = array( );
    if( $params['id'] != null && ! CRM_Utils_Rule::integer( $params['id'] ) ) {
        return civicrm_create_error( 'Invalid value for relationship type ID' );
    } else {
        $ids['relationshipType'] = $params['id'];
    }
    require_once 'CRM/Contact/BAO/RelationshipType.php';
    $relationType = CRM_Contact_BAO_RelationshipType::add( $params, $ids );
    
    $relType = array( );
    _civicrm_object_to_array( $relationType, $relType );
       
    return $relType;
    
}

/**
 * Delete a relationship type delete
 *
 * @param  id of relationship type  $id
 *
 * @return boolean  true if success, else false
 * @static void
 * @access public
 */
function civicrm_relationship_type_delete( &$params ) {

    if( ! CRM_Utils_Array::value( 'id',$params )  ) {
        return civicrm_create_error( 'Missing required parameter' );
    }
    if( $params['id'] != null && ! CRM_Utils_Rule::integer( $params['id'] ) ) {
        return civicrm_create_error( 'Invalid value for relationship type ID' );
    }
    
    $relationTypeBAO = new CRM_Contact_BAO_RelationshipType( );
    return $relationTypeBAO->del( $params['id'] ) ? civicrm_create_success( ):civicrm_create_error( ts( 'Could not delete relationship type' ) );
}
