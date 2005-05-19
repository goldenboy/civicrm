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
 * add static functions to include some common functionality
 * used across location sub object BAO classes
 *
 */

class CRM_Contact_BAO_Block {

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param Object $block         typically a Phone|Email|IM object
     * @param string $blockName     name of the above object
     * @param array  $params        input parameters to find object
     * @param array  $values        output values of the object
     * @param array  $ids           the array that holds all the db ids
     * @param int    $blockCount    number of blocks to fetch
     *
     * @return void
     * @access public
     * @static
     */
    static function getValues( $block, $blockName, &$params, &$values, &$ids, $blockCount = 0 )  {
        $block->copyValues( $params );

        $flatten = false;
        if ( empty($blockCount) ) {
            $blockCount = 1;
            $flatten       = true;
        } else {
            $values[$blockName] = array();
            $ids[$blockName]    = array();
        }

        $blocks = array( );

        // we first get the primary location due to the order by clause
        $block->orderBy( 'is_primary desc' );
        $block->find( );
        for ($i = 0; $i < $blockCount; $i++) {
            if ($block->fetch()) {
                if ( $flatten ) {
                    $block->storeValues( $values );
                    $ids[$blockName] = $block->id;
                } else {
                    $values[$blockName][$i+1] = array();
                    $block->storeValues( $values[$blockName][$i+1] );
                    $ids[$blockName][$i+1] = $block->id;
                }
                $blocks[$i + 1] = $block;
            }
        }
        return $blocks;
    }

    /**
     * check if the current block object has any valid data
     *
     * @param string $blockName     name of the above object
     * @param array  $blockFields   array of fields that are of interest for this object
     * @param array  $params        input parameters to find object
     * @param int    $locationId    the location id
     * @param int    $blockId       the block id
     *
     * @return boolean              true if the block has data, otherwise false
     * @access public
     * @static
     */
    static function dataExists( $blockName, $blockFields, &$params, $locationId, $blockId ) {
        // return if no data present
        if ( ! array_key_exists( $blockName , $params['location'][$locationId] ) ||
             ! array_key_exists( $blockId, $params['location'][$locationId][$blockName] ) ) {
            return false;
        }

        foreach ( $blockFields as $field ) {
            if ( empty( $params['location'][$locationId][$blockName][$blockId][$field] ) ) {
                return false;
            }
        }
        return true;
    }

}

?>