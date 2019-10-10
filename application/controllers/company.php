<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Company extends REST_Controller {

    function __construct()
    {
        parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('model_user');
		$this->load->model('model_user');
		$this->load->model('model_company');
    }

    private function Authorization(){
        $token = $this->input->get_request_header('Authorization');
        if (is_null($token)) {
            $this->response([
                'status' => FALSE,
                'message' => 'No envio token'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        } else {
            $lUser = $this->model_user->getUserSession($token);
            if (is_null($lUser)) {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No authorization'
                    ], REST_Controller::HTTP_UNAUTHORIZED);
            } else {
                return $lUser;
            }
        }
    }
    

	public function uploadImg($base64,$folder){
        try {
            $url = './uploads/' .($folder != null ? $folder . '/' : ''). date("Y") . '/' . date("d") . '/';
            if (!file_exists($url)) { 
                mkdir($url, 0777, true); 
            }
            $url = $url .  time() . '.png';
            $fileBin = file_get_contents($base64);
            $mimeType = mime_content_type($base64);
            file_put_contents($url, $fileBin);
            return $url;
        } catch (\Throwable $th) {
            throw $th;
        }
	}



    /**
     * Get usuarios sin parametro es all con id es uno en concreto
     *
     * @return void
     */
    public function myCompany_get()
    {
        $currentUser = $this->Authorization();
        $this->response([
            'status' => true,
            'message' => $this->model_company->getbyUser($currentUser['id'])
        ], REST_Controller::HTTP_OK);
        
    }

    public function addUserKey_post()
    {
        $currentUser = $this->Authorization();
        $key = $this->post('key');
        if ($key == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'No se resivio ninguna key'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $this->response($this->model_company->getCompanyByKey($key, $currentUser['id']) , REST_Controller::HTTP_OK);
    }
    

    public function changeState_post()
    {
        $id = $this->post('id');
        if ($id == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'El id es nesesario'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $status = $this->post('value');
        if ($status == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'El se envio el nuevo estado'
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $currentUser = $this->Authorization();
        if (!isset($currentUser['admin']) || !$currentUser['admin'] ) {
            $this->response([
                'status' => FALSE,
                'message' => 'No authorization'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        } 

        $this->response([
            'status' => FALSE,
            'message' => $this->model_company->changeState($id,$status)
        ], REST_Controller::HTTP_OK);
        
    }
    

    public function admin_post()
    {
        $currentUser = $this->Authorization();
        
        $configuration = $this->post('configuration');
        if ($configuration != null) {
            $lFilter = array();
            foreach ($configuration as $oConf) {
                $lFilter[] = $oConf['columna'] . $oConf['comparador'] . $oConf['default'];
            }
            $sFilter = join(" AND ",$lFilter);
        } else {
            $sFilter = '';
        }
        if ($currentUser['admin'])
            $this->response([
                'status' => true,
                'message' => $this->model_company->get(null,$sFilter)
            ], REST_Controller::HTTP_OK);
        
    }

    public function get_get()
    {
        $id = $this->get('id');
        if ($id === NULL || (int)$id <= 0){
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
        $id = (int) $id;


        $retorno = [];
        $retorno['company'] = $this->model_company->get($id);

        $this->response([
            'status' => true,
            'message' => $retorno 
        ], REST_Controller::HTTP_OK);
    }

    
    public function sector_get()
    {
        $this->response([
            'status' => true,
            'message' => $this->model_company->sector()
        ], REST_Controller::HTTP_OK);
        
    }
    

    public function add_post()
    {
        $data = [
            "active" => $this->post('active'), 
            'title' => $this->post('title'), 
            'rut' => $this->post('rut'),
            'bps' => $this->post('bps'), 
            'description' => $this->post('description'), 
            'debts' => $this->post('debts'), 
            'personal' => $this->post('personal'), 
            'x' => $this->post('x'), 
            'y' => $this->post('y'), 
            'id_user' => $this->post('id_user'), 
            'addres' => $this->post('addres'), 
            'phone' => $this->post('phone'), 
            'email' => $this->post('email'), 
            'date' => $this->post('date'), 
            'dateStart' => $this->post('dateStart'),
            'apoyoGoviernoDepartamental' => $this->post('apoyoGoviernoDepartamental'),
            'apoyoANII' => $this->post('apoyoANII'),
            'apoyoANR' => $this->post('apoyoANR'),
            'apoyoOtro' => $this->post('apoyoOtro')
        ];
        $currentUser = $this->Authorization();
     
        $validate = $this->model_company->valid($data);
        if ($validate != null) {
            $this->response([
                'status' => FALSE,
                'message' => $validate
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            
            $this->response([
                'status' => true,
                'message' => $this->model_company->post($data,$currentUser['id'])
            ], REST_Controller::HTTP_CREATED);
        }
    }

    public function index_put()
    {
        if ($this->put('id') == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'El id es nesesario'
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $data = [
            "id" => $this->put('id'), 
            "active" => $this->put('active'), 
            'title' => $this->put('title'), 
            'rut' => $this->put('rut'),
            'bps' => $this->put('bps'), 
            'description' => $this->put('description'), 
            'debts' => $this->put('debts'), 
            'personal' => $this->put('personal'), 
            'x' => $this->put('x'), 
            'y' => $this->put('y'), 
            'id_user' => $this->put('id_user'), 
            'addres' => $this->put('addres'), 
            'phone' => $this->put('phone'), 
            'email' => $this->put('email'), 
            'date' => $this->put('date'), 
            'dateStart' => $this->put('dateStart'),
            'apoyoGoviernoDepartamental' => $this->put('apoyoGoviernoDepartamental'),
            'apoyoANII' => $this->put('apoyoANII'),
            'apoyoANR' => $this->put('apoyoANR'),
            'apoyoOtro' => $this->put('apoyoOtro'),

            'ods' => $this->put('ods'),
            'sector' => $this->put('sector')
        ];

        $currentUser = $this->Authorization();
        if ($currentUser['id'] != $data['id'] && (!isset($currentUser['admin']) || !$currentUser['admin'])  ) {
            $this->response([
                'status' => FALSE,
                'message' => 'No authorization'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        } 
        
        $validate = $this->model_company->valid($data);
        if ($validate != null) {        
            $this->response([
                'status' => FALSE,
                'message' => $validate
            ], REST_Controller::HTTP_NOT_FOUND); 
        } 
        
        if (isset($data['Base64'])) $data['url_img'] = $this->uploadImg($data['Base64'], 'users');
        $this->response([
            'status' => TRUE,
            'message' => $this->model_company->put($data)
        ], REST_Controller::HTTP_CREATED);
    }

    /**
     * Metodo encargado de eliminar virtualmente los usuarios
     * @return void
     */
    public function index_delete($id)
    {
        if ($id == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'El id es nesesario'
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $currentUser = $this->Authorization();
        if ($currentUser['id'] != $id && (!isset($currentUser['admin']) || !$currentUser['admin'])  ) {
            $this->response([
                'status' => FALSE,
                'message' => 'No authorization'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        } 

        $this->response([
            'status' => FALSE,
            'message' => $this->model_company->delete($id)
        ], REST_Controller::HTTP_OK);
    }

}
