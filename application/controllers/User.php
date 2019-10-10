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
class User extends REST_Controller {
    
    function __construct()
    {
     
        parent::__construct();
    

        $this->load->helper('url');
		$this->load->library('session');
        $this->load->model('model_user');
    }
    private function  curl($html,$email,$name,$subject){
            
        include 'Mailin.php';
        $mailin = new Mailin('pecaalta@gmail.com', 'y6a8IsFbzKA2mgHk');
        $mailin->
        addTo($email, $name)->
        setFrom('pecaalta@gmail.com', 'Mauro Maximilieno Silva')->
        setReplyTo('pecaalta@gmail.com','Mauro Maximilieno Silva')->
        setSubject($subject)->setHtml($html);
        $res = $mailin->send();
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
            if (is_null($lUser) ) {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No authorization'
                    ], REST_Controller::HTTP_UNAUTHORIZED);
            } else {
                return $lUser;
            }
        }
    }
    
    public function test_post(){
        $atr = $this->post('id');
        $this->uploadImg($atr);
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
            $users = $this->model_user->getUser();
            if ($users){
                $this->response($users, REST_Controller::HTTP_OK);
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
        $user = $this->model_user->getUser($id);
        if (!empty($user)){
            $user[0]['password'] = '';
            $user[0]['remember_token'] = '';
            $this->set_response($user[0], REST_Controller::HTTP_OK);
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
            "email" => $this->post('email'),
            "name" => $this->post('name'),
            "age" => $this->post('age'),
            "CI" => $this->post('CI'),
            "phone" => $this->post('phone'),
            "url_img" => $this->post('url_img'),
            "created_at" => $this->post('created_at'),
            "updated_at" => $this->post('updated_at'),
            "password" => $this->post('password'),
            "Base64" => $this->post('Base64'),
            "remember_token" => $this->post('remember_token')
        ];
        $data['password'] = sha1($data['password']);
        $validate = $this->model_user->valid($data);
        if ($validate != null) {
            $this->response([
                'status' => FALSE,
                'message' => $validate
            ], REST_Controller::HTTP_NOT_FOUND);
        } else if (!$this->model_user->validUser($data['email'])) {
            $this->response([
                'status' => FALSE,
                'message' => 'El mail ya esta'
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            if (isset($data['Base64'])) $data['url_img'] = $this->uploadImg($data['Base64'], 'users');
            $data['remember_token'] = bin2hex(random_bytes(64));

            $datamail = array(
                'title' => '¡Hola!',
                'message' => 'Ya casi terminamos,<br>Confirmemos su dirección de correo electrónico.<br>Al hacer clic en el siguiente enlace, está confirmando su dirección de correo electrónico.',
                'nameLink' => 'Confirmacion',
                'link' => $data['remember_token'],
            );

            $this->curl($this->load->view('email', $datamail, TRUE),$data['email'],$data['name'], 'Confirmacion de correo');

            $this->response([
                'status' => true,
                'message' => $this->model_user->post($data)
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

        $data = [];
        $data["id"] = $this->put('id');
        $data['currentpass'] = $this->put('currentpass');

        $currentUser = $this->Authorization();
        if ( 
                (
                    $currentUser['id'] != $data['id'] || 
                    bin2hex($data['currentpass']) != $currentUser['password'] 
                ) && 
                !$currentUser['admin'] 
            ) {
            $this->response([
                'status' => FALSE,
                'message' => 'No authorization' . bin2hex($data['currentpass']) . ' ' . $currentUser['password']
            ], REST_Controller::HTTP_UNAUTHORIZED);
        } 

        $data["email"] = $this->put('email');
        $data["name"] = $this->put('name');
        $data["age"] = $this->put('age');
        $data["CI"] = $this->put('CI');
        $data["phone"] = $this->put('phone');
        $data["url_img"] = $this->put('url_img');
        $data["created_at"] = $this->put('created_at');
        $data["updated_at"] = $this->put('updated_at');
        $data["password"] = $this->put('password');
        $data["Base64"] = $this->put('Base64');
        $data['password'] = bin2hex($data['password']);

        $validate = $this->model_user->valid($data);
        if ($validate != null) {        
            $this->response([
                'status' => FALSE,
                'message' => $validate
            ], REST_Controller::HTTP_NOT_FOUND); 
        } 

        $user = $this->model_user->getUser($data['id']);
        $user = $user[0];
        if ($user['email'] != $data['email'] && !$this->model_user->validUser($data['email']) ) {
            $this->response([
                'status' => FALSE,
                'message' => 'El mail ya esta'
            ], REST_Controller::HTTP_NOT_FOUND);
        } 
        if (isset($data['Base64'])) $data['url_img'] = $this->uploadImg($data['Base64'], 'users');
        $this->response([
            'status' => TRUE,
            'message' => $this->model_user->put($data)
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
            'message' => $this->model_user->delete($id)
        ], REST_Controller::HTTP_OK);
    }

}
