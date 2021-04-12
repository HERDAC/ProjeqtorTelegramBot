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
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for
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

class TicketTemplate extends SqlElement {
    public $_sec_description;
    public $id;
    public $name;
    public $idTicketType;
    public $idProject;
    public $idActivity;
    public $idUrgency;
    public $idCriticality;
    public $idContext1;
    public $idContext3;
    public $idle;

    private static $_fieldsAttributes=array(
        "idTicketType"=>"required",
        "name"=>"required",
        "idProject"=>"required",
        "idContext1"=>"nobr,size1/2,title",
        "idContext3"=>"size1/2,title",
        "idActivity"=>"title"
    );
    private static $_colCaptionTransposition = array(
	"idContext1"=>"idContext",
	"idActivity"=>"planningActivity"
    );

    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="45%" >${name}</th>
    <th field="nameTicketType" width="15%" >${idTicketType}</th>
    <th field="nameProject" width="15%">${idProject}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
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

    public function getValidationScript($colName) {

      $colScript = parent::getValidationScript($colName);

      return $colScript;
    }

    public function control(){
      $result="";

      if ($result=="") {
        $result='OK';
      }
      return $result;
    }
}
