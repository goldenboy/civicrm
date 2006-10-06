--TEST--
validate_BE_post_code_strong.phpt: Unit tests for postalCode in strong mode in 'Validate/BE.php'
--FILE--
<?php
include (dirname(__FILE__).'/validate_BE_functions.inc.php');
require_once 'Validate/BE.php';

echo "Test postalCode STRONG Validate_BE\n";
echo "**********************************\n";

$noYes = array('KO', 'OK');

$postalCodeList = array('b-1234' => 'KO', 
                       'B-1234' => 'KO',
                       'b1234'  => 'KO',
                       'B1234'  => 'KO',
                       '1234'   => 'KO',
                       'b-6250' => 'OK',
                       'B-6250' => 'OK',
                       'b6250'  => 'OK',
                       'B6250'  => 'OK',
                       '6250'   => 'OK',
                       '012345' => 'KO',
                       '123'    => 'KO',
                       '0234'   => 'KO',
                       '7234'   => 'KO',
                       '2A34'   => 'KO',
                       '023X'   => 'KO');



echo (test_func('postalCode', $postalCodeList, true )) ? '... FAILED' : '... SUCCESS';

?>
--EXPECT--
Test postalCode STRONG Validate_BE
**********************************
---------
Test postalCode
 _ Value                  State Return
 V = validation result is right
 X = validation result is wrong
 V b-1234               : KO    KO
 V B-1234               : KO    KO
 V b1234                : KO    KO
 V B1234                : KO    KO
 V 1234                 : KO    KO
 V b-6250               : OK    OK
 V B-6250               : OK    OK
 V b6250                : OK    OK
 V B6250                : OK    OK
 V 6250                 : OK    OK
 V 012345               : KO    KO
 V 123                  : KO    KO
 V 0234                 : KO    KO
 V 7234                 : KO    KO
 V 2A34                 : KO    KO
 V 023X                 : KO    KO
... SUCCESS