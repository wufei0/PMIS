<?php

function logger($txt) {

$file = fopen("../../logs.txt","a+");
fwrite($file,"---- ".date("m/d/Y h:i:s A")." ----\r\n\r\n");
fwrite($file,$txt."\r\n\r\n");
fwrite($file,"-------------- end -------------\r\n");
fclose($file);

}

?>