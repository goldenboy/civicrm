<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
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

require_once 'CRM/Core/Form.php';

class CRM_Contact_Form_Merge extends CRM_Core_Form
{
    var $_defaults = array();

    function preProcess()
    {
        require_once 'api/Contact.php';
        require_once 'api/Search.php';
        require_once 'CRM/Dedupe/Merger.php';
        $cid   = CRM_Utils_Request::retrieve('cid', 'Positive', $this, false);
        $oid   = CRM_Utils_Request::retrieve('oid', 'Positive', $this, false);
        $diffs = CRM_Dedupe_Merger::findDifferences($cid, $oid);
        $main  = crm_get_contact(array('contact_id' => $cid));
        $other = crm_get_contact(array('contact_id' => $oid));
        $this->assign('contact_type', $main->contact_type);
        $this->assign('main_name',    $main->display_name);
        $this->assign('other_name',   $other->display_name);

        foreach (array('Contact', $main->contact_type) as $ct) {
            require_once "CRM/Contact/DAO/$ct.php";
            eval("\$fieldNames['$ct'] =& CRM_Contact_DAO_$ct::fields();");
        }

        foreach ($diffs[$main->contact_type] as $field) {
            $rows[]  = $field;
            $this->_defaults["{$field}[column]"] = $main->contact_type_object->$field;
            $group['main']  = HTML_QuickForm::createElement('radio', 'column', null, $main->contact_type_object->$field,  $main->contact_type_object->$field);
            $group['other'] = HTML_QuickForm::createElement('radio', 'column', null, $other->contact_type_object->$field, $other->contact_type_object->$field);
            $this->addGroup($group, $field, $fieldNames[$main->contact_type][$field]['title']);
        }
        foreach ($diffs['Contact'] as $field) {
            $rows[]  = $field;
            $this->_defaults["{$field}[column]"] = $main->$field;
            $group['main']  = HTML_QuickForm::createElement('radio', 'column', null, $main->$field,  $main->$field);
            $group['other'] = HTML_QuickForm::createElement('radio', 'column', null, $other->$field, $other->$field);
            $this->addGroup($group, $field, $fieldNames['Contact'][$field]['title']);
        }
        foreach (array('main', 'other') as $moniker) {
            $contact =& $$moniker;
            foreach ($contact->custom_values as $cv) {
                if (in_array($cv['custom_field_id'], $diffs['custom'])) {
                    $customValues[$moniker][$cv['custom_field_id']] = $cv['value'];
                    $customLabels[$moniker][$cv['custom_field_id']] = CRM_Core_BAO_CustomOption::getOptionLabel($id, $cv['value']);
                }
            }
        }
        foreach ($diffs['custom'] as $id) {
            $rows[] = "custom_$id";
            $this->_defaults["custom_{$id}[column]"] = $customValues['main'][$id];
            $group['main']  = HTML_QuickForm::createElement('radio', 'column', null, $customLabels['main'][$id],  $customValues['main'][$id]);
            $group['other'] = HTML_QuickForm::createElement('radio', 'column', null, $customLabels['other'][$id], $customValues['other'][$id]);
            $this->addGroup($group, "custom_$id", CRM_Core_BAO_CustomField::getTitle($id));
        }
        $this->assign('rows', $rows);
    }
    
    function setDefaultValues()
    {
        return $this->_defaults;
    }
    
    function addRules()
    {
    }

    public function buildQuickForm()
    {
        $this->addButtons(array(
            array('type' => 'next',   'name' => ts('Merge'), 'isDefault' => true),
            array('type' => 'cancel', 'name' => ts('Cancel')),
        ));
    }

    public function postProcess()
    {
    }
}

?>
