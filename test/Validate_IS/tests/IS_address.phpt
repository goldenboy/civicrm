--TEST--
Validate_IS::address()
--FILE--
<?php
    error_reporting(E_ALL & ~E_STRICT);
    require_once "Validate/IS.php";

    $addresses = array(
        "Reglubraut"    => 1,
        "M�nagata"      => 7,
        "A�alstr�ti"    => 6,
        "S�b�lsbraut"   => 1,
        "B�gus"         => 0,
        "Vestmannabraut" => 1
    );

    foreach($addresses as $address => $count) {
        $result = Validate_IS::address($address);
        printf("%-20s: %d (%d)\n", $address, is_array($result) ? count($result) : 0, $count);
    }

    print "\n";
    
    foreach($addresses as $address => $count) {
        $result = Validate_IS::address($address, 200);
        printf("%-20s: %d\n", $address, is_array($result) ? count($result) : 0);
    }
?>
--EXPECT--
Reglubraut          : 1 (1)
M�nagata            : 7 (7)
A�alstr�ti          : 6 (6)
S�b�lsbraut         : 1 (1)
B�gus               : 0 (0)
Vestmannabraut      : 1 (1)

Reglubraut          : 0
M�nagata            : 0
A�alstr�ti          : 0
S�b�lsbraut         : 1
B�gus               : 0
Vestmannabraut      : 0
