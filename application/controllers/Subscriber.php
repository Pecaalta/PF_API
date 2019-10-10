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
class Subscriber extends REST_Controller {
    
    function __construct()
    {     
        parent::__construct();

        $this->load->helper('url');
		$this->load->library('session');
        $this->load->model('model_subscriber');
    }
    
    public function  index_get(){
        return $this->model_subscriber->get();
    }
    public function  curl_get(){

        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\"personalizations\":[{\"to\":[{\"email\":\"pecaalta@gmail.com\",\"name\":\"John Doe\"}],\"dynamic_template_data\":{\"verb\":\"\",\"adjective\":\"\",\"noun\":\"\",\"currentDayofWeek\":\"\"},\"subject\":\"Hello, World!\"}],\"from\":{\"email\":\"pecaalta@gmail.com\",\"name\":\"John Doe\"},\"template_id\":\"d-3159fc03f6854d5190938b63059791b7\"}",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer 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",
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }

    }

    public function  index_post(){
    
        $email = $this->post('email');
        if ($email == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'No users were found'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $this->response([
            'status' => TRUE,
            'message' => $this->model_subscriber->set($email)
        ], REST_Controller::HTTP_OK);
    }
    
    public function index_delete()
    {
        $key_delete = $this->get('key_delete');

        if ($key_delete == null) {
            $this->response([
                'status' => FALSE,
                'message' => 'El id es nesesario'
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $this->response([
            'status' => TRUE,
            'message' => $this->model_subscriber->delete($key_delete)
        ], REST_Controller::HTTP_OK);
    }

}
