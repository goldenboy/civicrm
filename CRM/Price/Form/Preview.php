<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
//require_once 'CRM/Price/BAO/Set.php';
//
//require_once 'CRM/Core/BAO/CustomOption.php';
/**
 * This class generates form components for previewing custom data
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
 */
class CRM_Price_Form_Preview extends CRM_Core_Form
{
    /**
     * the group tree data
     *
     * @var array
     */
    protected $_groupTree;

    /**
     * pre processing work done here.
     * 
     * gets session variables for group or field id
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    function preProcess()
    {
        // get the controller vars
        $groupId  = $this->get('groupId');
        $fieldId  = $this->get('fieldId');
        
        if ($fieldId) {
            require_once 'CRM/Price/BAO/Set.php';
            $groupTree = CRM_Price_BAO_Set::getSetDetail($groupId);
            $this->_groupTree[$groupId]['fields'][$fieldId] = $groupTree[$groupId]['fields'][$fieldId];
            $this->assign('preview_type', 'field');
        } else {
            // group preview
            require_once 'CRM/Price/BAO/Set.php';
            $this->_groupTree  = CRM_Price_BAO_Set::getSetDetail($groupId);
            $this->assign('preview_type', 'group');
        }
    }
    
    /**
     * Set the default form values
     * 
     * @param null
     * 
     * @return array   the default array reference
     * @access protected
     */
    function &setDefaultValues()
    {
        $defaults = array();
        $groupId  = $this->get('groupId');
        $fieldId  = $this->get('fieldId');
        if ( $this->_groupTree[$groupId] ) {
            foreach( $this->_groupTree[$groupId]['fields'] as $key => $val ) {
                foreach ( $val['options'] as $keys => $values ) {
                    if ( $values['is_default'] ) {
                        if ( $val['html_type'] == 'CheckBox') {
                            $defaults["price_{$key}"][$keys] = 1;
                        } else {
                            $defaults["price_{$key}"] = $keys;
                        }
                    }
                }
            }
        }
        return $defaults;
    }
    
    /**
     * Function to actually build the form
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $this->assign('groupTree', $this->_groupTree);
        
        // add the form elements
        require_once 'CRM/Price/BAO/Field.php';
        
        foreach ($this->_groupTree as $group) {
            if ( is_array( $group['fields'] ) && !empty( $group['fields'] ) ) {
                foreach ($group['fields'] as $field) {
                    $fieldId = $field['id'];                
                    $elementName = 'price_' . $fieldId;
                    CRM_Price_BAO_Field::addQuickFormElement($this, $elementName, $fieldId, false, $field['is_required']);
                }
            }
        }
        
        $this->addButtons(array(
                                array ('type'      => 'cancel',
                                       'name'      => ts('Done with Preview'),
                                       'isDefault' => true),
                                )
                          );
    }
}

