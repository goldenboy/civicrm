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


/**
 * Business objects for managing custom data values.
 *
 */
class CRM_Core_BAO_CustomValue extends CRM_Core_DAO
{

    /**
     * Validate a value against a CustomField type
     *
     * @param string $type  The type of the data
     * @param string $value The data to be validated
     * 
     * @return boolean True if the value is of the specified type
     * @access public
     * @static
     */
    public static function typecheck($type, $value) 
    {
        switch($type) {
        case 'Memo':
            return true;
            
        case 'String':
            return CRM_Utils_Rule::string($value);
            
        case 'Int':
            return CRM_Utils_Rule::integer($value);
            
        case 'Float':
        case 'Money':
            return CRM_Utils_Rule::numeric($value);
            
        case 'Date':
            return CRM_Utils_Rule::date($value);
            
        case 'Boolean':
            return CRM_Utils_Rule::boolean($value);
            
        case 'StateProvince':
            return
                array_key_exists(strtolower($value),
                                 array_change_key_case( array_flip( CRM_Core_PseudoConstant::stateProvinceAbbreviation() ), CASE_LOWER ) )
                || array_key_exists(strtolower($value),
                                    array_change_key_case( array_flip( CRM_Core_PseudoConstant::stateProvince() ), CASE_LOWER ) );
        case 'Country':
            return
                array_key_exists(strtolower($value),
                         array_change_key_case( array_flip( CRM_Core_PseudoConstant::countryIsoCode() ), CASE_LOWER ) )
                || array_key_exists(strtolower($value),
                            array_change_key_case( array_flip( CRM_Core_PseudoConstant::country() ), CASE_LOWER ) );
        case 'Link':
            return CRM_Utils_Rule::string($value);
        }
        return false;
    }
    
  
    /**
     * given a 'civicrm' type string, return the mysql data store area
     *
     * @param string $type the civicrm type string
     *
     * @return the mysql data store placeholder
     * @access public
     * @static
     */
    public static function typeToField($type) 
    {
        switch ($type) {
        case 'String':
        case 'File':
            return 'char_data';
        case 'Boolean':
        case 'Int':
        case 'StateProvince':
        case 'Country':
            return 'int_data';
        case 'Float':
            return 'float_data';
        case 'Money':
            return 'decimal_data';
        case 'Memo':
            return 'memo_data';
        case 'Date':
            return 'date_data';
        case 'Link':
            return 'char_data';            
        default:
            return null;
        }
    }

    /**
     * given a field return the type associated with it
     *
     * @param string $type the civicrm type string
     *
     * @return the mysql data store placeholder
     * @access public
     * @static
     */
    public static function fieldToType($type)
    {
        switch ($type) {
        case 'char_data':
        case 'memo_data':
            return 'String';
        case 'int_data':
            return 'Int';
        case 'float_data':
        case 'decimal_data':
            return 'Float';
        case 'date_data':
            return 'Date';
        default:
            return null;
        }
    }

    /**
     * return the mysql type of the current value.
     * If boolean type, set the isBool flag too (since int and bool share
     * the same mysql type, we need another differentiator
     *
     * @param boolean  $isBool (reference )  set to true if boolean
     * 
     * @return string the mysql type
     * @access public
     */
    public function getField( &$isBool, $cf = null) 
    {
        if ($cf == null) {
            $cf =& new CRM_Core_BAO_CustomField();
            $cf->id = $this->custom_field_id;
            
            if (! $cf->find(true)) {
                return null;
            }
        }
        
        $isBool = $cf->data_type == 'Boolean' ? true : false;
        
        return $this->typeToField($cf->data_type);
    }
    
    /**
     * returns the string value of the curren object
     *
     * @param boolean $translateBoolean should a boolean value be translated to yes/no
     *
     * @return string the value
     * @access public
     */
    public function getValue($translateBoolean = false) 
    {
        $field = $this->getField($var1);
        
        if ($translateBoolean && $cf->data_type == 'Boolean') {
            return $this->$field ? 'yes' : 'no';
        }
        
        if ( $cf->data_type == 'Money' ) {
            return number_format( $this->$field, 3, '.', '' );
        }
        
        return $this->$field;
    }
    
   
    public static function fixFieldValueOfTypeMemo( &$formValues )
    { 
        if ( empty( $formValues ) ) {
            return null;
        }
        foreach( array_keys( $formValues ) as $key ){
            if ( substr($key,0,7) != 'custom_' ){
                continue;
            }else if( empty($formValues[$key]) ){
                continue;
            }
            
            $htmlType = CRM_Core_DAO::getFieldValue( 'CRM_Core_BAO_CustomField', 
                                                     substr($key,7), 'html_type');
            if ( ( $htmlType == 'TextArea' ) && 
                 ! ( ( substr( $formValues[$key],0,1) == '%' ) ||
                     ( substr( $formValues[$key],-1,1) == '%' ) ) ){
                $formValues[$key] = '%' .  $formValues[$key] . '%';
                
            }
        }
    }
}


