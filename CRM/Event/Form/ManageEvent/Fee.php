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

require_once 'CRM/Event/Form/ManageEvent.php';
require_once 'CRM/Event/BAO/EventPage.php';
require_once 'CRM/Core/OptionGroup.php';

/**
 * This class generates form components for Event Fees
 * 
 */
class CRM_Event_Form_ManageEvent_Fee extends CRM_Event_Form_ManageEvent
{

    /** 
     * Constants for number of options for data types of multiple option. 
     */ 
    const NUM_OPTION = 11;
    
    /** 
     * Constants for number of discounts for the event. 
     */ 
    const NUM_DISCOUNT = 6;

    /** 
     * Protected variable to show discount date dynamically one day after.
     */ 
    protected $_inDate;

    /** 
     * Protected flag to show discount date dynamically one day after.
     */
    protected $_check = TRUE;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        parent::preProcess( );
    }

    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return None
     */
    function setDefaultValues( )
    {  
        $parentDefaults = parent::setDefaultValues( );
        
        $eventId = $this->_id;
        $params   = array( 'event_id' => $eventId );
        $defaults = array( );
        
        CRM_Event_BAO_EventPage::retrieve( $params, $defaults );
        $eventPageId = CRM_Utils_Array::value( 'id', $defaults );
        
        if ( isset( $eventPageId ) ) {
            require_once 'CRM/Core/BAO/PriceSet.php';
            $price_set_id = CRM_Core_BAO_PriceSet::getFor( 'civicrm_event_page', $eventPageId );
            if ( $price_set_id ) {
                $defaults['price_set_id'] = $price_set_id;
            } else {
                require_once 'CRM/Core/OptionGroup.php'; 
                CRM_Core_OptionGroup::getAssoc( "civicrm_event_page.amount.{$eventPageId}", $defaults );
            }
        }
        
        //check if discounted
        require_once 'CRM/Core/BAO/Discount.php';
        $discountedEvent = CRM_Core_BAO_Discount::getOptionGroup($this->_id, "civicrm_event");
        
        if ( isset($discountedEvent) ) {
            $defaults['is_discount'] = 1;
            foreach ( $discountedEvent as $key => $optionGroupId ) {
                $name = $defaults["discount_name[$key]"] = 
                    CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', $optionGroupId, 'label' );
                
                $defaults["discount_start_date[$key]"] = 
                    CRM_Utils_Date::unformat(CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Discount', $optionGroupId, 
                                                                          'start_date', 'option_group_id' ));
                $defaults["discount_end_date[$key]"] =
                    CRM_Utils_Date::unformat(CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Discount', $optionGroupId, 
                                                                          'end_date', 'option_group_id' ));
                
                CRM_Core_OptionGroup::getAssoc( "civicrm_event_page.amount.{$eventPageId}.discount.{$name}", $defaultDiscounts );
                
                $defaults["discounted_label"] = $defaultDiscounts["label"];
                foreach( $defaultDiscounts["value"] as $k => $v ) {
                    $defaults["discounted_value"][$k][$key] = $v;
                }
                if ( array_values( $defaults["discount_end_date[$key]"] ) && $key < 5 && $this->_check) {
                    $end_date = CRM_Utils_Date::format( $defaults["discount_end_date[$key]"], '-' );
                    $this->_inDate[$key + 1] = CRM_Utils_Date::unformat( date('Y-m-d', 
                                                                              strtotime ("+1 days $end_date")
                                                                              ));
                    $this->_check = true;
                }
            }
            $this->set( 'discountSection', 1 );
            $this->buildQuickForm( );
        }
        $defaults = array_merge( $defaults, $parentDefaults );
        $defaults['id'] = $eventPageId;
        
        if ( CRM_Utils_Array::value( 'value', $defaults ) ) {
            foreach ( $defaults['value'] as $i => $v ) {
                if ( $defaults['amount_id'][$i] == $defaults['default_fee_id'] ) {
                    $defaults['default'] = $i;
                    break;
                }
            }
        }
        
        if ( !isset($defaults['default']) ) {
            $defaults['default'] = 1;
        }
        
        if ( !isset($defaults['is_monetary']) ) {
            $defaults['is_monetary'] = 1;
        }
        
        if ( !isset($defaults['fee_label']) ) {
            $defaults['fee_label'] = ts('Event Fee(s)') ;
        }
        
        if ( ! isset( $defaults['pay_later_text'] ) ||
             empty( $defaults['pay_later_text'] ) ) {
            $defaults['pay_later_text'] = ts( 'I will send payment by check' );
        }

        require_once 'CRM/Core/ShowHideBlocks.php';
        $this->_showHide =& new CRM_Core_ShowHideBlocks( );
        if ( !$defaults['is_monetary'] ) {
            $this->_showHide->addHide( 'event-fees' );
        }
        
        if ( isset($defaults['price_set_id']) ) {
            $this->_showHide->addHide( 'map-field' );
        }
        $this->_showHide->addToTemplate( );
        $this->assign('inDate', $this->_inDate );

        return $defaults;
    }
    
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addYesNo( 'is_monetary',
                         ts('Paid Event'),
                         null, 
                         null,
                         array( 'onclick' => "return showHideByValue('is_monetary','0','event-fees','block','radio',false);" ) );
        
        require_once 'CRM/Contribute/PseudoConstant.php';
        $paymentProcessor =& CRM_Core_PseudoConstant::paymentProcessor( );
        $this->assign('paymentProcessor',$paymentProcessor);
        $this->add( 'select', 'payment_processor_id',
                    ts( 'Payment Processor' ),
                    array('' => ts( '- select -' )) + $paymentProcessor );
        
        $this->add('select', 'contribution_type_id',ts( 'Contribution Type' ),
                   array(''=>ts( '- select -' )) + CRM_Contribute_PseudoConstant::contributionType( ) );
        
        // add pay later options
        $this->addElement('checkbox', 'is_pay_later', ts( 'Enable Pay Later option?' ), null, 
                          array( 'onclick' => "return showHideByValue('is_pay_later','','payLaterOptions','block','radio',false);" ));
        $this->addElement('textarea', 'pay_later_text', ts( 'Pay Later Label' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Event_DAO_EventPage', 'pay_later_text' ), 
                          false );
        $this->addElement('textarea', 'pay_later_receipt', ts( 'Pay Later Instructions' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Event_DAO_EventPage', 'pay_later_receipt' ),
                          false );

        $this->add('text','fee_label',ts('Fee Label'));

        require_once 'CRM/Core/BAO/PriceSet.php';
        $this->add('select', 'price_set_id', ts( 'Price Set' ),
                   array( '' => ts( '- none -' )) + CRM_Core_BAO_PriceSet::getAssoc( ),
                   null, array('onchange' => "return showHideByValue('price_set_id', '', 'map-field', 'block', 'select', false);")
                   );
        
        $default = array( );
        for ( $i = 1; $i <= self::NUM_OPTION; $i++ ) {
            // label 
            $this->add('text', "label[$i]", ts('Label'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'label')); 
            // value 
            $this->add('text', "value[$i]", ts('Value'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'value')); 
            $this->addRule("value[$i]", ts('Please enter a valid money value for this field (e.g. 99.99).'), 'money'); 
            
            // default
            $default[] = $this->createElement('radio', null, null, null, $i); 
        }
        
        $this->addGroup( $default, 'default' );
        
        $this->addElement('checkbox', 'is_discount', ts( 'Discounts by Signup Date?' ), null,
                          array( 'onclick' => "return showHideByValue('is_discount','','discount','block','radio',false);" ));
        
        $discountSection = $this->get( 'discountSection' );
        $this->assign('discountSection', $discountSection);
        
        require_once 'CRM/Core/ShowHideBlocks.php';
        // form fields of Discount sets
        $defaultOption = array();
        $_showHide =& new CRM_Core_ShowHideBlocks('','');
        
        for($i = 1; $i <= self::NUM_DISCOUNT; $i++) {
            //the show hide blocks
            $showBlocks = 'discount_'.$i;
            if ($i > 2) {
                $_showHide->addHide($showBlocks);
            } else {
                $_showHide->addShow($showBlocks);
            }
            //Increament by 1 of start date of previous end date.
            if ( array_values( $this->_submitValues['discount_end_date'][$i] ) && $i <  self::NUM_DISCOUNT - 1 ) {
                $end_date = CRM_Utils_Date::format( $this->_submitValues['discount_end_date'][$i], '-' );
                $this->_inDate[$i + 1] = CRM_Utils_Date::unformat( date('Y-m-d', 
                                                                       strtotime ("+1 days $end_date")
                                                                       ));
                $this->_check = false;
            }
            //discount name
            $this->add('text','discount_name['.$i.']', ts('Discount Name'), 
                       CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'label'));
            
            //discount start date
            $this->add('date', 'discount_start_date['.$i.']', ts('Discount Start Date'),
                       CRM_Core_SelectValues::date('activityDate') );
            
            //discount end date
            $this->add('date', 'discount_end_date['.$i.']', ts('Discount End Date'),
                       CRM_Core_SelectValues::date('activityDate') );
        }
        $_showHide->addToTemplate();   
        $this->addElement( 'submit', $this->getButtonName('refresh'), ts('Post & Reload Discount'), 
                           array( 'class' => 'form-submit' ) );             
        
        $this->buildAmountLabel( );
        parent::buildQuickForm();
    }
    
    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        $this->addFormRule( array( 'CRM_Event_Form_ManageEvent_Fee', 'formRule' ) );
    }

    /**
     * global validation rules for the form
     *
     * @param array $values posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( &$values ) 
    {
        $errors = array( );
        if ( $values['is_discount'] ) {
            $occurDiscount = array_count_values( $values['discount_name'] );
            for ( $i = 1; $i <= self::NUM_DISCOUNT; $i++ ) {
                if ( $values['discount_name'][$i] ) {
                    $start_date = CRM_Utils_Date::format( $values['discount_start_date'][$i] );
                    $end_date   = CRM_Utils_Date::format( $values['discount_end_date'][$i]   );
                    if ( $start_date && $end_date && (int ) $end_date < (int ) $start_date ) {
                        $errors["discount_end_date[$i]"] = ts( 'The discount end date cannot be prior to the start date.' );
                    }
                    
                    if ( $i > 1 ) {
                        if ( $start_date < CRM_Utils_Date::format( $values['discount_end_date'][$i-1]) ) {
                            $errors["discount_start_date[$i]"] = ts( 'Select non-overlapping discount start date.' );
                        }
                    }

                    foreach ( $occurDiscount as $key => $value )            
                        if ( $value > 1 && $key <> '' ) {
                            if ( $key == $values['discount_name'][$i] ) {
                                $errors['discount_name['.$i.']'] = ts( $key.' is already used for Discount Name.' );
                            }
                        }
                }
            }
        }
        
        if ( $values['is_monetary'] ) {
            //check if contribution type is selected
            if ( !$values['contribution_type_id'] ) {
                $errors['contribution_type_id'] = ts( "Please select contribution type." );
            }
            
            //check for the event fee label (mandatory)
            if ( !$values['fee_label'] ) {
                $errors['fee_label'] = ts( "Please enter the fee label for the paid event." );
            }
            
            //check fee label and amount
            $check = 0;
            foreach ( $values['label'] as $key => $val ) {
                if ( trim($val) && trim($values['value'][$key]) ) {
                    $check++;
                    break;
                }
            }
            
            if ( !$check && !$values['price_set_id'] ) {
                if ( !$values['label'][1] ) {
                    $errors['label[1]'] = "Please enter a label for at least one fee level.";
                }
                if ( !$values['value'][1] ) {
                    $errors['value[1]'] = "Please enter an amount for at least one fee level.";
                }
            }
            
            if ( isset( $values['is_pay_later'] ) ) {
                if ( empty( $values['pay_later_text'] ) ) {
                    $errors['pay_later_text'] = ts( 'Please enter the text displayed to the user' );
                }
                if ( empty( $values['pay_later_receipt'] ) ) {
                    $errors['pay_later_receipt'] = ts( 'Please enter the message to be sent to the user' );
                }
            }
            
        }
        return empty( $errors ) ? true : $errors;
    }


    public function buildAmountLabel()
    {
        $default = array( );
        for ( $i = 1; $i <= self::NUM_OPTION; $i++ ) {
            // label 
            $this->add('text', "discounted_label[$i]", ts('Label'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'label')); 
            // value 
            for ( $j = 1; $j <= self::NUM_DISCOUNT; $j++ ) {
                $this->add('text', "discounted_value[$i][$j]", ts('Value'),  array('size' => 10)); 
                $this->addRule("discounted_value[$i][$j]", ts('Please enter a valid money value for this field (e.g. 99.99).'), 
                               'money'); 
            }

            // default
            $default[] = $this->createElement('radio', null, null, null, $i); 
        }
        
        $this->addGroup( $default, 'discounted_default' );
    } 

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        $params = $ids = array();
        $params = $this->exportValues( );
        
        $this->set( 'discountSection', 0 );
        
        if ( CRM_Utils_Array::value( '_qf_Fee_refresh', $_POST ) ) {
            $this->buildAmountLabel( );
            $this->set( 'discountSection', 1 );
            return;
        }
        
        $params['event_id'] = $ids['event_id'] = $this->_id;
        $params['is_pay_later'] = CRM_Utils_Array::value( 'is_pay_later', $params, 0 );
        
        if ( $this->_id ) {
            require_once 'CRM/Core/BAO/PriceSet.php';
            
            // delete all the prior label values in the custom options table
            // and delete a price set if one exists
            $eventPageId = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_EventPage', $this->_id, 'id', 'event_id' );
            if ( $eventPageId ) {
                if ( ! CRM_Core_BAO_PriceSet::removeFrom( 'civicrm_event_page', $eventPageId ) ) {
                    require_once 'CRM/Core/OptionGroup.php';
                    CRM_Core_OptionGroup::deleteAssoc( "civicrm_event_page.amount.{$eventPageId}" );
                }
            }
        }
        
        if ( $params['is_monetary'] ) {
            //add record in event page 
            if ( ! $eventPageId ) {
                $eventPage = CRM_Event_BAO_EventPage::add( $params );
                $eventPageId = $eventPage->id;
            }

            if ( $params['price_set_id'] ) {
                CRM_Core_BAO_PriceSet::addTo( 'civicrm_event_page', $eventPageId, $params['price_set_id'] );
            } else {
                // if there are label / values, create custom options for them
                $labels  = CRM_Utils_Array::value( 'label'  , $params );
                $values  = CRM_Utils_Array::value( 'value'  , $params );
                $default = CRM_Utils_Array::value( 'default', $params ); 
                
                $options = array( );
                if ( ! CRM_Utils_System::isNull( $labels ) && ! CRM_Utils_System::isNull( $values )) {
                    for ( $i = 1; $i < self::NUM_OPTION; $i++ ) {
                        if ( ! empty( $labels[$i] ) && ! CRM_Utils_System::isNull( $values[$i] ) ) {
                            $options[] = array( 'label'      => trim( $labels[$i] ),
                                                'value'      => CRM_Utils_Rule::cleanMoney( trim( $values[$i] ) ),
                                                'weight'     => $i,
                                                'is_active'  => 1,
                                                'is_default' => $default == $i );
                        }
                    }
                    if ( ! empty( $options ) ) {
                        $params['default_fee_id'] = null;
                        CRM_Core_OptionGroup::createAssoc( "civicrm_event_page.amount.{$eventPageId}",
                                                           $options,
                                                           $params['default_fee_id'] );
                    }
                }
                
                if ( $params['is_discount'] == 1 ) {
                    //hack for CRM-3088
                    CRM_Core_OptionGroup::deleteAssoc ("civicrm_event_page.amount.{$eventPageId}.discount.%", "LIKE");

                    // if there are discounted set of label / values, 
                    // create custom options for them
                    $labels  = CRM_Utils_Array::value( 'discounted_label'  , $params );
                    $values  = CRM_Utils_Array::value( 'discounted_value'  , $params );
                    $default = CRM_Utils_Array::value( 'discounted_default', $params ); 
                    
                    if ( ! CRM_Utils_System::isNull( $labels ) && ! CRM_Utils_System::isNull( $values )) {
                        for ( $j = 1; $j <= self::NUM_DISCOUNT; $j++ ) {
                            $discountOptions = array( );
                            for ( $i = 1; $i < self::NUM_OPTION; $i++ ) {
                                if ( ! empty( $labels[$i] ) && ! CRM_Utils_System::isNull( $values[$i][$j] ) ) {
                                    $discountOptions[] = array( 'label'      => trim( $labels[$i] ),
                                                                'value'      => CRM_Utils_Rule::cleanMoney( trim( $values[$i][$j] ) ),
                                                                'weight'     => $i,
                                                                'is_active'  => 1,
                                                                'is_default' => $default == $i );
                                }
                            }
                            
                            if ( ! empty( $discountOptions ) ) {
                                $params['default_fee_id'] = null;
                                $discountOptionsGroupId = 
                                    CRM_Core_OptionGroup::createAssoc( "civicrm_event_page.amount.{$eventPageId}.discount.{$params[discount_name][$j]}",
                                                                       $discountOptions,
                                                                       $params['default_fee_id'],
                                                                       $params['discount_name'][$j]);
                                
                                $discountParams = array(
                                                        'entity_table'    => 'civicrm_event',
                                                        'entity_id'       => $this->_id,
                                                        'option_group_id' => $discountOptionsGroupId,
                                                        'start_date'      => CRM_Utils_Date::format( $params["discount_start_date"][$j]),                                                        'end_date'        => CRM_Utils_Date::format( $params["discount_end_date"][$j])                                                     );                                
                                require_once 'CRM/Core/BAO/Discount.php';
                                CRM_Core_BAO_Discount::add($discountParams);
                            }
                            
                        }
                    }
                }
            }
        } else {
            $params['contribution_type_id'] = '';
        }
        
        //update events table
        require_once 'CRM/Event/BAO/Event.php';
        CRM_Event_BAO_Event::add($params, $ids);
        
        if ( $eventPageId ) {
            //update event page record with default fee id
            CRM_Event_BAO_EventPage::add( $params );
        }
    }
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Event Fees');
    }

}