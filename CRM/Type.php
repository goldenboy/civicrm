<?php

require 'Validate.php';

class CRM_Type {
  const
    T_INT     =   1,
    T_BOOL    =   2,
    T_DOUBLE  =   3,
    T_MONEY   =   4,
    T_STRING  =   5,
    T_TEXT    =   6,
    T_DATE    =   7,
    T_EMAIL   =   8,
    T_URL     =   9,
    T_CCNUM   =  10;

  static $_match = array(
                         self::T_INT    => '/^-?\d+$/',
                         self::T_BOOL   => '/^[01]$/',
                         self::T_DOUBLE => '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/',
                         self::T_MONEY => '/(^\d\d*\.\d?\d?$)|(^\d\d*$)|(^\.\d?\d?$)/',
                         self::T_STRING => '/^[\w\s\'\&\,\$\#\|\_]+$/',
                         );
                         
  /**
   * given a type and a value, is the value valid for the type
   *
   * @param enum   $type  fixed set of types as defined above
   * @param string $value is value match the particular type
   *
   * @return bool  true if value is valid, else false
   * @access public
   */
  static function valid( $value, $type, $dateFormat = null ) {
    if ( ! isset( $value ) ) {
      return true;
    }

    switch ( $type ) {

    case self::T_INT:
    case self::T_BOOL:
    case self::T_DOUBLE:
    case self::T_MONEY:
    case self::T_STRING:
      if ( ! preg_match( self::$_match[$type], $value ) ) {
        return false;
      }
      return true;

    case self::T_EMAIL:
      return Validate::email( $value );

    case self::T_URL:
      return Validate::uri( $value );

    case self::T_CCNUM:
      return Validate::creditCard( $value );

    default:
      return true;

    }
  }

  /**
   * given a string and a type, eliminate any potential hacks. Primarily used for
   * string and text types
   *
   * @param enum   $type  fixed set of types as defined above
   * @param string $value is value match the particular type
   *
   * @return string the filtered value
   * @access public
   */
  static function filter( $type, $value ) {
    $value = trim( $value );

    switch ( $type ) {

    case self::T_INT:
    case self::T_BOOL:
    case self::T_DOUBLE:
    case self::T_DATE:
      return $value;

    case self::T_STRING:
    case self::T_TEXT:
      return $value;

    default:
      return null;

    }

  }

  /**
   * given a string and a type, format the object based on the type
   * applies to date objects only
   *
   * @param enum   $type  fixed set of types as defined above
   * @param string $value is value match the particular type
   *
   * @return string the formatted value
   * @access public
   */
  static function format( $value, $type ) {
    // string and text values are returned without any checks
    if ( $type == self::T_STRING || $type == self::T_TEXT ) {
      return $value;
    }

    $value = trim( $value );

    if ( ! self::valid( $value, $type ) ) {
      return null;
    }

    switch ( $type ) {

    case self::T_INT:
      return (int ) $value;

    case self::T_BOOL:
      return (boolean ) $value;

    case self::T_DOUBLE:
    case self::T_MONEY:
      return (double ) $value;

    case self::T_EMAIL:
    case self::T_URL:
    case self::T_CCNUM:
      return $value;

    }

    return null;
  }

}

?>