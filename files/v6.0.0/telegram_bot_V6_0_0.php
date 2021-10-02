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

define("TG_LOG_MSG", 0);
define("TG_LOG_DEBUG", 1);
define("TG_LOG_FULL_DEBUG", 2);

$DEBUG_LEVEL = TG_LOG_DEBUG;

//LOGIN TO ACCESS CLASSES AND FUNCTIONS

$batchMode=true;
$apiMode=true;
require_once('../tool/projeqtor.php');
$batchMode=false;

$user = SqlElement::getSingleSqlElementFromCriteria('User',array('id'=>Parameter::getGlobalParameter("telegramBotProjeqtorUser")));
$user->_API = true;
setSessionUser($user);

//END LOGIN

$TOKEN = Parameter::getGlobalParameter("telegramBotToken");
$VERSION = "V6.0.0";
$adminChatId = Parameter::getGlobalParameter("telegramBotAdminChatId");

$setEmoji = " ✅";

$commands = array(
	"create" => Parameter::getGlobalParameter("telegramBotCmdCreate"),
	"stop" => Parameter::getGlobalParameter("telegramBotCmdStop"),
	"state" => Parameter::getGlobalParameter("telegramBotCmdState"),
	"reply" => "reply",
	"callback" => "callback",
	"report" => Parameter::getGlobalParameter("telegramBotCmdReport"),
	"search" => Parameter::getGlobalParameter("telegramBotCmdSearch"),
	"about" => Parameter::getGlobalParameter("telegramBotCmdAbout"),
	"inline_query" => "inlinequery",
	"file" => "file"
);

$creatableClasses = array(
	"Ticket",
	"Question"
);

$createOptions = array(
	array(
		array("txt"=>ucfirst(i18nDec("colDescription")), "name"=>"description", "action"=>"field", "field"=>"desc")
	),
	array(
		array("txt"=>ucfirst(i18nDec("colModel")), "name"=>"model", "action"=>"field", "field"=>"model"),
		array("txt"=>ucfirst(i18nDec("colResponsible")), "name"=>"responsible", "action"=>"field", "field"=>"resp")
	),
	array(
		array("txt"=>ucfirst(i18nDec("colType")), "name"=>"type", "action"=>"field", "field"=>"type"),
		array("txt"=>ucfirst(i18nDec("colUrgency")), "name"=>"urgency", "action"=>"field", "field"=>"urge"),
		array("txt"=>ucfirst(i18nDec("colCriticality")), "name"=>"criticality", "action"=>"field", "field"=>"crit")
	),
	array(
		array("txt"=>ucfirst(i18nDec("colIdProject")), "name"=>"project", "action"=>"field", "field"=>"proj"),
		array("txt"=>ucfirst(i18nDec("colIdActivity")), "name"=>"activity", "action"=>"field", "field"=>"act")
	),
	array(
		array("txt"=>ucfirst(i18nDec("colEstimatedWork")), "name"=>"estimatedWork", "action"=>"field", "field"=>"work"),
		array("txt"=>ucfirst(i18nDec("colIdContext")), "name"=>"context", "action"=>"field", "field"=>"ctxt1")
	),
	array(
		array("txt"=>ucfirst(i18nDec("fileAttachment")), "name"=>"attachments", "action"=>"attachments")
	),
	array(
		array("txt"=>ucfirst(i18nDec("paramTelegramBotCmdCreate")), "action"=>"confirm")
	)
);

$states = array(
	0 => "Idle",
	1 => "Asking create",
	10 => "Asking name",
	20 => "Choosing field",
	21 => "Choosing project",
	30 => "Asking for field value (text)",
	40 => "Asking for field value (button)",
	50 => "Waiting for confirmation",
	100 => "Choosing display class / reference",
	110 => "Reference: multiple elements",
	120 => "Choosing display element",
	130 => "Displaying",
	140 => "Note selection",
	150 => "Note display",
	160 => "Note writing",
	200 => "Answering",
	210 => "Finalizing answer",
	400 => "Attachments"
);

$validateCbs = array(
	"estimatedWork" => function ($value) {
		return preg_match('/^\d+($|(\.\d+$))/', $value);
	}
);

$ATTACHMENT_DIRECTORY = "../files/telegram_files/";

/**
 * A message to be sent by telegram
 **/
class TelegramMessage {
	public $text;
	public $chatId;
	public $modifyId;
	public $replyId;
	public $markup;
	public $mode = "Markdown";
	public $other;

	/**
	 * Sends the message through Telegram API
	 * 
	 * @param $command: command to use (see https://core.telegram.org/bots/api#available-methods)
	 * @param $force: if true, sends the message even if missing info (useful for command which don't need text)
	 * @return false if text or chatId is not defined,
	 *         the response from Telegram API otherwise
	 **/
	public function send($command = "sendMessage", $force = false) {
		if ($force || (isset($this->text) && isset($this->chatId)) ) {
			$msg = array(
				"chat_id" => $this->chatId,
				"text" => $this->text,
				"parse_mode" => $this->mode
			);

			if (isset($this->modifyId)) {
				$command = "editMessageText";
				$msg["message_id"] = $this->modifyId;
			}

			if (isset($this->replyId)) {
				$msg["reply_to_message_id"] = $this->replyId;
			}

			if (isset($this->markup)) {
				$msg["reply_markup"] = json_encode($this->markup);
			}

			if (isset($this->other)) {
				foreach ($this->other as $key => $value) {
					if (!array_key_exists($key, $msg)) {
						$msg[$key] = $value;
					}
				}
			}

			global $TOKEN;
			//error_log($command);
			//error_log(json_encode($msg));
			//error_log( http_build_query($msg) );

			$url = "https://api.telegram.org/bot$TOKEN/$command";
			$options = array(
				'http' => array(
					'method' => 'POST',
					'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
					'content' => http_build_query($msg)
				)
			);
			$context = stream_context_create($options);
			$result = file_get_contents($url, false, $context);

			return $result;
		
		} else {
			return false;
		}
	}
}

/**
 * A handler for bot commands
 */
class CommandHandler {
	/**
	 * Notifies the user that an error occured
	 *
	 * @return void
	 **/
	private function error() {
		global $commands;
		$this->replyMessage->text = str_replace("{reportCommand}", $commands["report"], i18nDec("telegramBotMsgError"));
	}

	/**
	 * Handles commands issued to the bot. Interprets the commands and give back the appropriate responses
	 *
	 * @param $input: the input data received from Node-Red
	 * @param $botUser: an instance of TelegramBotUser representing the user who sent the command
	 * @param $replyMessage: an instance of TelegramMessage
	 * @return void
	 **/
	public function handle($input, $botUser, $replyMessage) {
		global $commands;

		$this->input = $input;
		$this->botUser = $botUser;
		$this->replyMessage = $replyMessage;

		$this->replyMessage->chatId = $this->botUser->chatId;

		if (isset($this->input["action"])) {
			switch ($this->input["action"]) {
				case $commands["create"]:
					$this->startCreate();
					break;

				case $commands["stop"]:
					$this->stop();
					break;
				
				case $commands["state"]:
					if (Parameter::getGlobalParameter("telegramBotEnableStateCmd")) {
						$this->state();
					
					} else {
						$this->replyMessage->text = i18nDec("telegramBotMsgDisabledCommand");
					}
					break;

				case $commands["reply"]:
					$this->handleReply();
					break;

				case $commands["callback"]:
					$this->handleCallback();
					break;

				case $commands["report"]:
					$this->report();
					break;

				case $commands["search"]:
					$this->startSearch();
					break;

				case $commands["about"]:
					$this->about();
					break;

				case $commands["inline_query"]:
					$this->handleInlineQuery();
					break;

				case $commands["file"]:
					$this->handleFile();
					break;

				default:
					$this->replyMessage->text = i18nDec("telegramBotMsgUnknownCommand");
					break;
			}
		}
	}

	/**
	 * If a command is already active, sends a message
	 * to stop it before doing anything else
	 *
	 * @return true if no active command, false otherwise
	 **/
	private function checkNotStarted() {
		global $commands;

		if ($this->botUser->state != 0) {
			$this->replyMessage->text = str_replace("{stopCommand}", $commands["stop"], i18nDec("telegramBotMsgAlreadyExecuting"));
			if (isset($this->botUser->buttonMsgId)) {
				$this->replyMessage->replyId = $this->botUser->buttonMsgId;
			}
			return false;
		}

		return true;
	}

	/**
	 * Utility function used by stop() and called after a command is finished.
	 * Sets state to Idle and resets data
	 *
	 * @return void
	 **/
	private function endCommand() {
		log_msg("End command",TG_LOG_FULL_DEBUG);

		$this->botUser->state = 0;

		//Keep persistent data, such as last project for created element
		$persistent = $this->botUser->getData("persistent");
		$this->botUser->setData(null, array("persistent" => $persistent));
		$this->botUser->buttonMsgId = null;
	}

	/**
	 * Stops the active command, if none, sends a warning.
	 * If there was an active button message, replaces it's
	 * content to indicate that it is no longer available
	 *
	 * @return void
	 **/

	private function stop() {
		log_msg("Stop",TG_LOG_FULL_DEBUG);

		if ($this->botUser->state == 0) {
			$this->replyMessage->text = i18nDec("telegramBotMsgNoCommand");
		
		} else {
			if (isset($this->botUser->buttonMsgId)) {
				$editMessage = new TelegramMessage();
				$editMessage->chatId = $this->botUser->chatId;
				$editMessage->modifyId = $this->botUser->buttonMsgId;
				$editMessage->text = i18nDec("telegramBotMsgUnavailable");

				$editMessage->send("editMessageText");
			}

			$this->replyMessage->text = i18nDec("telegramBotMsgStop");

			$this->endCommand();
		}
	}

	/**
	 * Sends all info about current state of the bot user
	 *
	 * @return void
	 **/

	private function state() {
		log_msg("State",TG_LOG_FULL_DEBUG);

		global $states;

		$this->replyMessage->text = "";
		$this->replyMessage->text .= "`chatId = ".$this->botUser->chatId ."`\n";
		$this->replyMessage->text .= "`state = ".$this->botUser->state ." (".$states[$this->botUser->state].")`\n";
		$this->replyMessage->text .= "`buttonMsgId = ".$this->botUser->buttonMsgId ."`\n";
		$this->replyMessage->text .= "`data = ````\n".json_encode($this->botUser->getData(), JSON_PRETTY_PRINT) ."```";
	}

	/**
	 * Sends a report (similar to state()) to the bot admin
	 *
	 * @return void
	 **/
	private function report() {
		log_msg("Report",TG_LOG_FULL_DEBUG);

		global $states;

		//Get chat info (i.e. user name, last name...)
		$chatInfoMsg = new TelegramMessage();
		$chatInfoMsg->chatId = $this->botUser->chatId;

		$chatInfo = $chatInfoMsg->send("getChat", true);
		$chatInfo = json_decode($chatInfo, true)["result"];

		$reportMessage = new TelegramMessage();

		$reportMessage->text = str_replace("{user}", $chatInfo["username"], i18nDec("telegramBotMsgReportFrom")) ."\n";
		$reportMessage->text .= "`chatId = ".$this->botUser->chatId ."`\n";
		$reportMessage->text .= "`state = ".$this->botUser->state ." (".$states[$this->botUser->state].")`\n";
		$reportMessage->text .= "`buttonMsgId = ".$this->botUser->buttonMsgId ."`\n";
		$reportMessage->text .= "`data = ````\n".json_encode($this->botUser->data, JSON_PRETTY_PRINT) ."```";

		$reportMessage->chatId = Parameter::getGlobalParameter("telegramBotAdminChatId");

		$reportMessage->send();

		$this->replyMessage->text = i18nDec("telegramBotMsgReportSent");
	}

	/**
	 * Sends all info about the bot
	 *
	 * @return void
	 **/
	private function about() {
		log_msg("About",TG_LOG_FULL_DEBUG);

		global $VERSION;

		$this->replyMessage->text = "";
		$this->replyMessage->text .= i18nDec("telegramBotMsgAbout") ."\n\n";
		$this->replyMessage->text .= "__*$VERSION*__ ([Changelog](https://github.com/HERDAC/ProjeqtorTelegramBot/blob/main/CHANGELOG.md))\n\n";
		$this->replyMessage->text .= i18nDec("telegramBotMsgAboutCreator") .":\n";
		$this->replyMessage->text .= "        @Baryhobal\n";
		$this->replyMessage->text .= "        baryhobal@herdac.ch";
	}

	/**
	 * Starts the process of creating an element
	 *
	 * @param $force: if true, forces command, otherwise, checks if no command is currently being executed
	 * @return void
	 **/
	private function startCreate($force=false) {
		log_msg("Start create",TG_LOG_FULL_DEBUG);

		global $creatableClasses;

		if ($force or $this->checkNotStarted()) {
			$this->botUser->state = 1;
			$this->botUser->setData("currentCommand", "create");
			$this->replyMessage->text = i18nDec("telegramBotMsgCreateClass");

			$keyboard = array();

			foreach ($creatableClasses as $class) {
				$keyboard[] = array("text"=>i18nDec($class),"callback_data"=>"{'action':'chooseCreate','class':'$class'}");
			}

			$keyboard = arrangeKeyboard($keyboard, 2, 3);

			$this->replyMessage->markup = array(
				"inline_keyboard" => $keyboard
			);
		}
	}

	/**
	 * Starts the process of searching for an element
	 *
	 * @param $force: if true, forces command, otherwise, checks if no command is currently being executed
	 * @return void
	 **/
	private function startSearch($force=false) {
		log_msg("Start search",TG_LOG_FULL_DEBUG);

		if ($force or $this->checkNotStarted()) {
			$this->botUser->setData("currentCommand", "search");
			$this->botUser->setData("byReference", false);

			$args = explode(" ", $this->input["content"]);

			if (count($args) >= 2) {
				$this->displayByReference($args[1]);
				
				return;
			}

			$this->botUser->state = 100;
			$this->replyMessage->text = i18nDec("telegramBotMsgSearchClass");

			$keyboard = array();

			$displayableClasses = getDisplayableClasses();

			foreach ($displayableClasses as $class) {
				$keyboard[] = array("text"=>i18nDec($class),"callback_data"=>"{'action':'chooseSearch','class':'$class'}");
			}

			$keyboard = arrangeKeyboard($keyboard, 2, 3);

			$this->replyMessage->markup = array(
				"inline_keyboard" => $keyboard
			);
		}
	}

	/**
	 * Handles callbacks from buttons
	 *
	 * @return void
	 **/
	private function handleCallback() {
		log_msg("Handling callback",TG_LOG_FULL_DEBUG);

		global $commands;

		//Check if not command /callback
		if (strpos($this->input["content"], "/") === 0) {
			log_msg("Suspicious command from ".$this->botUser->chatId.": ".$this->input["content"]);
			$this->error();
			return;
		}

		//Answer callback query -> removes pending indicator on button
		$ansCbQuery = new TelegramMessage();
		$ansCbQuery->other = array("callback_query_id"=>$this->input["callbackQueryId"]);
		$ansCbQuery->send("answerCallbackQuery", true);

		try {
			$callback = json_decode(str_replace("'", '"', $this->input["content"]), true);
			
		} catch (Exception $e) {
			try {
				$callback = uncsv($this->input["content"]);
				
			} catch (Exception $e) {
				log_msg("Unable to decode callback: ".$this->input["content"]);
				$this->error();
				return;
			}
		}

		$cbAction = $callback["action"];

		$this->replyMessage->modifyId = $this->botUser->buttonMsgId;

		if ($cbAction == "assignquestion") {
			$id = $callback["id"];

			$question = new Question($id);

			if (isset($question->idStatus) and $question->idStatus == 1) { //Double check status to prevent hack or bug
				$question->idStatus = 10;
				$question->save();

				//Remove buttons
				$editMsg = new TelegramMessage();
				$editMsg->chatId = $this->botUser->chatId;
				$editMsg->other = array(
					"message_id" => $this->input["messageId"]
				);
				$editMsg->send("editMessageReplyMarkup", true);

				//Send question to responsible (if they have added the bot on telegram)
				$respId = $question->idResource;
				$resp = new User($respId);

				if (isset($resp->chatIdTelegram)) {
					$chatIdResp = $resp->chatIdTelegram;
					$text = str_replace(array("{ref}","{id}", "{projeqtorUrl}"), array($question->reference, $id, Parameter::getGlobalParameter("telegramBotProjeqtorUrl")), i18nDec("telegramBotMsgAssignedQuestion"));
					$text .= "\n\n";
					
					$text .= $this->displayQuestion($id);
					

					$notif = new TelegramMessage();
					$notif->chatId = $chatIdResp;
					$notif->text = $text;
					$notif->markup = array(
						"inline_keyboard" => array(
							array(
								array("text"=>i18nDec("telegramBotAnswerQuestion"),"callback_data"=>"{'action':'answer','id':$id}")
							)
						)
					);
					$notif->send();

				} else {
					$cantSend = new TelegramMessage();
					$cantSend->chatId = $this->botUser->chatId;
					$cantSend->text = i18nDec("telegramBotMsgCantSendQuestion");
					$cantSend->send();
				}
			}

			return;

		} else if ($cbAction == "answer") {
			$id = $callback["id"];

			if (!(isset($callback["force"]) and $callback["force"]) and $this->botUser->state != 0) {
				$this->replyMessage->text = str_replace("{stopCommand}", $commands["stop"], i18nDec("telegramBotMsgStopBeforeAnswer"));
			
			} else {
				$question = new Question($id);
				$question->idStatus = 3;
				$question->save();

				$this->replyMessage->text = i18nDec("telegramBotMsgAnswer");
				$this->botUser->state = 200;
				$this->botUser->setData("answerId", $id);


				$editMsg = new TelegramMessage();
				$editMsg->chatId = $this->botUser->chatId;
				$editMsg->other = array(
					"message_id" => $this->input["messageId"]
				);
				$editMsg->send("editMessageReplyMarkup", true);

				$this->botUser->buttonMsgId = $this->input["messageId"];
				$this->replyMessage->replyId = $this->input["messageId"];
			}
			$this->replyMessage->modifyId = null;
		}

		switch ($this->botUser->state) {
			//Create class choice
			case 1:
				if ($cbAction == "chooseCreate") {
					$this->botUser->state = 10;
					$this->replyMessage->text = i18nDec("telegramBotMsgCreateName");

					$this->botUser->setData("createClass", $callback["class"]);
				}
				
				break;

			//Field choice
			case 20:
				if ($cbAction === "field") {
					switch ($callback["field"]) {
						case "desc":
							$this->askField("desc","description","text");
							break;

						case "model":
							$this->botUser->setData("currentField","model");
							$this->askField("model","model","classTicketTemplate");
							break;

						case "resp":
							$users = array();
							if ($this->botUser->getData("project")) {
								$projId = $this->botUser->getData("project");
								$proj = new Project($projId);

								$rows = getAffectations($proj);

								$names = array_column($rows, "name");
								$order = array_column($rows, "order");

								array_multisort($order, SORT_ASC, $names, SORT_ASC, $rows);

								foreach ($rows as $row) {
									$user = new User($row["id"]);
									$users[] = array("id"=>$row["id"],"text"=>$user->resourceName);
								}

							} else {
								$sql = new User();
								$results = $sql->getSqlElementsFromCriteria(array("idle"=>"0", "isResource"=>"1"), false, null, "fullName ASC");
								foreach ($results as $user) {
									$users[] = array("id"=>$user->id,"text"=>$user->resourceName);
								}
							}

							$this->askField("resp","responsible","list",null,null,$users);

							break;

						case "proj":
							$this->projectChoice("list", true);
							break;
						case "act": {
							$crits = array("idle"=>"0");
							$sql = new Activity();

							$projectId = $this->botUser->getData("project");

							if ($projectId) {
								$crits["idProject"] = $projectId;
							}

							$this->askField("act","activity","classActivity",null,$crits);
							break;
						}

						case "type":
						case "urge":
						case "crit":
						case "ctxt1":
							$full = array(
								"type" => "type",
								"urge" => "urgency",
								"crit" => "criticality",
								"ctxt1" => "context1"
							);
							$name = $full[$callback["field"]];
							$crits = array();

							if ($callback["field"] === "type") {
								$crits["scope"] = $this->botUser->getData("createClass");
							}

							$this->askField($callback["field"], $name, "class".ucfirst($name), null, $crits);

							break;

						case "work":
							$this->askField($callback["field"], "estimatedWork", "text");
							break;
						
						default:
							break;
					}
				
				} else if ($cbAction == "confirm") {
					$this->askConfirmation();
				
				} else if ($cbAction == "attachments") {
					$this->attachmentsMenu();
				}
				break;

			//Project choice
			case 21:
				if ($cbAction == "selProj") {
					$path = $this->botUser->getData("projectPath");
				
					if ($path === null or count($path) == 0) {
						$path = array();
					}

					//Add selected project to the path
					$path[] = $callback["id"];
					$this->botUser->setData("projectPath", $path);

					$params = $this->botUser->getData("projectChoiceParams");
					if ($params[0] === "list") {
						$this->chooseProject();
					} else {
						//If mode == "path" and start from last
						if ($params[0] === "path" and $params[4]) {
							$this->copyPathToPersistent();
						}

						//Continue project choice with same parameters
						$this->projectChoice(...$params);
					}

				} else if ($cbAction == "return") {
					$path = $this->botUser->getData("projectPath");
				
					if ($path === null or count($path) == 0) {
						if ($this->botUser->getData("currentCommand") === "search") {
							$this->startSearch(true);
							break;
						}
						$path = array();
					}

					//Remove last project from path
					array_pop($path);
					$this->botUser->setData("projectPath", $path);

					//Continue project choice with same parameters
					$params = $this->botUser->getData("projectChoiceParams");

					//Don't skip when going back
					$params[count($params)-2] = false;

					//If mode == "path" and start from last
					if ($params[0] === "path" and $params[4]) {
						$this->copyPathToPersistent();
					}

					$this->projectChoice(...$params);

				} else if ($cbAction == "choose") {
					$this->chooseProject();

				} else if ($cbAction == "chooseSub") {
					$this->chooseProject(true);
				}
				break;

			//Field value (button)
			case 40:
				$currentField = $this->botUser->getData("currentField");
				if (isset($callback["extra"])) {
					$extra = $callback["extra"];
				}

				if ($currentField === null) {
					log_msg("Trying to choose field value but currentField is not set");
				}

				if (substr($cbAction, 0, 3) === "sel") {
					//If extra is set to 'unset', reset field value
					if (isset($extra) and $extra == "unset") {
						$data = $this->botUser->getData();
						unset($data[$currentField]);
						$this->botUser->setData(null, $data);

					} else {
						$this->botUser->setData($currentField, $callback["id"]);
					}


					if ($currentField == "context1") {
						$this->askField("ctxt3", "context3", "classContext3");

					} else {
						if ($currentField == "model") {
							$this->applyTemplate($callback["id"]);
						}

						if ($this->botUser->getData("createClass") == "Question") {
							$this->askConfirmation(false);

						} else {
							$this->fieldChoice();
						}
					}
				} else if ($cbAction == "return") {
					$this->fieldChoice();

				} else if ($cbAction == "delAtt") {
					$attachments = $this->botUser->getData("attachments");

					if (isset($callback["id"])) {
						$attachmentId = $callback["id"];

						if ($attachments !== null) {
							unset($attachments[$attachmentId]);
						}
					}

					$buttons = array();

					if (count($attachments) > 0) {
						$this->replyMessage->text = i18nDec("telegramBotMsgDeleteAttachment");

						foreach ($attachments as $attachmentId => $attachmentInfo) {
							$caption = isset($attachmentInfo["caption"]) ? $attachmentInfo["caption"] : basename($attachmentInfo["fileloc"]);
							$buttons[] = array("text"=>$caption, "callback_data"=>"{'action':'delAtt','id':$attachmentId}");
						}

						$buttons = array_chunk($buttons, 2);
					
					} else {
						$this->replyMessage->text = i18nDec("telegramBotMsgNoAttachments");
					}

					$buttons[] = array(
						array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}")
					);

					$this->replyMessage->markup = array(
						"inline_keyboard" => $buttons
					);

					$this->botUser->setData("attachments", $attachments);

				} else if ($cbAction == "addAtt") {
					$this->replyMessage->text = i18nDec("telegramBotMsgAddAttachment");
					$this->botUser->state = 400;

					$this->replyMessage->markup = array(
						"inline_keyboard" => array(
							array(
								array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}")
							)
						)
					);
				}
				break;

			//Waiting for confirmation
			case 50:
				if ($cbAction == "modify") {
					$createClass = $this->botUser->getData("createClass");

					if ($createClass === "Question") {
						log_msg("ERROR: Questions can not be modified");

					} else {
						$this->fieldChoice();
					}

				} else if ($cbAction == "create") {
					$this->createElement();
				
				} else if ($cbAction == "abort") {
					$this->stop();
					$this->replyMessage->modifyId = null;
				}

				break;
			
			//Search class choice
			case 100:
				if ($cbAction == "chooseSearch") {
					$this->botUser->setData("searchClass", $callback["class"]);

					$this->projectChoice("path", true, true, 2, false, true, $callback["class"]);
				}
				
				break;

			//Multiple elements reference
			case 110:
				if ($cbAction == "chooseRef") {
					$this->botUser->setData("byReference", true);
					$this->displayElement($callback["class"], $callback["id"]);
				}

				break;

			//Search element choice
			case 120:
				if ($cbAction == "return") {
					$this->projectChoice("path", true, true, 2, false, false, $this->botUser->getData("searchClass"));
				
				} else if ($cbAction == "displayElement") {
					$this->displayElement($this->botUser->getData("searchClass"), $callback["id"]);
				}

				break;

			//Displaying element
			case 130:
				if ($cbAction == "return") {
					if ($this->botUser->getData("byReference")) {
						$this->startSearch(true);
					
					} else {
						$this->elementChoice();
					}

				} else if ($cbAction == "assign") {
					$id = $this->botUser->getData("id");
					$class = $this->botUser->getData("class");

					$element = new $class($id);

					if (isset($element->idStatus) and $element->idStatus == 1) { //Double check status to prevent hack or bug
						$element->idStatus = 10;
						$element->save();

						$this->displayElement($class, $id);
					}

				} else if ($cbAction == "startWork") {
					$id = $this->botUser->getData("id");
					$class = $this->botUser->getData("class");

					$element = new $class($id);
					$user = new User($this->botUser->idUser);

					$user->_API = true;
					setSessionUser($user);

					if ( isset($element->idStatus) and in_array($element->idStatus, array(10, 3)) ) { //Double check status to prevent hack or bug
						if (isset($element->WorkElement)) {
							if ($element->WorkElement->ongoing == 0) {
								$element->WorkElement->start();

								if ($element->idStatus == 10) {
									$element->idStatus = 3;
									$element->save();
								}
								$this->displayElement($class, $id);
							}
						}
					}

					$user = SqlElement::getSingleSqlElementFromCriteria('User',array('id'=>Parameter::getGlobalParameter("telegramBotProjeqtorUser")));
					$user->_API = true;
					setSessionUser($user);
					unset($user);

				} else if ($cbAction == "stopWork") {
					$id = $this->botUser->getData("id");
					$class = $this->botUser->getData("class");

					$element = new $class($id);

					$user = new User($this->botUser->idUser);

					$user->_API = true;
					setSessionUser($user);

					if (isset($element->WorkElement)) {
						if ($element->WorkElement->ongoing == 1) {
							$element->WorkElement->stop();
							$element->WorkElement->save();

							$this->displayElement($class, $id);
						}
					}

					$user = SqlElement::getSingleSqlElementFromCriteria('User',array('id'=>Parameter::getGlobalParameter("telegramBotProjeqtorUser")));
					$user->_API = true;
					setSessionUser($user);
					unset($user);

				} else if ($cbAction == "notes") {
					$this->botUser->state = 140;

					$this->displayNotes();

				} else if ($cbAction == "sendAnswer") {
					$answerId = isset($callback["answerId"]) ? $callback["answerId"] : $this->botUser->getData("answerId");
					
					$question = new Question($answerId);
					$question->idStatus = 4;

					$question->save();

					$this->replyMessage->text = i18nDec("telegramBotMsgSentAnswer");
					
					$this->endCommand();
				}

				break;

			//Note selection
			case 140:
				if ($cbAction == "return") {
					$this->botUser->state = 130;
					$this->displayElement($this->botUser->getData("class"), $this->botUser->getData("id"));
				
				} else if ($cbAction == "selNote") {
					$this->botUser->state = 150;

					$this->botUser->setData("noteId", $callback["id"]);
					$this->displayNote();
				
				} else if ($cbAction == "newNote") {
					$this->newNote();
				}

				break;

			//Note display
			case 150:
				if ($cbAction == "return") {
					$this->botUser->state = 140;
					$this->botUser->setData("noteId", null);

					$this->displayNotes();
				
				} else if ($cbAction == "newNote") {
					$this->newNote($this->botUser->getData("noteId"));
				}
				break;

			//Finalizing answer
			case 210:
				if ($cbAction == "saveAnswer" or $cbAction == "sendAnswer") {
					$answerId = isset($callback["answerId"]) ? $callback["answerId"] : $this->botUser->getData("answerId");
					
					$question = new Question($answerId);
					$answer = $this->botUser->getData("answer");

					if ($answer !== null) {
						if (isset($question->result)) {
							$question->result .= "\n".$answer;
						} else {
							$question->result = $answer;
						}
					}

					if ($cbAction == "sendAnswer") {
						$question->idStatus = 4;
					}

					$question->save();

					$this->replyMessage->text = i18nDec(array("saveAnswer"=>"telegramBotMsgSavedAnswer","sendAnswer"=>"telegramBotMsgSentAnswer")[$cbAction]);
					
					$this->endCommand();
				}
				break;

			//Attachments
			case 400:
				if ($cbAction == "return") {
					$this->fieldChoice();

					break;
				}

			default:
				break;
		}
	}

	/**
	 * Handles replies to question from the bot
	 *
	 * @return void
	 **/
	private function handleReply() {
		log_msg("Handling reply",TG_LOG_FULL_DEBUG);

		//Check if not command /reply
		if (strpos($this->input["content"], "/") === 0) {
			log_msg("Suspicious command from ".$this->botUser->chatId.": ".$this->input["content"]);
			$this->error();
			return;
		}

		switch ($this->botUser->state) {
			//Create element name
			case 10:
				$this->botUser->setData("name", $this->input["content"]);
				$createClass = $this->botUser->getData("createClass");

				if ($createClass === "Question") {
					//Choose project: path mode, no sub-project choice, no elements needed, start from last path, no skip
					$this->projectChoice("path", false, false, 0, true, false);

				} else {
					$this->fieldChoice();
				}
				break;

			//Text field
			case 30:
				global $validateCbs;

				$field = $this->botUser->getData("currentField");
				$value = $this->input["content"];

				//In case there is a validation function for this field, check that the value is valid
				if (!array_key_exists($field, $validateCbs) || $validateCbs[$field]($value)) {
					$this->botUser->setData($field, $value);
					$createClass = $this->botUser->getData("createClass");

					if ($createClass === "Question") {
						$users = array();

						if ($this->botUser->getData("project")) {
							$projId = $this->botUser->getData("project");
							$proj = new Project($projId);

							$rows = getAffectations($proj);

							$names = array_column($rows, "name");
							$order = array_column($rows, "order");

							array_multisort($order, SORT_ASC, $names, SORT_ASC, $rows);

							foreach ($rows as $row) {
								$user = new User($row["id"]);
								$users[] = array("id"=>$row["id"],"text"=>$user->resourceName);
							}

						} else { //In case of error or anything else idk
							log_msg("Question responsible selection but project is not set");
							trace();

							$sql = new User();
							$results = $sql->getSqlElementsFromCriteria(array("idle"=>"0"), false, null, "fullName ASC");
							
							foreach ($results as $user) {
								$users[] = array("id"=>$user->id,"text"=>$user->resourceName);
							}
						}

						//               short  field         type  cb   crit list   opt
						$this->askField("resp","responsible","list",null,null,$users,false);

					} else {
						$this->fieldChoice();
					}
				} else {
					$this->replyMessage->text = i18nDec("telegramBotMsgInvalidField".ucfirst($field));
				}

				break;

			//Choosing display class / reference
			case 100:
				$deleteLastMsg = new TelegramMessage();
				$deleteLastMsg->chatId = $this->botUser->chatId;
				$deleteLastMsg->modifyId = $this->botUser->buttonMsgId;
				$deleteLastMsg->text = i18nDec("telegramBotMsgSearchClass");
				$deleteLastMsg->send("editMessageText");

				$this->displayByReference($this->input["content"]);

				break;

			//Note writing
			case 160:
				$note = new Note();
				$noteId = $this->botUser->getData("noteId");

				if ($noteId !== null) {
					$note->idNote = $noteId;
					$parent = new Note($noteId);
					$note->replyLevel = $parent->replyLevel + 1;
					
					$this->botUser->setData("noteId", null);
				}

				$id = $this->botUser->getData("id");
				$class = $this->botUser->getData("class");

				$note->refType = $class;
				$note->refId = $id;
				$note->idUser = $this->botUser->idUser;
				$note->note = $this->input["content"];

				$note->save();

				$this->botUser->state = 140;

				$this->displayNotes();

				break;

			//Answering
			case 200:
				$this->botUser->setData("answer", $this->input["content"]);
				$this->botUser->state = 210;
				$this->replyMessage->text = i18nDec("telegramBotMsgAnswerFinalize");
				$this->replyMessage->markup = array(
					"inline_keyboard" => array(
						array(
							array("text"=>i18nDec("telegramBotSaveAnswer"),"callback_data"=>"{'action':'saveAnswer'}"),
							array("text"=>i18nDec("telegramBotSendAnswer"),"callback_data"=>"{'action':'sendAnswer'}")
						)
					)
				);
				break;
			
			default:
				break;
		}
	}

	/**
	 * Handles inline queries (aka search by reference)
	 *
	 * @return void
	 **/
	private function handleInlineQuery() {
		log_msg("Handling inline query",TG_LOG_FULL_DEBUG);

		global $commands;

		//Check if not command /inlinequery
		if (strpos($this->input["content"], "/") === 0) {
			log_msg("Suspicious command from ".$this->botUser->chatId.": ".$this->input["content"]);
			$this->error();
			return;
		}

		//Check not command is running
		if ($this->botUser->state == 0) {
			$ref = $this->input["content"];

			//Prevent SQL injection (only digits, letters and hyphens are accepted) (basic but should be enough)
			if (preg_match('/[^\da-zA-Z\-]/', $ref) === 0) {
				$dbName = Parameter::getGlobalParameter("paramDbName");

				$query = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('reference') AND TABLE_SCHEMA='$dbName'";
				$tables = Sql::query($query);

				$results = array();
				
				foreach ($tables as $table) {
					$class = capitalizeClass($table["TABLE_NAME"]);

					$obj = new $class();

					$name = Sql::query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='name' AND TABLE_NAME='".$table["TABLE_NAME"]."' AND TABLE_SCHEMA='$dbName'");

					$hasName = (is_a($name, "PDOStatement") and $name->rowCount() != 0);

					$elements = Sql::query("SELECT id, reference".($hasName ? ", name" : "")." FROM ".$table["TABLE_NAME"]." WHERE reference LIKE '$ref%'");

					foreach ($elements as $element) {
						$results[] = array(
							"type" => "article",
							"id" => $class."#".$element["id"],
							"title" => $element["reference"],
							"description" => ($hasName ? $element["name"] : i18nDec($class)."#".$element["id"]),
							"input_message_content" => array("message_text"=>"/".$commands["search"]." ".$element["reference"])
						);
					}
				}

				$offset = (($this->input["offset"] === "") ? 0 : $this->input["offset"]);
				$results = array_chunk($results, 50);
				if (count($results) > 0) {
					$results = $results[min(count($results), $offset)];
				
				} else {
					$results = array();
				}
				
			} else {
				$results = array();
			}
			
		
		} else {
			$results = array(
				array(
					"type" => "article",
					"id" => "alreadyExecuting",
					"title" => "-",
					"description" => str_replace("{stopCommand}", $commands["stop"], i18nDec("telegramBotMsgAlreadyExecuting")),
					"input_message_content" => array("message_text"=>"/".$commands["stop"])
				)
			);
		}

		$ansInlineQuery = new TelegramMessage();
		$ansInlineQuery->other = array("inline_query_id"=>$this->input["inlineQueryId"], "results" => json_encode($results), "cache_time" => 3);
		$ansInlineQuery->send("answerInlineQuery", true);
	}

	/**
	 * Handles files sent to the bot (i.e attachments)
	 *
	 * @return void
	 **/
	private function handleFile() {
		log_msg("Handling file",TG_LOG_FULL_DEBUG);

		$url = $this->input["weblink"];
		$caption = null;
		$index = $this->input["seq_index"];

		if (isset($this->input["caption"])) {
			$caption = $this->input["caption"];
		
		} else if (isset($this->input["originalMessage"][$this->input["type"]]["file_name"])) {
			$caption = $this->input["originalMessage"][$this->input["type"]]["file_name"];
		}

		if ($this->botUser->state == 400) {
			if ($this->botUser->buttonMsgId !== null) {
				$editMsg = new TelegramMessage();
				$editMsg->chatId = $this->botUser->chatId;
				$editMsg->other = array(
					"message_id" => $this->botUser->buttonMsgId
				);
				$editMsg->send("editMessageReplyMarkup", true);

				$this->botUser->buttonMsgId = null;
			}

			$this->addAttachment($url, $this->input["type"], $caption);

			if ($index == 0) {
				$this->replyMessage->text = i18nDec("telegramBotMsgAddAttachment");

				$this->replyMessage->markup = array(
					"inline_keyboard" => array(
						array(
							array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}")
						)
					)
				);
			}
		}
	}

	/**
	 * Adds an attachment for the element being created.
	 * It downloads the file from Telegram's servers in
	 * the directory set in global parameters
	 *
	 * @param $url: url of the file provided by telegram api
	 * @param $type: type of file
	 * @param $caption: file's caption = file name
	 * @return void
	 **/
	private function addAttachment($url, $type, $caption=null) {
		global $ATTACHMENT_DIRECTORY;

		$attachments = $this->botUser->getData("attachments");

		$ch = curl_init($url);

		$filename = basename($url);

		//$attachmentDirectory = Parameter::getGlobalParameter ( 'paramAttachmentDirectory' );
		$directory = $ATTACHMENT_DIRECTORY . $this->botUser->chatId;
		$fileloc = $directory . "/" . $filename;

		if(!is_dir($directory)){
			mkdir($directory, 0777, true);
		}

		log_msg("Downloading file to $fileloc");
		
		$fp = fopen($fileloc, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		
		log_msg("Downloaded file to $fileloc");

		if ($attachments !== null) {
			$attachments = array();
		}

		$attachment = array("fileloc"=>$fileloc);
		if ($caption) {
			$attachment["caption"] = $caption;
		}
		$attachments[] = $attachment;

		$this->botUser->setData("attachments", $attachments);
	}

	/**
	 * Allow the user to choose a field to modify
	 * 
	 * @return void
	 **/
	private function fieldChoice() {
		log_msg("Choosing field",TG_LOG_FULL_DEBUG);

		$this->botUser->state = 20;
		$createClass = $this->botUser->getData("createClass");

		$keyboard = $this->getFieldsMenu($createClass);

		$this->replyMessage->text = i18nDec("telegramBotMsgFieldChoice");
		$this->replyMessage->markup = array(
			"inline_keyboard" => $keyboard
		);
	}

	/**
	 * Asks the user to choose a value for a given field
	 *
	 * @param $short: short name for the field (like desc, resp, ...)
	 * @param $field: full name of the field (i.e. name of the class property)
	 * @param $type: type of field. one of "text","class","project"
	 * @param $validateCb: name of callback function to validate the user's input.
	 *     Should return true if valid, false otherwise
	 *     (NOT USED)
	 * @param $crits: criteria array for type "class"
	 * @param $list: list of choices for type "list". Elements should be of the form:
	 *     array("id" => returnedId, "text" => displayedText)
	 * @param $optional: wether the field can be unset or not
	 * @return void
	 **/
	
	private function askField($short,$field,$type="text",$validateCb=null,$crits=array(),$list=array(),$optional=true) {
		log_msg("Asking field",TG_LOG_FULL_DEBUG);
		
		global $setEmoji;

		$this->botUser->setData("currentField",$field);

		$this->replyMessage->text = i18nDec("telegramBotMsgField".ucfirst($short));

		if ($type === "text") {
			$this->botUser->state = 30;


		} else if (substr($type, 0, 5) === "class") {
			$this->botUser->state = 40;
			$class = substr($type, 5);
			$sql = new $class();
			$elements = $sql->getSqlElementsFromCriteria($crits);
			$buttons = array();
			foreach ($elements as $element) {
				$id = $element->id;
				$text = $element->name;

				if ($this->botUser->getData($field) && $this->botUser->getData($field) == $id) {
					$text .= " $setEmoji";
				}

				array_push($buttons, array("text"=>$text, "callback_data"=>"{'action':'sel".ucfirst($short)."','id':'$id'}"));
			}

			if (count($elements) == 0) {
				$this->replyMessage->text = i18nDec("telegramBotMsgNoField".ucfirst($short));
			}

			$buttons = array_chunk($buttons, 2);

			if ($optional) {
				array_push($buttons, array(
					array(
						"text" => i18nDec("telegramBotUnsetField"),
						"callback_data" => "{'action':'sel".ucfirst($short)."','extra':'unset'}"
					)
				));
			}

			$this->replyMessage->markup = array("inline_keyboard" => $buttons);
			
		} else if ($type === "list") {
			$this->botUser->state = 40;
			$buttons = array();
			foreach ($list as $element) {
				$id = $element["id"];
				$text = $element["text"];

				if ($this->botUser->getData($field) && $this->botUser->getData($field) == $id) {
					$text .= " $setEmoji";
				}

				array_push($buttons, array("text"=>$text, "callback_data"=>"{'action':'sel".ucfirst($short)."','id':'$id'}"));
			}

			$buttons = array_chunk($buttons, 2);

			if ($optional) {
				array_push($buttons, array(
					array(
						"text" => i18nDec("telegramBotUnsetField"),
						"callback_data" => "{'action':'sel".ucfirst($short)."','extra':'unset'}"
					)
				));
			}

			$this->replyMessage->markup = array("inline_keyboard" => $buttons);
		}
	}

	/**
	 * Allow the user to choose a project
	 * 
	 * @param $mode: either "path" or "list". "path" -> choose a project, then a sub-project, ... "list" -> choose a subproject from a flat list
	 * @param $canReturn: if true, return button is also visible if no project is selected,
	 *     otherwise, it is only present after at least one project has been selected 
	 * @param $canChooseSub: wether to display a "Choose project + all sub-projects" button (used mainly when searching for an element) (only for "path" mode)
	 * @param $mustHaveElements:
	 *     0: no restriction
	 *     1: only projects with elements can be selected (directly linked or linked to a sub-project)
	 *     2: only projects with elements of class $class can be selected (directly linked or linked to a sub-project)
	 * @param $startFromLast: if true, selection starts at the last path selected with this option enabled (only for "path" mode)
	 * @param $skip: wether to enable skipping of non-selectable projects (this option is used when the user clicks on a return button for example)
	 * @param $class: class of children elements (see $mustHaveElements)
	 * @return void
	 **/
	private function projectChoice($mode="path", $canReturn=false, $canChooseSub=false, $mustHaveElements=0, $startFromLast=false, $skip=true, $class=null) {
		log_msg("Choosing project",TG_LOG_FULL_DEBUG);

		//Store parameters for future calls
		if ($this->botUser->getData("projectChoiceParams") === null) {
			$this->botUser->setData("projectChoiceParams", array($mode, $canReturn,$canChooseSub,$mustHaveElements,$startFromLast,$skip,$class));
		}

		$this->botUser->state = 21;

		$msg = "Not done yet";

		if ($mode === "path") {
			if ($this->botUser->getData("projectPath")) {
				$this->replyMessage->modifyId = $this->botUser->buttonMsgId;
			}

			$buttons = array();

			if ($startFromLast and isset($this->botUser->getData("persistent")["projectPath"])) {
				$this->botUser->setData("projectPath", $this->botUser->getData("persistent")["projectPath"]);
			}
			
			$path = $this->botUser->getData("projectPath");
		
			if ($path === null or count($path) == 0) {
				$path = array();
			}

			$projectId = end($path);

			$user = new User($this->botUser->idUser);

			$affectedProjects = $user->getAffectedProjects();

			$projects = getProjectsFromPath($path, $this->botUser->idUser, $checkElements, $class);

			if (count($projects) == 0 and count($path) <= 1) {
				$msg = i18nDec("telegramBotMsgNoProjectsAccess");

			//Skip if only one selectable project and user can't choose current project
			} else if ($skip and count($projects) == 1 and $projectId and !array_key_exists($projectId, $affectedProjects)) {
				$path[] = $projects[0]->id;
				$this->botUser->setData("projectPath", $path);

				if ($mode === "path" and $startFromLast) {
					$this->copyPathToPersistent();
				}
				
				$this->projectChoice($mode, $canReturn, $canChooseSub, $mustHaveElements, $startFromLast, $skip, $class);
				return;

			} else {
				$msg = i18nDec("telegramBotMsgChooseCreateProject");
				
				foreach ($projects as $project) {
					$id = $project->id;
					$buttons[] = array("text"=>$project->name, "callback_data"=>"{'action':'selProj','id':'$id'}");
				}

			}

			if (count($path) == 0) {
				$msg = str_replace("{project}", "", $msg);
			
			} else {
				$proj = new Project($projectId);
				$msg = str_replace("{project}", "\n".i18nDec("telegramBotMsgCurrentProject").": *".$proj->name."*", $msg);
			}

			$buttons = array_chunk($buttons, 1);
			$extraButtons = array();

			//If selected at least one project
			if (count($path) >= 1 or $canReturn) {
				if ( $projectId and array_key_exists($projectId, $affectedProjects)) {
					$extraButtons[] = array("text"=>i18nDec("telegramBotChoose"),"callback_data"=>"{'action':'choose'}");

					if ($canChooseSub) {
						$extraButtons[] = array("text"=>i18nDec("telegramBotChooseSub"),"callback_data"=>"{'action':'chooseSub'}");
					}
				}

				$returnButton = array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}");

				if (count($extraButtons) == 1) {
					array_unshift($extraButtons, $returnButton);
				} else {
					$extraButtons[] = $returnButton;
				}
			}

			if (count($extraButtons) > 0) {
				$buttons = array_merge($buttons, array_chunk($extraButtons,2));
			}
		
		} else if ($mode === "list") {
			global $setEmoji;

			$this->replyMessage->modifyId = $this->botUser->buttonMsgId;

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
				if (in_array($this->botUser->idUser, array_column(getAffectations($project), "id"))) {
					$id = $project->id;

					array_push($buttons, array("text"=>$project->name.($this->botUser->getData("project")==$id ? " ".$setEmoji : ""), "callback_data"=>"{'action':'selProj','id':'$id'}"));
				}
			}

			$msg = i18nDec("telegramBotMsgFieldProj");
			$buttons = array_chunk($buttons, 2);

			//If selected at least one project
			if ($canReturn) {
				$buttons[] = array(array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}"));
			}
		}

		$markup = null;
		if (isset($buttons)) {
			$markup = array("inline_keyboard"=>$buttons);
		}

		$this->replyMessage->text = $msg;
		$this->replyMessage->markup = $markup;
	}

	/**
	 * Finalizes choice of project
	 *
	 * @param $chooseSub: if true, also chooses sub projects, else only the last project in the path
	 * @return void
	 **/
	private function chooseProject($chooseSub=false) {
		log_msg("Chose project",TG_LOG_FULL_DEBUG);

		$path = $this->botUser->getData("projectPath");
		$this->botUser->setData("project", end($path));
		$this->botUser->setData("chooseSub", $chooseSub);

		$currentCommand = $this->botUser->getData("currentCommand");

		if ($currentCommand === "create") {
			$createClass = $this->botUser->getData("createClass");

			if ($createClass === "Question") {
				$this->askField("desc","description","text");

			} else {
				$this->fieldChoice();
			}
		} else if ($currentCommand === "search") {
			$this->elementChoice();

		} else {
			log_msg("Project chosen but command is neither create nor search. Current command: " . ($currentCommand ?? "none") );
		}
	}

	/**
	 * Allow the user to choose an element
	 *
	 * @return void
	 **/
	private function elementChoice() {
		log_msg("Choosing element",TG_LOG_FULL_DEBUG);

		$this->botUser->state = 120;

		//Get all matching elements
		$class = $this->botUser->getData("searchClass");
		$projectId = $this->botUser->getData("project");
		$userId = $this->botUser->idUser;
		$includeSub = $this->botUser->getData("chooseSub");

		$elements = getElementsFromProject($class, $projectId, $userId, $includeSub);

		//Sort elements by status, then by priority, then by id
		$rows = array();

		foreach ($elements as $element) {
			$statusOrder = 0;
			$priorityOrder = 0;

			if (isset($element->idStatus)) {
				$status = new Status($element->idStatus);
				$statusOrder = $status->sortOrder;
			}
			if (isset($element->idPriority)) {
				$priority = new Priority($element->idPriority);
				$priorityOrder = $priority->sortOrder;
			}
			$rows[] = array("element"=>$element, "statusOrder"=>$statusOrder, "priorityOrder"=>$priorityOrder, "id"=>$element->id);
		}

		$statuses = array_column($rows, "statusOrder");
		$priorities = array_column($rows, "priorityOrder");
		$ids = array_column($rows, "id");

		array_multisort($statuses, SORT_ASC, $priorities, SORT_ASC, $ids, SORT_ASC, $rows);

		$elements = array_column($rows, "element");
		$buttons = array();

		foreach ($elements as $element) {
			$id = $element->id;
			$emojis = "";

			//Add status emoji if it exists
			if (isset($element->idStatus)) {
				$status = new Status($element->idStatus);

				if (isset($status->tgEmoji) and $status->tgEmoji != "") {
					$emojis .= " ".$status->tgEmoji;
				}
			}

			//Add priority emoji if it exists
			if (isset($element->idPriority)) {
				$priority = new Priority($element->idPriority);

				if (isset($priority->tgEmoji) and $priority->tgEmoji != "") {
					$emojis .= " ".$priority->tgEmoji;
				}
			}

			$buttons[] = array("text"=>$element->name.$emojis, "callback_data"=>"{'action':'displayElement','id':'$id'}");
		}

		$buttons = array_chunk($buttons, 1);
		$buttons[] = array(
			array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}")
		);

		$this->replyMessage->text = i18nDec("telegramBotChooseElement");
		$this->replyMessage->markup = array("inline_keyboard" => $buttons);
	}

	/**
	 * Try to display an element from its reference
	 *
	 * @param $reference: reference of the element
	 * @return void
	 **/
	private function displayByReference($reference) {
		global $commands;

		$element = $this->getElementsFromReference($reference);

		//No matching element -> warning
		if (count($element) == 0) {
			$this->replyMessage->text = str_replace("{reportCommand}", $commands["report"], i18nDec("telegramBotMsgInvalidReference"));

		//1 matching element -> display
		} else if (count($element) == 1) {
			$element = $element[0];

			$this->botUser->setData("byReference", true);
			$this->displayElement(get_class($element), $element->id);

		//Multiple matching elements -> choose
		} else {
			$this->chooseReferenceElement($reference, $element);
		}
	}

	/**
	 * Gets a list of elements whose reference is $reference 
	 *
	 * @param $reference: reference of elements to get
	 * @return array of elements
	 **/
	private function getElementsFromReference($reference) {
		$dbName = Parameter::getGlobalParameter("paramDbName");
		$query = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('reference') AND TABLE_SCHEMA='$dbName'";
		$tables = Sql::query($query);
		$results = array();
		foreach ($tables as $table) {
			$tableName = $table["TABLE_NAME"];
			$class = capitalizeClass($tableName);

			$obj = new $class();
			
			$results = array_merge($results, $obj->getSqlElementsFromCriteria(array("reference"=>$reference)));
		}

		return $results;
	}

	/**
	 * Allow the user to choose an element from multiple elements with the same reference
	 *
	 * @param $reference: reference of the elements
	 * @param $elements: array of elements with the same reference
	 * @return void
	 **/
	private function chooseReferenceElement($reference, $elements) {
		$this->replyMessage->text = str_replace("{reference}", $reference, i18nDec("telegramBotMsgMultipleReference"));

		$buttons = array();

		foreach ($elements as $element) {
			$name = $element->name;
			$class = get_class($element);
			$id = $element->id;

			$buttons[] = array("text"=>$name, "callback_data"=>"{'action':'chooseRef','class':'$class','id':$id}");
		}

		$buttons = array_chunk($buttons, 1);

		$this->replyMessage->markup = array("inline_keyboard" => $buttons);
		$this->botUser->state = 110;
	}

	/**
	 * Display a question (used when question is assigned)
	 *
	 * @return message text
	 **/
	private function displayQuestion($id) {
		$prevState = $this->botUser->state;
		$prevText = $this->replyMessage->text;
		$prevMarkup = $this->replyMessage->markup;

		$this->displayElement("Question", $id, false);
		$text = $this->replyMessage->text;
		
		$this->botUser->state = $prevState;
		$this->replyMessage->text = $prevText;
		$this->replyMessage->markup = $prevMarkup;

		return $text;
	}

	/**
	 * Display information about an element
	 *
	 * @param $class: class of the element
	 * @param $id: id of the element
	 * @param $canReturn: if false, no return button will be available (used when searching by reference)
	 * @return void
	 **/
	private function displayElement($class, $id, $canReturn=true) {
		log_msg("Display element $class#$id",TG_LOG_FULL_DEBUG);

		if ($canReturn) {
			$this->botUser->setData("id", $id);
			$this->botUser->setData("class", $class);
		}
		$this->botUser->state = 130;
		$element = new $class($id);

		$result = "";
		$lines = array();
		
		$template = $this->getDisplayTemplate($element);

		if ($template and $template->template) {
			$result = $template->format($element);
		
		} else {
			$result = i18nDec("telegramBotMsgNoDisplayTemplate");
		}

		if ($template) {
			$buttons = array();

			$buttons[] = array("text"=>i18nDec("telegramBotNotes"),"callback_data"=>"{'action':'notes'}");

			if ($template->butAssign and isset($element->idStatus) and $element->idStatus == 1) {
				$buttons[] = array("text"=>i18nDec("telegramBotAssign"),"callback_data"=>"{'action':'assign'}");
			}

			if ($template->butWork and isset($element->WorkElement) and isset($element->idStatus) and in_array($element->idStatus, array(10, 3))) {
				$state = $element->WorkElement->ongoing;
				
				if ($state == 0) {
					$buttons[] = array("text"=>i18nDec("telegramBotStartWork"), "callback_data"=>"{'action':'startWork'}");

				} else if ($state == 1) {
					$buttons[] = array("text"=>i18nDec("telegramBotStopWork"), "callback_data"=>"{'action':'stopWork'}");
				}
			}

			if ($class == "Question") {
				if (in_array($element->idStatus, array(10, 3))) {
					if ($template->butReply) {
						$buttons[] = array("text"=>i18nDec("telegramBotAnswerQuestion"),"callback_data"=>"{'action':'answer','id':$id,'force':true}");
					}

					if ($template->butSend and $element->result) {
						$buttons[] = array("text"=>i18nDec("telegramBotSendAnswer"),"callback_data"=>"{'action':'sendAnswer','answerId':$id}");
					}
				}
			}
			
			$buttons = array($buttons);

			if ($canReturn and $template->butReturn) {
				$buttons[] = array(array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}"));
			}
			$markup = array("inline_keyboard"=>$buttons);

		} else {
			$markup = array(
				"inline_keyboard" => array(
					array(
						array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}")
					)
				)
			);
		}

		$this->replyMessage->text = $result;
		$this->replyMessage->markup = $markup;

	}

	/**
	 * Gets the display template for a given class
	 * If none is found, returns null
	 *
	 * @param $element: element for which to get a template
	 * @return null or TelegramDisplayTemplate
	 **/
	private function getDisplayTemplate($element) {
		$class = get_class($element);
		$typeName = "id".$class."Type";
		$idMailable = SqlList::getIdFromName("Mailable", $class);

		$disTemp = new TelegramDisplayTemplate();
		$withStatusWithType = $disTemp->getSqlElementsFromCriteria(array(
			"idle"=>"0",
			"idMailable"=>$idMailable,
			"idStatus"=> $element->idStatus,
			"idType"=>$element->$typeName
		));
		$withoutStatusWithType = $disTemp->getSqlElementsFromCriteria(array(
			"idle"=>"0",
			"idMailable"=>$idMailable,
			"idStatus"=>null,
			"idType"=>$element->$typeName
		));
		$withStatusWithoutType = $disTemp->getSqlElementsFromCriteria(array(
			"idle"=>"0",
			"idMailable"=>$idMailable,
			"idStatus"=> $element->idStatus,
			"idType"=>null
		));
		$withoutStatusWithoutType = $disTemp->getSqlElementsFromCriteria(array(
			"idle"=>"0",
			"idMailable"=>$idMailable,
			"idStatus"=>null,
			"idType"=>null
		));

		$template = null;

		if (count($withStatusWithType) > 0) {
			$template = $withStatusWithType[0];
		
		} else if (count($withoutStatusWithType) > 0) {
			$template = $withoutStatusWithType[0];
		
		} else if (count($withStatusWithoutType) > 0) {
			$template = $withStatusWithoutType[0];
		
		} else if (count($withoutStatusWithoutType) > 0) {
			$template = $withoutStatusWithoutType[0];
		}
		
		return $template;
	}

	/**
	 * <[ WILL CHANGE ]>
	 * 
	 * Gets the appropriate button menu of editable fields for a given class
	 *
	 * @param $class: the name of the class
	 * @return array of rows of buttons
	 **/
	private function getFieldsMenu($class) {
		global $createOptions, $setEmoji;

		$keyboard = array();

		foreach ($createOptions as $line) {
			$row = array();

			foreach ($line as $button) {
				$cbData = $button;
				$txt = $button["txt"];

				if (isset($cbData["name"])) {
					if ($cbData["name"] == "context") {
						if ($this->botUser->getData("context1") || $this->botUser->getData("context3")) {
							$txt .= " $setEmoji";
						}
					} else if ($cbData["name"] == "attachments" and $this->botUser->getData("attachments") !== null) {
						$txt .= ": " . count($this->botUser->getData("attachments"));

					} else if ($this->botUser->getData($cbData["name"])) {
						$txt .= " $setEmoji";
					}

				}
				unset($cbData["txt"]);
				unset($cbData["name"]);

				$row[] = array("text" => $txt, "callback_data" => json_encode($cbData));
			}

			$keyboard[] = $row;
		}

		return $keyboard;
	}

	/**
	 * <[ WILL CHANGE ]>
	 * Set values from a predefined template
	 *
	 * @param $id: template id
	 * @return void
	 **/
	private function applyTemplate($id) {
		$template = new TicketTemplate($id);

		if ( $template->idTicketType  ) { $this->botUser->setData("type"       , $template->idTicketType);  }
		if ( $template->idProject     ) { $this->botUser->setData("project"    , $template->idProject);     }
		if ( $template->idUrgency     ) { $this->botUser->setData("urgency"    , $template->idUrgency);     }
		if ( $template->idCriticality ) { $this->botUser->setData("criticality", $template->idCriticality); }
		if ( $template->idContext1    ) { $this->botUser->setData("context1"   , $template->idContext1);    }
		if ( $template->idContext3    ) { $this->botUser->setData("context3"   , $template->idContext3);    }
		if ( $template->idActivity    ) { $this->botUser->setData("activity"   , $template->idActivity);    }
		if ( $template->idResource    ) { $this->botUser->setData("responsible", $template->idResource);    }
	}

	/**
	 * Returns a summary of the current element being created
	 *
	 * @return summary
	 **/
	private function summariseCreate() {
		$elementData = $this->getCreateData();
		$summary = "";

		$class = $this->botUser->getData("createClass");
		$idMailable = SqlList::getIdFromName("Mailable", $class);

		$sumTemp = new TelegramSummaryTemplate();
		$default = $sumTemp->getSqlElementsFromCriteria(array(
			"idle"=>"0",
			"idMailable"=>null
		));
		$specific = $sumTemp->getSqlElementsFromCriteria(array(
			"idle"=>"0",
			"idMailable"=>$idMailable
		));

		if (count($default) > 0) {
			$template = $default[0];
		}

		if (count($specific) > 0) {
			$template = $specific[0];
		}

		if (!isset($template)) {
			log_msg("ERROR -> no summary template found");
		}

		return $template->format($this->botUser->getData());
	}

	/**
	 * Creates the element according to the defined values
	 *
	 * @return void
	 **/
	private function createElement() {
		$elementData = $this->getCreateData();

		$elementData["idUser"] = $this->botUser->idUser;

		if ( isset($elementData["idCriticality"])
			 and isset($elementData["idUrgency"]) ) {

			$crit = new Criticality($elementData["idCriticality"]);
			$urge = new Urgency($elementData["idUrgency"]);

			//Calculate priority based on criticality and urgency
			$priorityValue = round($urge->value * $crit->value / 2);
			$sql = new Priority();
			$priority = $sql->getSqlElementsFromCriteria(null, false, "value <= $priorityValue");

			usort($priority, function ($a, $b) { return $a->value <=> $b->value; });
			
			if (count($priority)) {
				$priority = end($priority);
				$elementData["idPriority"] = $priority->id;
			}
		}


		if (isset($elementData["estimatedWork"])) {
			$elementData["estimatedWork"] = Work::convertImputation($elementData["estimatedWork"]);
		}

		$class = $this->botUser->getData("createClass");
		$element = new $class();
		
		foreach ($elementData as $key => $value) {
			if ($key == "estimatedWork") {
				$element->WorkElement->plannedWork = $value;

			} else if ($key == "attachments") {
				//Skip attachments, treated differently
				continue;

			} else {
				$element->$key = $value;
			}
		}

		$element->save();

		$id = $element->id;
		$elementData["id"] = $id;
		$elementData["reference"] = $element->reference;

		if (isset($elementData["estimatedWork"])) {
			$elementData["estimatedWork"] = Work::displayImputation($elementData["estimatedWork"]);
		}

		//Move attachments in the correct folder
		$this->moveAttachements($class, $id);

		$this->replyMessage->text = $this->summariseCreate();

		$createMsg = new TelegramMessage();
		$createMsg->text = str_replace(
			array( "{class}", "{tradclass}"  , "{ref}"                  , "{id}", "{projeqtorUrl}"                                        ),
			array(  $class  , i18nDec($class), $elementData["reference"], $id   , Parameter::getGlobalParameter("telegramBotProjeqtorUrl")),
			i18nDec("telegramBotMsgCreated")
		);
		$createMsg->chatId = $this->botUser->chatId;
		$createMsg->send();

		if ($this->botUser->getData("createClass") == "Question") {
			$buttons = array(
				array(
					array("text"=>i18nDec("telegramBotAssign"),"callback_data"=>"{'action':'assignquestion','id':$id}")
				)
			);

			$this->replyMessage->markup = array("inline_keyboard" => $buttons);
		}

		$this->endCommand();
	}

	/**
	 * Move attached file to a folder specific for the created element
	 *
	 * @param $class: class of the created element
	 * @param $id: id of the element
	 * @return void
	 **/
	private function moveAttachements($class, $id) {
		$attachments = $this->botUser->getData("attachments");

		//If no attachments, do nothing
		if ($attachments == null or count($attachments) == 0) {
			return;
		}

		$result="";
		$user= new User($this->botUser->idUser);

		Sql::beginTransaction();
		$refType = $class;
		if ($refType == "TicketSimple") {
			$refType = "Ticket";
		}

		$refId = $id;
		$error = false;

		foreach ($attachments as $fileid => $fileinfo) {
			$caption = isset($fileinfo["caption"]) ? $fileinfo["caption"] : null;
			$fileloc = $fileinfo["fileloc"];
			$filename = basename($fileinfo["fileloc"]);

			$attachment=new Attachment();

			$attachment->refId=$refId;
			$attachment->refType=$refType;
			$attachment->idUser=$this->botUser->idUser;
			
			$ress=new Resource($this->botUser->idUser);
			
			$attachment->idTeam=$ress->idTeam;

			$attachment->idPrivacy=1; //Public

			$attachment->creationDate=date("Y-m-d H:i:s");

			$attachment->fileName=trim($filename);
			$ext = strtolower ( pathinfo ( $attachment->fileName, PATHINFO_EXTENSION ) );
			if (substr($ext,0,3)=='php' or substr($ext,0,3)=='pht' or substr($ext,0,3)=='sht' or $ext=='htaccess' or $ext=='htpasswd') {
				$attachment->fileName .= ".projeqtor";
			}
			$attachment->mimeType=mime_content_type($fileloc);
			$attachment->fileSize=filesize($fileloc);

			$attachment->type="file";

			if ($caption) {
				$attachment->description = $caption;
			}

			$subResult=$attachment->save();

			$newId=$attachment->id;
			
			if (! $result) {
				$result=$subResult;

			} else {
				$pos=strpos($result, '#');

				if ($pos) {
				  $result=substr_replace($result, '#'.$newId.', #', $pos,1);
				} 
			}

			$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
			$attachmentDirectory=Parameter::getGlobalParameter('paramAttachmentDirectory');

			if (! $error) {
			  $uploaddir = $attachmentDirectory . $pathSeparator . "attachment_" . $newId . $pathSeparator;

			  if (! file_exists($uploaddir)) {
			    mkdir($uploaddir,0777,true);
			  }

			  $paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');

			  if ($paramFilenameCharset) {
			  	$uploadfile = $uploaddir . iconv("UTF-8", $paramFilenameCharset.'//TRANSLIT//IGNORE',$attachment->fileName);

			  } else {
			    $uploadfile = $uploaddir . $attachment->fileName;
			  }

			  log_msg("Moving $fileloc to $uploadfile");

			  if ( ! rename($fileloc, $uploadfile)) {
			     $error = htmlGetErrorMessage(i18n('errorUploadFile',array('hacking')));
			     //errorLog(i18n('errorUploadFile','hacking ?'));
			     //$error=true;
			     log_msg("Error");
			     $attachment->delete(); 
			  } else {
			    $attachment->subDirectory=str_replace(Parameter::getGlobalParameter('paramAttachmentDirectory'),'${attachmentDirectory}',$uploaddir);
			    $otherResult=$attachment->save();
			  }
			}

			if (! $error and $attachment->idPrivacy==1) { // send mail if new attachment is public
			  $elt=new $refType($refId);
				$mailResult=$elt->sendMailIfMailable(false,false,false,false,false,true,false,false,false,false,false,true);
				if ($mailResult) {
				  $pos=strpos($result,'<input type="hidden"');
				  if ($pos) {
					  $result=substr($result, 0,$pos).' - ' . Mail::getResultMessage($mailResult).substr($result, $pos);
				  }
				}
			}
		}

		if (! $error) {
		  // Message of correct saving
		  $status = getLastOperationStatus ( $result );
		  if ($status == "OK") {
		    Sql::commitTransaction ();
		  } else {
		    Sql::rollbackTransaction ();
		  }
		}
	}

	/**
	 * Returns an array with only the useful data to create the new element
	 *
	 * @return array of properties name => value
	 **/
	private function getCreateData() {
		$data = $this->botUser->getData();
		$createClass = $this->botUser->getData("createClass");

		$availableFields = getEditableFields($createClass);

		$elementData = getDefaultFieldValues($createClass);
		$elementData["name"] = $data["name"];

		foreach ($data as $field => $value) {
			if (array_key_exists($field, $availableFields)) {
				$elementData[$availableFields[$field]] = $value;
			}
		}

		if (isset($data["attachments"])) {
			$elementData["attachments"] = $data["attachments"];
		}

		return $elementData;
	}

	/**
	 * Displays a summary of the element being created and ask for confirmation
	 *
	 * @param $canModify: if true, a button will allow the user to go back and modify the element
	 *                    if false, a button will allow the user to abort the creation
	 * @return void
	 **/
	private function askConfirmation($canModify=true) {
		$this->replyMessage->text = i18nDec("telegramBotMsgConfirmCreate");

		$this->replyMessage->text .= "\n\n";
		$this->replyMessage->text .= $this->summariseCreate();

		if ($canModify) {
			$extraButton = array("text"=>i18nDec("telegramBotModify"), "callback_data"=>"{'action':'modify'}");
		} else {
			$extraButton = array("text"=>i18nDec("telegramBotAbort"), "callback_data"=>"{'action':'abort'}");
		}

		$buttons = array(
			array(
				$extraButton,
				array("text"=>i18nDec("telegramBotCreate"), "callback_data"=>"{'action':'create'}")
			)
		);
		$this->replyMessage->markup = array("inline_keyboard" => $buttons);
		$this->botUser->state = 50;
	}

	/**
	 * Utility function to copy projectPath from normal user data to persistent user data
	 *
	 * @return void
	 **/
	private function copyPathToPersistent() {
		$persistent = $this->botUser->getData("persistent") ?? array();
		$persistent["projectPath"] = $this->botUser->getData("projectPath");
		$this->botUser->setData("persistent", $persistent);
	}

	/**
	 * Allow the user to manage attachments
	 * for the element being created
	 *
	 * @return void
	 **/
	private function attachmentsMenu() {
		$this->botUser->state = 40;
		$this->replyMessage->text = i18nDec("telegramBotMsgAddDeleteAttachment");

		$buttons = array(
			array("text"=>i18nDec("telegramBotAddAttachment"), "callback_data"=>"{'action':'addAtt'}")
		);
		
		$attachments = $this->botUser->getData("attachments");
		if ($attachments !== null and count($attachments) > 0) {
			$buttons[] = array("text"=>i18nDec("telegramBotDeleteAttachment"), "callback_data"=>"{'action':'delAtt'}");
		}

		$this->replyMessage->markup = array(
			"inline_keyboard" => array(
				array(
					array("text"=>i18nDec("telegramBotReturn"),"callback_data"=>"{'action':'return'}")
				),
				$buttons
			)
		);
	}


	/**
	 * Allows the user to select or create a note
	 *
	 * @return void
	 **/
	private function displayNotes() {
		$id = $this->botUser->getData("id");
		$class = $this->botUser->getData("class");

		$element = new $class($id);

		$buttons = array();

		$msg = i18nDec("telegramBotMsgChooseNote")."\n```";

		if (isset($element->_Note)) {
			$notes=$element->_Note;
		} else {
			$notes=array();
		}
		$ress=new Resource($this->botUser->idUser);
		$user=new User($this->botUser->idUser);
		//damian
		$noteDiscussionMode = Parameter::getUserParameter('userNoteDiscussionMode');
		if($noteDiscussionMode == null){
			$noteDiscussionMode = Parameter::getGlobalParameter('globalNoteDiscussionMode');
		}
		
		function sortNotes(&$listNotes, &$result, $parent){
			foreach ($listNotes as $note){
				if($note->idNote == $parent){
					$result[] = $note;
					sortNotes($listNotes, $result, $note->id); 
				}
			}
		}
		if($noteDiscussionMode == 'YES'){
			$result = array();
			$notes=array_reverse($notes,true);
			sortNotes($notes, $result, null);
			$notes = $result;
		}
		foreach ($notes as $note) {
			//florent
			$userCanChange=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$user->idProfile,'scope'=>'canChangeNote'));
			if ($user->id==$note->idUser or $note->idPrivacy==1 or ($note->idPrivacy==2 and $ress->idTeam==$note->idTeam) ) {
			//
				$idUser=$note->idUser;
				$userName=SqlList::getNameFromId('User', $idUser);

				$msg .= "\n";
				
				if($noteDiscussionMode == 'YES'){
					for($i=0; $i<$note->replyLevel; $i++){
						if($i >= 5){
							break;
						}
						//$msg .= ($i % 2 == 0) ? "-" : "+";
						$msg .= "> ";
					}
					
				}

				$content = $note->note;
				$content = str_replace("&nbsp;", "", $content);
				$title = false;

				if (strpos($content, "<strong>") !== false) {
					$content = explode("<strong>", $content)[1];
					$content = explode("</strong>", $content)[0];
					$title = true;
				}
				
				$content = str_replace("\n\n", "\n", html_entity_decode(strip_tags($content), ENT_COMPAT|ENT_QUOTES, 'UTF-8'));
				if (mb_strlen($content) > 25) {
					$content = mb_substr($content, 0, 25)."...";
				
				} else if ($title) {
					$content .= "\n...";
				}

				$spaces = "";

				if ($noteDiscussionMode == 'YES') {
					$spaces = str_repeat(" ", min($note->replyLevel, 5)*2);
				}

				$content = explode("\n", $content);
				array_walk($content, function (&$value) use ($spaces) {
					$value = $spaces.$value;
				});

				$content = rtrim(implode("\n", $content))."\n";

				$msg .= "#".$note->id." - ".$userName."\n$content";

				$buttons[] = array("text"=>"#".$note->id, "callback_data"=>"{'action':'selNote','id':".$note->id."}");
			}
		}


		$msg .= "```";
		$this->replyMessage->text = $msg;

		$buttons = array_chunk($buttons, 4);
		$buttons[] = array(
			array("text"=>i18nDec("telegramBotReturn"), "callback_data"=>"{'action':'return'}"),
			array("text"=>i18nDec("telegramBotNewNote"), "callback_data"=>"{'action':'newNote'}")
		);

		$this->replyMessage->markup = array("inline_keyboard" => $buttons);
	}

	/**
	 * Display a note
	 *
	 * @return void
	 **/
	private function displayNote() {
		$note = new Note($this->botUser->getData("noteId"));
		$id = $note->id;
		$content = str_replace("\n\n", "\n", html_entity_decode(strip_tags($note->note), ENT_COMPAT|ENT_QUOTES, 'UTF-8'));
		
		$this->replyMessage->text = "Note #$id```\n$content```";

		$this->replyMessage->markup = array(
			"inline_keyboard" => array(
				array(
					array("text"=>i18nDec("telegramBotReturn"), "callback_data"=>"{'action':'return'}"),
					array("text"=>i18nDec("telegramBotNoteReply"), "callback_data"=>"{'action':'newNote'}")
				)
			)
		);
	}

	/**
	 * Allows the user to create a new note
	 *
	 * @param $replyId: id of the note to reply to
	 * @return void
	 **/
	private function newNote($replyId=null) {
		$this->replyMessage->text = i18nDec("telegramBotMsgNewNote");
		$this->botUser->state = 160;

		if ($replyId !== null) {
			$note = new Note($replyId);
			$this->replyMessage->text = str_replace(
				array("{id}","{content}"),
				array($note->id,str_replace("\n\n", "\n", html_entity_decode(strip_tags($note->note), ENT_COMPAT|ENT_QUOTES, 'UTF-8'))),
				i18nDec("telegramBotNewNoteReply")
			);
		}
	}
}


/***********************************************/
/*                 End classes                 */
/*---------------------------------------------*/
/*               Begin functions               */
/***********************************************/

/**
 * Logs a message to error_log, prepending an
 * identifier from the script
 *
 * @param $msg: msg to log
 * @return void
 **/
function log_msg($msg, $level=TG_LOG_MSG) {
	global $DEBUG_LEVEL;

	if ($level <= $DEBUG_LEVEL) {
		error_log("<[Telegram Bot]> ".$msg);
	}
}

/**
 * Logs a trace to error_log, prepending an
 * identifier from the script
 *
 * @return void
 **/
function trace() {
	$trace = debug_backtrace();
	
	error_log("<[Telegram Bot]> Trace:");

	foreach ($trace as $line) {
		error_log("    ".formatTraceLine($line));
	}
}

/**
 * Formats a line of debug_backtrace
 *
 * @param $values: a line returned by debug_backtrace()
 * @return formatted line
 **/
function formatTraceLine($values) {
	$function = $values["function"] ?? "";
	$line = $values["line"] ?? "";
	$file = $values["file"] ?? "";
	$class = $values["class"] ?? "";
	$type = $values["type"] ?? "";
	$args = $values["args"] ?? "";

	if ($args !== "") {
		$args = array_map(function ($val) {
			if (is_string($val) or is_numeric($val) or is_bool($val) or is_null($val)) {
				return json_encode($val);
			} else if (is_array($val)) {
				return "array(...)";
			} else if (is_object($val)) {
				return get_class($val)."(...)";
			} else {
				return "...";
			}
		}, $args);
		$args = implode(", ", $args);
	}

	$result = "$file:$line >> $class$type$function($args)";
	return $result;
}

/**
 * Decodes html entities from i18n
 *
 * @return decoded translation
 **/
function i18nDec($code) {
	return html_entity_decode(i18n($code));
}

/**
 * Arranges an array of buttons by making rows
 *
 * @param $keyboard: 1-D array of buttons to arrange
 * @param $min: min buttons per line
 * @param $max: max buttons per line
 * @return new arranged keyboard (array of rows of buttons)
 **/
function arrangeKeyboard($keyboard, $min, $max) {
	return array_chunk($keyboard, count($keyboard) > $max ? $min : $max);
}

/**
 * Encodes data in custom csv format (key|value,key|value,key|value)
 *
 * @param $data: data to encode
 * @return encoded data 
 **/
function csv($data) {
	$result = array();

	foreach ($data as $key => $value) {
		array_push($result, "$key|$value");
	}

	$result = implode(",", $result);

	return $result;
}

/**
 * Decodes data from custom csv format (key|value,key|value,key|value)
 *
 * @param $data: data to decode
 * @return decoded data 
 **/
function uncsv($data) {
	$result = array();

	$data = explode(",", $data);

	foreach ($data as $kv) {
		$kv = explode("|", $kv);
		$result[$kv[0]] = $kv[1];
	}

	return $result;
}

/**
 * Checks if a project is visible to the user
 *
 * @param $projectId: id of the project
 * @param $userId: id of the user
 * @return true if the project is visible, false otherwise 
 **/
function isVisibleProject($projectId, $userId) {
	$user = new User($userId);

	$visible = $user->getVisibleProjects();

	return array_key_exists($projectId, $visible);
}

/**
 * Gets a list of projects at a given path (only projects to which the user has access)
 * 
 * @param $path: root path where to look for projects (array of project ids)
 * @param $userId: id of the user
 * @param $checkElements:
 *     0: no restriction
 *     1: only returns projects with at least one child element
 *     2: only returns projects with at least one child element of class $class
 * @param $class: class of children elements (see $checkElements)
 * @return array of projects
 **/
function getProjectsFromPath($path, $userId, $checkElements=1, $class=null) {
	if ($checkElements !== 2) {
		$class = null;
	}

	if (count($path) == 0) {
		$crit = array("idle"=>"0", "idProject"=>null);
	
	} else {
		$crit = array("idle"=>"0", "idProject"=>end($path));
	}

	$sql = new Project();

	$projects = $sql->getSqlElementsFromCriteria($crit);
	
	$withElements = array();

	foreach ($projects as $project) {
		if (isVisibleProject($project->id, $userId) and hasAffectedSubProjects($project->id, $userId)) {
			if ($checkElements == 0 or count(getElementsFromProject($class, $project->id, $userId)) > 0) {
				$withElements[] = $project;
			}
		}
	}

	return $withElements;
}

/**
 * Checks if the user is affected to any sub-project of a project
 *
 * @param $projectId: id of the parent project
 * @param $userId: id of the user
 * @return true if the user is affected to a sub-project, false otherwise
 **/
function hasAffectedSubProjects($projectId, $userId) {
	$user = new User($userId);
	$affectedProjects = $user->getAffectedProjects();

	$project = new Project($projectId);
	$projectIds = array_keys($project->getRecursiveSubProjectsFlatList(true, true));

	$hasAffectedSubProjects = false;

	foreach ($projectIds as $id) {
		if (isVisibleProject($id, $userId)) {
			if (array_key_exists($id, $affectedProjects)) {
				$hasAffectedSubProjects = true;
				break;
			}
		}
	}

	return $hasAffectedSubProjects;
}

/**
 * Get all elements of a particular class from a project
 *
 * @param $class: class of the elements to retrieve
 * @param $id: id of the project
 * @param $userId: id of the user
 * @param $includeSubProjects: if true, also retrieves element from sub-projects
 * @return array of elements
 **/
function getElementsFromProject($class, $id, $userId, $includeSubProjects=true) {
	if ($includeSubProjects) {
		$project = new Project($id);
		$projectIds = array_keys($project->getRecursiveSubProjectsFlatList(true, true));
	
	} else {
		$projectIds = array($id);
	}

	$elements = array();

	$sql = new $class();

	foreach ($projectIds as $projectId) {
		if (isVisibleProject($projectId, $userId)) {
			$result = $sql->getSqlElementsFromCriteria(array("idle"=>"0", "idProject"=>$projectId));

			$elements = array_merge($elements, $result);
		}
	}

	return $elements;
}

/**
 * <[ WILL CHANGE ]>
 * 
 * Gets the list of editable fields for a given class
 * 
 * If the class doesn't have any editable field, it returns an empty array
 *
 * @return array of abreviation => field name
 **/
function getEditableFields($class) {
	$fields = array(
		"Ticket" => array(
			"description" => "description",
			"type" => "idTicketType",
			"project" => "idProject",
			"responsible" => "idResource",
			"urgency" => "idUrgency",
			"criticality" => "idCriticality",
			"context1" => "idContext1",
			"context3" => "idContext3",
			"activity" => "idActivity",
			"estimatedWork" => "estimatedWork"
		),
		"Question" => array(
			"description" => "description",
			"type" => "idQuestionType",
			"project" => "idProject",
			"responsible" => "idResource",
		)
	);

	if (array_key_exists($class, $fields)) {
		return $fields[$class];
	}

	return array();
}

/**
 * Returns the default values $class
 * If the class doesn't have any default values, it returns an empty array
 *
 * @param $class: name of the class
 * @return default field values
 **/
function getDefaultFieldValues($class) {
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

	if (array_key_exists($class, $defaultFields)) {
		return $defaultFields[$class];
	}

	return array();
}

/**
 * Returns a list of users affected to a project.
 * Each entry has these keys:
 *   id: user id
 *   name: user name
 *   order: sorting order of the user's profile
 *
 * @param $project: a project
 * @return array of entries as described above
 **/
function getAffectations($project) {
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

/**
 * Returns a list of classes which can be displayed with the search command
 *
 * @return array of class names
 **/
function getDisplayableClasses() {
	$disTmp = new TelegramDisplayTemplate();
	$templates = $disTmp->getSqlElementsFromCriteria(array("idle" => "0"));

	$classes = array();
	foreach ($templates as $template) {
		$mailable = new Mailable($template->idMailable);
		$class = $mailable->name;

		if (!in_array($class, $classes)) {
			$classes[] = $class;
		}
	}

	return $classes;
}

/**
 * Converts a database table name to the correct capitalized name of the class
 *
 * @param $table: name of the table
 * @return correct capitalized name of the corresponding class
 **/
function capitalizeClass($table) {
	$classes = array(
		"action" => "Action",
		"activity" => "Activity",
		"assettransaction" => "AssetTransaction",
		"bill" => "Bill",
		"callfortender" => "CallForTender",
		"changerequest" => "ChangeRequest",
		"command" => "Command",
		"decision" => "Decision",
		"deliverable" => "Deliverable",
		"delivery" => "Delivery",
		"document" => "Document",
		"documentdirectory" => "DocumentDirectory",
		"expense" => "Expense",
		"globalview" => "GlobalView",
		"issue" => "Issue",
		"meeting" => "Meeting",
		"milestone" => "Milestone",
		"opportunity" => "Opportunity",
		"providerbill" => "ProviderBill",
		"providerorder" => "ProviderOrder",
		"question" => "Question",
		"quotation" => "Quotation",
		"requirement" => "Requirement",
		"risk" => "Risk",
		"tender" => "Tender",
		"testcase" => "TestCase",
		"testsession" => "TestSession",
		"ticket" => "Ticket",
		"workunit" => "WorkUnit"
	);

	if (array_key_exists($table, $classes)) {
		return $classes[$table];
	}

	return $table;
}

/***********************************************/
/*                End functions                */
/*---------------------------------------------*/
/*                Begin script                 */
/***********************************************/


// Get values passed by node-red
$input = json_decode(file_get_contents("php://input"),TRUE);

$replyMessage = new TelegramMessage();

// Check access to bot
$chatId = $input["chatId"];

$userWithChatId = SqlElement::getSingleSqlElementFromCriteria("User", array("chatIdTelegram"=>$chatId));

$hasAccess = true;

if (!isset($userWithChatId->id)) {
	$replyMessage->chatId = $chatId;
	$replyMessage->text = i18nDec("telegramBotMsgNoAccess");

	$hasAccess = false;
}

if ($hasAccess) {
	//Get the corresponding bot user

	$userId = $userWithChatId->id;
	unset($userWithChatId);

	$botUser = SqlElement::getSingleSqlElementFromCriteria("TelegramBotUser", array("idUser" => $userId));

	if (!$botUser or !$botUser->id) {
		$botUser = new TelegramBotUser();
		$botUser->idUser = $userId;
		$botUser->chatId = $chatId;
		$botUser->save();
	}

	unset($userId);
	unset($chatId);

	$ch = new CommandHandler();
	$ch->handle($input, $botUser, $replyMessage);

	$botUser->save();
}

$result = $replyMessage->send();
if ($result) {
	//error_log($result);

	$result = json_decode($result, true);
	if (isset($replyMessage->markup)) {
		if (isset($botUser)) {
			$botUser->buttonMsgId = $result["result"]["message_id"];
			$botUser->save();
		}
	}
}

?>