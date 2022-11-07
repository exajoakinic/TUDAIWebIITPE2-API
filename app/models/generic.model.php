<?php

require_once('./app/models/connection.db.php');

class GenericApiModel extends ConnectionDB {
    protected $table;
    private $fields; //campos de la tabla a utilizar en verificacion

    private $fieldsOnSelect; //campos que irán en el select
    private $joinSentence; //fragmento JOIN
    private $orderByField; //String con campo/campos a utilizar: ORDER BY $orderByField
    private $defaultOrderBySentence; //sentencia ORDER BY generada por setOrderBy($field)

    function __construct($nameTable, $fields, 
            $params = [ "fieldsOnSelect" => "*",
                        "joinSentence" => "",
                        "orderByField" => ""]){
        parent::__construct();
        $this->table = $nameTable;
        $this->fields = $fields;
        $this->fieldsOnSelect = $params["fieldsOnSelect"] ?? "*";
        $this->joinSentence = $params["joinSentence"] ?? "";

        $this->setOrderBy($params["orderByField"] ?? "");
    }

    private function setOrderBy($field) {
        if (empty($field)) {
            $this->orderBySentence = "";
            return;
        }

        $this->defaultOrderBySentence = "ORDER BY $field";
    }


    public function getAll($params = []) {
        $sanitizedItems=[];
        $sql = "SELECT $this->fieldsOnSelect FROM $this->table $this->joinSentence";
        //WHERE
        if (isset($params['filter'])) {
            $where = "";
            foreach ($params['filter'] as $key => $value) {
                if (!empty($where)) {
                    $where .= " AND ";
                }
                $where .= $key . " " . $params['operator'] ." ?";
                $sanitizedItems[] = $value;
            }
            if (!empty($where)) {
                $sql .= " WHERE " . $where;
            }
        }
        //ORDER BY
        if (isset($params['sort_by'])){
            $sql .= " ORDER BY " . $params['sort_by'];
        } else {
            $sql .= " " . $this->defaultOrderBySentence;
        }
        
        //ORIENTACIÓN ORDEN
        if (isset($params['sort_by']) || !empty($this->defaultOrderBySentence)){
            $sql .= " " . ($params['order']??"ASC");
        }
        //LIMIT
        if (isset($params['limit'])) {
            $sql .= " LIMIT " . $params['limit'];
        }
        //PAGE - OFFSET
        if (isset($params['page'])) {
            $offset = $params['limit'] * ($params['page'] - 1);
            $sql .= " OFFSET " . $offset;
        }
        $sql .= ";";
        // echo $sql;
        // die;
        $query = $this->db->prepare($sql);
        $query->execute($sanitizedItems);
        
        $items = $query->fetchAll(PDO::FETCH_OBJ); // devuelve un arreglo de objetos
        
        return $items;
    }
    
    /**
     * getAllBy($field, $value)
     * DEVUELVE TODOS LOS REGISTROS CON $field = $value
     */
    function getAllBy($field, $value) {
        $query = $this->db->prepare("SELECT $this->fieldsOnSelect FROM $this->table $this->joinSentence WHERE $field=? $this->defaultOrderBySentence;");
        $query->execute([$value]);
        
        $items = $query->fetchAll(PDO::FETCH_OBJ);
        
        return $items;
    }

    /**
     * getBy($field, $value)
     * DEVUELVE PRIMER OCURRENCIA CON $field = $value
     * $field debe ser un campo de la tabla correspondiente al modelo.
     */
    public function getBy($localField, $value) {
        // Se intentó reemplazar $field por ? pero la $query devolvía siempre false
        // entiendo esto no supone un problema de seguridad dado que no es el 
        // usuario quien completa el campo field.

        $query = $this->db->prepare("SELECT $this->fieldsOnSelect FROM $this->table $this->joinSentence WHERE  $this->table.$localField = ?;");
        $query->execute([$value]);
        
        $item = $query->fetch(PDO::FETCH_OBJ);

        return $item;
    }
    
    public function get ($id) {
        return $this->getBy("id", $id);
    }

    public function remove ($id) {
        $query = $this->db->prepare("DELETE FROM $this->table WHERE id = ?");
        $query->execute([$id]);
    }
    

    public function edit ($item) {
        $sql = "";
        $values = [];
        foreach ($item as $field => $value) {
            //Verifica que exista el campo para prevenir inyección de un
            //controlador malicioso, o tonto que pase un field escrito por el usuario
            if (in_array($field, $this->fields) && $field != "id"){
                if ($sql) {
                    $sql = "$sql, ";
                }
                $sql = "$sql$field = ?";
                $values[] = $value;
            }
        }

        $sql = "UPDATE $this->table SET $sql WHERE id = ?";
        $values[] = $item->id;

        $query = $this->db->prepare("$sql");
        
        $query->execute($values);
    }

    public final function insert ($item) {
        $listFields = "";
        $questionMarks ="";
        $values = [];
        foreach ($item as $field => $value) {
            //Verifica exista el campo para prevenir inyección sql
            //de un controlador malicioso:
            if (!in_array($field, $this->fields)) {
                return false;
            }
            if (in_array($field, $this->fields) && $field != "id"){
                if ($listFields) {
                    $listFields = "$listFields, ";
                    $questionMarks = "$questionMarks, ";
                }
                $listFields = $listFields . $field;
                $questionMarks = $questionMarks . "?";
                $values[] = $value;
            }
        }

        $sql = "INSERT INTO  $this->table ($listFields) VALUES ($questionMarks)";

        $query = $this->db->prepare($sql);
        $query->execute($values);

        return $this->db->lastInsertId();
    }

    function countBy($localField, $id){
        $query = $this->db->prepare("SELECT COUNT(*) AS result FROM $this->table WHERE $this->table.$localField = ?");
        $query->execute([$id]);

        return $query->fetch(PDO::FETCH_OBJ)->result;
    }
}