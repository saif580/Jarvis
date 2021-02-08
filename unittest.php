<?php
include "settings.php";
include "config.php";
include "classes/data.php";

$obj = new data;
//$obj->getData();

if (!ISSET($_SESSION['userid'])) {
	$obj->StartSession();
}

$obj->setParameters("ARE YOU MORE INTELLIGENT THAN ME");
$obj->ProcessInput();
echo $obj->getResponse();
echo "<hr />";

$obj->setParameters("WHAT IS 5 + 5?");
$obj->ProcessInput();
echo $obj->getResponse();
echo "<hr />";

$obj->setParameters("ARE YOU CAPABLE OF DYING");
$obj->ProcessInput();
$obj->getResponse();
echo "<hr />";

$obj->setParameters("ARE YOU EASY TO HACK?");
$obj->ProcessInput();
echo $obj->getResponse();
echo "<hr />";
?>