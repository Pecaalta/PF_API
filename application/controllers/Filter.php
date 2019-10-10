<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

class Filter extends REST_Controller {

    function __construct()
    {
        parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('model_user');
		$this->load->model('model_filter');
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
            if (is_null($lUser) || $lUser['admin'] != '1' ) {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No authorization'
                    ], REST_Controller::HTTP_UNAUTHORIZED);
            } else {
                return $lUser;
            }
        }
    }

    /**
     * Get usuarios sin parametro es all con id es uno en concreto
     *
     * @return void
     */
    public function index_get()
    {
        $this->Authorization();

        $id = $this->get('id');
        if ($id === NULL) {
            $lNews = $this->model_filter->get();
            if ($lNews){
                $this->response([
                    'status' => TRUE,
                    'message' => $lNews
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'No se encontro filtros'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        $id = (int) $id;
        if ($id <= 0){
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
        $oNews = $this->model_filter->get($id);
        if (!empty($oNews)){
            $this->set_response([
                'status' => TRUE,
                'message' => $oNews
            ], REST_Controller::HTTP_OK);
        } else {
            $this->set_response([
                'status' => FALSE,
                'message' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); 
        }
    }

    public function index_post()
    {
        $data = [
            "name" => $this->post('name'),
            "configuration" => $this->post('configuration')
        ];
        $this->response([
            'status' => true,
            'message' => $this->model_filter->post($data)
        ], REST_Controller::HTTP_CREATED);
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
            "name" => $this->put('name'),
            "configuration" => $this->put('configuration')
        ];
        $this->response([
            'status' => TRUE,
            'message' => $this->model_filter->put($data)
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
        $this->response([
            'status' => TRUE,
            'message' => $this->model_filter->delete($id)
        ], REST_Controller::HTTP_OK);
    }

}
