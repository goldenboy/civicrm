<?php

require_once 'Smarty/Smarty.class.php';
require_once 'PHP/Beautifier.php';

$smarty = new Smarty( );
$smarty->template_dir = './templates';
$smarty->compile_dir  = '/tmp/templates_c';

$dbXML =& parseInput( 'Contacts.xml' );
// print_r( $dbXML );

$database =& getDatabase( $dbXML );
// print_r( $database );

$tables   =& getTables( $dbXML, $database );
// print_r( $tables );

$smarty->assign_by_ref( 'database', $database );
$smarty->assign_by_ref( 'tables'  , $tables   );

$sql = $smarty->fetch( 'schema.tpl' );
$fd = fopen( "./gen/sql/Contacts.sql", "w" );
fputs( $fd, $sql );
fclose($fd);


$oToken = new PHP_Beautifier(); // create a instance
$oToken->addFilter('ArrayNested');
$oToken->addFilter('Pear'); // add one or more filters
$oToken->addFilter('NewLines', array( 'after' => 'class, public, require, comment' ) ); // add one or more filters
$oToken->setIndentChar(' ');
$oToken->setIndentNumber(4);
$oToken->setNewLine("\n");

foreach ( array_keys( $tables ) as $name ) {
    $smarty->clear_all_assign( );

    $smarty->assign_by_ref( 'table', $tables[$name] );
    $php = $smarty->fetch( 'dao.tpl' );

    $oToken->setInputString( $php );
    $oToken->setOutputFile( "./gen/php/$name.php" );
    $oToken->process(); // required
    
    $oToken->save( );
}

function &parseInput( $file ) {
    $dbXML = simplexml_load_file( $file );
    return $dbXML;
}

function &getDatabase( &$dbXML ) {
    $database = array( 'name' => trim( $dbXML->name ) );

    $attributes = '';
    checkAndAppend( $attributes, $dbXML, 'character_set', 'DEFAULT CHARACTER SET ', '' );
    checkAndAppend( $attributes, $dbXML, 'collate', 'COLLATE ', '' );
    $database['attributes'] = $attributes;

    $tableAttributes = '';
    checkAndAppend( $tableAttributes, $dbXML, 'table_type', 'ENGINE=', '' );
    $database['tableAttributes'] = trim( $tableAttributes . ' ' . $attributes );

    $database['comment'] = value( 'comment', $dbXML, '' );

    return $database;
}

function &getTables( &$dbXML, &$database ) {
    $tables = array();
    foreach ( $dbXML->table as $tableXML ) {
        getTable( $tableXML, $database, $tables );
    }

    return $tables;
}

function getTable( $tableXML, &$database, &$tables ) {
    $name  = trim($tableXML->name );
    $table = array( 'name'       => $name,
                    'attributes' => trim($database['tableAttributes']),
                    'comment'    => value( 'comment', $tableXML ) );
    
    $fields  = array( );
    foreach ( $tableXML->field as $fieldXML ) {
        getField( $fieldXML, $fields );
    }
    $table['fields' ] =& $fields;

    if ( value( 'primaryKey', $tableXML ) ) {
        getPrimaryKey( $tableXML->primaryKey, $fields, $table );
    }

    if ( value( 'index', $tableXML ) ) {
        $index   = array( );
        foreach ( $tableXML->index as $indexXML ) {
            getIndex( $indexXML, $fields, $index );
        }
        $table['index' ] =& $index;
    }

    if ( value( 'foreignKey', $tableXML ) ) {
        $foreign   = array( );
        foreach ( $tableXML->foreignKey as $foreignXML ) {
            getForeignKey( $foreignXML, $fields, $foreign );
        }
        $table['foreignKey' ] =& $foreign;
    }

    $tables[$name] =& $table;
    return;
}

function getField( &$fieldXML, &$fields ) {
    $name  = trim( $fieldXML->name );
    $field = array( 'name' => $name );
    
    $type = (string ) $fieldXML->type;
    switch ( $type ) {
    case 'varchar':
        $field['sqlType'] = 'varchar(' . $fieldXML->length . ')';
        $field['phpType'] = 'string';
        $field['crmType'] = 'CRM_Type::T_STRING';
        $field['length' ] = $fieldXML->length;
        break;

    case 'char':
        $field['sqlType'] = 'char(' . $fieldXML->length . ')';
        $field['phpType'] = 'string';
        $field['crmType'] = 'CRM_Type::T_STRING';
        $field['length' ] = $fieldXML->length;
        break;

    default:
        $field['sqlType'] = $field['phpType'] = $type;
        if ( $type == 'int unsigned' ) {
            $field['crmType'] = 'CRM_Type::T_INT';
        } else {
            $field['crmType'] = 'CRM_Type::T_' . strtoupper( $type );
        }
        
        break;
    }

    $field['required'] = value( 'required', $fieldXML );
    $field['comment' ] = value( 'comment' , $fieldXML );
    $field['default' ] = value( 'default' , $fieldXML );

    $fields[$name] =& $field;
}

function getPrimaryKey( &$primaryXML, &$fields, &$table ) {
    $name = trim( $primaryXML->name );
    
    /** need to make sure there is a field of type name */
    if ( ! array_key_exists( $name, $fields ) ) {
        echo "primary key $name does not have a  field definition, ignoring\n";
        return;
    }

    // set the autoincrement property of the field
    $auto = value( 'autoincrement', $primaryXML );
    $fields[$name]['autoincrement'] = $auto;
    $primaryKey = array( 'name'          => $name,
                         'autoincrement' => $auto );
    $table['primaryKey'] =& $primaryKey;
}

function getIndex( &$indexXML, &$fields, &$indices ) {
    $name      = trim( $indexXML->name );
    $fieldName = trim( $indexXML->fieldName );

    /** need to make sure there is a field of type name */
    if ( ! array_key_exists( $fieldName, $fields ) ) {
        echo "index $name does not have a  field definition, ignoring\n";
        return;
    }

    $index = array( 'name'      => $name,
                    'fieldName' => $fieldName,
                    'unique'     => value( 'unique', $indexXML ) );
    $indices[$name] =& $index;
}

function getForeignKey( &$foreignXML, &$fields, &$foreignKeys ) {
    $name = trim( $foreignXML->name );
    
    /** need to make sure there is a field of type name */
    if ( ! array_key_exists( $name, $fields ) ) {
        echo "foreign $name does not have a  field definition, ignoring\n";
      return;
    }

    /** need to check for existence of table and key **/
    $foreignKey = array( 'name'       => $name,
                         'table'      => trim( value( 'table' , $foreignXML ) ),
                         'key'        => trim( value( 'key'   , $foreignXML ) ),
                         'attributes' => trim( value( 'attributes', $foreignXML, 'ON DELETE CASCADE' ) ),
                         );
    $foreignKeys[$name] =& $foreignKey;
}

function value( $key, &$object, $default = null ) {
    if ( isset( $object->$key ) ) {
        return (string ) $object->$key;
    }
    return $default;
}

function checkAndAppend( &$attributes, &$object, $name, $pre = null, $post = null ) {
    if ( ! isset( $object->$name ) ) {
        return;
    }

    $value = $pre . trim($object->$name) . $post;
    append( $attributes, ' ', trim($value) );
        
}

function append( &$str, $delim, $name ) {
    if ( empty( $name ) ) {
        return;
    }

    if ( is_array( $name ) ) {
        foreach ( $name as $n ) {
            if ( empty( $n ) ) {
                continue;
            }
            if ( empty( $str ) ) {
                $str = $n;
            } else {
                $str .= $delim . $n;
            }
        }
    } else {
        if ( empty( $str ) ) {
            $str = $name;
        } else {
            $str .= $delim . $name;
        }
    }
}

?>

