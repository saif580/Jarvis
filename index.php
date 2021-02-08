<?php
include "settings.php";
include "config.php";
include "classes/data.php";
include "classes/html.php";

$obj = new data;
$resp = new html;
if (!ISSET($_SESSION['userid'])) {
	$obj->StartSession();
}

echo $resp->DrawChatBox();

if (ISSET($_POST['message'])) {
	$obj->setParameters($_POST['message']);
	$obj->ProcessInput();
	$input = $obj->getResponse();
	echo $resp->DrawResponse($input);
}
?>