<?php
/*
4/27/11 initial installation
*/
/* Constants */
define( "BIT_0", 0 );
define( "BIT_1", 1 );
define( "BIT_2", 2 );
define( "BIT_3", 4 );
define( "BIT_4", 8 );
define( "BIT_5", 16 );
define( "BIT_6", 32 );
define( "BIT_7", 64 );
define( "BIT_8", 128 );
define( "BIT_9", 256 );
define( "BIT_10", 512 );
define( "BIT_11", 1024 );
define( "BIT_12", 2048 );
define( "BIT_13", 4096 );
define( "BIT_14", 8192 );
define( "BIT_15", 16384 );
define( "BIT_16", 32768 );
define( "BIT_17", 65536 );
define( "BIT_18", 131072 );
define( "BIT_19", 262144 );
define( "BIT_20", 524288 );
define( "BIT_21", 1048576 );
define( "BIT_22", 2097152 );
define( "BIT_23", 4194304 );
define( "BIT_24", 8388608 );
define( "BIT_25", 16777216 );
define( "BIT_26", 33554432 );
define( "BIT_27", 67108864 );
define( "BIT_28", 134217728 );
define( "BIT_29", 268435456 );
define( "BIT_30", 536870912 );
define( "BIT_31", 1073741824 );

class CBitField {
  var $bitfield = 0;
  function CBitField () {
      $this -> bitfield = ($this -> bitfield | 0);
  }
  function QueryBit ($bit) {
    if (($this -> bitfield & $bit) > 0 )
      return 1;
    else
      return 0;
  }
  function SetBit ($bit, $boolean) {
    if ($boolean == 1)
      $this -> bitfield |= $bit;
    else
      $this -> bitfield &= ~$bit;
  }
  function FlipBit ($bit) {
    $this -> bitfield ^= $bit;
  }
}

/*
$bits = new CBitField;
$bits -> SetBit (BIT_1, 1);
$bits -> SetBit (BIT_2, 0);
$bits -> SetBit (BIT_3, 0);
$bits -> FlipBit (BIT_3);
echo $bits -> QueryBit(BIT_1).",";
echo $bits -> QueryBit(BIT_2).",";
echo $bits -> QueryBit(BIT_3);
*/
?>