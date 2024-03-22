<?php


session_start();


require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();};

$mods = array(
	"MOD000",
	"MOD001",
	"MOD002",
	"MOD003",
	"MOD004",
	"MOD005",
	"MOD006",
	"MOD007",
	"MOD008",
	"MOD009",
	"MOD010",
	"MOD011",
	"MOD012",
	"MOD013",
	"MOD014",
	"MOD015",
	"MOD016",
	"MOD017",
	"MOD018",
	"MOD019",
	"MOD020",
	"MOD021",
	"MOD022",
	"MOD023"
);

// echo "$_SESSION[user]<br>";

// foreach ($mods as $mod) {
	// var_dump(str_split($Authentication->getAuthorization($_SESSION['user'],$mod)));
// }

function hex2bin_($hx) {
	$b="";$h=str_split($hx);foreach($h as $_b16){switch($_b16){case"0":$b.="0000";break;case"1":$b.="0001";break;case"2":$b.="0010";break;case"3":$b.="0011";break;case"4":$b.="0100";break;case"5":$b.="0101";break;case"6":$b.="0110";break;case"7":$b.="0111";break;case"8":$b.="1000";break;case"9":$b.="1001";break;case"A":$b.="1010";break;case"B":$b.="1011";break;case"C":$b.="1100";break;case"D":$b.="1101";break;case"E":$b.="1110";break;case"F":$b.="1111";break;}}return $b;
};

function bin2hex_($bn) {
	$h="";$b=str_split($bn,4);foreach($b as $_b2){switch($_b2){case"0000":$h.="0";break;case"0001":$h.="1";break;case"0010":$h.="2";break;case"0011":$h.="3";break;case"0100":$h.="4";break;case"0101":$h.="5";break;case"0110":$h.="6";break;case"0111":$h.="7";break;case"1000":$h.="8";break;case"1001":$h.="9";break;case"1010":$h.="A";break;case"1011":$h.="B";break;case"1100":$h.="C";break;case"1101":$h.="D";break;case"1110":$h.="E";break;case"1111":$h.="F";break;}}return $h;
};

// var_dump(str_split($Authentication->getAuthorization($_SESSION['user'],"MOD018")));

$x = "100100100100100";
$y = bin2hex_($x);
echo $y;
echo "<br>";
echo hex2bin_($y);

?>
