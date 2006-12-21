<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                  |
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
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/


require_once 'CRM/Utils/Type.php';
require_once 'CRM/Event/PseudoConstant.php';

class CRM_Event_Import_Field
{
    /**#@+
     * @access protected
     * @var string
     */

    /**
     * name of the field
     */
    public $_name;

    /**
     * title of the field to be used in display
     */
    public $_title;

    /**
     * type of field
     * @var enum
     */
    public $_type;
    
    /**
     * regexp to match the CSV header of this column/field
     * @var string
     */
     public $_headerPattern;
     
    /**
     * regexp to match the pattern of data from various column/fields
     * @var string
     */
     public $_dataPattern;
     
    /**
     * value of this field
     * @var object
     */
    public $_value;
    
    function __construct( $name, $title, $type = CRM_Utils_Type::T_INT, $headerPattern = '//', $dataPattern = '//')
    {
        $this->_name      = $name;
        $this->_title     = $title;
        $this->_type      = $type;
        $this->_headerPattern = $headerPattern;
        $this->_dataPattern = $dataPattern;
    
        $this->_value     = null;
    }
    
    function resetValue( )
    {
        $this->_value     = null;
    }
    
    /**
     * the value is in string format. convert the value to the type of this field
     * and set the field value with the appropriate type
     */
    function setValue( $value )
    {
        $this->_value = $value;
    }
    
    function validate( )
    {
        if ( CRM_Utils_System::isNull( $this->_value ) ) {
            return true;
        }
        
        switch ($this->_name) {
        case 'contact_id':
            // note: we validate extistence of the contact in API, upon
            // insert (it would be too costlty to do a db call here)
            return CRM_Utils_Rule::integer($this->_value);
            break;
        case 'register_date':
            return CRM_Utils_Rule::date($this->_value);
            break;
            /*
        case 'event_id':
            static $events = null;
            if (!$events) {
                $events =& CRM_Event_PseudoConstant::event();
            }
            if (in_array($this->_value, $events)) {
                return true;
            } else {
                return false;
            }
            break;
            */
        default:
            break;
        }
        
        // check whether that's a valid custom field id
        // and if so, check the contents' validity
        if ($customFieldID = CRM_Core_BAO_CustomField::getKeyID($this->_name)) {
            static $customFields = null;
            if (!$customFields) {
                $customFields =& CRM_Core_BAO_CustomField::getFields('Membership');
            }
            if (!array_key_exists($customFieldID, $customFields)) {
                return false;
            }
            return CRM_Core_BAO_CustomValue::typecheck($customFields[$customFieldID][2], $this->_value);
        }
        return true;
    }
}
?>