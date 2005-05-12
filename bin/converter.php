#!/opt/local/bin/php
<?php

ini_set( 'include_path', ".:../packages" );

require_once 'PHP/Beautifier.php';

function createDir( $dir, $perm = 0755 ) {
    if ( ! is_dir( $dir ) ) {
        mkdir( $dir, $perm, true );
    }
}

// lets see if this is feasible..!
// proof of concept : php5 -> php4 code conversion...

class PHP_DownGrade {
    
    public $tokens = array();
    
    const T_SELF = 999;
    
    function __construct($file)
    {
        echo "open $file\n";
        $this->constants = array( );
        $this->statics   = array( );
        $this->tokens = token_get_all(file_get_contents($file));
        foreach(array_keys($this->tokens) as $i) {
            if (is_string($this->tokens[$i])) {
                $this->tokens[$i] = array(ord($this->tokens[$i]) ,$this->tokens[$i] );
            }
            if (($this->tokens[$i][0] == T_STRING) && ($this->tokens[$i][1] == 'self')) {
                $this->tokens[$i][0] = self::T_SELF;
            }
        }
        // $this->dump( );
        
    }
    
    function toPHP4() {
        $this->findStart();
        $this->classdef();
        $this->funcdefs(); 
        
        $this->exceptions();
        //$this->dereferences();
        
        //$this->cloning();
        return $this->toString();
    }
    
    
    /**
     * Where is the start of the code (ignoring comments etc..)
     *
     * @return   none
     * @access   public
     */
  
    function findStart()
    {
        for($i=0;$i<count($this->tokens);$i++) {
            
            switch ($this->tokens[$i][0]) {
            case T_COMMENT:
            case T_WHITESPACE:
            case T_OPEN_TAG:
            case T_INLINE_HTML:
            case T_DOC_COMMENT:
                continue;
            default:
                    
                $this->start = $i;
                return;
            }
        }
    }
    
    function classdef() 
    {
        for($i=0;$i<count($this->tokens);$i++) {
            
            switch ($this->tokens[$i][0]) {
            case T_CLASS:
                $i++;
                while($this->tokens[$i][0] != T_STRING) {
                    $i++;
                }
                $class = $this->tokens[$i][1];
                $i++;
                break;
            
            case T_CONST:
                // look for ;
                $this->tokens[$i][1] = '';
                $ii = $i+1;
                while($this->tokens[$i][1] != ';') {
                    while($this->tokens[$i][0] != T_STRING) {
                        $i++;
                    }
                    $const = $this->tokens[$i][1];
                    $this->tokens[$i][1] = '';
                    $i++;
                    while($this->tokens[$i][1] != '=') {
                        $i++;
                    }
                    $this->tokens[$i][1] = '';
                    $i++;
                    
                    $value = '';
                    while($this->tokens[$i][0] != T_CONSTANT_ENCAPSED_STRING && $this->tokens[$i][0] != T_LNUMBER) {
                        $i++;
                    }
                    $value = $this->tokens[$i][1];
                    $this->constants[$class][$const] = $value;
                    $this->tokens[$i][1] = '';
                    $i++;

                    while($this->tokens[$i][1] != ',' && $this->tokens[$i][1] != ';') {
                        $i++;
                    }
                    if ( $this->tokens[$i][1] == ';' ) {
                        $this->tokens[$i][1] = '';
                        $i++;
                        break;
                    } else {
                        $this->tokens[$i][1] = '';
                        $i++;
                    }

                }
                $this->tokens[$i][1] = '';
                $i++;
                break;

            case T_PRIVATE: 
            case T_PUBLIC:
            case T_PROTECTED:
                $start = $i;
                // what can follow :
                // T_STATIC  = static var..
                // T_VARIABLE = var definition.
                // T_FUNCTION 
                $i++;
                $static = false;
                while($i++) {
                    switch($this->tokens[$i][0]) {
                    case T_STATIC:
                        $static = $i;
                        $this->tokens[$i][1] = ''; // strip statics..
                        break;
                    case T_VARIABLE:
                        if ($static) {
                            // we need ot strip it out totally and use $GLOBALS[_classname][...]
                            $i= $this->convertToStatic($class,$i);
                            for($ii = $start;$ii<$i+1;$ii++) {
                                $this->tokens[$ii][1] = ''; 
                            } 
                                    
                            $this->tokens[$static][1] = ''; 
                            break 3;
                        }
                        $this->tokens[$start][1] = 'var'; // change it to var.
                        break 3;
                    case T_FUNCTION:
                        if ($static) {
                            $this->tokens[$static][1] = ''; 
                        }
                        $this->tokens[$start  ][1] = ''; // change it to var.
                        break 3;
                    }
                    // hopefully we wont loop forever!
                }
                
            case self::T_SELF:
                $this->tokens[$i][1] = $class;
                $start = $i;
                $i++;
                while($this->tokens[$i][1] != '::') {
                    $i++;
                }
                while($i++) {
                    switch($this->tokens[$i][0]) {
                    case T_VARIABLE:
                        $this->tokens[$start][1] = 
                            '$GLOBALS[\'_'.strtoupper($class).'\'][\''.
                            substr($this->tokens[$i][1],1).'\']';
                        for($ii = $start+1;$ii<$i+1;$ii++) {
                            $this->tokens[$ii][1] = ''; 
                        }
                        break 3;
                    case T_STRING:
                        while($i++) {
                            switch($this->tokens[$i][0]) {
                            case ord('('):
                                // got a function!
                                break 5;
                            case T_WHITESPACE:
                                break;
                            default:
                                // got a constant:
                                for ( $ii=$i-1; $ii >= 0; $ii-- ) {
                                    if ( $this->tokens[$ii][0] != T_WHITESPACE ) {
                                        break;
                                    }
                                }

                                $this->tokens[$start][1] = 
                                    strtoupper($class).'_'.
                                    $this->tokens[$ii][1];
                                                
                                for($ii = $start+1;$ii<$i;$ii++) {
                                    $this->tokens[$ii][1] = ''; 
                                }    
                                                
                                break 5;
                            }
                        }
                    default:
                        break 2;
                    }
                }
                     
                
            }
        }
        // print_r($this->constants);
        
        
    }
    
    function convertToStatic($class,$i) {
        $name  = substr($this->tokens[$i][1],1);
        
        
        $i++;
        while($this->tokens[$i][1] != '=') {
            if ($this->tokens[$i][1] == ';') {
                $this->statics[$class][$name] = "''";
                return $i;
            }
            $i++;
        }
        
        $i++;
        
        $value = '';
        while($this->tokens[$i][1] != ';') {
            
            $value .= $this->tokens[$i][1];
            $this->tokens[$i][1] = '';
            $i++;
        }
        $this->statics[$class][$name] = $value;
        return $i;
    
    }
    
    
    function funcdefs() {
    
        for($i=0;$i<count($this->tokens);$i++) {
            
                
            switch ($this->tokens[$i][0]) {
            case T_CLASS:
                $i++;
                while($this->tokens[$i][0] != T_STRING) {
                    $i++;
                }
                $class = $this->tokens[$i][1];
                $i++;
                break;

            case 373:
                // make sure the previous and next tokens are strings
                if ( $this->tokens[$i-1][0] == T_STRING && $this->tokens[$i+1][0] == T_STRING ) {
                    // make sure the following token are not open paran and hence a function call
                    $func = false;
                    for ( $ii = $i+2; $ii < $i + 4; $ii++ ) {
                        if ( $this->tokens[$ii][1] == '(' ) {
                            $func = true;
                            break;
                        } else if ( $this->tokens[$ii][1] == ')' || $this->tokens[$ii][1] == ',' ) {
                            break;
                        }
                    }
                    if ( $func ) {
                        break;
                    }
                    $this->tokens[$i-1][1] = strtoupper($this->tokens[$i-1][1]) . '_' . $this->tokens[$i+1][1];
                    $this->tokens[$i  ][1] = '';
                    $this->tokens[$i+1][1] = '';
                }

            case T_FUNCTION:
                $i++;
                while($this->tokens[$i][0] != T_STRING) {
                    $i++;
                }
                $func = $this->tokens[$i][1];
                if ($func == '__construct') {
                    $this->tokens[$i][1] = $class;
                }
                // mmh what about __destruct ! = ignore!!
                while($this->tokens[$i][1] != '(') {
                    $i++;
                }
                while($i++) {
                    switch($this->tokens[$i][0]) {
                        case ord(')');
                        break 3;
                    case T_VARIABLE:
                        while($this->tokens[$i][1] != ',') {
                            if ($this->tokens[$i][1] == ')') {
                                break 4;
                            }
                            $i++;
                        }
                        $i++;
                    case T_STRING:
                        $this->tokens[$i][1] = '';
                        break;
                    }
                }
            }
        }
                
            
    
    }
    
    
    
    function exceptions() 
    {
        for($i=0;$i<count($this->tokens);$i++) {
            switch ($this->tokens[$i][0]) {
            case T_THROW:
                $start = $i;
                $i++;
                while($this->tokens[$i][0] != T_NEW) {
                    $i++;
                }
                $i++;
                while($this->tokens[$i][0] != T_STRING) {
                    $i++;
                }
                $i++;
                $value = '';
                // not really very safe!
                while($this->tokens[$i][1] != ')') {
                    $value .= $this->tokens[$i][1];
                    $i++;
                }
                for($ii = $start+1;$ii<$i;$ii++) {
                    $this->tokens[$ii][1] = ''; 
                }  
                    
                    
                    
                $this->tokens[$start][1] = "require_once 'PEAR.php'; ".
                    "return PEAR::raiseError{$value},null,PEAR_ERROR_RETURN";
                break;
            }
        }
                    
    }
    
    
    
    function toString() 
    {
        $ret = '';
        for($i=0;$i<count($this->tokens);$i++) {
            if ($i == $this->start) {
                foreach( $this->constants as $class => $consts) {
                    $ret ."\n";
                    foreach($consts as $name =>$val) {
                        $ret .= "define( '".strtoupper($class) . "_{$name}',$val);\n";
                    }
                }
                foreach( $this->statics as $class => $consts) {
                    $ret ."\n";
                    foreach($consts as $name =>$val) {
                        $ret .= "\$GLOBALS['_".strtoupper($class) . "']['{$name}'] = $val;\n";
                    }
                }
            }
            $ret .= $this->tokens[$i][1];
            
            
        }
        return $ret;
    }
    
    
    function dump() {
        foreach(array_keys($this->tokens) as $i) {
            echo token_name($this->tokens[$i][0]) .':' . $this->tokens[$i][0]  . ':' . $this->tokens[$i][1] . "\n";
        }
    }
    
    function vars() 
    {
        $this->dump();
    }
    
    
}

/**
$rootDir = "../CRM";
$destDir = "../../crm.php4/CRM";

$dir = new RecursiveIteratorIterator(
                                     new RecursiveDirectoryIterator($rootDir), true);

foreach ( $dir as $file ) {
    if ( substr( $file, -4, 4 ) == '.php' ) {
        echo str_repeat("--", $dir->getDepth()) . ' ' . $file->getPath( ) . " $file\n";
        $x    = new PHP_DownGrade($file->getPath( ) . '/' . $file);
        $php4 = $x->toPHP4( );
        
        $php4Dir  = str_replace( $rootDir, $destDir, $file->getPath( ) );
        createDir( $php4Dir );
        $fd   = fopen( $php4Dir . '/' . $file, "w" );
        fputs( $fd, $php4 );
        fclose( $fd );
    }
}
**/

$x = new PHP_DownGrade($argv[1]);
echo $x->toPHP4();
