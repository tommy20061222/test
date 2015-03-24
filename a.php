<?php

	$phrase  = "Cyber shot";
	$healthy = array("_", "-");
	$yummy   = array("", "");


	$newphrase = str_replace($healthy, $yummy, $phrase);
	echo $newphrase;

?>