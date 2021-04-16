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

$data = json_decode(file_get_contents("php://input"),TRUE);

$chatId = $data["chatId"];

log_msg("Getting chatId ($chatId)");
$tgChat = get_req("https://api.telegram.org/botBOT-TOKEN/getChat?chat_id=$chatId");

$alright = false;

if ($tgChat["ok"] == true) {
	$idTelegram = "@".$tgChat["result"]["username"];

	log_msg("Getting Resource info");
	$resource = get_req("PROJEQTOR-URL/api/Resource/search/idTelegram='$idTelegram'", true);

	if (isset($resource["items"][0]["id"])) {
		require("../external/phpAES/aes.class.php");
		require("../external/phpAES/aesctr.class.php");

		log_msg("Updating Resource fields");
		$resourceId = $resource["items"][0]["id"];

		$url = "PROJEQTOR-URL/api/User/";

		$data = '{"id": '.$resourceId.', "chatIdTelegram": '.$chatId.'}';
		$data = AesCtr::encrypt($data, 'PROJEQTOR-API', 128);

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

		error_log(json_encode($curl_response));

		$alright = true;
	}
}

$msg = array(
	"chatId" => $chatId,
	"content" => $alright ? "Initialisation effectuée avec succès ✅" : "Il y a eu un problème ❌",
	"type" => "message"
);

echo json_encode($msg);

?>
