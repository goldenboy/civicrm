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
 | http://www.civicrm.org/licensing/                                 |
 +--------------------------------------------------------------------+
*/


/**
 * College Match Ranking Information Form Page
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2006
 * $Id$
 *
 */

require_once 'CRM/Quest/Form/App.php';
require_once 'CRM/Quest/BAO/Student.php'; 
require_once 'CRM/Core/OptionGroup.php';

/**
 * This class generates form components for relationship
 * 
 */
class CRM_Quest_Form_MatchApp_CmRanking extends CRM_Quest_Form_App
{
     /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        
        parent::preProcess();
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
      
        $defaults = array();
        require_once 'CRM/Quest/DAO/PartnerRanking.php';
        $dao = & new CRM_Quest_DAO_PartnerRanking();
        $dao->s_forward = '0';
        $dao->contact_id = $this->_contactID;
        $dao->find();
        while( $dao->fetch() ){
            $defaults['college_ranking_'.$dao->partner_id] = $dao->ranking ;
        }
        $defaults['match_likely_id'] = CRM_Core_DAO::getFieldValue( 'CRM_Quest_DAO_Student',
                                                        $this->_studentID,
                                                        'match_likely_id' );        
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
        $attributes = CRM_Core_DAO::getAttribute('CRM_Quest_DAO_Student');
       
        require_once "CRM/Quest/BAO/Partner.php";
        require_once "CRM/Quest/DAO/Partner.php";
        
        $partners = CRM_Quest_BAO_Partner::getPartners();

        for ($i = 1; $i<=count($partners); $i++ ) {
            $ranking[$i] = $i;
        }

        $ranking  = $ranking + array('Not Interested');
        
        foreach ( $partners as $k => $v) {
            $this->addElement('select',"college_ranking_{$k}", ts( 'Ranking' ),array('' => ts('- select -')) + $ranking, 'required');
            $this->addRule("college_ranking_{$k}", ts('Please select a ranking for "%1"', array(1 => $v)), 'required');

            $collegeLink =& new CRM_Quest_DAO_Partner();
            $collegeLink->name = $v;
            $collegeLink->find(true);
            $link[$k] = $collegeLink->url_learn;
            unset($collegeLink);
        }
        $this->addRadio( 'match_likely_id',
                         ts('How serious are you about participating in the College Match program\'s binding process?'),
                         CRM_Core_OptionGroup::values('match_likely'),null,null,true );
        
        
        $this->assign( 'collegeType', $partners);

        $this->assign( 'url_link', $link);

        $this->addFormRule(array('CRM_Quest_Form_MatchApp_CmRanking', 'formRule'));

        parent::buildQuickForm( );
    }
    
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
        $ranks = array();
        //remoave the elements the are not 'college_ranking'
        foreach( $params as $k => $value) {
            if (substr($k,0,15) !="college_ranking") {
                unset($params[$k]);
            }
        }
        
        foreach ( $params as $key => $value ) {
            $tempArray = $params;
            unset($tempArray[$key]);
            if ( $value && in_array( $value , $tempArray) ) {
                $errors[$key] = "No two colleges can have the same ranking";
            } else if ( $value && substr($key,0,15) =="college_ranking")  {
                $ranks[$key] = $value;   
            }
        }
        
        // check  if values are in sequence 
        if ( empty( $errors ) ) {
            asort($ranks);
            $fields = array_keys ($ranks);
            sort($ranks);
            
            if ($ranks[0] != 1) {
                $errors['_qf_default'] = "Please assign rankings sequentially (e.g. 1, 2, 3...).";
            }
            
            foreach ( $ranks as $key=>$value) {
                if ( $ranks[$key+1] && ( $ranks[$key+1] != $value+1)) {
                    $errors[$fields[$key+1]] = "Please assign rankings sequentially (e.g. 1, 2, 3...).";
                }
            }
        }
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
        self::preProcess();
        if ( ! ( $this->_action &  CRM_Core_Action::VIEW ) ) {
            $params = $this->controller->exportValues( $this->_name );

            $partners = CRM_Quest_BAO_Partner::getPartners();
            foreach ( $partners as $key=>$value ) {
                $dao = & new CRM_Quest_DAO_PartnerRanking();
                $ranking = array();
                $ranking['contact_id'] = $this->_contactID;
                $ranking['partner_id'] = $key;
                $ranking['ranking'] = $params['college_ranking_'.$key];
                $dao->partner_id = $key;
                $dao->contact_id = $this->_contactID;
                $dao->find(true);
                $dao->copyValues( $ranking );
                $dao->save();
            }
            $ids = array( 'id'         => $this->_studentID,
                          'contact_id' => $this->_contactID );
            $student = CRM_Quest_BAO_Student::create( $params, $ids);            
        }
        parent::postProcess( );
    }
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return ts('College Match Ranking');
    }

}

?>
