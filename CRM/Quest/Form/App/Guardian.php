<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
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
 | at http://www.openngo.org/faqs/licensing.html                      |
 +--------------------------------------------------------------------+
*/


/**
 * Personal Information Form Page
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Quest/Form/App.php';
require_once 'CRM/Core/OptionGroup.php';

/**
 * This class generates form components for relationship
 * 
 */
class CRM_Quest_Form_App_Guardian extends CRM_Quest_Form_App
{
    protected $_personID;
    protected $_relationshipID;

    const INDUSTRY_UNEMPLOYED = 47;

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        parent::preProcess();
        $this->_personID        = $this->_options['personID'];
        $this->_relationshipID  = $this->_options['relationshipID'];
    }
   
    /**
     * This function sets the default values for the form. Relationship that in edit/view action
     * the default values are retrieved from the database
     * 
     * @access public
     * @return void
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        if ( $this->_personID ) {
            $dao = & new CRM_Quest_DAO_Person();
            $dao->id = $this->_personID ;
            if ($dao->find(true)) {
                CRM_Core_DAO::storeValues( $dao , $defaults );
            }
            
            // format date
            require_once 'CRM/Utils/Date.php';
            $dateFields = array('deceased_year_date','separated_year','college_grad_year','prof_grad_year');
            foreach( $dateFields as $field ) {
                $date = CRM_Utils_Date::unformat( $defaults[$field],'-' );  
                if (! empty( $date) ) {
                    $defaults[$field] = $date;
                } else {
                    $defaults[$field] = '';
                }
            }
        }
        return $defaults;
    }
    

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Quest_DAO_Person');

        $this->addElement( 'text', "first_name",
                           ts('First Name'),
                           $attributes['first_name'] );
        $this->addRule('first_name',ts('Please enter First Name'),'required');

        $this->addElement( 'text', "last_name",
                           ts('Last Name'),
                           $attributes['last_name'] );
        $this->addRule('last_name',ts('Please enter Last Name'),'required');

        $extra = array( 'onchange' => "return showHideByValue('marital_status_id', '42|43|44', 'separated-year', 'table-row', 'select', false);" );
        $this->addSelect('marital_status', ts( 'Marital Status?' ), null, null, $extra );

        $this->addElement( 'date', 'separated_year', 
                           ts( 'Year your parents separated or divorced' ),
                           CRM_Core_SelectValues::date( 'custom', 30, 1, "Y" ) );
        
        $this->addYesNo( 'is_deceased', ts( 'Deceased?' ), null,true, array ('onchange' => "return showHideByValue('is_deceased', '1', 'deceased_year_date', 'table-row', 'radio', false);"));

        $this->addElement( 'date', 'deceased_year_date', 
                           ts( 'Year Deceased' ),
                           CRM_Core_SelectValues::date( 'custom', 50, 1, "Y" ) );
        
        $this->addElement( 'text', "age",
                           ts('Age'),
                           $attributes['age'] );
        $this->addRule('age',ts('Please enter current Age for this person.'),'required');
        $this->addRule('age',ts('Please enter a valid number for the Age of this person.'),'integer');

        $this->add( 'text', "lived_with_from_age", ts( 'From Age' ),
                           $attributes['lived_with_from_age'], true );
        $this->addRule('lived_with_from_age',ts('Please enter a valid number for From Age.'),'integer');

        $this->add( 'text', "lived_with_to_age", ts( 'To Age' ),
                           $attributes['lived_with_to_age'], true );
        $this->addRule('lived_with_to_age',ts('Please enter a valid number for To Age.'),'integer');

        $extra1 = array( 'onchange' => "return showHideByValue('industry_id', '47', 'job_organization|job_occupation|job_current_years', 'table-row', 'select', true);" );
        $this->addSelect('industry', ts( 'Industry' ),null, true, $extra1 );


        $this->addElement( 'text', "job_organization",
                           ts( 'Name of business or organization' ),
                           $attributes['job_organization'] );
        $this->addElement( 'text', 'job_occupation',
                           ts('Occupation/Job Title'),
                           $attributes['job_occupation'] );
        $this->addElement( 'text', 'job_current_years',
                           ts('Number of years in current occupation'),
                           $attributes['job_current_years']);
        $this->addRule('job_current_years',ts('not a valid number'),'integer');

        $extra2 = array( 'onchange' => "showHideByValue('highest_school_level_id', '118|119|120|121|122', 'college_name|college_country|college_grad_year|college_major', 'table-row', 'select', false); return showHideByValue('highest_school_level_id', '122', 'prof_school_name|prof_school_degree|prof_grad_year', 'table-row', 'select', false);" );
        $this->addSelect('highest_school_level', ts('Highest level of schooling'),null,true,$extra2);
        $this->addElement( 'text', 'college_name', ts('College Name'),
                           $attributes['college_name'] );
        $this->addCountry( 'college_country_id', ts('Which country is the college located in?'));
        $this->addElement( 'date',
                           'college_grad_year',
                           ts('Year of college completion'),
                           CRM_Core_SelectValues::date( 'custom', 50, 1, "Y" ) ); 

        $this->addElement( 'text',
                           'college_major',
                           ts('Area of concentration'),
                           $attributes['college_major'] );
        $this->addElement( 'text',
                           'prof_school_name',
                           ts('Name of professional or graduate school'),
                           $attributes['prof_school_name'] );
        $this->addElement( 'date',
                           'prof_grad_year',
                           ts('Year in which graduate degree was received'),
                           CRM_Core_SelectValues::date( 'custom', 50, 1, "Y" ) );
        $this->addSelect( 'prof_school_degree', ts('Degree received in professional or graduate school ') );
        $this->addElement( 'textarea',
                           'description',
                           ts('If there are any extenuating circumstances, or details regarding your parent(s), guardian(s), or household situation that you would like to add or clarify, please do so here'),
                           $attributes['description'] );

        $this->addFormRule(array('CRM_Quest_Form_App_Guardian', 'formRule'));

        parent::buildQuickForm();
    }//end of function
    
    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    public function formRule(&$params) {
        $errors = array( );

        return empty($errors) ? true : $errors;
    }

    /** 
     * process the form after the input has been submitted and validated 
     * 
     * @access public 
     * @return void 
     */ 
    public function postProcess()  
    {
        $params  = $this->controller->exportValues( $this->_name );
       
        $params['relationship_id'] = $this->_relationshipID;

        $relationship = CRM_Core_OptionGroup::values( 'relationship' );
        $relationshipName = trim( CRM_Utils_Array::value( $this->_relationshipID,
                                                          $relationship ) );
        
        $params['contact_id']      = $this->get('contact_id'); 
        $params['is_parent_guardian'] = true;

        $ids['id'] = $this->_personID;

        // format date
        require_once 'CRM/Utils/Date.php';
        $dateFields = array('deceased_year_date','separated_year','college_grad_year','prof_grad_year');
        foreach( $dateFields as $field ) {
            $date = CRM_Utils_Date::format( $params[$field]);  
            if (! empty( $date) ) {
                $params[$field] = $date;  
            } else {
                $params[$field] = '';
            }
        }

        require_once 'CRM/Quest/BAO/Person.php';
        $person = CRM_Quest_BAO_Person::create( $params , $ids );

        // fix the details array
        $details = $this->controller->get( 'householdDetails' );
        $details[$this->_name]['title']   = "{$params['first_name']} {$params['last_name']} Details";
        $details[$this->_name]['options']['personID'] = $person->id;
        $details[$this->_name]['options']['relationshipID'] = $this->_relationshipID;
        $details[$this->_name]['options']['relationshipName'] = $relationshipName;
        $this->set( 'householdDetails', $details );
        
        // check if this person has a job, if so add to incomeArray
        if ( $params['industry_id'] && $params['industry_id'] != self::INDUSTRY_UNEMPLOYED ) {
            // add an income form for this person
            $incomeDetails = $this->controller->get( 'incomeDetails' );
            $incomeID = null;
            if ( CRM_Utils_Array::value( "Income-{$person->id}", $incomeDetails ) ) {
                $incomeID = $incomeDetails[ "Income-{$person->id}" ]['options']['incomeID'];
            }
            $incomeDetails[ "Income-{$person->id}" ] =
                array( 'className' => 'CRM_Quest_Form_App_Income',
                       'title'     => "{$params['first_name']} {$params['last_name']} Income Details",
                       'options'   => array( 'personID' => $person->id,
                                             'incomeID' => $incomeID ) );
            $this->controller->set( 'incomeDetails', $incomeDetails );
        }
    }

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return $this->_title ? $this->_title : ts('Parent/Guardian Detail');
    }

}

?>