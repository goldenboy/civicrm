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

require_once 'CRM/Core/Form.php';

/**
 * This class previews the uploaded file and returns summary
 * statistics
 */
class CRM_Event_Import_Form_Preview extends CRM_Core_Form
{
    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        $skipColumnHeader = $this->controller->exportValue( 'UploadFile', 'skipColumnHeader' );
        
        //get the data from the session             
        $dataValues         = $this->get('dataValues');
        $mapper             = $this->get('mapper');
        $invalidRowCount    = $this->get('invalidRowCount');
        $conflictRowCount   = $this->get('conflictRowCount');
        $mismatchCount      = $this->get('unMatchCount');
        
        //get the mapping name displayed if the mappingId is set
        $mappingId = $this->get('loadMappingId');
        if ( $mappingId ) {
            require_once 'CRM/Core/DAO/Mapping.php';
            $mapDAO =& new CRM_Core_DAO_Mapping();
            $mapDAO->id = $mappingId;
            $mapDAO->find( true );
            $this->assign('loadedMapping', $mappingId);
            $this->assign('savedName', $mapDAO->name);
        }
        
        if ( $skipColumnHeader ) {
            $this->assign( 'skipColumnHeader' , $skipColumnHeader );
            $this->assign( 'rowDisplayCount', 3 );
        } else {
            $this->assign( 'rowDisplayCount', 2 );
        }
        
        if ($invalidRowCount) {
            $this->set('downloadErrorRecordsUrl', CRM_Utils_System::url('civicrm/export', 'type=1&realm=event'));
        }
        
        if ($conflictRowCount) {
            $this->set('downloadConflictRecordsUrl', CRM_Utils_System::url('civicrm/export', 'type=2&realm=event'));
        }
        
        if ($mismatchCount) {
            $this->set('downloadMismatchRecordsUrl', CRM_Utils_System::url('civicrm/export', 'type=4&realm=event'));
        }
        
        $properties = array( 'mapper',
                             'dataValues', 'columnCount',
                             'totalRowCount', 'validRowCount', 
                             'invalidRowCount', 'conflictRowCount',
                             'downloadErrorRecordsUrl',
                             'downloadConflictRecordsUrl',
                             'downloadMismatchRecordsUrl'
                             );
        
        foreach ( $properties as $property ) {
            $this->assign( $property, $this->get( $property ) );
        }
    }
    
    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( )
    {
        $this->addButtons( array(
                                 array ( 'type'      => 'back',
                                         'name'      => ts('<< Previous') ),
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Import Now >>'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( )
    {
        return ts('Preview');
    }
    
    /**
     * Process the mapped fields and map it into the uploaded file
     * preview the file and extract some summary statistics
     *
     * @return void
     * @access public
     */
    public function postProcess( )
    {
        $fileName         = $this->controller->exportValue( 'UploadFile', 'uploadFile' );
        $skipColumnHeader = $this->controller->exportValue( 'UploadFile', 'skipColumnHeader' );
        $invalidRowCount    = $this->get('invalidRowCount');
        $conflictRowCount   = $this->get('conflictRowCount');
        $onDuplicate        = $this->get('onDuplicate');
        
        $config =& CRM_Core_Config::singleton( );
        $seperator = $config->fieldSeparator;
        
        $mapper = $this->controller->exportValue( 'MapField', 'mapper' );
        $mapperKeys = array();
        
        foreach ($mapper as $key => $value) {
            $mapperKeys[$key] = $mapper[$key][0];
        }
        
        require_once 'CRM/Event/Import/Parser/Participant.php';
        $parser =& new CRM_Event_Import_Parser_Participant( $mapperKeys );
        
        $mapFields = $this->get('fields');
        
        foreach ($mapper as $key => $value) {
            $header = array();
            if ( isset($mapFields[$mapper[$key][0]]) ) {
                $header[] = $mapFields[$mapper[$key][0]];
            }
            $mapperFields[] = implode(' - ', $header);
        }
        
        $parser->run( $fileName, $seperator, 
                      $mapperFields,
                      $skipColumnHeader,
                      CRM_Event_Import_Parser::MODE_IMPORT,
                      $this->get('contactType'),
                      $onDuplicate);
        
        // add all the necessary variables to the form
        $parser->set( $this, CRM_Event_Import_Parser::MODE_IMPORT );
        
        // check if there is any error occured
        
        $errorStack =& CRM_Core_Error::singleton();
        $errors     = $errorStack->getErrors();
        $errorMessage = array();
       
        if( is_array( $errors ) ) {
            foreach($errors as $key => $value) {
                $errorMessage[] = $value['message'];
            }
            
            $errorFile = $fileName . '.error.log';
            
            if ( $fd = fopen( $errorFile, 'w' ) ) {
                fwrite($fd, implode('\n', $errorMessage));
            }
            fclose($fd);
            
            $this->set('errorFile', $errorFile);
            $this->set('downloadErrorRecordsUrl', CRM_Utils_System::url('civicrm/export', 'type=1&realm=event'));
            $this->set('downloadConflictRecordsUrl', CRM_Utils_System::url('civicrm/export', 'type=2&realm=event'));
            $this->set('downloadMismatchRecordsUrl', CRM_Utils_System::url('civicrm/export', 'type=4&realm=event'));
        }
    }
}

