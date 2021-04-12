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

$TOKEN = "BOT-TOKEN";
$adminChatId = "ADMIN-CHATID";

$setEmoji = " ✅";

$commands = array(
	"create" => "ticket",
	"stop" => "stop",
	"state" => "state",
	"reply" => "reply",
	"callback" => "callback",
	"report" => "report"
);

$msgs = array(
	"stop" => "Création du ticket annulée",
	"noTicket" => "Il n'y a pas de ticket en cours de création",
	"alreadyCreating" => "Vous êtes déjà en train de créer un ticket",
	"name" => "Quel nom voulez-vous donner au ticket ?",
	"nameConfirmation" => "Voulez-vous créer le ticket ':name' ?",
	"confirm" => "Voulez-vous créer ce ticket ?",
	"created" => "*Ticket *[:ref](PROJEQTOR-URL/view/main.php?directAccess=true&objectClass=Ticket&objectId=:id)* créé !*",
	"choose" => "Choisissez un champ à modifier ou enregistrez le ticket",
	"desc" => "Écrivez une description pour le ticket",
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

	"unset" => "Laisser le champ vide",

	"invalid-work" => "Format invalide, référez-vous à l'exemple",

	"reportSent" => "Rapport envoyé à un administrateur",
	"report" => " [###] __*Error Report*__ [###]".
				"\nNom: :firstName :lastName (@:username)".
				"\nChat id: :chatId".
				"\nState:```\n:state\n```"
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

$states = array(
	"Idle",
	"Asking name",
	"Choosing field",
	"Asking for field value (text)",
	"Asking for field value (button)",
	"Waiting for confirmation"
);

$cbActions = array(
	"confirm"=> 5,
	"desc"   => 3,
	"model"  => 4,
	"resp"   => 4,
	"urge"   => 4,
	"crit"   => 4,
	"work"   => 3,
	"type"   => 4,
	"proj"   => 4,
	"ctxt1"  => 4,
	"act"    => 4
);

$fieldOptions = array(
	"urge" => array("class"=>"Urgency", "chunk"=>"2/3"),
	"crit" => array("class"=>"Criticality", "chunk"=>"2/3"),
	"type" => array("class"=>"TicketType", "chunk"=>"2/3"),
	"ctxt1" => array("class"=>"Context1", "chunk"=>"2")
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

	$url = "PROJEQTOR-URL/api/Ticket";

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
	global $TOKEN;

	$url = "https://api.telegram.org/bot$TOKEN/$command";
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

function choose(&$data, &$state) {
	global $msgs, $options, $msg, $markup, $setEmoji;
	$msg = $msgs["choose"];
	$buttons = array();

	foreach($options as $l) {
		$line = array();

		foreach($l as $button) {
			$action = $button["action"];
			$isSet = isset($data["fields"]) && array_key_exists($action, $data["fields"]);

			if ($action == "ctxt1") {
				$isSet = isset($data["fields"]) && (array_key_exists("ctxt1", $data["fields"]) || array_key_exists("ctxt3", $data["fields"]));
			}

			array_push($line, array("text"=>$button["txt"].($isSet ? $setEmoji : ""), "callback_data"=>"{'action': '$action'}"));
		}

		array_push($buttons, $line);
	}

	$markup = array("inline_keyboard"=>$buttons);
	$state = 2;
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

function isFieldSet($data, $id) {
	global $setEmoji;
	$set = isset($data["fields"]) && isset($data["fields"][$data["field"]]) && $data["fields"][$data["field"]] == $id;
	
	return $set ? $setEmoji : "";
}

$input = json_decode(file_get_contents("php://input"),TRUE);

$chatId = $input["chatId"];

$user = SqlElement::getSingleSqlElementFromCriteria("User", array("chatIdTelegram"=>$chatId));
$userId = $user->id;
unset($user);

$msg = "";
$markup = array();
$userVars = get_states();
$modify = false;
$reset = false;

//log_msg(json_encode($input));

if (!array_key_exists($chatId, $userVars)) {
	$userVars = array("state" => 0, "data" => array());
} else {
	$userVars = $userVars[$chatId];
}

if (isset($input["action"])) {
	$action = $input["action"];

	$state = &$userVars["state"];
	$data = &$userVars["data"];

	switch ($action) {
		case $commands["create"]: {
			if ($state == 0) {
				$msg = $msgs["name"];
				$userVars["data"] = array();

				$state = 1;
			} else {
				$msg = $msgs["alreadyCreating"];
			}
			break;

		}
		case $commands["stop"]: {
			if ($state != 0) {
				if (isset($userVars["choose_msg_id"])) {
					$editMsg = array("chat_id"=>$chatId,"message_id"=>$userVars["choose_msg_id"],"text"=>"_Création annulée, choix indisponible_", "parse_mode"=>"Markdown");

					send_msg($editMsg, "editMessageText");
				}

				$msg = $msgs["stop"];
				$reset = true;

			} else {
				$msg = $msgs["noTicket"];
			}
			break;

		}
		case $commands["state"]: {
			$msg = $states[$state]."\n\n";
			$msg .= "State: ".json_encode($userVars, JSON_PRETTY_PRINT);

			break;
		}
		case $commands["reply"]: {
			//Check if not command /reply
			if (strpos($input["content"], "/") !== 0) {
				switch ($state) {
					case 1: {
						$state = 2;
						$data["name"] = $input["content"];
						choose($data, $state);
						break;
					}

					case 3: {
						unset($userVars["choose_msg_id"]);

						$field = $data["field"];
						$valid = true;
						if (isset($verifyFunctions[$field])) {
							$valid = $verifyFunctions[$field]($input["content"]);
						}

						if ($valid) {
							$data["fields"][$field] = $input["content"];
							choose($data, $state);

						} else {
							$msg = $msgs["invalid-$field"];
						}
						break;
					}
				}
			}

			break;
		}

		case $commands["callback"]: {
			//Check if not command /callback
			if (strpos($input["content"], "/") !== 0) {
				//Is json
				if ($input["content"][0] == "{") {
					$callback = json_decode(str_replace("'", '"', $input["content"]), TRUE);

				} else {
					$callback = uncsv($input["content"]);
				}

				$cbAction = $callback["action"];

				$cbExtra = array_key_exists("extra", $callback) ? $callback["extra"] : null;

				send_msg(array("callback_query_id"=>$input["callbackQueryId"]), "answerCallbackQuery");

				$modify = true;

				if ($cbExtra === "unset") {
					$field = str_replace("sel", "", $cbAction);
					$field = strtolower($field);
					if (isset($data["fields"]) and array_key_exists($field, $data["fields"])) {
						unset($data["fields"][$field]);
					}
				}

				switch ($state) {
					case 5: {
						if ($cbAction == "create") {
							$ticketData = get_all_data($chatId, $userVars);

							$ticketData["idUser"] = $userId;

							if ( isset($ticketData["idCriticality"])
								 and isset($ticketData["idUrgency"]) ) {

								$crit = new Criticality($ticketData["idCriticality"]);
								$urge = new Urgency($ticketData["idUrgency"]);

								$priorityValue = round($urge->value * $crit->value / 2);
								$sql = new Priority();
								$priority = $sql->getSqlElementsFromCriteria(null, false, "value <= $priorityValue");

								usort($priority, function ($a, $b) { return $a->value <=> $b->value; });
								
								if (count($priority)) {
									$priority = end($priority);
									$ticketData["idPriority"] = $priority->id;
								}
							}

							if (isset($ticketData["plannedWork"])) {
								$ticketData["plannedWork"] = Work::convertImputation($ticketData["plannedWork"]);
							}

							$result = insert_projeqtor($ticketData)["items"][0];

							//log_msg(json_encode($result));

							//Extract id from result msg
							$ticketData["id"] = explode(" ", explode("#", $result["apiResultMessage"])[1])[0];
							$ticketData["reference"] = $result["reference"];

							if (isset($ticketData["plannedWork"])) {
								$ticketData["plannedWork"] = Work::displayImputation($ticketData["plannedWork"]);
							}

							$msg = get_summary($ticketData);
							send_msg(
								array(
									"chat_id" => $chatId,
									"text" => str_replace(array(":ref", ":id"), array($ticketData["reference"], $ticketData["id"]), $msgs["created"]),
									"parse_mode" => "Markdown"
								)
							);

							$reset = true;

						} else if ($cbAction == "modify") {
							choose($data, $state);
						}
						break;
					}
					case 2: {
						if (array_key_exists($cbAction, $cbActions)) {
							$msg = $msgs[$cbAction];
							$data["field"] = $cbAction;
							$state = $cbActions[$cbAction];
						}
						switch ($cbAction) {
							case "confirm": {
								$ticketData = get_all_data($chatId, $userVars);

								$msg .= "\n\n";
								$msg .= get_summary($ticketData);

								$markup["inline_keyboard"] = array(array(
									array("text"=>"Modifier", "callback_data"=>"{'action':'modify'}"),
									array("text"=>"Valider", "callback_data"=>"{'action':'create'}")
								));

								break;
							}
							case "model": {
								$sql = new TicketTemplate();
								$models = $sql->getSqlElementsFromCriteria(array("idle"=>"0"));

								$buttons = array();
								foreach ($models as $model) {
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
										array_push($buttons, array("text"=>$model->name.isFieldSet($data, $id), "callback_data"=>"{'action':'selModel','id':'$id'}"));
									}
								}

								$markup["inline_keyboard"] = array_chunk($buttons, 2);

								break;
							}
							case "resp": {
								if (isset($data["fields"]) and isset($data["fields"]["proj"])) {
									$projId = $data["fields"]["proj"];
									$proj = new Project($projId);

									$rows = get_affectations($proj);

									$names = array_column($rows, "name");
									$order = array_column($rows, "order");

									array_multisort($order, SORT_ASC, $names, SORT_ASC, $rows);

									$users = array();
									foreach ($rows as $row) {
										$users[] = new User($row["id"]);
									}

								} else {
									$sql = new User();
									$users = $sql->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "fullName ASC");
								}

								$buttons = array();
								foreach ($users as $resource) {
									$id = $resource->id;

									array_push($buttons, array("text"=>$resource->resourceName.isFieldSet($data, $id), "callback_data"=>"{'action':'selResp','id':'$id'}"));
								}

								$markup["inline_keyboard"] = array_chunk($buttons, 2);

								break;
							}
							case "proj": {
								$sql = new Project();
								$projects = $sql->getSqlElementsFromCriteria(array("idle"=>"0"));

								usort($projects, function ($pA, $pB) {
									$planningA = SqlElement::getSingleSqlElementFromCriteria("PlanningElement", array("idProject"=>$pA->id, "refId"=>$pA->id));
									$planningB = SqlElement::getSingleSqlElementFromCriteria("PlanningElement", array("idProject"=>$pB->id, "refId"=>$pB->id));

									return $planningA->wbsSortable <=> $planningB->wbsSortable;
								});

								$buttons = array();
								foreach ($projects as $project) {
									//Check if user has access to project
									if (in_array($userId, array_column(get_affectations($project), "id"))) {
										$id = $project->id;

										array_push($buttons, array("text"=>$project->name.isFieldSet($data, $id), "callback_data"=>"{'action':'selProj','id':'$id'}"));
									}
								}

								$markup["inline_keyboard"] = array_chunk($buttons, 2);

								break;
							}
							case "act": {
								$sql = new Activity();

								$crit = array("idle"=>"0");

								if (isset($data["fields"]) and isset($data["fields"]["proj"])) {
									$crit["idProject"] = $data["fields"]["proj"];
								}

								$activities = $sql->getSqlElementsFromCriteria($crit);

								$buttons = array();
								foreach ($activities as $activity) {
									$id = $activity->id;

									array_push($buttons, array("text"=>$activity->name.isFieldSet($data, $id), "callback_data"=>"{'action':'selAct','id':'$id'}"));
								}

								$markup["inline_keyboard"] = array_chunk($buttons, 1);
								break;
							}

							case "urge":
							case "crit":
							case "type":
							case "ctxt1": {
								$sql = new $fieldOptions[$cbAction]["class"]();
								$options = $sql->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "sortOrder ASC");

								$buttons = array();
								foreach ($options as $option) {
									$id = $option->id;

									array_push($buttons, array("text"=>$option->name.isFieldSet($data, $id), "callback_data"=>"{'action':'sel".ucfirst($cbAction)."','id':'$id'}"));
								}

								switch ($fieldOptions[$cbAction]["chunk"]) {
									case "2/3":
										$chunk = count($buttons)>3 ? 2 : 3;
										break;

									default:
										$chunk = $fieldOptions[$cbAction]["chunk"];
										break;
								}
								$markup["inline_keyboard"] = array_chunk($buttons, $chunk);
								
								if ($cbAction == "ctxt1") {
									$markup["inline_keyboard"][] = array(array("text"=>$msgs["unset"], "callback_data"=>"{'action':'selCtxt1','extra':'unset'}"));
								}

								break;
							}
						}
						break;
					}
					case 4: {
						switch ($cbAction) {
							case "selResp":
							case "selUrge":
							case "selCrit":
							case "selType":
							case "selProj":
							case "selCtxt3":
							case "selAct": {
								if ($cbExtra !== "unset") {
									$data["fields"][$data["field"]] = $callback["id"];
								}
								choose($data, $state);

								break;
							}
							case "selModel": {
								$data["fields"]["model"] = $callback["id"];

								$model = new TicketTemplate($callback["id"]);
								if ($model->idTicketType) { $data["fields"]["type"] = $model->idTicketType; }
								if ($model->idProject) { $data["fields"]["proj"] = $model->idProject; }
								if ($model->idUrgency) { $data["fields"]["urge"] = $model->idUrgency; }
								if ($model->idCriticality) { $data["fields"]["crit"] = $model->idCriticality; }
								if ($model->idContext1) { $data["fields"]["ctxt1"] = $model->idContext1; }
								if ($model->idContext3) { $data["fields"]["ctxt3"] = $model->idContext3; }
								if ($model->idActivity) { $data["fields"]["act"] = $model->idActivity; }
								choose($data, $state);

								break;
							}
							case "selCtxt1": {
								if ($cbExtra !== "unset") {
									$data["fields"]["ctxt1"] = $callback["id"];
								}

								$msg = $msgs["ctxt3"];
								$state = 4;
								$data["field"] = "ctxt3";

								$sql = new Context3();
								$ctxts = $sql->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "sortOrder ASC");

								$buttons = array();
								foreach ($ctxts as $ctxt) {
									$id = $ctxt->id;

									array_push($buttons, array("text"=>$ctxt->name.isFieldSet($data, $id), "callback_data"=>"{'action':'selCtxt3','id':'$id'}"));
								}

								$markup["inline_keyboard"] = array_chunk($buttons, count($buttons)>3 ? 2 : 3);

								$markup["inline_keyboard"][] = array(array("text"=>$msgs["unset"], "callback_data"=>"{'action':'selCtxt3','extra':'unset'}"));

								break;
							}
						}
						break;
					}
					default: {
						$modify = false;
					}
				}
			}
			break;
		}
		case $commands["report"]: {
			$chatInfo = send_msg(array("chat_id"=>$chatId), "getChat");
			$chatInfo = json_decode($chatInfo, true)["result"];

			$report = str_replace(
				array(":firstName", ":lastName", ":username", ":chatId", ":state"),
				array($chatInfo["first_name"], $chatInfo["last_name"], $chatInfo["username"], $chatId, json_encode($userVars, JSON_PRETTY_PRINT)),
				$msgs["report"]
			);

			send_msg(
				array(
					"chat_id" => $adminChatId,
					"text" => $report,
					"parse_mode" => "Markdown"
				)
			);

			$msg = $msgs["reportSent"];

			break;
		}
		default: {
			break;
		}
	}
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
		if (!isset($userVars["choose_msg_id"])) { $userVars["choose_msg_id"] = $input["messageId"]; }
		$replyMsg["message_id"] = $userVars["choose_msg_id"];
		$userVars["choose_msg_id"] = $input["messageId"];
		send_msg($replyMsg, "editMessageText");
	}
}

if ($reset) {
	$userVars = array("state"=>0, "data"=>array());
}

set_states($userVars, $chatId);
?>
