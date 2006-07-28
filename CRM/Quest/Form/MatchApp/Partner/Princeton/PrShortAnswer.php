<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.5                                                |
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
 * Princeton ShortAnswer
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
 * This class generates form components for the Princeton application
 * 
 */
class CRM_Quest_Form_MatchApp_Partner_Princeton_PrShortAnswer extends CRM_Quest_Form_App
{
    
    protected $_fields;

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        
        parent::preProcess();
        
        $this->_fields =
            array(
                  'Favorite1' => ( 'Your favorite book:'),
                  'Favorite2' => ( 'Your favorite movie:'),
                  'Favorite3' => ( 'Your favorite website:'),
                  'Favorite4' => ( 'Your favorite line from a movie:'),
                  'Favorite6' => ( 'Your favorite recording:'),
                  'Favorite7' => ( 'Your favorite keepsake or memento:'),
                  'Favorite8' => ( 'Your favorite source of inspiration:'),
                  'Favorite9' => ( 'Your favorite word:'),
                  'Favorite10'=> ( 'Two adjectives your friends would use to describe you:')
                  );
    }
    
   
     /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        foreach ( $this->_fields as $name => $titles ) {
            $this->add( 'text', $name, $titles, null);
        }
            require_once 'CRM/Quest/BAO/Essay.php';
            $this->_essays = CRM_Quest_BAO_Essay::getFields( 'cm_partner_princeton_short_essay', $this->_contactID, $this->_contactID );
            CRM_Quest_BAO_Essay::buildForm( $this, $this->_essays );
           
            $this->assign_by_ref('fields',$this->_fields);
    }
  /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
         return ts('Short Answers');
    }

}
?>