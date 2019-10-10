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
class News extends REST_Controller {

    function __construct()
    {
        parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('model_user');
		$this->load->model('model_news');
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
    public function index_get()
    {
        $id = $this->get('id');
        if ($id === NULL) {
            $lNews = $this->model_news->get();
            if ($lNews){
                $this->response($lNews, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'No users were found'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        $id = (int) $id;
        if ($id <= 0){
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
        $oNews = $this->model_news->get($id);
        if (!empty($oNews)){
            $this->set_response($oNews[0], REST_Controller::HTTP_OK);
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
            "title" => $this->post('title'),
            "url_img" => $this->post('url_img'),
            "id_User" => $this->post('id_User'),
            "html" => $this->post('html'),
            "Base64" => $this->post('Base64'),
            "description" => $this->post('description'),
            "created_at" => $this->post('created_at')
        ];
        $validate = $this->model_news->valid($data);
        if ($validate != null) {
            $this->response([
                'status' => FALSE,
                'message' => $validate
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            if (isset($data['Base64'])) $data['url_img'] = $this->uploadImg($data['Base64'], 'users');
            $this->response([
                'status' => true,
                'message' => $this->model_news->post($data)
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
            "title" => $this->put('title'),
            "url_img" => $this->put('url_img'),
            "id_User" => $this->put('id_User'),
            "html" => $this->put('html'),
            "Base64" => $this->put('Base64'),
            "description" => $this->put('description'),
            "created_at" => $this->put('created_at')
        ];

        $currentUser = $this->Authorization();
        if ($currentUser['id'] != $data['id'] && (!isset($currentUser['admin']) || !$currentUser['admin'])  ) {
            $this->response([
                'status' => FALSE,
                'message' => 'No authorization'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        } 
        
        $validate = $this->model_news->valid($data);
        if ($validate != null) {        
            $this->response([
                'status' => FALSE,
                'message' => $validate
            ], REST_Controller::HTTP_NOT_FOUND); 
        } 
        
        if (isset($data['Base64'])) $data['url_img'] = $this->uploadImg($data['Base64'], 'users');
        $this->response([
            'status' => TRUE,
            'message' => $this->model_news->put($data)
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
            'message' => $this->model_news->delete($id)
        ], REST_Controller::HTTP_OK);
    }

}
