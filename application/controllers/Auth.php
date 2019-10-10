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
class Auth extends REST_Controller {

    function __construct()
    {
        parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('model_user');
    }

    public function index_post()
    {

        $data = [];
        $data["user"] = $this->post('user');
        $data["password"] = $this->post('pass');
        $data['password'] = sha1($data['password']);
        $currentUser = $this->model_user->getUserByEmail($data["user"]);

        if ($currentUser == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'Error no se encontro el usuario'
            ], REST_Controller::HTTP_NOT_FOUND);
        } else if ($currentUser['password'] != $data["password"]) {
            $this->response([
                'status' => FALSE,
                'message' => 'La contraseña no es valida'
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->response($this->model_user->login($currentUser), REST_Controller::HTTP_OK);
        } 
    }

}
