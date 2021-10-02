<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 *
 * ProjeQtOr is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.    See the GNU Affero General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org
 *
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/* ============================================================================
 * Menu defines list of items to present to users.
 */

require_once('_securityCheck.php');

class TelegramSummaryTemplate extends SqlElement {
	public $_sec_description;
	public $id;
	public $idMailable;
	public $idle;
	public $_sec_message;
	public $template;
	//public $_sec_void;
	//Damian
	public $_spe_listItemTemplate;
	//public $_spe_buttonInsertInTemplate;

	private static $_fieldsAttributes=array("idMailable"=>"",
			"template"=>"required"
	);    
	private static $_colCaptionTransposition = array();
	private static $_layout='
	<th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
	<th field="nameMailable" width="80%" formatter="nameFormatter">${idMailable}</th>
	<th field="idle" width="10%" formatter="booleanFormatter" >${idle}</th>
	';
	
	
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct($id,$withoutDependentObjects);
	}
	
	
	/** ==========================================================================
	 * Destructor
	 * @return void
	 */
	function __destruct() {
		parent::__destruct();
	}

	// ============================================================================**********
	// GET STATIC DATA FUNCTIONS
	// ============================================================================**********
	
	/** ==========================================================================
	 * Return the specific layout
	 * @return the layout
	 */
	protected function getStaticLayout() {
		return self::$_layout;
	}
	/** ============================================================================
	 * Return the specific colCaptionTransposition
	 * @return the colCaptionTransposition
	 */
	protected function getStaticColCaptionTransposition($fld=null) {
		return self::$_colCaptionTransposition;
	}
	
	protected function getStaticFieldsAttributes() {
		return self::$_fieldsAttributes;
	}
	
	private function getMailableItem() {
		$mailableItem = null;
		if ($this->id) {
			$mailableItem = new Mailable($this->idMailable);
		}

		return $mailableItem;
	}

	private function getField($field) {
		$fields = array(
			"name" => "colName",
			"description" => "colDetail",
			"type" => "colType",
			"project" => "colIdProject",
			"urgency" => "colIdUrgency",
			"criticality" => "colIdCriticality",
			"context" => "colContext",
			"activity" => "colIdActivity",
			"responsible" => "colResponsible",
			"estimatedWork" => "colEstimatedWork",
			"attachments" => "fileAttachment"
		);

		return $fields[$field] ?? null;
	}
	
	public function drawListItem($item,$readOnly=false,$refresh=false) {
		global $largeWidth, $print, $toolTip, $outMode;
		
		if ($print or $outMode=="pdf" or $readOnly) {
			return("");
		}
		
		$itemLab = "listFieldsTitle";
		$itemEnd = str_replace("listItem","", $item);

		$arrayFields = array(
			"_name" => i18n($this->getField("name")),
			"_description" => i18n($this->getField("description")),
			"_type" => i18n($this->getField("type")),
			"_project" => i18n($this->getField("project")),
			"_urgency" => i18n($this->getField("urgency")),
			"_criticality" => i18n($this->getField("criticality")),
			"_context" => i18n($this->getField("context")),
			"_activity" => i18n($this->getField("activity")),
			"_responsible" => i18n($this->getField("responsible")),
			"_estimatedWork" => i18n($this->getField("estimatedWork")),
			"_attachments" => i18n($this->getField("attachments"))
		);

		$fieldAttributes=$this->getFieldAttributes($item);
		if(strpos($fieldAttributes,'required')!==false) {
			$isRequired = true;
		} else {
			$isRequired = false;
		}
		$notReadonlyClass=($readOnly?"":" generalColClassNotReadonly ");
		$notRequiredClass=($isRequired?"":" generalColClassNotRequired ");
		$style=$this->getDisplayStyling($item);
		$labelStyle=$style["caption"];
		$fieldStyle=$style["field"];
		$fieldWidth=$largeWidth;
		$extName="";
		$fullItem = "_spe_$item";
		$name=' id="' . $fullItem . '" name="' . $fullItem . $extName . '" ';
		$attributes =' required="true" missingMessage="' . i18n('messageMandatory', array($this->getColCaption($itemLab))) . '" invalidMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '"';
		
		//$colScript    = '';
		
		$result    = '<table><tr class="detail generalRowClass">';
		$result .= '<td class="label" style="font-weight:normal;"><label>' . i18n("col".ucfirst($itemLab));
		$result .= '&nbsp;:&nbsp;</label></td>';
		$result .= '<td>';
		$result .= '<select dojoType="dijit.form.Select" class="input '.(($isRequired)?'required':'').' generalColClass '.$notReadonlyClass.$notRequiredClass.$item.'Class"';
		$result .= '    style="width: ' . ($fieldWidth-150) . 'px;' . $fieldStyle . '; "';
		$result .= $name;
		$result .=$attributes;
		$result .=">";
		
		$first=true;
		foreach ($arrayFields as $key => $value) {
			$result .= '<option value="' . $key . '"';
			if($first) {
				$result .= ' selected="selected" ';
				$first=false;
			}
			$result .= '> <span>'. ucfirst(htmlEncode($value)) . '</span>
									</option>';
		}
		//$result .=$colScript;
		$result .="</select>";
		$itemEnd = str_replace("buttonAddIn","", $item);
		$editor = getEditorType();
		$textBox = strtolower($itemEnd);;
		$result .= '<button id="_spe_listItemTemplate_button" dojoType="dijit.form.Button" showlabel="true" style="position:relative;top:-2px;width:145px;height:17px">';
		$result .= i18n('operationInsert');
		$result .= '<script type="dojo/connect" event="onClick" args="evt">';
		$result .= '    addFieldInTextBoxForEmailTemplateItem("text");';
		$result .= '    formChanged();';
		$result .= '</script>';
		$result .= '</button>';
		$result .='</td>';
		$result .= '</tr></table>';
		return $result;
	}

	
	public function drawSpecificItem($item,$readOnly=false,$refresh=false){
		if ($item=='listItemTemplate') {
			return $this->drawListItem($item, $readOnly, $refresh);
		} 
		return "";
	}
	
	public function control(){
		$result="";

		preg_match_all('(\$\{([a-zA-Z0-9_\-]+)\})', $this->template, $matches, PREG_SET_ORDER);

		$nonExistent = array();

		foreach ($matches as $field) {
			$field = $field[1];
			if ($this->getField($field) === null) {
				$nonExistent[] = $field;
			}
		}

		if (count($nonExistent) > 0) {
			$result = i18n("nonExistentFields").": ".implode(", ", $nonExistent);
		}

		if ($result=="") {
			$result='OK';
		}
		return $result;
	}

	public function format($data) {
		$result = "";

		$result = $this->parseTelegramMessage($this->template, $data);

		return $result;
	}

	public function parseTelegramMessage($template, $data) {
		$fields = array();

		preg_match_all('(\$\{([a-zA-Z0-9_\-]+)\})', $template, $matches, PREG_SET_ORDER);

		foreach ($matches as $field) {
			$field = $field[1];
			$value = null;

			if ($field == "estimatedWork" and isset($data["estimatedWork"])) {
				$value = "`".$data["estimatedWork"]."h`";
			
			} else if ($field == "context") {
				if (isset($data["context1"]) or isset($data["context2"]) or isset($data["context3"])) {
					$ctxt1 = $data["context1"] ?? "X";
					$ctxt2 = $data["context2"] ?? "X";
					$ctxt3 = $data["context3"] ?? "X";

					if ($ctxt1 != "X") {
						$ctxt1 = (new Context1($ctxt1))->name;
					}
					if ($ctxt2 != "X") {
						$ctxt2 = (new Context2($ctxt2))->name;
					}
					if ($ctxt3 != "X") {
						$ctxt3 = (new Context3($ctxt3))->name;
					}
					
					$value = "`$ctxt1 — $ctxt2 — $ctxt3`";
				}
			
			} else if ($field == "attachments" and isset($data["attachments"])) {
				$attachments = $data["attachments"];

				$line = count($attachments) . "```";
				
				foreach ($attachments as $attId => $attInfo) {
					$line .= "\n  ";
					$filename = basename($attInfo["fileloc"]);
					if (isset($attInfo["caption"])) {
						$line .= $attInfo["caption"]." ($filename)";
					
					} else {
						$line .= $filename;
					}
				}
				$line .= "\n```";

				$value = $line;

			} else if ($field == "responsible" and isset($data["responsible"])) {
				$value = "`".(new Resource($data["responsible"]))->name."`";
			
			} else {
				if (isset($data[$field])) {
					$trans = $this->getField($field);
					$value = $data[$field];

					if ($field == "type" or strpos($trans, "Id") !== false) {
						$obj = new $field($data[$field]);
						$value = $obj->name;
					}

					if ($field == "description") {
						$value = "```\n$value\n```";
					} else {
						$value = "`$value`";
					}
				}
			}

			if ($value !== null) {
				$trans = $this->getField($field);
				$fields[$trans] = $value;
			}
		}

		$summary = array();
		
		foreach ($fields as $name => $value) {
			$summary[] = "*".ucfirst(i18n($name))."*: ".$value;
		}

		return implode("\n", $summary);
	}
}
