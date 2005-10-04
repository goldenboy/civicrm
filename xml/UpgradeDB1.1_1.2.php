<?php

ini_set( 'include_path', ".:../packages:.." );

$versionFile = "version.xml";
$versionXML = & parseInput( $versionFile );
$build_version = $versionXML->version_no;
If($build_version != 1.1) {
    echo "Your current version is not 1.1\n";
    exit();
}
$build_version = 1.2 ;


if ( substr( phpversion( ), 0, 1 ) != 5 ) {
    echo phpversion( ) . ', ' . substr( phpversion( ), 0, 1 ) . "\n";
    echo "
CiviCRM requires a PHP Version >= 5
Please upgrade your php / webserver configuration
Alternatively you can get a version of CiviCRM that matches your PHP version
";
    exit( );
}

// for SQL l10n use
require_once '../modules/config.inc.php';
require_once 'Smarty/Smarty.class.php';
require_once 'PHP/Beautifier.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/I18n.php';
require_once 'DB.php';

$coreConfig =& new CRM_Core_Config();
$configValues = DB::parseDSN($coreConfig->dsn);
$username  = $configValues['username'];
$password  = $configValues['password'];
$dbase  = $configValues['database'];


//dump the 1.1 version data 
echo "Dumping 1.1 database....\n";
$mysqldumpPath = exec("which mysqldump"); 
exec($mysqldumpPath." -t -n -u".$username." -p".$password." ". $dbase."  > generated_data_1.1.mysql");


$dsn = 'mysql://'.$username.':'.$password.'@localhost/'.$dbase;
$dbConnect = DB::connect($dsn);


$sql = 'SELECT id , prefix , suffix , gender FROM civicrm_individual ';
$query = $dbConnect->query($sql);
$prefixSuffix =array();

while($row = $query->fetchRow( DB_FETCHMODE_ASSOC )) {
    $prefixSuffix[$row['id']] = array($row['prefix'],$row['suffix'],$row['gender']);
}


function createDir( $dir, $perm = 0755 ) {
    if ( ! is_dir( $dir ) ) {
        mkdir( $dir, $perm, true );
    }
}

$smarty =& new Smarty( );
$smarty->template_dir = './templates';
$smarty->compile_dir  = '/tmp/templates_c';

createDir( $smarty->compile_dir );

$smarty->clear_all_cache();

$file = 'schema/Schema.xml';

$sqlCodePath = '../sql/';
$phpCodePath = '../';

echo "Parsing input file $file\n";
$dbXML =& parseInput( $file );

echo "Extracting database information\n";
$database =& getDatabase( $dbXML );

$classNames = array( );

echo "Extracting table information\n";
$tables   =& getTables( $dbXML, $database );
resolveForeignKeys( $tables, $classNames );
$tables = orderTables( $tables );


$smarty->assign_by_ref( 'database', $database );
$smarty->assign_by_ref( 'tables'  , $tables   );
$tmpArray = array_keys( $tables );
$tmpArray = array_reverse( $tmpArray );
$smarty->assign_by_ref( 'dropOrder', $tmpArray );
$smarty->assign( 'mysql', 'modern' );


echo "Generating sql file\n";
$sql = $smarty->fetch( 'schema.tpl' );

createDir( $sqlCodePath );
$fd = fopen( $sqlCodePath . "1.2_civicrm_41.mysql", "w" );
fputs( $fd, $sql );
fclose($fd);

// now generate the mysql4.0 version
$smarty->assign( 'mysql', 'simple' );
echo "Generating mysql 4.0 file\n";
$sql = $smarty->fetch( 'schema.tpl' );

createDir( $sqlCodePath );
$fd = fopen( $sqlCodePath . "1.2_civicrm_40.mysql", "w" );
fputs( $fd, $sql );
fclose($fd);


$mysqlPath = exec("which mysql");
echo "Creating the Database for 1.2\n";
exec($mysqlPath." -u".$username." -p".$password." ". $dbase." < ../sql/1.2_civicrm_41.mysql");
exec($mysqlPath." -u".$username." -p".$password." ". $dbase." < generated_data_1.1.mysql");

$prefix = array(1 => 'Mrs', 2 => 'Ms', 3 => 'Mr', 4 => 'Dr');
$suffix = array(1 => 'Jr', 2 => 'Sr', 3 => 'II');
$gender = array(1 => 'Female', 2 =>'Male',3 => 'Transgender');

//add perfix in individual_prefix
foreach($prefix as $key=>$value ){
    $query = "INSERT INTO civicrm_individual_prefix(domain_id,name,weight,is_active) VALUES ( 1,'$value', $key, 1)";
    $dbConnect->query($query);
}

//add suffix in individual_suffix
foreach($suffix as $key=>$value ){
    $query = "INSERT INTO civicrm_individual_suffix(domain_id,name,weight,is_active) VALUES ( 1,'$value', $key, 1)";
    $dbConnect->query($query);
}

//add gender in individual_gender
foreach($gender as $key=>$value ){
    $query = "INSERT INTO civicrm_gender(domain_id,name,weight,is_active) VALUES ( 1,'$value', $key, 1)";
    $dbConnect->query($query);
}

foreach($prefixSuffix as $key=>$value) {
    $prefix_id = array_keys($prefix, $value[0]);
    $suffix_id = array_keys($suffix, $value[1]);
    $gender_id = array_keys($gender, $value[2]);
    $updateColumn = array();

    if(count($prefix_id)) {
        $updateColumn[] = ' prefix_id='.$prefix_id[0];
    } 

    if(count($suffix_id)) {
        $updateColumn[] = ' suffix_id='.$suffix_id[0];
    } 

    if(count($gender_id)) {
        $updateColumn[] = ' gender_id='.$gender_id[0];
    } 
    
    if ( count($updateColumn) ) {
        $columns = implode(" , ", $updateColumn);
        $query = "UPDATE civicrm_individual SET ". $columns ." WHERE id = ".$key;
        $dbConnect->query($query);
    }

}

$dbConnect->disconnect();

$beautifier =& new PHP_Beautifier(); // create a instance
$beautifier->addFilter('ArrayNested');
$beautifier->addFilter('Pear'); // add one or more filters
$beautifier->addFilter('NewLines', array( 'after' => 'class, public, require, comment' ) ); // add one or more filters
$beautifier->setIndentChar(' ');
$beautifier->setIndentNumber(4);
$beautifier->setNewLine("\n");

foreach ( array_keys( $tables ) as $name ) {
    echo "Generating $name as " . $tables[$name]['fileName'] . "\n";
    $smarty->clear_all_assign( );

    $smarty->assign_by_ref( 'table', $tables[$name] );
    $php = $smarty->fetch( 'dao.tpl' );

    $beautifier->setInputString( $php );
    
    if ( empty( $tables[$name]['base'] ) ) {
        echo "No base defined for $name, skipping output generation\n";
        continue;
    }

    $directory = $phpCodePath . $tables[$name]['base'];
    createDir( $directory );
    $beautifier->setOutputFile( $directory . $tables[$name]['fileName'] );
    $beautifier->process(); // required
    
    $beautifier->save( );
}
$version = "<?xml version=\"1.03\" encoding=\"iso-8859-1\" ?>\n";
$version .= "<version>\n";
$version .=  "   <version_no>1.2</version_no>\n";
$version .=  "</version>\n";
$fp = fopen($versionFile,"w");
fwrite($fp,$version);

echo "upgradation completed !!!";

function &parseInput( $file ) {
    $dom = DomDocument::load( $file );
    $dom->xinclude( );
    $dbXML = simplexml_import_dom( $dom );
    return $dbXML;
}

function &getDatabase( &$dbXML ) {
    $database = array( 'name' => trim( (string ) $dbXML->name ) );

    $attributes = '';
    checkAndAppend( $attributes, $dbXML, 'character_set', 'DEFAULT CHARACTER SET ', '' );
    checkAndAppend( $attributes, $dbXML, 'collate', 'COLLATE ', '' );
    $database['attributes'] = $attributes;

    
    $tableAttributes = '';
    checkAndAppend( $tableAttributes, $dbXML, 'table_type', 'ENGINE=', '' );
    $database['tableAttributes_modern'] = trim( $tableAttributes . ' ' . $attributes );
    $database['tableAttributes_simple'] = trim( $tableAttributes );

    $database['comment'] = value( 'comment', $dbXML, '' );

    return $database;
}

function &getTables( &$dbXML, &$database ) {
    global $build_version ;
    $tables = array();
    foreach ( $dbXML->tables as $tablesXML ) {
        foreach ( $tablesXML->table as $tableXML ) {
            if ( $tableXML->drop > 0 and $tableXML->drop <= $build_version) {
                continue;
            }
            if ( $tableXML->add <= $build_version ) {
                
                getTable( $tableXML, $database, $tables );
                
            }
   
        }
    }

    return $tables;
}

function resolveForeignKeys( &$tables, &$classNames ) {
    foreach ( array_keys( $tables ) as $name ) {
       
        resolveForeignKey( $tables, $classNames, $name );
    }
}

function resolveForeignKey( &$tables, &$classNames, $name ) {
    if ( ! array_key_exists( 'foreignKey', $tables[$name] ) ) {
        return;
    }
    
    foreach ( array_keys( $tables[$name]['foreignKey'] ) as $fkey ) {
        $ftable = $tables[$name]['foreignKey'][$fkey]['table'];
        if ( ! array_key_exists( $ftable, $classNames ) ) {
            echo "$ftable is not a valid foreign key table in $name";
            continue;
        }
        $tables[$name]['foreignKey'][$fkey]['className'] = $classNames[$ftable];
    }
    
}

function orderTables( &$tables ) {
    $ordered = array( );

    while ( ! empty( $tables ) ) {
        foreach ( array_keys( $tables ) as $name ) {
            if ( validTable( $tables, $ordered, $name ) ) {
                $ordered[$name] = $tables[$name];
                unset( $tables[$name] );
            }
        }
    }
    return $ordered;

}

function validTable( &$tables, &$valid, $name ) {
    if ( ! array_key_exists( 'foreignKey', $tables[$name] ) ) {
        return true;
    }

    foreach ( array_keys( $tables[$name]['foreignKey'] ) as $fkey ) {
        $ftable = $tables[$name]['foreignKey'][$fkey]['table'];
        if ( ! array_key_exists( $ftable, $valid ) && $ftable !== $name ) {
            return false;
        }
    }
    return true;
}

function getTable( $tableXML, &$database, &$tables ) {
    global $classNames;
    global $build_version ;
    $name  = trim((string ) $tableXML->name );
    $klass = trim((string ) $tableXML->class );
    $base  = value( 'base', $tableXML ) . '/DAO/';
    $pre   = str_replace( '/', '_', $base );
    $classNames[$name]  = $pre . $klass;

    $table = array( 'name'       => $name,
                    'base'       => $base,
                    'fileName'   => $klass . '.php',
                    'objectName' => $klass,
                    'labelName'  => substr($name, 8),
                    'className'  => $classNames[$name],
                    'attributes_simple' => trim($database['tableAttributes_simple']),
                    'attributes_modern' => trim($database['tableAttributes_modern']),
                    'comment'    => value( 'comment', $tableXML ) );
    
    $fields  = array( );
    foreach ( $tableXML->field as $fieldXML ) {
        
        if ( $fieldXML->drop > 0 and $fieldXML->drop <= $build_version) {
            continue;
        }
        if ( $fieldXML->add <= $build_version) {
            getField( $fieldXML, $fields );
        }
    }

    $table['fields' ] =& $fields;
   
    $table['hasEnum'] = false;
    foreach ($table['fields'] as $field) {
        if ($field['crmType'] == 'CRM_Utils_Type::T_ENUM') {
            $table['hasEnum'] = true;
            break;
        }
    }

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
            
            if ( $foreignXML->drop > 0 and $foreignXML->drop <= $build_version) {
                continue;
            }
            if ( $foreignXML->add <= $build_version) {
                
                getForeignKey( $foreignXML, $fields, $foreign );
            }
            
        }
        $table['foreignKey' ] =& $foreign;
    }

    $tables[$name] =& $table;
    return;
}

function getField( &$fieldXML, &$fields ) {
   
    $name  = trim( (string ) $fieldXML->name );
    $field = array( 'name' => $name );
    
    $type = (string ) $fieldXML->type;
    switch ( $type ) {
    case 'varchar':
        $field['sqlType'] = 'varchar(' . (int ) $fieldXML->length . ')';
        $field['phpType'] = 'string';
        $field['crmType'] = 'CRM_Utils_Type::T_STRING';
        $field['length' ] = (int ) $fieldXML->length;
        $field['size'   ] = getSize($field['length']);
        break;

    case 'char':
        $field['sqlType'] = 'char(' . (int ) $fieldXML->length . ')';
        $field['phpType'] = 'string';
        $field['crmType'] = 'CRM_Utils_Type::T_STRING';
        $field['length' ] = (int ) $fieldXML->length;
        $field['size'   ] = getSize($field['length']);
        break;

    case 'enum':
        $value = (string ) $fieldXML->values;
        $field['sqlType'] = 'enum(';
        $field['values']  = array( );
        $values = explode( ',', $value );
        $first = true;
        foreach ( $values as $v ) {
            $v = trim($v);
            $field['values'][]  = $v;

            if ( ! $first ) {
                $field['sqlType'] .= ', ';
            }
            $first = false;
            $field['sqlType'] .= "'$v'";
        }
        $field['sqlType'] .= ')';
        $field['phpType'] = $field['sqlType'];
        $field['crmType'] = 'CRM_Utils_Type::T_ENUM';
        break;

    case 'text':
        $field['sqlType'] = $field['phpType'] = $type;
        $field['crmType'] = 'CRM_Utils_Type::T_' . strtoupper( $type );
        $field['rows']    = value( 'rows', $fieldXML );
        $field['cols']    = value( 'cols', $fieldXML );
        break;

    case 'datetime':
        $field['sqlType'] = $field['phpType'] = $type;
        $field['crmType'] = 'CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME';
        break;

    case 'boolean':
        // need this case since some versions of mysql do not have boolean as a valid column type and hence it
        // is changed to tinyint. hopefully after 2 yrs this case can be removed.
        $field['sqlType'] = 'tinyint';
        $field['phpType'] = $type;
        $field['crmType'] = 'CRM_Utils_Type::T_' . strtoupper($type);
        break;

    case 'decimal':
        $field['sqlType'] = 'decimal(20,2)';
        $field['phpType'] = 'float';
        $field['crmType'] = 'CRM_Utils_Type::T_FLOAT';
        break;

    default:
        $field['sqlType'] = $field['phpType'] = $type;
        if ( $type == 'int unsigned' ) {
            $field['crmType'] = 'CRM_Utils_Type::T_INT';
        } else {
            $field['crmType'] = 'CRM_Utils_Type::T_' . strtoupper( $type );
        }
        
        break;
    }

    $field['required'] = value( 'required', $fieldXML );
    $field['comment' ] = value( 'comment' , $fieldXML );
    $field['default' ] = value( 'default' , $fieldXML );
    $field['import'  ] = value( 'import'  , $fieldXML );
    $field['rule'    ] = value( 'rule'    , $fieldXML );
    $field['title'   ] = value( 'title'   , $fieldXML );
    if ( ! $field['title'] ) {
        $field['title'] = composeTitle( $name );
    }
    $field['headerPattern'] = value( 'headerPattern', $fieldXML );
    $field['dataPattern'] = value( 'dataPattern', $fieldXML );

    $fields[$name] =& $field;
}

function composeTitle( $name ) {
    $names = explode( '_', strtolower($name) );
    $title = '';
    for ( $i = 0; $i < count($names); $i++ ) {
        if ( $names[$i] === 'id' || $names[$i] === 'is' ) {
            // id's do not get titles
            return null;
        }

        if ( $names[$i] === 'im' ) {
            $names[$i] = 'IM';
        } else {
            $names[$i] = ucfirst( trim($names[$i]) );
        }

        $title = $title . ' ' . $names[$i];
    }
    return trim($title);
}

function getPrimaryKey( &$primaryXML, &$fields, &$table ) {
    $name = trim( (string ) $primaryXML->name );
    
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

function getIndex(&$indexXML, &$fields, &$indices)
{
  
    $index = array();
    $indexName = trim((string)$indexXML->name);   // empty index name is fine
    $index['name'] = $indexName;
    $index['field'] = array();

    // populate fields
    foreach ($indexXML->fieldName as $v) {
        $fieldName = (string)($v);
        $index['field'][] = $fieldName;
    }

    // check for unique index
    if (value('unique', $indexXML)) {
        $index['unique'] = true;
    }

   

    // field array cannot be empty
    if (empty($index['field'])) {
        echo "No fields defined for index $indexName\n";
        return;
    }

    // all fieldnames have to be defined and should exist in schema.
    foreach ($index['field'] as $fieldName) {
        if (!$fieldName) {
            echo "Invalid field defination for index $indexName\n";
            return;
        }
        if (!array_key_exists($fieldName, $fields)) {
            echo "Table does not contain $fieldName\n";
            print_r( $fields );
            exit( );
            return;
        }
    }
    $indices[$indexName] =& $index;
}


function getForeignKey( &$foreignXML, &$fields, &$foreignKeys ) {
    $name = trim( (string ) $foreignXML->name );
    
    /** need to make sure there is a field of type name */
    if ( ! array_key_exists( $name, $fields ) ) {
        echo "foreign $name does not have a  field definition, ignoring\n";
        return;
    }

    /** need to check for existence of table and key **/
    global $classNames;
    $table = trim( value( 'table' , $foreignXML ) );
    $foreignKey = array( 'name'       => $name,
                         'table'      => $table,
                         'key'        => trim( value( 'key'   , $foreignXML ) ),
                         'import'     => value( 'import', $foreignXML, false ),
                         'className'  => null, // we do this matching in a seperate phase (resolveForeignKeys)
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

/**
 * four
 * eight
 * twelve
 * sixteen
 * medium (20)
 * big (30)
 * huge (45)
 */

function getSize( $maxLength ) {
    if ( $maxLength <= 2 ) {
        return 'CRM_Utils_Type::TWO';
    } 
    if ( $maxLength <= 4 ) {
        return 'CRM_Utils_Type::FOUR';
    } 
    if ( $maxLength <= 8 ) {
        return 'CRM_Utils_Type::EIGHT';
    } 
    if ( $maxLength <= 16 ) {
        return 'CRM_Utils_Type::TWELVE';
    } 
    if ( $maxLength <= 32 ) {
        return 'CRM_Utils_Type::MEDIUM';
    } 
    if ( $maxLength <= 64 ) {
        return 'CRM_Utils_Type::BIG';
    } 
    return 'CRM_Utils_Type::HUGE';
}

?>

