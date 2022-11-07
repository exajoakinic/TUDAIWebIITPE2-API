<?php
require_once "./app/views/api.view.php";
require_once "./app/helpers/auth.helper.php";

abstract class GenericApiController {
    protected $model;
    protected $view;
    private $fields;
    private $validFilterColumns;


    protected const MIN_LIMIT = 1;
    protected const DEFAULT_LIMIT = 30;
    protected const MAX_LIMIT = 1000;
    protected const VALID_FILTER_OPERATORS = [
        'like' => 'like',
        'equal' => '=',
        '=' => '=',
        '>' => '>',
        '>=' => '>=',
        '<=' => '<=',
        '<=' => '<=',
    ];

    function __construct($model, $fields, $validFilterColumns) {
        $this->model = $model;
        $this->fields = $fields; 
        $this->view = new ApiView();

        $this->validFilterColumns= $validFilterColumns;

        // lee el body del request
        $this->data = file_get_contents("php://input");


    }

    private function getData() {
        return json_decode($this->data);
    }

    /**
     * MUESTRA TODOS LOS ITEMS DE LA ENTIDAD
     */
    function get($params = null) {
        $id = $params[':ID'];
        $item = $this-> model-> get($id);

        $this->view->response($item, 200);
    }
    /**
     * MUESTRA TODOS LOS ITEMS DE LA ENTIDAD
     */
    function getAll($params = null) {
        $items = $this-> model-> getAll(
                              $this->validateUserGets()
                             );

        $this->view->response($items, 200);
    }

    /**
     * PARA FILTRO, ORDEN, LÍMITE, PAGINACIÓN
     * VALIDA VALORES RECIBIDOS POR $_GET Y DEVUELVE ARREGLO ASOCIATIVO
     * PARA EL MODEL CON VALORES QUE SE VAN A INYECTAR SANITIZADOS
     */
    function validateUserGets() {
        $res = [];
        foreach ($_GET as $key => $value) {
            $key = strtolower($key);
            $value = strtolower($value);
            switch ($key) {
                case 'orderby':
                    $key = sort_by;
                case 'order_by':
                    $key = sort_by;
                case 'sortby':
                    $key = sort_by;
                case 'sort_by':
                    if (isset($this->validFilterColumns[$value])) {
                        $res['sort_by'] = $value;
                    } else {
                        $this->view->response("$value no es un valor válido para la clave $key", 400);
                        die;
                    }
                    break;
                case 'asc':
                    $res['order'] = $key;
                    break;
                case 'desc':
                    $res['order'] = $key;
                    break;
                case 'l':
                    $key = 'limit';
                case 'limit':
                    $limit = (int)$value;
                    if ($limit < self::MIN_LIMIT || $limit > self::MAX_LIMIT) {
                        $this->view->response("limit debe ser un valor entre " . BookController::MIN_LIMIT ." y " . BookController::MAX_LIMIT, 400);
                        die;
                    }
                    $res['limit'] = $limit;
                    break;
                case 'p':
                    $key = 'page';
                case 'page':
                    $page = (int)$value;
                    if ($page < 1) {
                        $this->view->response("'page' debe ser mayor o igual que 1", 400);
                        die;
                    }
                    $res['page'] = $page;
                    break;
                case 'o':
                    $key='operator';
                case 'operator':
                    if (isset(self::VALID_FILTER_OPERATORS[$value])) {
                        $res['operator'] = self::VALID_FILTER_OPERATORS[$value];
                    } else {
                        $this->view->response("$value no es un operador válido", 400);
                        die;
                    }
                    break;
                case 'resource':
                    //nada que hacer, salvo prevenir ejecución del default
                    break;
                default:  //filtrado por columna
                    if (isset($this->validFilterColumns[$key])) {
                        $res['filter'][$this->validFilterColumns[$key]] = $value;
                    } else {
                        $this->view->response("Clave $key no identificada", 400);
                        die;
                    }
                    break;
            }
        }

        //Operador Default: =
        $res['operator'] = $res['operator'] ?? '=';
        //Límite Default si no fue definido
        $res['limit'] = $res['limit'] ?? self::DEFAULT_LIMIT;
        return $res;
    }

    /**
     * 
     */
    function defaultAction($params = null) {
        $this->view->response("No encontrado", 404);
        die;
    }

    /**
     * ---------------------------------------------------------------------------
     *                                   A B M
     * ---------------------------------------------------------------------------
     */

    /**
     * AGREGAR
     */
    public function insert($params = null) {
        $item = $this->getAndValidateBeforeInsert();

        $id = $this->model->insert($item);
        $item = $this->model->get($id);
        $this->view->response($item, 201);
    }
    /**
    * EDITAR
    */
   function update ($params = null) {
       $id=$params[":ID"];
       //VERIFICA QUE ESTÉ LOGUEADO
       // AuthHelper::checkLoggedIn();
       
       $item = $this->getAndValidateBeforeUpdate($id);

       $this->model->edit($item);
       $this->view->response($this->model->get($id), 200);
    }
    
    
    /**
     * ELIMINAR
     */
    public function delete ($params = null) {
        $id=$params[":ID"];
        //VERIFICA QUE ESTÉ LOGUEADO
        // AuthHelper::checkLoggedIn();
        
        $item = $this->getAndValidateBeforeDelete($id);
        $this->model->remove($id);
        $this->view->response($item);
    }
    
    /**
     * VALIDACIÓN ANTES DE AGREGAR
     */
    protected function getAndValidateBeforeInsert() {
        return $this->getAndValidateFromBody();
    }
    
    /**
     * Genera objeto con los datos esperados por POST,
     * previa verificación de que estén todos los datos seteados.
     */
    protected function getAndValidateFromBody() {
        $item = $this->getData();
        //keys de $item y valores de $this->fields deben ser biyectivos
        //Valida que hayan llegado todos los campos necesarios
        foreach ($this->fields as $field) {
            if (!isset($item->$field)) {
                $this->view->response("El campo '$field' debe estar seteado", 400);
                die;
            }
        }
        //Valida que no haya llegado campos inválidos
        foreach ($item as $key => $field) {
            if (!in_array($key, $this->fields)) {
                $this->view->response("'$key' no es un campo válido", 400);
                die;
            }            
        }

        return $item;
    }
    
    /**
     * VALIDACIÓN ANTES DE EDITAR
     * Devuelve item con el id y los datos recibidos por post
     */
    protected function getAndValidateBeforeUpdate($id) {
        if (!$this->model->get($id)) {
            $this->view->response("Elemento con 'id'=$id inexistente", 404);
            die;
        }
        $item = $this->getAndValidateFromBody();
        $item->id = $id;
        return $item;
    }
       /**
        * VALIDACIÓN ANTES DE ELIMINAR
        */
       protected function getAndValidateBeforeDelete($id) {
            $item = $this->model->get($id);
            if (!$item) {
                $this->view->response("Elemento con 'id'=$id inexistente", 404);
                die;
            }
            return $item;
       }
    
}