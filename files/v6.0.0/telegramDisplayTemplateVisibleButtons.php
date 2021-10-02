<?php
	require_once "../tool/projeqtor.php";
	
	$idMailable=$_REQUEST['idMailable'];

	$mailable = new Mailable($idMailable);
	
	$buttons = array(
		"hidden" => array(),
		"visible" => array()
	);

	if ($mailable->name=='Ticket') {
		$buttons["visible"][] = "butWork";
	} else {
		$buttons["hidden"][] = "butWork";
	}

	if ($mailable->name=='Question') {
		$buttons["visible"][] = "butReply";
		$buttons["visible"][] = "butSend";
	} else {
		$buttons["hidden"][] = "butReply";
		$buttons["hidden"][] = "butSend";
	}

	echo json_encode($buttons);
?>