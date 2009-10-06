<?php

/**
 Insert copyright here
 */

/**
 *
 * @package CRM
 * @author Marshal Newrock <marshal@idealso.com>
 * $Id$
 */

require_once 'CRM/Core/DAO/LineItem.php';

/**
 * Business objects for Line Items generated by monetary transactions
 */
class CRM_Core_BAO_LineItem extends CRM_Core_DAO_LineItem {

    /**
     * Creates a new entry in the database.
     *
     * @param array $params (reference) an assoc array of name/value pairs
     *
     * @return object CRM_Core_DAO_LineItem object
     * @access public
     * @static
     */
    static function create (&$params)
    {
        $lineItemBAO =& new CRM_Core_BAO_LineItem();
        $lineItemBAO->copyValues($params);
        return $lineItemBAO->save();
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects.  Typically, the valid params are only
     * price_field_id.  This is the inverse function of create.  It also
     * stores all of the retrieved values in the default array.
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_LineItem object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults )
    {
        $lineItem =& new CRM_Core_DAO_LineItem( );
        $lineItem->copyValues( $params );
        if ( $lineItem->find( true ) ) {
            CRM_Core_DAO::storeValues( $lineItem, $defaults );
            return $lineItem;
        }
        return null;
    }
    
    /**
     * Given a participant id/contribution id, 
     * return contribution/fee line items
     *
     * @param $entityId  int    participant/contribution id
     * @param $entity    string Participant/Contribution.
     *
     * @return array of line items
     */
    static function getLineItems( $entityId, $entity = 'Participant' ) 
    {
        $whereClause  = $fromCluase = null;
        $selectClause = "SELECT li.id, li.label, li.qty, li.unit_price, li.line_total";
        if ( $entity == 'Participant' ) {
            $fromClause = "
FROM      civicrm_participant as p 
LEFT JOIN civicrm_participant_payment pp ON ( pp.participant_id = p.id ) 
LEFT JOIN civicrm_line_item li ON ( li.entity_id = pp.contribution_id AND li.entity_table = 'civicrm_contribution')";
            $whereClause = "WHERE p.id = %1";
        } else if ( $entity == 'Contribution' ) {
            $fromClause = "
FROM      civicrm_contribution c
LEFT JOIN civicrm_line_item li ON ( li.entity_id = c.id AND li.entity_table = 'civicrm_contribution')";
            $whereClause = "WHERE c.id = %1";
        }
        
        $lineItems = array( );
        if ( !$entityId || !$entity || !$fromClause ) return $lineItems; 
        
        require_once 'CRM/Core/DAO.php';
        $params = array( 1 => array( $entityId, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( "$selectClause $fromClause $whereClause", $params );
        while ( $dao->fetch() ) {
            $lineItems[$dao->id] = array( 'qty'        => $dao->qty,
                                          'label'      => $dao->label,
                                          'unit_price' => $dao->unit_price,
                                          'line_total' => $dao->line_total
                                          );
        }
        
        return $lineItems;
    }

}

