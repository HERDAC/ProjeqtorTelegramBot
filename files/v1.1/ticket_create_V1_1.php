<?php


//LOGIN TO ACCESS CLASSES AND FUNCTIONS

$batchMode=true;
$apiMode=true;
require_once('../tool/projeqtor.php');
$batchMode=false;

$user = SqlElement::getSingleSqlElementFromCriteria('User',array('name'=>'PROJEQTOR-USER'));
$user->_API = true;
setSessionUser($user);

//END LOGIN


$msgs = array(
	"stop" => "Création du ticket annulée",
	"noTicket" => "Il n'y a pas de ticket en cours de création",
	"alreadyCreating" => "Vous êtes déjà en train de créer un ticket",
	"name" => "Quel nom voulez-vous donner au ticket ?",
	"nameConfirmation" => "Voulez-vous créer le ticket ':name' ?",
	"confirm" => "Voulez-vous créer ce ticket ?",
	"created" => "*Ticket *[:ref](PROJEQTOR-URL/view/main.php?directAccess=true&objectClass=Ticket&objectId=:id)* créé !*",
	"description" => "Écrivez une description pour le ticket",
	"choose" => "Choisissez un champ à modifier ou enregistrez le ticket",
	"choose_disabled" => "Champ sélectionné: :field",
	"model" => "Choisissez un modèle de ticket",
	"resp" => "Choisissez un responsable",
	"urge" => "Choisissez un niveau d'urgence",
	"crit" => "Choisissez un niveau de criticité",
	"work" => "Définissez un nombre d'heures estimée de travail (p. ex. 3.5)",
	"type" => "Choisissez un type de ticket",
	"proj" => "Choisissez un projet",
	"ctxt1" => "Choisissez un contexte",
	"ctxt3" => "Choisissez un contexte",
	"act" => "Choisissez une activité",

	"invalid-work" => "Format invalide, référez-vous à l'exemple"
);

$options = array(
	array(
		array("txt"=>"Description", "action"=>"desc")
	),
	array(
		array("txt"=>"Modèle", "action"=>"model"),
		array("txt"=>"Responsable", "action"=>"resp")
	),
	array(
		array("txt"=>"Type", "action"=>"type"),
		array("txt"=>"Urgence", "action"=>"urge"),
		array("txt"=>"Criticité", "action"=>"crit")
	),
	array(
		array("txt"=>"Projet", "action"=>"proj"),
		array("txt"=>"Activité", "action"=>"act")
	),
	array(
		array("txt"=>"Travail estimé", "action"=>"work"),
		array("txt"=>"Contexte", "action"=>"ctxt1")
	),
	array(
		array("txt"=>"Créer", "action"=>"confirm")
	)
);

$fields = array(
	"desc" => "description",
	"type" => "idTicketType",
	"proj" => "idProject",
	"resp" => "idResource",
	"urge" => "idUrgency",
	"crit" => "idCriticality",
	"ctxt1" => "idContext1",
	"ctxt3" => "idContext3",
	"act" => "idActivity",
	"work" => "plannedWork"
);

$summary = array(
	"name" => array("text"=>"*Nom*: `:field`"),
	"description" => array("text"=>"\n*Description*: ```\n:field\n```"),
	"idTicketType" => array("text"=>"*Type*: `:field`", "class"=>"Type"),
	"idProject" => array("text"=>"*Projet*: `:field`", "class"=>"Project"),
	"idUrgency" => array("text"=>"*Urgence*: `:field`", "class"=>"Urgency"),
	"idCriticality" => array("text"=>"*Criticité*: `:field`", "class"=>"Criticality"),
	"idContext" => array("text"=>"*Contexte*: `:field1` — `:field2`", "class"=>"Context"),
	"idActivity" => array("text"=>"*Activité*: `:field`", "class"=>"Activity"),
	"idResource" => array("text"=>"*Responsable*: `:field`", "class"=>"Resource"),
	"plannedWork" => array("text"=>"*Travail estimé*: `:fieldh`")
);

$verifyFunctions = array(
	"work" => function ($value) {
		return preg_match('/^\d+($|(\.\d+$))/', $value);
	}
);

$statesFilePath = "./ticket_create_states.dat";

function log_msg($msg) {
	error_log("Telegram Bot: ".$msg);
}

function get_req($url, $login=false) {
	$curl = curl_init();
	if ($login) {
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, "PROJEQTOR-USER:PROJEQTOR-PWD");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = json_decode(curl_exec($curl), TRUE);

	curl_close($curl);

	return $result;
}

function insert_projeqtor($data) {
	require("../external/phpAES/aes.class.php");
	require("../external/phpAES/aesctr.class.php");

	$url = "http://admin.herdac.ch/api/Ticket";

	$data = AesCtr::encrypt(json_encode($data), 'PROJEQTOR-API', 128);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($curl, CURLOPT_USERPWD, "PROJEQTOR-USER:PROJEQTOR-PWD");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, array('data'=>$data));

	$curl_response = json_decode(curl_exec($curl), TRUE);
	curl_close($curl);

	return $curl_response;
}

function send_msg($data, $command="sendMessage") {
	$url = "https://api.telegram.org/botBOT-TOKEN/$command";
	$options=array('http'=>array('method'=>'POST','header'=>"Content-Type:application/x-www-form-urlencoded\r\n",'content'=>http_build_query($data)));
	$context=stream_context_create($options);
	$result=file_get_contents($url,false,$context);
	return $result;
}

function get_states() {
	global $statesFilePath;

	if (!file_exists($statesFilePath)) {
		$result = array();
		file_put_contents($statesFilePath, serialize($result));
	} else {
		$result = unserialize(file_get_contents($statesFilePath));
	}

	return $result;
}

function set_states($states, $chatId) {
	global $statesFilePath;

	$prevStates = get_states();
	$prevStates[$chatId] = $states;
	file_put_contents($statesFilePath, serialize($prevStates));
}

function choose(&$msg, &$markup, &$userState, &$state, $chatId) {
	global $msgs, $options;
	$msg = $msgs["choose"];
	$buttons = array();

	foreach($options as $l) {
		$line = array();

		foreach($l as $button) {
			$action = $button["action"];
			$isSet = isset($userState["data"]["fields"]) && array_key_exists($action, $userState["data"]["fields"]);

			array_push($line, array("text"=>$button["txt"].($isSet ? " ✅" : ""), "callback_data"=>"{'action': '$action'}"));
		}

		array_push($buttons, $line);
	}

	$markup = array("inline_keyboard"=>$buttons);
	$state = 2;
	$userState["state"] = $state;


	if (!isset($userState["fields"])) {
		$userState["fields"] = array();
	}
}

function csv($data) {
	$result = array();

	foreach ($data as $key => $value) {
		array_push($result, "$key|$value");
	}

	$result = implode(",", $result);

	return $result;
}

function uncsv($data) {
	$result = array();

	$data = explode(",", $data);

	foreach ($data as $kv) {
		$kv = explode("|", $kv);
		$result[$kv[0]] = $kv[1];
	}

	return $result;
}

function get_affectations($project) {
	$allProjects = $project->getTopProjectList(true);
	$rows = array();

	foreach ($allProjects as $projectId) {
		$aff = new Affectation();
		$affectations = $aff->getSqlElementsFromCriteria(array("idProject"=>$projectId,"idle"=>"0"));
		foreach ($affectations as $affectation) {
			if (!in_array($affectation->idResource, array_column($rows, "id"))) {
				$profile = new Profile($affectation->idProfile);
				$user = new User($affectation->idResource);
				$rows[] = array("id"=>$affectation->idResource, "name"=>$user->name, "order"=>$profile->sortOrder);
			}
		}
	}

	return $rows;
}

function get_all_data($chatId, $states) {
	global $fields;

	$name = $states["data"]["name"];
	$data = array(
		"name"=>$name,
		"description" => "",
		"idTicketType"=>209,
		"idProject"=>16,
		"idStatus"=>1
	);

	if (isset($states["data"]["fields"])) {
		foreach ($states["data"]["fields"] as $field => $value) {
			if (array_key_exists($field, $fields)) {
				$data[$fields[$field]] = $value;
			}
		}
	}

	return $data;
}

function get_summary($data) {
	global $summary;
	$result = "";

	$lines = array();

	foreach ($summary as $id => $values) {
		if ($id == "idContext" and (isset($data[$id."1"]) or isset($data[$id."3"]) )) {
			$ctxt1 = "X";
			$ctxt3 = "X";
			if (isset($data[$id."1"])) {
				$objId = $data[$id."1"];
				$ctxt = new $values["class"]($objId);
				$ctxt1 = $ctxt->name;
			}
			if (isset($data[$id."3"])) {
				$objId = $data[$id."3"];
				$ctxt = new $values["class"]($objId);
				$ctxt3 = $ctxt->name;
			}

			array_push($lines, str_replace(array(":field1", ":field2"), array($ctxt1, $ctxt3), $values["text"]));

		} else {
			if (isset($data[$id])) {
				$field = $data[$id];
				if (isset($values["class"])) {
					$objId = $data[$id];
					$obj = new $values["class"]($objId);
					$field = $obj->name;
				}

				array_push($lines, str_replace(":field", $field, $values["text"]));
			}
		}
	}

	$result .= implode("\n", $lines);

	return $result;
}

$input = json_decode(file_get_contents("php://input"),TRUE);

$chatId = $input["chatId"];

$msg = "";
$markup = array();
$states = get_states();
$modify = false;

if (!array_key_exists($chatId, $states)) {
	$states = array("state" => 0, "data" => array());
} else {
	$states = $states[$chatId];
}

if (isset($input["action"])) {
	$action = $input["action"];

	$state = $states["state"];

	if ($action == "ticket") {
		if ($state == 0) {
			$msg = $msgs["name"];
			$states["data"] = array();

			$state = 1;
		} else {
			$msg = $msgs["alreadyCreating"];
		}

	} else if ($action == "stop") {
		if ($state != 0) {
			if (isset($states["choose_msg_id"])) {
				$data = array("chat_id"=>$chatId,"message_id"=>$states["choose_msg_id"],"text"=>"_Création annulée, choix indisponible_", "parse_mode"=>"Markdown");

				send_msg($data, "editMessageText");
			}

			$msg = $msgs["stop"];
			$state = 0;
			$states = array("state"=>0, "data"=>array());

		} else {
			$msg = $msgs["noTicket"];
		}

	} else if ($action == "state") {
		$msg = "State: ".json_encode($states, JSON_PRETTY_PRINT);

	} else if ($action == "reply") {
		if ($state == 1) {
			$state = 2;
			$states["data"]["name"] = $input["content"];
			choose($msg, $markup, $states, $state, $chatId);

		} else if ($state == 3) {
			unset($states["choose_msg_id"]);

			$field = $states["data"]["field"];
			$valid = true;
			if (isset($verifyFunctions[$field])) {
				$valid = $verifyFunctions[$field]($input["content"]);
			}

			if ($valid) {
				$states["data"]["fields"][$field] = $input["content"];
				choose($msg, $markup, $states, $state, $chatId);

			} else {
				$msg = $msgs["invalid-$field"];
			}
		}

	} else if ($action == "callback") {
		if ($input["content"][0] == "{") {
			$callback = json_decode(str_replace("'", '"', $input["content"]), TRUE);

		} else {
			$callback = uncsv($input["content"]);
		}

		$cbAction = $callback["action"];

		send_msg(array("callback_query_id"=>$input["callbackQueryId"]), "answerCallbackQuery");

		$modify = true;

		if ($state == 5) {
			if ($cbAction == "create") {
				$data = get_all_data($chatId, $states);

				$user = SqlElement::getSingleSqlElementFromCriteria("User", array("chatIdTelegram"=>$chatId));
				$data["idUser"] = $user->id;

				if (isset($data["idCriticality"]) and isset($data["idUrgency"])) {
					$crit = new Criticality($data["idCriticality"]);
					$urge = new Urgency($data["idUrgency"]);

					$priorityValue = round($urge->value * $crit->value / 2);
					$sql = new Priority();
					$priority = $sql->getSqlElementsFromCriteria(null, false, "value <= $priorityValue");
					usort($priority, function ($a, $b) { return $a->value <=> $b->value; });
					if (count($priority)) {
						$priority = end($priority);
						$data["idPriority"] = $priority->id;
					}
				}

				if (isset($data["plannedWork"])) {
					$data["plannedWork"] = Work::convertImputation($data["plannedWork"]);
				}

				$result = insert_projeqtor($data)["items"][0];

				$data["id"] = explode(" ", explode("#", $result["apiResultMessage"])[1])[0];
				$data["reference"] = $result["reference"];

				if (isset($data["plannedWork"])) {
					$data["plannedWork"] = Work::displayImputation($data["plannedWork"]);
				}

				$msg = get_summary($data);
				send_msg(array(
					"chat_id" => $chatId,
					"text" => str_replace(array(":ref", ":id"), array($data["reference"], $data["id"]), $msgs["created"]),
					"parse_mode" => "Markdown"
				));

				$state = 0;
				$states = array("state"=>0, "data"=>array());

			} else if ($cbAction == "modify") {
				$state = 2;
				choose($msg, $markup, $states, $state, $chatId);
			}

		} else if ($state == 2) {
			if ($cbAction == "confirm") {
				$state = 5;
				$data = get_all_data($chatId, $states);
				$msg = $msgs["confirm"];
				$msg .= "\n\n";
				$msg .= get_summary($data);
				$markup["inline_keyboard"] = array(array(
					array("text"=>"Modifier", "callback_data"=>"{'action':'modify'}"),
					array("text"=>"Valider", "callback_data"=>"{'action':'create'}")
				));

			} else if ($cbAction == "desc") {
				$msg = $msgs["description"];
				$state = 3;
				$states["data"]["field"] = "desc";

			} else if ($cbAction == "model") {
				$msg = $msgs["model"];
				$state = 4;
				$states["data"]["field"] = "model";

				$tt = new TicketTemplate();
				$values = $tt->getSqlElementsFromCriteria(array("idle"=>"0"));

				$user = SqlElement::getSingleSqlElementFromCriteria("User", array("chatIdTelegram"=>$chatId));
				$userId = $user->id;

				$buttons = array();
				foreach ($values as $model) {
					$id = $model->id;

					$valid = false;

					$projId = $model->idProject;
					$proj = new Project($projId);
					$allProjects = $proj->getTopProjectList(true);

					foreach ($allProjects as $projectId) {
						$aff = new Affectation();
						$affectations = $aff->getSqlElementsFromCriteria(array("idProject"=>$projectId,"idle"=>"0"));
						foreach ($affectations as $affectation) {
							if ($affectation->idResource == $userId) {
								$valid = true;
								break 2;
							}
						}
					}

					if ($valid) {
						$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["model"]) && $states["data"]["fields"]["model"] == $id;

						array_push($buttons, array("text"=>$model->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selModel','id':'$id'}"));
					}
				}

				$markup["inline_keyboard"] = array_chunk($buttons, 2);

			} else if ($cbAction == "resp") {
				$msg = $msgs["resp"];
				$state = 4;
				$states["data"]["field"] = "resp";

				if (isset($states["data"]["fields"]) and isset($states["data"]["fields"]["proj"])) {
					$projId = $states["data"]["fields"]["proj"];
					$proj = new Project($projId);

					$rows = get_affectations($proj);

					$names = array_column($rows, "name");
					$order = array_column($rows, "order");

					array_multisort($order, SORT_ASC, $names, SORT_ASC, $rows);


					$values = array();
					foreach ($rows as $row) {
						$values[] = new User($row["id"]);
					}

				} else {
					$user = new User();
					$values = $user->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "fullName ASC");
				}

				$buttons = array();
				foreach ($values as $resource) {
					$id = $resource->id;
					$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["resp"]) && $states["data"]["fields"]["resp"] == $id;

					array_push($buttons, array("text"=>$resource->resourceName.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selResp','id':'$id'}"));
				}

				$markup["inline_keyboard"] = array_chunk($buttons, 2);

			} else if ($cbAction == "urge") {
				$msg = $msgs["urge"];
				$state = 4;
				$states["data"]["field"] = "urge";

				$urg = new Urgency();
				$values = $urg->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "sortOrder ASC");

				$buttons = array();
				foreach ($values as $urgency) {
					$id = $urgency->id;
					$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["urge"]) && $states["data"]["fields"]["urge"] == $id;

					array_push($buttons, array("text"=>$urgency->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selUrge','id':'$id'}"));
				}

				$markup["inline_keyboard"] = array_chunk($buttons, count($buttons)>3 ? 2 : 3);

			} else if ($cbAction == "crit") {
				$msg = $msgs["crit"];
				$state = 4;
				$states["data"]["field"] = "crit";

				$crit = new Criticality();
				$values = $crit->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "sortOrder ASC");

				$buttons = array();
				foreach ($values as $criticality) {
					$id = $criticality->id;
					$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["crit"]) && $states["data"]["fields"]["crit"] == $id;

					array_push($buttons, array("text"=>$criticality->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selCrit','id':'$id'}"));
				}

				$markup["inline_keyboard"] = array_chunk($buttons, count($buttons)>3 ? 2 : 3);

			} else if ($cbAction == "work") {
				$msg = $msgs["work"];
				$state = 3;
				$states["data"]["field"] = "work";

			} else if ($cbAction == "type") {
				$msg = $msgs["type"];
				$state = 4;
				$states["data"]["field"] = "type";

				$type = new TicketType();
				$values = $type->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "sortOrder ASC");

				$buttons = array();
				foreach ($values as $type) {
					$id = $type->id;
					$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["type"]) && $states["data"]["fields"]["type"] == $id;

					array_push($buttons, array("text"=>$type->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selType','id':'$id'}"));
				}

				$markup["inline_keyboard"] = array_chunk($buttons, count($buttons)>3 ? 2 : 3);

			} else if ($cbAction == "proj") {
				$msg = $msgs["proj"];
				$state = 4;
				$states["data"]["field"] = "proj";

				$project = new Project();
				$values = $project->getSqlElementsFromCriteria(array("idle"=>"0"));

				$user = SqlElement::getSingleSqlElementFromCriteria("User", array("chatIdTelegram"=>$chatId));
				$userId = $user->id;

				$buttons = array();
				foreach ($values as $project) {
					if (in_array($userId, array_column(get_affectations($project), "id"))) {
						$id = $project->id;
						$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["proj"]) && $states["data"]["fields"]["proj"] == $id;

						array_push($buttons, array("text"=>$project->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selProj','id':'$id'}"));
					}
				}

				$markup["inline_keyboard"] = array_chunk($buttons, 2);

			} else if ($cbAction == "ctxt1") {
				$msg = $msgs["ctxt1"];
				$state = 4;
				$states["data"]["field"] = "ctxt1";

				$ctxt1 = new Context1();
				$values = $ctxt1->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "sortOrder ASC");

				$buttons = array();
				foreach ($values as $ctxt) {
					$id = $ctxt->id;
					$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["ctxt1"]) && $states["data"]["fields"]["ctxt1"] == $id;

					array_push($buttons, array("text"=>$ctxt->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selCtxt1','id':'$id'}"));
				}

				$markup["inline_keyboard"] = array_chunk($buttons, 2);

			} else if ($cbAction == "act") {
				$msg = $msgs["act"];
				$state = 4;
				$states["data"]["field"] = "act";
				$sql = new Activity();

				$crit = array("idle"=>"0");

				if (isset($states["data"]["fields"]) and isset($states["data"]["fields"]["proj"])) {
					$crit["idProject"] = $states["data"]["fields"]["proj"];
				}

				$values = $sql->getSqlElementsFromCriteria($crit);

				$buttons = array();
				foreach ($values as $activity) {
					$id = $activity->id;
					$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["act"]) && $states["data"]["fields"]["act"] == $id;

					array_push($buttons, array("text"=>$activity->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selAct','id':'$id'}"));
				}

				$markup["inline_keyboard"] = array_chunk($buttons, 1);
			}

		} else if ($state == 4) {
			if ($cbAction == "selResp") {
				$states["data"]["fields"]["resp"] = $callback["id"];
				choose($msg, $markup, $states, $state, $chatId);

			} else if ($cbAction == "selModel") {
				$states["data"]["fields"]["model"] = $callback["id"];

				$model = new TicketTemplate($callback["id"]);
				if ($model->idTicketType) { $states["data"]["fields"]["type"] = $model->idTicketType; }
				if ($model->idProject) { $states["data"]["fields"]["proj"] = $model->idProject; }
				if ($model->idUrgency) { $states["data"]["fields"]["urge"] = $model->idUrgency; }
				if ($model->idCriticality) { $states["data"]["fields"]["crit"] = $model->idCriticality; }
				if ($model->idContext1) { $states["data"]["fields"]["ctxt1"] = $model->idContext1; }
				if ($model->idContext3) { $states["data"]["fields"]["ctxt3"] = $model->idContext3; }
				if ($model->idActivity) { $states["data"]["fields"]["act"] = $model->idActivity; }
				choose($msg, $markup, $states, $state, $chatId);

			} else if ($cbAction == "selUrge") {
				$states["data"]["fields"]["urge"] = $callback["id"];
				choose($msg, $markup, $states, $state, $chatId);

			} else if ($cbAction == "selCrit") {
				$states["data"]["fields"]["crit"] = $callback["id"];
				choose($msg, $markup, $states, $state, $chatId);

			} else if ($cbAction == "selType") {
				$states["data"]["fields"]["type"] = $callback["id"];
				choose($msg, $markup, $states, $state, $chatId);

			} else if ($cbAction == "selProj") {
				$states["data"]["fields"]["proj"] = $callback["id"];
				choose($msg, $markup, $states, $state, $chatId);

			} else if ($cbAction == "selCtxt1") {
				$states["data"]["fields"]["ctxt1"] = $callback["id"];
				//choose($msg, $markup, $states, $state, $chatId);

				$msg = $msgs["ctxt3"];
				$state = 4;
				$states["data"]["field"] = "ctxt3";

				$ctxt3 = new Context3();
				$values = $ctxt3->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "sortOrder ASC");

				$buttons = array();
				foreach ($values as $ctxt) {
					$id = $ctxt->id;
					$isSet = isset($states["data"]["fields"]) && isset($states["data"]["fields"]["ctxt3"]) && $states["data"]["fields"]["ctxt3"] == $id;

					array_push($buttons, array("text"=>$ctxt->name.($isSet ? " ✅" : ""), "callback_data"=>"{'action':'selCtxt3','id':'$id'}"));
				}

				$markup["inline_keyboard"] = array_chunk($buttons, count($buttons)>3 ? 2 : 3);

			} else if ($cbAction == "selCtxt3") {
				$states["data"]["fields"]["ctxt3"] = $callback["id"];
				choose($msg, $markup, $states, $state, $chatId);

			} else if ($cbAction == "selAct") {
				$states["data"]["fields"]["act"] = $callback["id"];
				choose($msg, $markup, $states, $state, $chatId);
			}

		} else {
			$modify = false;
		}
	}

	$states["state"] = $state;
}

if ($msg != "") {
	$replyMsg = array(
		"chat_id" => $chatId,
		"text" => $msg,
		"parse_mode" => "Markdown"
	);

	if ($markup != array()) {
		$replyMsg["reply_markup"] = json_encode($markup);
	}

	if (!$modify) {
		send_msg($replyMsg);

	} else {
		if (!isset($states["choose_msg_id"])) { $states["choose_msg_id"] = $input["messageId"]; }
		$replyMsg["message_id"] = $states["choose_msg_id"];
		$states["choose_msg_id"] = $input["messageId"];
		send_msg($replyMsg, "editMessageText");

		if ($state == 0) {
			$states = array("state"=>0, "data"=>array());
		}
	}
}

set_states($states, $chatId);
?>
