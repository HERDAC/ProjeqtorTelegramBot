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

class TelegramDisplayTemplate extends SqlElement {
	private static $emojis = array(
		"checkbox0" => "❌",
		"checkbox1" => "✅"
	);
	
	public $_sec_description;
	public $id;
	public $name;
	public $idMailable;
	public $idType;
	public $idStatus;
	public $idle;
	public $_sec_message;
	public $template;
	//public $_sec_void;
	//Damian
	public $_spe_listItemTemplate;
	//public $_spe_buttonInsertInTemplate;
	public $_sec_tgButtons;
	public $butReturn;
	public $butAssign;
	public $butWork;
	public $butReply;
	public $butSend;

	private static $_fieldsAttributes=array("idMailable"=>"",
			"idType"=>"nocombo",
			"name"=>"required",
			"template"=>"required"
	);    
	private static $_colCaptionTransposition = array(
			'idType' => 'type'
	);
	private static $_layout='
	<th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
	<th field="name" width="60%" >${name}</th>
	<th field="nameMailable" width="15%" formatter="nameFormatter">${idMailable}</th>
	<th field="nameType" width="15%" formatter="nameFormatter">${type}</th>
    <th field="colorNameStatus" width="6%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="butReturn" width="5%" formatter="booleanFormatter" >${butReturn}</th>
    <th field="butAssign" width="5%" formatter="booleanFormatter" >${butAssign}</th>
    <th field="butWork" width="5%" formatter="booleanFormatter" >${butWork}</th>
    <th field="butReply" width="5%" formatter="booleanFormatter" >${butReply}</th>
    <th field="butSend" width="5%" formatter="booleanFormatter" >${butSend}</th>
	<th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
	';
	
	
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct($id,$withoutDependentObjects);
		$this->updateButtons();
	}
	
	
	/** ==========================================================================
	 * Destructor
	 * @return void
	 */
	function __destruct() {
		parent::__destruct();
	}
	
	private function updateButtons() {
		$mailable = new Mailable($this->idMailable);

		if ($mailable->name=='Ticket') {
			self::$_fieldsAttributes["butWork"] = "";
		} else {
			self::$_fieldsAttributes["butWork"] = "invisible";
		}

		if ($mailable->name=='Question') {
			self::$_fieldsAttributes["butReply"] = "";
			self::$_fieldsAttributes["butSend"] = "";
		} else {
			self::$_fieldsAttributes["butReply"] = "invisible";
			self::$_fieldsAttributes["butSend"] = "invisible";
		}
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
		//$this->updateButtons();
		return self::$_fieldsAttributes;
	}
	
	public function getValidationScript($colName) {
		//error_log("getValidationScript($colName)");

		$colScript = parent::getValidationScript($colName);

		$updateButtons = "";
		$updateButtons .= '    function hideButton(id) {';
		$updateButtons .= '      dojo.byId(id).parentNode.parentNode.parentNode.style.display="none";';
		$updateButtons .= '      dojo.byId(id).parentNode.style.display="none";';
		$updateButtons .= '      dojo.byId(id).parentNode.parentNode.parentNode.firstChild.firstChild.style.display="none";';
		$updateButtons .= '    }';
		$updateButtons .= '    function showButton(id) {';
		$updateButtons .= '      dojo.byId(id).parentNode.parentNode.parentNode.style.display="";';
		$updateButtons .= '      dojo.byId(id).parentNode.style.display="";';
		$updateButtons .= '      dojo.byId(id).parentNode.parentNode.parentNode.firstChild.firstChild.style.display="";';
		$updateButtons .= '    }';
		$updateButtons .= '    dojo.xhrGet({';
		$updateButtons .= '      url : "../tool/telegramDisplayTemplateVisibleButtons.php?idMailable=" + dijit.byId("idMailable").get("value"),';
		$updateButtons .= '      handleAs : "json",';
		$updateButtons .= '      load : function(buttons) {';
		$updateButtons .= '        buttons["hidden"].forEach(but => hideButton(but));';
		$updateButtons .= '        buttons["visible"].forEach(but => showButton(but));';
		$updateButtons .= '      },';
		$updateButtons .= '      error : function() {}';
		$updateButtons .= '    });';
		
		if ($colName=='idMailable') {
			$colScript .= '<script type="dojo/connect" event="onChange" args="evt">';
			$colScript .= $updateButtons;
			$colScript .= '    dijit.byId("idType").set("value",null);';
			$colScript .= '    refreshList("idType","scope", mailableArray[this.value]);';
			$colScript .= '    refreshListFieldsInTemplate(dijit.byId("idMailable").get("value"));';
			$colScript .= '    formChanged();';
			$colScript .= '</script>';
			$colScript .= '<script type="dojo/connect" event="onLoad" args="evt">';
			$colScript .= '    refreshListFieldsInTemplate(dijit.byId("idMailable").get("value"));';
			$colScript .= '    formChanged();';
			$colScript .= '</script>';
		
		}
		
		return $colScript;
	}
	
	
	private function getMailableItem() {
		$mailableItem = null;
		if ($this->id) {
			$mailableItem = new Mailable($this->idMailable);
		}

		return $mailableItem;
	}
	
	public function drawListItem($item,$readOnly=false,$refresh=false) {
		global $largeWidth, $print, $toolTip, $outMode;
		
		if ($print or $outMode=="pdf" or $readOnly) {
			return("");
		}
		
		$itemLab = "listFieldsTitle";
		$itemEnd = str_replace("listItem","", $item);
		
		$arrayFields = array();
		$newArrayFields = array();
		if($this->getMailableItem() != null){
			$mailableItem=$this->getMailableItem();
			if ($mailableItem->id != null) {
				$nameMailableItem = SqlList::getFieldFromId('Mailable', $mailableItem->id, 'name',false);
				$arrayFields = getObjectClassTranslatedFieldsList(trim($nameMailableItem));
				foreach ($arrayFields as $elmt=>$val){
					$newArrayFields[$elmt]=$val;
					if(substr($elmt, 0, 2) == "id" and substr($elmt, 2) != "" and $elmt != "idle" and $elmt != "idleDateTime"){
						$newArrayFields['name'.ucfirst(substr($elmt, 2))]=$val.' ('.i18n('colName').')';
					}
				}
			}else{
				$newArrayFields['_id'] = 'id';
				$newArrayFields['_name'] = i18n('colName');
				$newArrayFields['_idProject'] = 'id'.i18n('colIdProject');
				$newArrayFields['_nameProject'] = i18n('colIdProject').' ('.i18n('colName').')';
				$newArrayFields['_description'] = 'colDescription';
			}
		}else{
			$newArrayFields['_id'] = 'id';
			$newArrayFields['_name'] = i18n('colName');
			$newArrayFields['_idProject'] = 'id'.i18n('colIdProject');
			$newArrayFields['_nameProject'] = i18n('colIdProject').' ('.i18n('colName').')';
			$newArrayFields['_description'] = 'colDescription';
		}
		$newArrayFields['_item'] = i18n('mailableItem');
		$newArrayFields['_dbName'] = i18n('mailableDbName');
		$newArrayFields['_responsible'] = i18n('colResponsible').', '.i18n('synonymResponsible');
		$newArrayFields['_sender'] = i18n('mailableSender');
		$newArrayFields['_project'] = i18n('colIdProject').', '.i18n('synonymProject');
		$newArrayFields['_url'] = i18n('mailableUrl');
		$newArrayFields['_goto'] = i18n('mailableGoto');
		//$newArrayFields['_HISTORY'] = i18n('mailableHistory');
		//$newArrayFields['_HISTORYFULL'] = i18n('mailableHistoryFull');
		$newArrayFields['_LINK'] = i18n('mailableLink');
		$newArrayFields['_NOTE'] = i18n('mailableNote');
		$newArrayFields['_NOTESTD'] = i18n('mailableNoteTd');
		//$newArrayFields['_allAttachments'] = i18n('mailableAttachments');
		//$newArrayFields['_lastAttachment'] = i18n('mailableLastAttachments');
		if($this->getMailableItem() != null){
			if($mailableItem->name=="Meeting" OR $mailableItem->name=="TestSession" OR $mailableItem->name=="Activity"){
				$newArrayFields['ASSIGNMENT'] = i18n('colListAssignment');
			}
		}
		$arrayFields = $newArrayFields;
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
			$result .= '> <span>'. htmlEncode($value) . '</span>
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
		if(strpos($this->template,'${lastAttachment}')and strpos($this->template, '${allAttachments}')){
			$result=i18n('onlyOne',array('${lastAttachment}/${allAttachments}'));
		}else if(mb_substr_count ($this->template,'${lastAttachment}')>1 or mb_substr_count ($this->template, '${allAttachments}')>1){
			$var='${lastAttachment}';
			if(strpos($this->template,'${allAttachments}')){
				$var='${allAttachments}';
			}
			$result=i18n('onlyOne',array($var));
		}            
		if ($result=="") {
			$result='OK';
		}
		return $result;
	}

	public function format($object) {
		$result = "";

		$result = $this->getTelegramDetailFromTemplate($this->template, $object);

		return $result;
	}

	public function getTelegramReferenceUrl($object) {
		$ref = Parameter::getGlobalParameter("telegramBotProjeqtorUrl");
		$ref .= '/view/main.php?directAccess=true&objectClass=' . get_class ( $object ) . '&objectId=' . $object->id;
		return $ref;
	}

	private function formatDateTime($val) {
		$date = new DateTime($val);
		return $date->format("H:i d/m");
	}
	private function formatDate($val) {
		$date = new DateTime($val);
		return $date->format("d/m/y");
	}

	private function displayTelegramCheckbox($val) {
		if ($val != '0' and !$val==null) {
			return self::$emojis["checkbox1"];
		}
		return self::$emojis["checkbox0"];
	}

	public function parseTelegramMessage($message, $obj) {
		$arrayFrom = array();
		$arrayTo = array();
		$objectClass = get_class($obj);
		if ($objectClass == 'TicketSimple') { $objectClass = 'Ticket'; }
		$item = i18n ( $objectClass );
		if ($objectClass == 'Project') {
			$project = $obj;
		} else if (property_exists ( $obj, 'idProject' )) {
			$project = new Project ( $obj->idProject );
		} else {
			$project = new Project ();
		}
		
		// db display name
		$arrayFrom [] = '${dbName}';
		$arrayTo [] = Parameter::getGlobalParameter ( 'paramDbDisplayName' );
		
		// Class of item
		$arrayFrom [] = '${item}';
		$arrayTo [] = $item;
		
		// id
		$arrayFrom [] = '${id}';
		$arrayTo [] = $obj->id;
		
		// name
		$arrayFrom [] = '${name}';
		$arrayTo [] = (property_exists ( $obj, 'name' )) ? $obj->name : '';
		
		// status
		$arrayFrom [] = '${status}';
		$arrayTo [] = (property_exists ( $obj, 'idStatus' )) ? SqlList::getNameFromId ( 'Status', $obj->idStatus ) : '';
		
		// project
		$arrayFrom [] = '${project}';
		$arrayTo [] = $project->name;
		
		// type
		$typeName = 'id' . $objectClass . 'Type';
		$arrayFrom [] = '${type}';
		$arrayTo [] = (property_exists ( $obj, $typeName )) ? SqlList::getNameFromId ( $objectClass . 'Type', $obj->$typeName ) : '';
		
		// reference
		$arrayFrom [] = '${reference}';
		$arrayTo [] = (property_exists ( $obj, 'reference' )) ? $obj->reference : '';
		
		// externalReference
		$arrayFrom [] = '${externalReference}';
		$arrayTo [] = (property_exists ( $obj, 'externalReference' )) ? $obj->externalReference : '';
		
		// issuer
		$arrayFrom [] = '${issuer}';
		$arrayTo [] = (property_exists ( $obj, 'idUser' )) ? SqlList::getNameFromId ( 'User', $obj->idUser ) : '';
		
		// responsible
		$arrayFrom [] = '${responsible}';
		$arrayTo [] = (property_exists ( $obj, 'idResource' )) ? SqlList::getNameFromId ( 'Resource', $obj->idResource ) : '';
		
		// sender
		$arrayFrom [] = '${sender}';
		$user = getSessionUser ();
		$arrayTo [] = ($user->resourceName) ? $user->resourceName : $user->name;
		
		// context1 to context3
		$arrayFrom [] = '${context1}';
		$arrayFrom [] = '${context2}';
		$arrayFrom [] = '${context3}';
		$arrayTo [] = (property_exists ( $obj, 'idContext1' )) ? SqlList::getNameFromId ( 'Context', $obj->idContext1 ) : '';
		$arrayTo [] = (property_exists ( $obj, 'idContext2' )) ? SqlList::getNameFromId ( 'Context', $obj->idContext2 ) : '';
		$arrayTo [] = (property_exists ( $obj, 'idContext3' )) ? SqlList::getNameFromId ( 'Context', $obj->idContext3 ) : '';
		
		// sponsor
		$arrayFrom [] = '${sponsor}';
		$arrayTo [] = SqlList::getNameFromId ( 'Sponsor', $project->idSponsor );
		
		// projectCode
		$arrayFrom [] = '${projectCode}';
		$arrayTo [] = $project->projectCode;
		
		// ContractCode
		$arrayFrom [] = '${contractCode}';
		$arrayTo [] = $project->contractCode;
		
		// Customer
		$arrayFrom [] = '${customer}';
		$arrayTo [] = SqlList::getNameFromId ( 'Client', $project->idClient );
		
		// url (direct access to item)
		$arrayFrom [] = '${url}';
		if ($objectClass == 'User') {
			// FIX FOR IIS
			$arrayTo [] = self::getBaseUrl ();
		} else {
			$arrayTo [] = $this->getTelegramReferenceUrl ($obj);
		}
		
		// login
		$arrayFrom [] = '${login}';
		$arrayTo [] = ($objectClass == 'User') ? $obj->name : getSessionUser ()->name;
		
		// password
		$arrayFrom [] = '${password}';
		$arrayTo [] = ($objectClass=='User')?(($obj->crypto==null)?$obj->password:'('.i18n("passwordAlreadyChanged").')'):'';
		
		// admin mail
		$arrayFrom [] = '${adminMail}';
		$arrayTo [] = Parameter::getGlobalParameter ( 'paramAdminMail' );
		
		// Format title
		return str_replace ( $arrayFrom, $arrayTo, $message );
	}

	public function getTelegramDetailFromTemplate($templateToReplace, $object, $lastChangeDate=null) {
		global $lastEmailChangeDate, $obj;
		$obj = $object;
		$lastEmailChangeDate=$lastChangeDate;
		$templateToReplace = $this->parseTelegramMessage($templateToReplace, $object);
		$ref = $this->getTelegramReferenceUrl ($object);
		
		return preg_replace_callback('(\$\{[a-zA-Z0-9_\-]+\})',
			function ($matches) {
				global $lastEmailChangeDate, $obj;
				$property = trim($matches[0], '${}');
				if (property_exists($obj, $property)) {
					if (isset($obj, $property) and $obj->$property != '') {
						if (substr($property,-8)=='DateTime') {
							return $this->formatDateTime($obj->$property,false, true);
						} else if (substr($property,-4)=='Date') {
							return $this->formatDate($obj->$property,true);
						} else if ($property=="WorkElement") {
							$workelement = $obj->WorkElement;
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

								return "*Travail*: `".$planned."h - ".$real."h = ".$left."h`";
							} else {
								return "-";
							}

						} else {
							if (substr($property,0,2)=='is') {
								return $this->displayTelegramCheckbox($obj->$property);
							} else {
								if ($property=="idStatus") {
									$status_obj = new Status($obj->$property);
									if (isset($status_obj->tgEmoji)) {
										return $status_obj->tgEmoji;
									}
								} else if ($property == "description" or $property == "result") {
									return html_entity_decode(strip_tags($obj->$property), ENT_COMPAT|ENT_QUOTES, 'UTF-8');
								}
								return $obj->$property;
							}
						}
					} else {
						return "-";
					}
				} else if (substr($property,0,4)=='name' and $property!='name' and substr($property,4,1)==strtoupper(substr($property,4,1)) ){
					$cls=substr($property,4);
					$fld='id'.$cls;
					if (strpos($property,'__id')>0) {
						$expl=explode('__',$property);
						$cls=substr($expl[1],2);
					}
					if (property_exists($obj, $fld)) {
						if ($cls=='User' or $cls=='Resource' or $cls=='Contact') $cls='Affectable';

						$extra = "";
						if ($cls=='Status') {
							$status_obj = new Status($obj->$fld);

							if (isset($status_obj->tgEmoji)) {
								$extra = " ".$status_obj->tgEmoji;
							}
						}
						return SqlList::getNameFromId($cls, $obj->$fld).$extra;
					} else {
						return "\$$fld not a property to define $property of " . get_class($obj);
					}
				} else if (substr($property,-4)=='Date' and property_exists($obj, $property.'Time')) {
					$propertyTime=$property.'Time';
					return $this-formatDate($obj->$propertyTime);
				} else if ($property == 'responsible' and property_exists($obj, 'idResource')) { 
					return SqlList::getNameFromId('Affectable', $obj->idResource);
				} else if ($property == 'dbName') {
					return Parameter::getGlobalParameter('paramDbName');
				} else if ($property == 'goto') {
					$goto = $this->getTelegramReferenceUrl ($obj);
					return '['.i18n(get_class($obj)) . ' #' . $obj->id.']('.$goto.')';
				} else if ($property == "project") {
					if (property_exists($obj, 'idProject')) {
						return SqlList::getNameFromId('Project', $obj->idProject);
					} else {
						return "-";
					}
				}else if($property == 'ASSIGNMENT'){
					return $this->getAssignmentTab($obj);
				} else if ($property == 'sender') {
					return SqlList::getNameFromId('Affectable', getCurrentUserId());
				} else if ($property == 'url') {
					return $this->getTelegramReferenceUrl($obj);
				} else if ($property == 'HISTORY') {
					return $this->getLastChangeTabForObject($obj,$lastEmailChangeDate);
				} else if ($property == 'HISTORYFULL') {
					return $this->getLastChangeTabForObject($obj,'full');                
				} else if ($property == 'LINK') {
					return $this->getLinksTab($obj);
				} else if ($property == 'NOTE') {
					return $this->getNotesTab($obj);

				/*}else if($property == 'NOTESTD'){
					//florent
					$rowStart = '<tr>';
					$rowEnd = '</tr>';
					$labelStart = '<td style="background:#DDDDDD;font-weight:bold;text-align: right;width:25%;vertical-align: middle;">&nbsp;&nbsp;';
					$labelEnd = '&nbsp;</td>';
					$fieldStart = '<td style="width:2px;">&nbsp;</td><td style="background:#FFFFFF;text-align: left;">';
					$fieldEnd = '</td>';
					$sectionStart = '<td colspan="3" style="background:#555555;color: #FFFFFF; text-align: center;font-size:10pt;font-weight:normal;">';
					$sectionEnd = '</td>';
					$notes = '<table style="font-size:9pt; width: 95%;font-family: Verdana, Arial, Helvetica, sans-serif;">';
					$notes =$this->getNotesClassicTab($notes, $rowStart, $rowEnd, $sectionStart, $sectionEnd, $labelStart, $labelEnd, $fieldStart, $fieldEnd);
					$notes .='</table>';
					return $notes; */
				}else if ($property == 'allAttachments') {
					return;
				} else if ($property == 'lastAttachment') {
					return;
				}
				 else {
					return "\$$property not a property of " . get_class($this);
				}
			},
			$templateToReplace);
	}

	private function formatTable($headers, $rows) {
		$result = "";

		$maxWidths = array();

		for ($i=0; $i<count($headers); $i++) {
			$header = $headers[$i];
			$maxWidths[$i] = 0;
			if (strlen($header) > $maxWidths[$i]) {
				$maxWidths[$i] = mb_strlen($header);
			}
		}

		foreach ($rows as $row) {
			for ($i=0; $i<count($row); $i++) {
				$cell = $row[$i];
				if (strlen($cell) > $maxWidths[$i]) {
					$maxWidths[$i] = mb_strlen($cell);
				}
			}
		}

		/*
		for ($i=0; $i<count($headers); $i++) {
			$header = $headers[$i];
			$result .= "$header ".str_repeat(" ", $maxWidths[$i]-mb_strlen($header));
		}*/

		foreach ($rows as $row) {
			for ($i=0; $i<count($row); $i++) {
				$cell = $row[$i];
				$result .= "$cell ";//.str_repeat(" ", $maxWidths[$i]-mb_strlen($cell));
			}
			$result .= "\n\n";
		}

		return $result;
	}

	private function getAssignmentTab($obj){
		$ass = new Assignment();
		$class=get_class($obj);
		$id=$obj->id;
		$crit = " refType='$class' and refId=$id ";
		$linkAss = $ass->getSqlElementsFromCriteria(null,null,$crit);

		$rows = array();
		foreach ($linkAss as $link) {
			array_push($rows, array(SqlList::getNameFromId('Affectable', $link->idResource)));
		}
		
		return $this->formatTable(array(ucfirst(i18n('assignedResourceList'))), $rows);
	}

	private function getLinksTab($object) {
		$link = new Link;
		$class=get_class($object);
		$id=$object->id;
		$crit = " (ref1Type='$class' and ref1Id=$id ) or (ref2Type='$class' and ref2Id=$id )";
		$linkList = $link->getSqlElementsFromCriteria(null,null,$crit);
		$table = "*Linked Items*\n";
		$headers = array(ucfirst(i18n('colType')), ucfirst(i18n('colName')), ucfirst(i18n('Status')));
		$rows = array();
		
		$status = '';
		foreach ($linkList as $link) {
			if ($class==$link->ref1Type and $id==$link->ref1Id) { 
				$obj = new $link->ref2Type($link->ref2Id);
			} else { 
				$obj = new $link->ref1Type($link->ref1Id);
			}
			$goto = $this->getTelegramReferenceUrl ($obj);
			$row = array("[".i18n(get_class($obj)) . ' #' . $obj->id."]($goto)", $obj->name);
			
			if (property_exists($obj, 'idStatus')) {
				$status_obj = new Status($obj->idStatus);
				if (isset($status_obj->tgEmoji)) {
					array_push($row, $status_obj->tgEmoji);

				} else {
					array_push($row, SqlList::getNameFromId('Status', $obj->idStatus));
				}
			}
			$status = '';

			array_push($rows, $row);
		}
		return $table.$this->formatTable($headers, $rows);
	}

	//florent ticket 4790
	/*private function getNotesClassicTab($msg, $rowStart,$rowEnd, $sectionStart, $sectionEnd,$labelStart, $labelEnd,$fieldStart,$fieldEnd){
		$msg .= $rowStart . $sectionStart.'<table style="float:left;"><tr>';
		$msg .= '<td>&nbsp;</td>';
		$msg .= '<td><img style="float:left;width:22px; height:22px;" src="'.SqlElement::getBaseUrl().'/view/css/customIcons/new/iconEmailNotes.png" /></td>';
		$msg .= '<td>&nbsp;</td>';
		$msg .= '<td style="color: #FFFFFF;font-size:14pt;font-weight:normal;font-family:Verdana,Arial,Helvetica,sans-serif">'.i18n ( 'sectionNote' ).'</td>';
		$msg .= '<td style="width:90%;">&nbsp;</td>';
		$msg .= '</tr></table>'.$sectionEnd.$rowEnd;
		$msg .= $rowStart.'<td><br></td>'.$rowEnd;
		$note = new Note ();
		$notes = $note->getSqlElementsFromCriteria ( array('refType' => get_class ( $this ), 'refId' => $this->id), false, null, 'id desc' );
		foreach ( $notes as $note ) {
			if ($note->idPrivacy == 1) {
				$userId = $note->idUser;
				$userName = SqlList::getNameFromId ( 'User', $userId );
				$creationDate = $note->creationDate;
				$updateDate = $note->updateDate;
				if ($updateDate == null) {
					$updateDate = '';
				}
				$msg .= $rowStart . $labelStart;
				$msg .= '<b>'.$userName.'&nbsp;&nbsp;</b><br>';
				if ($updateDate) {
					$msg .= '<i>' . htmlFormatDateTime ( $updateDate ,false, false, false) . '</i>';
				} else {
					$msg .= htmlFormatDateTime ( $creationDate ,false, false, false);
				}
				$msg .= $labelEnd . $fieldStart;
				// $msg.=htmlEncode($note->note,'print');
				$text = new Html2Text ( $note->note );
				$plainText = $text->getText ();
				if (mb_strlen ( $plainText ) > 10000) { // Should not send too long email
					$noteTruncated = nl2br ( mb_substr ( $plainText, 0, 10000 ) );
					$msg .= htmlSetClickableImages($noteTruncated,450);
				} else {
					$msg .= htmlSetClickableImages($note->note,450);
				}
				$msg .= $fieldEnd . $rowEnd;
			}
		}
		return    $msg;
	}*/

	private function getNotesTab($obj) {
		$html = '';
		$note = new Note();
		$critArray = array('refType' => get_class($obj), 'refId' => $obj->id, 'idPrivacy'=>'1');
		$order=Parameter::getGlobalParameter("paramOrderNoteMail");
		$noteList = $note->getSqlElementsFromCriteria($critArray);
		if($order=='ASC'){
			arsort($noteList);
		}
		$table = "*Notes*\n";
		$headers = array(ucfirst(i18n('colId')), ucfirst(i18n('colName')), ucfirst(i18n('colDate')));
		$rows = array();

		$status = '';
		foreach ($noteList as $note) {
			//$row = array($note->id, html_entity_decode(strip_tags($note->note)));
			if (property_exists($note, 'updateDate') and $note->updateDate != '')
				$date = $note->updateDate;
			else if (property_exists($note, 'creationDate') and isset($note->creationDate))
				$date = $note->creationDate;
			//array_push($row, $this->formatDateTime($date));
			array_push($rows, array($note->id." - ".$this->formatDateTime($date)."\n".$note->note));
		}
		return $table.$this->formatTable($headers, $rows);
	}

	public function getLastChangeTabForObject($obj,$lastChangeDate) {
		global $cr, $print, $treatedObjects;
		if ($lastChangeDate=='full') {
			$lastChangeToShow='1970-01-01 00:00:00';
		} else {
			if (!$lastChangeDate) {
				$lastChangeDate=date('Y-m-d H:i:s');
			}
			$lastChangeToShow=date('Y-m-d H:i:s',strtotime($lastChangeDate)-10); // Get last changes (including last 10 seconds, not only last change)
		}
		require_once "../tool/formatter.php";
		if ($obj->id) {
			$inList="( ('" . get_class($obj) . "', " . Sql::fmtId($obj->id) . ") )";
		} else {
			$inList="( ('x',0) )";
		}
		$showWorkHistory=true;
		$where=' (refType, refId) in ' . $inList;
		$order=' operationDate desc, id asc';
		$hist=new History();
		$historyList=$hist->getSqlElementsFromCriteria(null, false, $where, $order, false, false);

		$table = "*".i18n('elementHistory'.(($lastChangeDate=='full')?'':'Last'))."*\n";
		$headers = array(i18n('colOperation'), i18n('colColumn'), i18n('colValueBefore'), i18n('colValueAfter'), i18n('colDate'), i18n('colUser'));

		$stockDate=null;                             
		$stockUser=null;
		$stockOper=null;
		if (is_array($historyList) and count($historyList)>0 and is_object($historyList[0]))
			$dateCmp = new DateTime($historyList[0]->operationDate);
		else
			return $table;

		$rows = array();

		foreach ( $historyList as $hist ) {
			if ($hist->operationDate<$lastChangeToShow) break;
			if (substr($hist->colName, 0, 24) == 'subDirectory|Attachment|'    or substr($hist->colName, 0, 18) == 'idTeam|Attachment|'
			 or substr($hist->colName, 0, 25) == 'subDirectory|Attachement|' or substr($hist->colName, 0, 19) == 'idTeam|Attachement|') {
				continue;
			}
			$colName=($hist->colName == null)?'':$hist->colName;
			$split=explode('|', $colName);
			if (count($split) == 3) {
				$colName=$split [0];
				$refType=$split [1];
				$refId=$split [2];
				$refObject='';
			} else if (count($split) == 4) {
				$refObject=$split [0];
				$colName=$split [1];
				$refType=$split [2];
				$refId=$split [3];
			} else {
				$refType='';
				$refId='';
				$refObject='';
			}
			if ($refType=='Attachement') {
				$refType='Attachment'; // New in V5 : change Class name, must preserve display for history
			}
			$curObj=null;
			$dataType="";
			$dataLength=0;
			$hide=false;
			$oper=i18n('operation' . ucfirst($hist->operation));
			$user=$hist->idUser;
			$user=SqlList::getNameFromId('User', $user);
			$date=$this->formatDateTime($hist->operationDate);
			$class="NewOperation";
			if ($stockDate == $hist->operationDate and $stockUser == $hist->idUser and $stockOper == $hist->operation) {
				$oper="";
				$user="";
				$date="";
				$class="ContinueOperation";
			}
			if ($colName != '' or $refType != "") {
				if ($refType) {
					if ($refType == "TestCase") {
						$curObj=new TestCaseRun();
					} else {
						$curObj=new $refType();
					}
				} else {
					$curObj=new $hist->refType();
				}
				if ($curObj) {
					if ($refType) {
						$colCaption=i18n($refType) . ' #' . $refId . ' ' . $curObj->getColCaption($colName);
						if ($refObject) {
							$colCaption=i18n($refObject) . ' - ' . $colCaption;
						}
					} else {
						$colCaption=$curObj->getColCaption($colName);
					}
					$dataType=$curObj->getDataType($colName);
					$dataLength=$curObj->getDataLength($colName);
					if (strpos($curObj->getFieldAttributes($colName), 'hidden') !== false) {
						$hide=true;
					}
				}
			} else {
				$colCaption='';
			}
			if (substr($hist->refType, -15) == 'PlanningElement' and $hist->operation == 'insert') {
				$hide=true;
			}
			if ($hist->isWorkHistory and ! $showWorkHistory) {
				$hide=true;
			}
			if (!$hide) {
				$row = array();
				array_push($row, $oper);
				array_push($row, $colCaption);
				
				$oldValue=$hist->oldValue;
				$newValue=$hist->newValue;
				if ($dataType == 'int' and $dataLength == 1) { // boolean
					$oldValue=$this->displayTelegramCheckbox($oldValue);
					$newValue=$this->displayTelegramCheckbox($newValue);
				} else if (substr($colName, 0, 2) == 'id' and strlen($colName) > 2 and strtoupper(substr($colName, 2, 1)) == substr($colName, 2, 1)) {
					if ($oldValue != null and $oldValue != '') {
						if ($oldValue == 0 and $colName == 'idStatus') {
							$oldValue='';
						} else {
							$oldValue=SqlList::getNameFromId(substr($colName, 2), $oldValue);
						}
					}
					if ($newValue != null and $newValue != '') {
						$newValue=SqlList::getNameFromId(substr($colName, 2), $newValue);
					}
				
				/*} else if ($colName == "color") {
					$oldValue=htmlDisplayColoredFull("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $oldValue);
					$newValue=htmlDisplayColoredFull("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $newValue);*/

				} else if ($dataType == 'date') {
					$oldValue=$this->formatDate($oldValue);
					$newValue=$this->formatDate($newValue);
				} else if ($dataType == 'datetime') {
					$oldValue=$this->formatDateTime($oldValue);
					$newValue=$this->formatDateTime($newValue);
				} else if ($dataType == 'decimal' and substr($colName, -4, 4) == 'Work') {
					$oldValue=Work::displayWork($oldValue) . ' ' . Work::displayShortWorkUnit();
					$newValue=Work::displayWork($newValue) . ' ' . Work::displayShortWorkUnit();
				} else if ($dataType == 'decimal' and (substr($colName, -4, 4) == 'Cost' or strtolower(substr($colName,-6,6))=='amount')) {
					$oldValue=str_replace("$nbsp;", " ", htmlDisplayCurrency($oldValue));
					$newValue=str_replace("$nbsp;", " ", htmlDisplayCurrency($newValue));
				} else if (substr($colName, -8, 8) == 'Duration') {
					$oldValue=$oldValue . ' ' . i18n('shortDay');
					$newValue=$newValue . ' ' . i18n('shortDay');
				} else if (substr($colName, -8, 8) == 'Progress') {
					$oldValue=$oldValue . ' ' . i18n('colPct');
					$newValue=$newValue . ' ' . i18n('colPct');
				} else if ($colName=='password' or $colName=='apiKey') {
					$allstars="**********";
					if ($oldValue) $oldValue=substr($oldValue,0,5).$allstars.substr($oldValue,-5);
					if ($newValue) $newValue=substr($newValue,0,5).$allstars.substr($newValue,-5);
				} else {
					if (! isTextFieldHtmlFormatted($oldValue)) {
						$oldValue = htmlEncode($oldValue, 'print');
						$oldValue=wordwrap($oldValue, 30, '<wbr>', false);
					}
					if (! isTextFieldHtmlFormatted($newValue)) {
						$newValue = htmlEncode($newValue, 'print');                
						$newValue=wordwrap($newValue, 30, '<wbr>', false);
					}
				}

				array_push($row, $oldValue);
				array_push($row, $newValue);
				array_push($row, $date);
				array_push($row, $user);
				
				$stockDate=$hist->operationDate;
				$stockUser=$hist->idUser;
				$stockOper=$hist->operation;

				array_push($rows, $row);
			}
		}
		return $table.$this->formatTable($headers, $rows);
	}
}
