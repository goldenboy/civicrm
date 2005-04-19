<?php
/*
 +----------------------------------------------------------------------+
 | CiviCRM version 1.0                                                  |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                    |
 +----------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                      |
 |                                                                      |
 | CiviCRM is free software; you can redistribute it and/or modify it   |
 | under the terms of the Affero General Public License Version 1,      |
 | March 2002.                                                          |
 |                                                                      |
 | CiviCRM is distributed in the hope that it will be useful, but       |
 | WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
 | See the Affero General Public License for more details at            |
 | http://www.affero.org/oagpl.html                                     |
 |                                                                      |
 | A copy of the Affero General Public License has been been            |
 | distributed along with this program (affero_gpl.txt)                 |
 +----------------------------------------------------------------------+
*/


require_once 'HTML/QuickForm/Rule/Email.php';

class CRM_Rule {

    static function title( $str ) {
    
        // check length etc
        if ( empty( $str ) || strlen( $str ) < 3 || strlen( $str ) > 127 ) {
            return false;
        }
    
        // make sure it include valid characters, alpha numeric and underscores
        if ( ! preg_match('/^[a-z][\w\s\'\&\,\$\#\-\.\"]+$/i', $str ) ) {
            return false;
        }

        return true;
    }

    static function variable( $str ) {
        // check length etc
        if ( empty( $str ) || strlen( $str ) < 3 || strlen( $str ) > 31 ) {
            return false;
        }
    
        // make sure it include valid characters, alpha numeric and underscores
        if ( ! preg_match('/^[a-z][\w]+$/i', $str ) ) {
            return false;
        }

        return true;
    }

    static function phone( $phone ) {
        // check length etc
        if ( empty( $phone ) || strlen( $phone ) > 16 ) {
            return false;
        }
    
        // make sure it include valid characters, (, \s and numeric
        if ( preg_match('/^[\d\(\)\-\.\s]+$/', $phone ) ) {
            return true;
        }
        return false;
    }


    static function query( $query ) {

        // check length etc
        if ( empty( $query ) || strlen( $query ) < 3 || strlen( $query ) > 127 ) {
            return false;
        }
    
        // make sure it include valid characters, alpha numeric and underscores
        if ( ! preg_match('/^[\w\s\%\'\&\,\$\#]+$/i', $query ) ) {
            return false;
        }

        return true;
    }

    static function url( $url, $checkDomain = false) {
        $options = array( 'domain_check'    => $checkDomain,
                          'allowed_schemes' => array( 'http', 'https', 'mailto', 'ftp' ) );

        return Validate::uri( $url, $options );
    }

    static function date($value, $default = null) {
        if (is_string($value) &&
            preg_match('/^\d\d\d\d-\d\d-\d\d$/', $value)) {
            return $value;
        }
        return $default;
    }

    static function qfDate($date) {
        if ( ! empty( $date['M'] ) && ! empty( $date['d'] ) && ! empty( $data['Y'] ) ) {
            return checkdate( $date['M'], $date['d'], $date['Y'] );
        }
        return true;
    }

    static function integer($value) {
        if (is_int($value)) {
            return true;
        }
    
        if (is_numeric($value) && preg_match('/^\d+$/', $value)) {
            return true;
        }

        return false;
    }

    static function string($value, $maxLength = 0) {
        if (is_string($value) &&
            ($maxLength === 0 || strlen($value) <= $maxLength)) {
            return true;
        }
        return false;
    }

    static function email($value, $checkDomain = false) {
        static $qfRule = null;
        if ( ! isset( $qfRule ) ) {
            $qfRule = new HTML_QuickForm_Rule_Email();
        }
        return $qfRule->validate( $value, $checkDomain );
    }

    /**
     * see how file rules are written in HTML/QuickForm/file.php
     * Checks to make sure the uploaded file is ascii
     *
     * @param     array     Uploaded file info (from $_FILES)
     * @access    private
     * @return    bool      true if file has been uploaded, false otherwise
     */
    static function asciiFile( $elementValue ) {
        if ((isset($elementValue['error']) && $elementValue['error'] == 0) ||
            (!empty($elementValue['tmp_name']) && $elementValue['tmp_name'] != 'none')) {
            return CRM_File::isAscii($elementValue['tmp_name']);
        }
        return false;
    }

    /**
     * see how file rules are written in HTML/QuickForm/file.php
     * Checks to make sure the uploaded file is html
     *
     * @param     array     Uploaded file info (from $_FILES)
     * @access    private
     * @return    bool      true if file has been uploaded, false otherwise
     */
    static function htmlFile( $elementValue ) {
        if ((isset($elementValue['error']) && $elementValue['error'] == 0) ||
            (!empty($elementValue['tmp_name']) && $elementValue['tmp_name'] != 'none')) {
            return CRM_File::isHtmlFile($elementValue['tmp_name']);
        }
        return false;
    }


}

?>