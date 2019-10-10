<?php

class Model_subscriber extends CI_Model
{
  
    public $noneFilter = true;
    public $table = "subscriber";
    public $fillable = array(
        "email",
        "key_delete",
        "active",
        "id"
    );
    
    public $hidden = array(
    );

    function __contruct(){
        parent::__construct();
    }
    
    public function get()
    {
        $sql = "SELECT * FROM subscriber";
        $list = $this->db->query($sql)->result_array();
        return $list;
    }

    public function set($email)
    {
        try {
            $sql = "SELECT * FROM subscriber where email=?";
            $list = $this->db->query($sql, array($email))->result_array();
            if ($list == null || empty($list)) {
                $this->db->insert(
                    'subscriber',
                    array(
                        'email' => $email,
                        'key_delete' => bin2hex(random_bytes(64))
                    )
                );
                return $this->db->insert_id();
            } else {
                return $list[0]['id']; 
            }
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function delete($key_delete)
    {
        $this->db->set( 'active' , 0);
        $this->db->where('key_delete', $key_delete);
        return $this->db->update('subscriber'); 
    }


    
    
}
