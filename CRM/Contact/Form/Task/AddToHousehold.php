<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.1                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Social Source Foundation                        |
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
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Social Source Foundation (c) 2005
 * $Id$
 *
 */

/**
 * This class provides the functionality to add contact(s) to Household
 */
class CRM_Contact_Form_Task_AddToHousehold extends CRM_Contact_Form_Task {
    /**
     * Build the form
     *
     * @access public
     * @return void
     */

    function preProcess( ) {
        /*
         * initialize the task and row fields
         */
        parent::preProcess( );
    }
    
    /**
     * Function to build the form
     *
     * @access public
     * @return None
     */
    function buildQuickForm( ) {

        CRM_Utils_System::setTitle( ts('Add Members To Household') );
        $this->addElement('text', 'name'      , ts('Find Target Household') );
        
        $this->addElement('select',
                          'relationship_type_id',
                          ts('Relationship Type'),
                          array('' => ts('- select -')) +
                          CRM_Contact_BAO_Relationship::getRelationType("Household"));
        $this->addRule('relationship_type_id', ts('Please select a relationship type.'), 'required' );
        
        $searchRows    = $this->get( 'searchRows' );
        $searchCount   = $this->get( 'searchCount' );
        if ( $searchRows ) {
            $checkBoxes = array( );
            $chekFlag = 0;
            foreach ( $searchRows as $id => $row ) {
                $checked = '';
                if (!$chekFlag) {
                    $checked = array( 'checked' => null);
                    $chekFlag++;
                }
                
                $checkBoxes[$id] = $this->createElement('radio',null, null,null,$id, $checked );
            }
            
            $this->addGroup($checkBoxes, 'contact_check');
            $this->assign('searchRows', $searchRows );

        }


        $this->assign( 'searchCount', $searchCount );
        $this->assign( 'searchDone'  , $this->get( 'searchDone'   ) );
        $this->assign( 'contact_type_display', ts('Household') );
        $this->addElement( 'submit', $this->getButtonName('refresh'), ts('Search'), array( 'class' => 'form-submit' ) );
        $this->addElement( 'submit', $this->getButtonName('cancel' ), ts('Cancel'), array( 'class' => 'form-submit' ) );


        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Add To Household'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {

        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
       
        $this->set( 'searchDone', 0 );
        if ( CRM_Utils_Array::value( '_qf_AddToHousehold_refresh', $_POST ) ) {
            $searchParams['contact_type'] = array('Household' => 'Household');
            CRM_Contact_Form_Relationship::search( $searchParams );
            $this->set( 'searchDone', 1 );
            return;
        }
       
        $data = array ();
        //$params['relationship_type_id']='4_a_b';
        $data['relationship_type_id'] = $params['relationship_type_id'];
        $invalid = 0;
        $valid = 0;
        $duplicate = 0;
        if ( is_array($this->_contactIds)) {
            foreach ( $this->_contactIds as $value) {
                $ids = array();
                $ids['contact'] = $value;
                //contact b --> household
                // contact a  -> individual
                $errors = CRM_Contact_BAO_Relationship::checkValidRelationship( $params, $ids, $params['contact_check']);
                if($errors)
                    {
                        $invalid=$invalid+1;
                        continue;
                    }
                
                if ( CRM_Contact_BAO_Relationship::checkDuplicateRelationship( $params,
                                                                               CRM_Utils_Array::value( 'contact', $ids ),
                                                                               $params['contact_check'])) { // step 2
                    $duplicate++;
                    continue;
                }
                CRM_Contact_BAO_Relationship::add($data, $ids, $params['contact_check']);
                $valid++;
            }
            
            $status = array(
                            ts('Added Contact(s) to Household'),
                            ts('Total Selected Contact(s): %1', array(1 => $valid+$invalid+$duplicate))
                            );
            if ( $valid ) {
                $status[] = ts('New relationship record(s) created: %1.', array(1 => $valid)) . '<br>';
            }
            if ( $invalid ) {
                $status[] = ts('Relationship record(s) not created due to invalid target contact type: %1.', array(1 => $invalid)) . '<br>';
            }
            if ( $duplicate ) {
                $status[] = ts('Relationship record(s) not created - duplicate of existing relationship: %1.', array(1 => $duplicate)) . '<br>';
            }
            CRM_Core_Session::setStatus( $status );
        }
    }//end of function

}

?>
