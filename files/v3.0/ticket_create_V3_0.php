<?php
/*** COPYRIGHT NOTICE *********************************************************
    ProjeQtOr Telegram Bot
    Copyright (C) 2021  HERDAC - Louis HEREDERO - baryhobal@herdac.ch

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published
    by the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
    
*** DO NOT REMOVE THIS NOTICE ************************************************/



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
$VERSION = "V3.0";
$adminChatId = "ADMIN-CHATID";

$setEmoji = " ✅";

$commands = array(
	"create" => "creer",
	"stop" => "stop",
	"state" => "state",
	"reply" => "reply",
	"callback" => "callback",
	"report" => "report",
	"display" => "afficher",
	"about" => "about"
);

$msgs = array(
	"noCommand" => "Il n'y a pas de commande en cours d'exécution",
	"alreadyCreating" => "Vous êtes déjà en train de créer un ticket\n/stop pour arrêter",
	"alreadyDisplaying" => "Vous êtes déjà en train d'afficher un élément\n/stop pour arrêter",
	"confirm" => "Voulez-vous créer ce ticket ?",
	"confirmQuestion" => "Voulez-vous créer cette question ?",

	"chooseCreate" => "Que voulez-vous créer ?",
	"nameTicket" => "Quel nom voulez-vous donner au ticket ?",
	"nameQuestion" => "Quel nom voulez-vous donner à votre question ?",
	"created" => "*:tradclass *[:ref](PROJEQTOR-URL/view/main.php?directAccess=true&objectClass=:class&objectId=:id)* créé(e) !*",
	"choose" => "Choisissez un champ à modifier ou enregistrez l'élément",

	"desc" => "Écrivez une description",
	"model" => "Choisissez un modèle",
	"resp" => "Choisissez un responsable",
	"urge" => "Choisissez un niveau d'urgence",
	"crit" => "Choisissez un niveau de criticité",
	"work" => "Définissez un nombre d'heures estimée de travail (p. ex. 3.5)",
	"type" => "Choisissez un type",
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
				"\nState:```\n:state\n```",

	"chooseCreateProject" => "Choisissez un projet:project",

	"chooseDisplayClass" => "Choisissez une classe",
	"chooseDisplayProject" => "Choisissez un projet:project",
	"chooseDisplayElement" => "Choisissez un élément",
	"noProjectsAccess" => "Vous n'avez accès à aucun projet",
	"noProjects" => "Vous n'avez aucun projet ayant des :classs:project",
	"noSubprojects" => "Vous n'avez aucun sous-projet ayant des :classs:project",

	"about" => "*Bot Telegram pour ProjeQtOr (HERDAC)*".
			 "\n".
			 "\n__*$VERSION*__ ([Changelog](https://github.com/HERDAC/ProjeqtorTelegramBot/blob/main/CHANGELOG.md))".
			 "\n".
			 "\nCréateur:".
			 "\n        @Baryhobal".
			 "\n        baryhobal@herdac.ch",

	"unavailable-create" => "_Création annulée, message indisponible_",
	"unavailable-display" => "_Affichage terminé, message indisponible_",
	"stop-create" => "Création annulée",
	"stop-display" => "Affichage de l'élément terminé",

	"stopBeforeAnswer" => "Vous êtes déjà en train d'exécuter une commande\n/stop pour arrêter",
	"answer" => "Écrivez votre réponse",
	"answerAdd" => "Écrivez votre complément",
	"answerFinalize" => "Que voulez-vous faire ?",
	"savedAnswer" => "Réponse enregistrée",
	"sentAnswer" => "Réponse envoyée",
	"cantSendQuestion" => "Votre question a été assignée mais n'a pas pu être envoyée directement au responsable",
	"assignedQuestion" => "*La question *[:ref](PROJEQTOR-URL/view/main.php?directAccess=true&objectClass=Question&objectId=:id)* vous a été assignée !*"
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
	"Ticket" => array(
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
	),
	"Question" => array(
		"desc" => "description",
		"type" => "idQuestionType",
		"proj" => "idProject",
		"resp" => "idResource",
	)
);

$defaultFields = array(
	"Ticket" => array(
		"description" => "",
		"idTicketType"=>209,
		"idProject"=>16,
		"idStatus"=>1
	),

	"Question" => array(
		"description" => "",
		"idQuestionType"=>213,
		"idStatus"=>1,
		"creationDate"=>date ( 'Y-m-d' )
	)
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

$elementInfo = array(
	"Ticket" => array(
		"name" => array("text"=>"*Nom*: `:field`"),
		"description" => array("text"=>"\n*Description*: ```\n:field\n```"),
		"idTicketType" => array("text"=>"*Type*: `:field`", "class"=>"Type"),
		"idProject" => array("text"=>"*Projet*: `:field`", "class"=>"Project"),
		"idUrgency" => array("text"=>"*Urgence*: `:field`", "class"=>"Urgency"),
		"idCriticality" => array("text"=>"*Criticité*: `:field`", "class"=>"Criticality"),
		"idContext" => array("text"=>"*Contexte*: `:field1` — `:field2`", "class"=>"Context"),
		"idActivity" => array("text"=>"*Activité*: `:field`", "class"=>"Activity"),
		"idResource" => array("text"=>"*Responsable*: `:field`", "class"=>"Resource"),
		"work" => array("text"=>"*Travail*: `:field1h - :field2h = :field3h`"),
		"idStatus" => array("text"=>"*État*: `:field`", "class"=>"Status")
	),
	
	"Activity" => array(
		"name" => array("text"=>"*Nom*: `:field`"),
		"description" => array("text"=>"\n*Description*: ```\n:field\n```"),
		"idActivityType" => array("text"=>"*Type*: `:field`", "class"=>"Type"),
		"idProject" => array("text"=>"*Projet*: `:field`", "class"=>"Project"),
		"idResource" => array("text"=>"*Responsable*: `:field`", "class"=>"Resource"),
		"work" => array("text"=>"*Travail*: `:field1h - :field2h = :field3h`"),
		"idComponent" => array("text"=>"*Composant*: `:field`", "class"=>"Component"),
		"idProduct" => array("text"=>"*Produit*: `:field`", "class"=>"Product"),
		"idStatus" => array("text"=>"*État*: `:field`", "class"=>"Status")
	),

	"Question" => array(
		"name" => array("text"=>"*Nom*: `:field`"),
		"description" => array("text"=>"\n*Description*: ```\n:field\n```"),
		"idQuestionType" => array("text"=>"*Type*: `:field`", "class"=>"Type"),
		"idResource" => array("text"=>"*Responsable*: `:field`", "class"=>"Resource"),
		"result" => array("text"=>"\n*Réponse*: ```\n:field\n```"),
		"idStatus" => array("text"=>"*État*: `:field`", "class"=>"Status")
	)
);

$createClasses = array(
	"Ticket",
	"Question"
);

$displayClasses = array(
	"Ticket",
	"Activity"
);

$verifyFunctions = array(
	"work" => function ($value) {
		return preg_match('/^\d+($|(\.\d+$))/', $value);
	}
);

$states = array(
	0 => "Idle",
	1 => "Asking create",
	10 => "Asking name",
	11 => "(Question) Asking project",
	20 => "Choosing field",
	30 => "Asking for field value (text)",
	40 => "Asking for field value (button)",
	50 => "Waiting for confirmation",
	100 => "Choosing display class",
	110 => "Choosing display project",
	120 => "Choosing display ticket",
	130 => "Displaying",
	200 => "Answering",
	210 => "Finalizing answer"
);

$cbActions = array(
	"confirm"=> 50,
	"desc"   => 30,
	"model"  => 40,
	"resp"   => 40,
	"urge"   => 40,
	"crit"   => 40,
	"work"   => 30,
	"type"   => 40,
	"proj"   => 40,
	"ctxt1"  => 40,
	"act"    => 40
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

function isVisibleProject($id) {
	global $userId;
	$user = new User($userId);

	$visible = $user->getVisibleProjects();

	return array_key_exists($id, $visible);
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

function chooseCreate(&$data, &$state) {
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
	$state = 20;
}

function chooseDisplay(&$data, &$state, $first=false, $subProjects=true) {
	global $msgs, $displayClasses, $msg, $markup, $modify;

	if (!$first) {
		$modify = true;
	}

	$buttons = array();

	//Choice of class
	if ($state == 0) {
		$state = 100;

		$msg = $msgs["chooseDisplayClass"];
		$data["displayPath"] = array();

		foreach ($displayClasses as $className) {
			$buttons[] = array("text"=>i18n($className), "callback_data"=>"{'action':'displayClass','class':'$className'}");
		}
		$buttons = array_chunk($buttons, 2);

	//Choice of project
	} else if ($state == 100 or $state == 110) {
		$state = 110;

		$path = $data["displayPath"];

		$projects = getProjectsFromPath($path);


		if (count($projects) == 0) {
			$m = count($path) == 1 ? "noProjects" : "noSubprojects";
			$msg = str_replace(":class", strtolower(i18n($path[0])), $msgs[$m]);

		} else {
			$msg = $msgs["chooseDisplayProject"];
			
			foreach ($projects as $project) {
				$id = $project->id;
				$buttons[] = array("text"=>$project->name, "callback_data"=>"{'action':'displayProj','id':'$id'}");
			}

		}

		if (count($path) == 1) {
			$msg = str_replace(":project", "", $msg);
		
		} else {
			$proj = new Project(array_pop($path));
			$msg = str_replace(":project", "\nProjet actuel: *".$proj->name."*", $msg);
		}

		$buttons = array_chunk($buttons, 1);
		$extraButtons = array();

		//If selected at least one project
		if (count($data["displayPath"]) > 1) {
			//TODO: check if access to project

			$extraButtons[] = array(
				array("text"=>"Choisir","callback_data"=>"{'action':'choose'}"),
				array("text"=>"Choisir (+sous-projets)","callback_data"=>"{'action':'chooseSub'}")
			);
		}

		$extraButtons[] = array(array("text"=>"Retour","callback_data"=>"{'action':'return'}"));

		$buttons = array_merge($buttons, $extraButtons);
	
	//Choice of element
	} else if ($state == 120) {
		$path = $data["displayPath"];
		$class = $path[0];

		$msg = $msgs["chooseDisplayElement"];
		$elements = getElementsFromProject($class, array_pop($path), $subProjects);

		$rows = array();

		foreach ($elements as $element) {
			$statusOrder = 0;
			$prioOrder = 0;

			if (isset($element->idStatus)) {
				$status = new Status($element->idStatus);
				$statusOrder = $status->sortOrder;
			}
			if (isset($element->idPriority)) {
				$prio = new Priority($element->idPriority);
				$prioOrder = $prio->sortOrder;
			}
			$rows[] = array("element"=>$element, "statusOrder"=>$statusOrder, "prioOrder"=>$prioOrder, "id"=>$element->id);
		}

		$statuses = array_column($rows, "statusOrder");
		$priorities = array_column($rows, "prioOrder");
		$ids = array_column($rows, "id");

		array_multisort($statuses, SORT_ASC, $priorities, SORT_ASC, $ids, SORT_ASC, $rows);

		$elements = array_column($rows, "element");

		foreach ($elements as $element) {
			$id = $element->id;
			$emojis = "";
			if (isset($element->idStatus)) {
				$stat = new Status($element->idStatus);
				if (isset($stat->tgEmoji) and $stat->tgEmoji != "") {
					$emojis .= " ".$stat->tgEmoji;
				}
			}
			if (isset($element->idPriority)) {
				$prio = new Priority($element->idPriority);
				if (isset($prio->tgEmoji) and $prio->tgEmoji != "") {
					$emojis .= " ".$prio->tgEmoji;
				}
			}
			$buttons[] = array("text"=>$element->name.$emojis, "callback_data"=>"{'action':'displayElement','id':'$id'}");
		}

		$buttons = array_chunk($buttons, 1);
		$buttons[] = array(
			array("text"=>"Retour","callback_data"=>"{'action':'return'}")
		);
	}


	$markup = array("inline_keyboard"=>$buttons);
}

function getProjectsFromPath($path, $checkElements=true) {
	global $userId;

	if (count($path) == 1) {
		$crit = array("idle"=>"0", "idProject"=>null);
	
	} else {
		$crit = array("idle"=>"0", "idProject"=>end($path));
	}

	$sql = new Project();

	$projects = $sql->getSqlElementsFromCriteria($crit);
	
	$withElements = array();

	foreach ($projects as $project) {
		if (isVisibleProject($project->id) and hasAffectedSubProjects($project->id)) {
			if (!$checkElements or count(getElementsFromProject($path[0], $project->id)) > 0) {
				$withElements[] = $project;
			}
		}
	}

	return $withElements;
}

function hasAffectedSubProjects($projectId) {
	global $userId;

	$user = new User($userId);
	$affectedProjects = $user->getAffectedProjects();

	$project = new Project($projectId);
	$projectIds = array_keys($project->getRecursiveSubProjectsFlatList(true, true));

	$hasAffectedSubProjects = false;

	foreach ($projectIds as $id) {
		if (isVisibleProject($id)) {
			if (array_key_exists($id, $affectedProjects)) {
				$hasAffectedSubProjects = true;
				break;
			}
		}
	}

	return $hasAffectedSubProjects;
}

function getElementsFromProject($class, $id, $includeSubProjects=true) {
	if ($includeSubProjects) {
		$project = new Project($id);
		$projectIds = array_keys($project->getRecursiveSubProjectsFlatList(true, true));
	
	} else {
		$projectIds = array($id);
	}

	$elements = array();

	$sql = new $class();

	foreach ($projectIds as $projectId) {
		if (isVisibleProject($projectId)) {
			$result = $sql->getSqlElementsFromCriteria(array("idle"=>"0", "idProject"=>$projectId));

			$elements = array_merge($elements, $result);
		}
	}

	return $elements;
}

function chooseQuestionProject(&$data, &$state, $first=false, $skip=true) {
	global $msgs, $userVars, $msg, $markup, $modify, $userId;

	if (!$first) {
		$modify = true;
	}

	$buttons = array();

	if (isset($userVars["createPath"]) ) {
		$data["createPath"] = $userVars["createPath"];
	}

	//Choice of project
	if ($state == 11) {
		if (!isset($data["createPath"]) or count($data["createPath"]) == 0) {
			$data["createPath"] = array("Question");
			$userVars["createPath"] = $data["createPath"];
		}
		$path = $data["createPath"];

		$projectId = end($path);


		$user = new User($userId);

		$affectedProjects = $user->getAffectedProjects();


		$projects = getProjectsFromPath($path, false);

		if (count($projects) == 0 and count($path) <= 1) {
			$msg = $msgs["noProjectsAccess"];

		//Skip if only one project and can't choose current project
		} else if ($skip and count($projects) == 1 and !array_key_exists($projectId, $affectedProjects)) {
			$data["createPath"][] = $projects[0]->id;
			$userVars["createPath"] = $data["createPath"];
			chooseQuestionProject($data, $state, $first, $skip);
			return;

		} else {
			$msg = $msgs["chooseCreateProject"];
			
			foreach ($projects as $project) {
				$id = $project->id;
				$buttons[] = array("text"=>$project->name, "callback_data"=>"{'action':'createProj','id':'$id'}");
			}

		}

		if (count($path) == 1) {
			$msg = str_replace(":project", "", $msg);
		
		} else {
			$proj = new Project($projectId);
			$msg = str_replace(":project", "\nProjet actuel: *".$proj->name."*", $msg);
		}

		$buttons = array_chunk($buttons, 1);
		$extraButtons = array();

		//If selected at least one project
		if (count($data["createPath"]) > 1) {
			//TODO: check if access to project

			$extraButtons[] = array("text"=>"Retour","callback_data"=>"{'action':'return'}");

			if ( array_key_exists($projectId, $affectedProjects)) {
				$extraButtons[] = array("text"=>"Choisir","callback_data"=>"{'action':'choose'}");
			}
		}

		$buttons[] = $extraButtons;
	
	}

	$markup = array("inline_keyboard"=>$buttons);
}

function createQuestion(&$data, &$state) {
	global $msgs, $msg, $markup;
	$state = 50;
	$elementData = get_all_data($data);

	$msg = $msgs["confirmQuestion"];
	$msg .= "\n\n";
	$msg .= get_summary($elementData);

	$markup["inline_keyboard"] = array(array(
		array("text"=>"Abandonner", "callback_data"=>"{'action':'stop'}"),
		array("text"=>"Valider", "callback_data"=>"{'action':'create'}")
	));

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

function get_all_data($data) {
	global $fields, $defaultFields;

	$availableFields = $fields[$data["createClass"]];

	$elementData = $defaultFields[$data["createClass"]];
	$elementData["name"] = $data["name"];

	if (isset($data["fields"])) {
		foreach ($data["fields"] as $field => $value) {
			if (array_key_exists($field, $availableFields)) {
				$elementData[$availableFields[$field]] = $value;
			}
		}
	}

	return $elementData;
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

function displayElement($id, $class, $customButtons=false) {
	global $elementInfo, $msg, $markup;

	$result = "";

	$lines = array();

	$element = new $class($id);

	$info = $elementInfo[$class];

	foreach ($info as $id => $values) {

		if ($id == "idContext" and (isset($element->idContext1) or isset($element->idContext3) )) {
			$ctxt1 = "X";
			$ctxt3 = "X";
			if (isset($element->idContext1)) {
				$objId = $element->idContext1;
				$ctxt = new $values["class"]($objId);
				$ctxt1 = $ctxt->name;
			}
			if (isset($element->idContext3)) {
				$objId = $element->idContext3;
				$ctxt = new $values["class"]($objId);
				$ctxt3 = $ctxt->name;
			}

			array_push($lines, str_replace(array(":field1", ":field2"), array($ctxt1, $ctxt3), $values["text"]));

		} else if ($id == "work" and isset($element->WorkElement)) {
			$workelement = $element->WorkElement;
			if (isset($workelement->plannedWork) or isset($workelement->realWork) or isset($workelement->leftWork) ) {
				$planned = 0;
				$real = 0;
				$left = 0;

				if (isset($workelement->plannedWork)) {
					$planned = $workelement->plannedWork;
				}
				if (isset($workelement->realWork)) {
					$real = $workelement->realWork;
				}
				if (isset($workelement->leftWork)) {
					$left = $workelement->leftWork;
				}

				$planned = Work::displayImputation($planned);
				$real = Work::displayImputation($real);
				$left = Work::displayImputation($left);

				array_push($lines, str_replace(array(":field1", ":field2", ":field3"), array($planned, $real, $left), $values["text"]));
			}

		} else {
			if (isset($element->$id)) {
				$field = $element->$id;
				if (isset($values["class"])) {
					$objId = $element->$id;
					$obj = new $values["class"]($objId);
					$field = $obj->name;
				}

				if ($id == "description" or $id == "result") {
					$field = html_entity_decode(strip_tags($field), ENT_COMPAT|ENT_QUOTES, 'UTF-8');
				}

				array_push($lines, str_replace(":field", $field, $values["text"]));
			}
		}
	}

	$result .= implode("\n", $lines);

	if (!$customButtons) {
		$buttons = array(
			array("text"=>"Retour","callback_data"=>"{'action':'return'}")
		);

		if (isset($element->idStatus) and $element->idStatus == 1) {
			$buttons[] = array("text"=>"Assigner","callback_data"=>"{'action':'assign'}");
		}

		if (isset($element->WorkElement) and isset($element->idStatus) and in_array($element->idStatus, array(10, 3))) {
			$state = $element->WorkElement->ongoing;
			
			if ($state == 0) {
				$buttons[] = array("text"=>"Commencer le travail", "callback_data"=>"{'action':'startWork'}");

			} else if ($state == 1) {
				$buttons[] = array("text"=>"Arrêter le travail", "callback_data"=>"{'action':'stopWork'}");
			}
		}
		
		$msg = $result;
		$markup = array("inline_keyboard"=>array($buttons));
	
	} else {
		return $result;
	}

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
$replyToMsg = false;
$dontChangeChooseMsgId = false;

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
			if ($state > 0) {
				if ($state >= 100 and $state < 200) {

					if (isset($userVars["choose_msg_id"])) {
						$editMsg = array("chat_id"=>$chatId,"message_id"=>$userVars["choose_msg_id"],"text"=>$msgs["unavailable-display"], "parse_mode"=>"Markdown");

						send_msg($editMsg, "editMessageText");
					}

					$state = 0;
					$data = array();
				}
			}

			if ($state == 0) {
				$msg = $msgs["chooseCreate"];
				$state = 1;

				$buttons = array();

				foreach ($createClasses as $class) {
					$buttons[] = array("text"=>i18n($class), "callback_data"=>"{'action':'chooseCreate','class':'$class'}");
				}

				$markup["inline_keyboard"] = array_chunk($buttons, count($buttons) > 3 ? 2 : 3);

			} else {
				$msg = $msgs["alreadyCreating"];
				$replyToMsg = true;
				$dontChangeChooseMsgId = true;
			}
			break;

		}
		case $commands["stop"]: {
			if ($state != 0) {
				if (isset($userVars["choose_msg_id"])) {
					$editMsg = array("chat_id"=>$chatId,"message_id"=>$userVars["choose_msg_id"],"text"=>$msgs["unavailable-".(($state>=100 and $state < 200) ? "display" : "create")], "parse_mode"=>"Markdown");

					send_msg($editMsg, "editMessageText");
				}

				$msg = $msgs["stop-".(($state>=100 and $state < 200) ? "display" : "create")];
				$reset = true;

			} else {
				$msg = $msgs["noCommand"];
			}
			break;

		}
		case $commands["state"]: {
			$msg = $states[$state]."\n\n";
			$msg .= "State: ".json_encode($userVars, JSON_PRETTY_PRINT);

			$dontChangeChooseMsgId = true;
			break;
		}
		case $commands["reply"]: {
			//Check if not command /reply
			if (strpos($input["content"], "/") !== 0) {
				switch ($state) {
					case 10: {
						$data["name"] = $input["content"];
						if (isset($data["createClass"]) and $data["createClass"] == "Question") {
							$state = 11;
							chooseQuestionProject($data, $state, true);

						} else {
							$state = 20;
							chooseCreate($data, $state);
						}
						break;
					}

					case 30: {
						unset($userVars["choose_msg_id"]);

						$field = $data["field"];
						$valid = true;
						if (isset($verifyFunctions[$field])) {
							$valid = $verifyFunctions[$field]($input["content"]);
						}

						if ($valid) {
							$data["fields"][$field] = $input["content"];
							
							if (isset($data["createClass"]) and $data["createClass"] == "Question") {
								$state = 40;

								$data["field"] = "resp";
								$msg = $msgs["resp"];

								//Should normally be this case
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

								} else { //In case of error or anything else idk
									$sql = new User();
									$users = $sql->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "fullName ASC");
								}

								$buttons = array();
								foreach ($users as $resource) {
									$id = $resource->id;

									array_push($buttons, array("text"=>$resource->resourceName.isFieldSet($data, $id), "callback_data"=>"{'action':'selResp','id':'$id'}"));
								}

								$markup["inline_keyboard"] = array_chunk($buttons, 2);

							} else {
								chooseCreate($data, $state);
							}

						} else {
							$msg = $msgs["invalid-$field"];
						}
						break;
					}
					case 200: {
						$data["answer"] = $input["content"];
						$state = 210;
						$msg = $msgs["answerFinalize"];
						$markup["inline_keyboard"] = array(
							array(
								array("text"=>"Sauvegarder","callback_data"=>"{'action':'saveAnswer'}"),
								array("text"=>"Envoyer","callback_data"=>"{'action':'sendAnswer'}")
							)
						);
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

				if ($cbAction == "stop") {
					if ($state != 0) {
						if (isset($userVars["choose_msg_id"])) {
							$editMsg = array("chat_id"=>$chatId,"message_id"=>$userVars["choose_msg_id"],"text"=>$msgs["unavailable-".($state>=100 ? "display" : "create")], "parse_mode"=>"Markdown");

							send_msg($editMsg, "editMessageText");
						}

						$msg = $msgs["stop-".($state>=100 ? "display" : "create")];
						$reset = true;

					} else {
						$msg = $msgs["noCommand"];
					}
					$modify = false;
					break;
				
				} else if ($cbAction == "assignquestion") {
					$id = $callback["id"];

					$question = new Question($id);

					if (isset($question->idStatus) and $question->idStatus == 1) { //Double check status to prevent hack or bug
						$question->idStatus = 10;
						$question->save();

						$editMsg = array("chat_id"=>$chatId,"message_id"=>$input["messageId"]);

						send_msg($editMsg, "editMessageReplyMarkup");

						//Send question to responsible
						$respId = $question->idResource;
						$resp = new User($respId);
						if (isset($resp->chatIdTelegram)) {
							$chatIdResp = $resp->chatIdTelegram;
							$text = str_replace(array(":ref",":id"), array($question->reference, $id), $msgs["assignedQuestion"]);
							$text .= "\n\n";
							$text .= displayElement($id, "Question", true);
							$notif = array(
								"chat_id" => $chatIdResp,
								"text" => $text,
								"reply_markup" => json_encode(array(
									"inline_keyboard" => array(
										array(
											array("text"=>"Répondre","callback_data"=>"{'action':'answer','id':$id}")
										)
									))
								),
								"parse_mode" => "Markdown"
							);
							send_msg($notif);
						} else {
							send_msg(
								array(
									"chat_id" => $chatId,
									"text" => $msgs["cantSendQuestion"],
									"parse_mode" => "Markdown"
								)
							);
						}
					}
					break;
				
				} else if ($cbAction == "answer") {
					$id = $callback["id"];
					if ($state > 0) {
						$msg = $msgs["stopBeforeAnswer"];
					
					} else {
						$question = new Question($id);
						$question->idStatus = 3;
						$question->save();

						$msg = $msgs["answer"];
						$state = 200;
						$data["answerId"] = $id;

						$editMsg = array("chat_id"=>$chatId,"message_id"=>$input["messageId"]);

						send_msg($editMsg, "editMessageReplyMarkup");

						$userVars["choose_msg_id"] = $input["messageId"];
						$modify = false;
						$replyToMsg = true;
					}

					break;
				}

				switch ($state) {
					case 1: {
						if ($cbAction == "chooseCreate") {
							$class = $callback["class"];
							$msg = $msgs["name".$class];
							$userVars["data"] = array("createClass"=>$class);
							$state = 10;
						}
						break;
					}
					case 50: {
						if ($cbAction == "create") {
							$elementData = get_all_data($data);

							$elementData["idUser"] = $userId;

							if ( isset($elementData["idCriticality"])
								 and isset($elementData["idUrgency"]) ) {

								$crit = new Criticality($elementData["idCriticality"]);
								$urge = new Urgency($elementData["idUrgency"]);

								$priorityValue = round($urge->value * $crit->value / 2);
								$sql = new Priority();
								$priority = $sql->getSqlElementsFromCriteria(null, false, "value <= $priorityValue");

								usort($priority, function ($a, $b) { return $a->value <=> $b->value; });
								
								if (count($priority)) {
									$priority = end($priority);
									$elementData["idPriority"] = $priority->id;
								}
							}

							if (isset($elementData["plannedWork"])) {
								$elementData["plannedWork"] = Work::convertImputation($elementData["plannedWork"]);
							}

							$class = $data["createClass"];
							$element = new $class();
							
							foreach ($elementData as $key => $value) {
								if ($key == "plannedWork") {
									$element->WorkElement->plannedWork = $value;

								} else {
									$element->$key = $value;
								}
							}

							$element->save();

							$id = $element->id;
							$elementData["id"] = $id;
							$elementData["reference"] = $element->reference;

							if (isset($elementData["plannedWork"])) {
								$elementData["plannedWork"] = Work::displayImputation($elementData["plannedWork"]);
							}

							$msg = get_summary($elementData);
							send_msg(
								array(
									"chat_id" => $chatId,
									"text" => str_replace(array(":class", ":tradclass", ":ref", ":id"), array($class, i18n($class), $elementData["reference"], $id), $msgs["created"]),
									"parse_mode" => "Markdown"
								)
							);

							if (isset($data["createClass"]) and $data["createClass"] == "Question") {
								$markup["inline_keyboard"] = array(
									array(
										array("text"=>"Assigner","callback_data"=>"{'action':'assignquestion','id':$id}")
									)
								);
							}

							$reset = true;

						} else if ($cbAction == "modify") {
							chooseCreate($data, $state);
						}
						break;
					}
					case 20: {
						if (array_key_exists($cbAction, $cbActions)) {
							$msg = $msgs[$cbAction];
							$data["field"] = $cbAction;
							$state = $cbActions[$cbAction];
						}
						switch ($cbAction) {
							case "confirm": {
								$ticketData = get_all_data($data);

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
					case 40: {
						switch ($cbAction) {
							case "selResp": {
								if (isset($data["createClass"]) and $data["createClass"] == "Question") {
									$data["fields"][$data["field"]] = $callback["id"];

									createQuestion($data, $state);
									break;
								}
							}

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
								chooseCreate($data, $state);

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
								chooseCreate($data, $state);

								break;
							}
							case "selCtxt1": {
								if ($cbExtra !== "unset") {
									$data["fields"]["ctxt1"] = $callback["id"];
								}

								$msg = $msgs["ctxt3"];
								$state = 40;
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
					case 11: {
						switch ($cbAction) {
							case "return": {
								if (count($data["createPath"]) > 1) {
									//Remove last id of path
									array_pop($data["createPath"]);
									$userVars["createPath"] = $data["createPath"];
								}

								chooseQuestionProject($data, $state, false, false);

								break;
							}
							case "createProj": {
								$data["createPath"][] = $callback["id"];
								$userVars["createPath"] = $data["createPath"];
								chooseQuestionProject($data, $state);

								break;
							}
							case "choose": {
								$path = $data["createPath"];
								$data["fields"]["proj"] = array_pop($path);
								
								$msg = $msgs["desc"];
								$data["field"] = "desc";
								$state = $cbActions["desc"];

								break;
							}
						}
						break;
					}

					case 100: {
						if ($cbAction == "displayClass") {
							$data["displayPath"][0] = $callback["class"];
							chooseDisplay($data, $state);
						}
						break;
					}
					case 110: {
						switch ($cbAction) {
							case "return": {
								if (count($data["displayPath"]) > 1) {
									//Remove last id of path
									array_pop($data["displayPath"]);

								} else {
									//Come back to class selection
									$state = 0;
								}

								chooseDisplay($data, $state);

								break;
							}
							case "displayProj": {
								$data["displayPath"][] = $callback["id"];
								chooseDisplay($data, $state);

								break;
							}
							case "choose": {
								$state = 120;

								chooseDisplay($data, $state, false, false);

								break;
							}
							case "chooseSub": {
								$state = 120;

								chooseDisplay($data, $state, false, true);

								break;
							}
						}
						break;
					}
					case 120: {
						if ($cbAction == "return") {
							$state = 110;
							chooseDisplay($data, $state);
						
						} else if ($cbAction == "displayElement") {
							$state = 130;
							$data["displayPath"][] = $callback["id"];
							displayElement($callback["id"], $data["displayPath"][0]);
						}

						break;
					}
					case 130: {
						switch ($cbAction) {
							case "return": {
								$state = 120;
								array_pop($data["displayPath"]);

								chooseDisplay($data, $state);

								break;
							}
							case "assign": {
								$path = $data["displayPath"];
								$class = $path[0];
								$id = array_pop($path);

								$element = new $class($id);

								if (isset($element->idStatus) and $element->idStatus == 1) { //Double check status to prevent hack or bug
									$element->idStatus = 10;
									$element->save();

									displayElement($id, $class);
								}

								break;
							}
							case "startWork": {
								$path = $data["displayPath"];
								$class = $path[0];
								$id = array_pop($path);

								$element = new $class($id);

								if ( isset($element->idStatus) and in_array($element->idStatus, array(10, 3)) ) { //Double check status to prevent hack or bug
									if (isset($element->WorkElement)) {
										if ($element->WorkElement->ongoing == 0) {
											$element->WorkElement->start();
											$element->WorkElement->idUser = $userId;
											$element->WorkElement->save();

											if ($element->idStatus == 10) {
												$element->idStatus = 3;
												$element->save();
											}
											displayElement($id, $class);
										}
									}
								}

								break;
							}
							case "stopWork": {
								$path = $data["displayPath"];
								$class = $path[0];
								$id = array_pop($path);

								$element = new $class($id);

								if (isset($element->WorkElement)) {
									if ($element->WorkElement->ongoing == 1) {
										$element->WorkElement->stop();
										$element->WorkElement->idUser = $userId;
										$element->WorkElement->save();
										displayElement($id, $class);
									}
								}

								break;
							}
						}

						break;
					}
					case 210: {
						if ($cbAction == "saveAnswer" or $cbAction == "sendAnswer") {
							$question = new Question($data["answerId"]);

							if (isset($question->result)) {
								$question->result .= $data["answer"];
							} else {
								$question->result = $data["answer"];
							}

							if ($cbAction == "sendAnswer") {
								$question->idStatus = 4;
							}

							$question->save();

							$msg = $msgs[array("saveAnswer"=>"savedAnswer","sendAnswer"=>"sentAnswer")[$cbAction]];
							$reset = true;
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
			
			$dontChangeChooseMsgId = true;

			break;
		}
		case $commands["display"]: {
			if ($state == 0) {
				chooseDisplay($data, $state, true);

			} else {
				if ($state >= 100 and $state < 200) {
					$msg = $msgs["alreadyDisplaying"];
				} else {
					$msg = $msgs["alreadyCreating"];
				}

				$replyToMsg = true;
				$dontChangeChooseMsgId = true;
			}

			break;
		}
		case $commands["about"]: {
			$msg = $msgs["about"];

			$dontChangeChooseMsgId = true;

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

	if ($replyToMsg) {
		if (isset($userVars["choose_msg_id"])) {
			$replyMsg["reply_to_message_id"] = $userVars["choose_msg_id"];
		}
	}

	if ($markup != array()) {
		$replyMsg["reply_markup"] = json_encode($markup);
	}

	if (!$modify) {
		$result = json_decode(send_msg($replyMsg), true);

		if (!$dontChangeChooseMsgId) {
			$userVars["choose_msg_id"] = $result["result"]["message_id"];
		}

	} else {
		if (!isset($userVars["choose_msg_id"])) { $userVars["choose_msg_id"] = $input["messageId"]; }
		$replyMsg["message_id"] = $userVars["choose_msg_id"];
		$userVars["choose_msg_id"] = $input["messageId"];
		send_msg($replyMsg, "editMessageText");
	}
}

if ($reset) {
	$state = 0;
	$data = array();
	unset($userVars["choose_msg_id"]);
}

set_states($userVars, $chatId);
?>
