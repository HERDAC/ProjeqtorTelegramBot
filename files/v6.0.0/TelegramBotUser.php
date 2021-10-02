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
require_once('_securityCheck.php');
class TelegramBotUser extends SqlElement {
	public $id;
	public $idUser;
	public $chatId;
	public $state = 0;
	public $buttonMsgId = null;
	public $data = null;

	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct($id,$withoutDependentObjects);
	}

	function __destruct() {
		parent::__destruct();
	}

	function save() {
		parent::save();
	}

	function setData($key, $value) {
		$data = $this->getData();

		if ($key === null) {
			$data = $value;

		} else {
			$data[$key] = $value;
		}

		$this->data = json_encode($data);
	}

	function getData($key=null) {
		$data = array();

		if (isset($this->data)) {
			$data = json_decode($this->data, true);
		}

		if ($key === null) {
			$value = $data;
		} else {
			$value = array_key_exists($key, $data) ? $data[$key] : null;
		}

		return $value;
	}
}
?>