<?php
require_once "./app/views/api.view.php";
require_once "./app/helpers/auth.helper.php";

abstract class GenericApiController {
    protected $model;
    protected $view;
    private $fields;
    private $validFilterColumns;

    protected const MIN_LIMIT = 1;
    protected const MAX_LIMIT = 500;

    function __construct($model, $fields, $validFilterColumns) {
        $this->model = $model;
        $this->fields = $fields; 
        $this->view = new ApiView();

        $this->validFilterColumns= $validFilterColumns;
        AuthHelper::openSession();
    }


    /**
     * MUESTRA TODOS LOS ITEMS DE LA ENTIDAD
     */
    function get($params = null) {
        $id = $params[':ID'];
        $item = $this-> model-> getById($id);

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

        $res['limit'] = $res['limit'] ?? 500;
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
     * --------------------------------------------------------------
     * 
     * VALIDAR LO QUE SIGUE... ESCRITO ANTERIORMENTE
     * 
     * --------------------------------------------------------------
     */

     /**
     * EDITAR
     * Responsabilidad de validación: getAndValidateBeforeEdit($id)
     */
    function edit ($id) {
        //VERIFICA QUE ESTÉ LOGUEADO
        AuthHelper::checkLoggedIn();

        $item = $this->getAndValidateBeforeEdit($id);

        $this->model->edit($item);
    }

    /**
     * VALIDACIÓN ANTES DE EDITAR
     * Devuelve item con el id y los datos recibidos por post
     */
    protected function getAndValidateBeforeEdit($id) {
        if (!$this->model->getById($id)) {
            $this->view->showErrorNotFinded();
            header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
            die;
        }
        $item = $this->getAndValidateFromPost();
        $item->id = $id;
        return $item;
    }

    /**
     * AGREGAR
     * Responsabilidad validación POST: getAndValidateFromPost()
     * Responsabilidad de redirección: redirectionAfterAdd()
     */
    function add () {
        //VERIFICA QUE ESTÉ LOGUEADO
        AuthHelper::checkLoggedIn();

        $item = $this->getAndValidateFromPost();
        $id = $this->model->add($item);
    }

    /**
     * VALIDACIÓN ANTES DE AGREGAR
     */
    protected function getAndValidateBeforeAdd() {
        return $this->getAndValidateFromPost();
    }

    /**
     * ELIMINAR
     * 1 Corre función getAndValidateBeforeRemove($id) -> debe frenar ejecución si no hay que eliminar
     * 2 Ejecuta redirectionAfterRemove si pudo eliminar
     * 2 Muestra mensaje error si no debió eliminar
     */
    public function remove ($id) {
        //VERIFICA QUE ESTÉ LOGUEADO
        AuthHelper::checkLoggedIn();

        $item = $this->getAndValidateBeforeRemove($id);

        if ($item) {
            $this->model->remove($id);
        }
    }

    /**
     * VALIDACIÓN ANTES DE ELIMINAR
     */
    protected function getAndValidateBeforeRemove($id) {
        $item = $this->model->getById($id);
        if ($item) {
            return $item;
        } else {
            $this->view->showErrorNotFinded();
            header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
            die;
        }
    }

    /**
     * Genera objeto Book con los datos esperados por POST,
     * previa verificación de que estén todos los datos seteados.
     * 
     * Inicialmente esta función era abstracta en GenericController y
     * se definía en cada controlador específico.
     * Luego se abstrajo mediante el foreach, previo recibir los campos
     * a través del constructor.
     */
    protected function getAndValidateFromPost() {
        $item = new stdClass();
        foreach ($this->fields as $field) {
            if (!isset($_POST[$field])) {
                $this->view->showError("Se ha cancelado la operación. El campo '$field' debe estar seteado", "Error en datos recibidos");
                die;
            }
            $item->$field = $_POST[$field];
        }
        return $item;
    }
    
}