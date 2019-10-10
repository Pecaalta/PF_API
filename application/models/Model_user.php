<?php

class Model_user extends CI_Model
{
  
    public $noneFilter = true;
    public $table = "user";
    public $fillable = array(
        "email", 
        "name", 
        "age", 
        "CI", 
        "phone", 
        "url_img", 
        "admin", 
        "created_at", 
        "updated_at"
    //    "active", 
    //    "remember_token", 
    );
    
    public $hidden = array(
        "password"
    );

    function __contruct(){
        parent::__construct();
    }
    
    function valid($objet){
      if (false === filter_var($objet["email"], FILTER_VALIDATE_EMAIL)) return 'El email no es correcto';
      if ('' == $objet["name"]) return 'El nombre no es correcto';
      if (false === filter_var($objet["age"], FILTER_VALIDATE_INT)) return 'La edad no es correcto';
      if (false === filter_var($objet["CI"], FILTER_VALIDATE_INT)) return 'El cedula no es correcto';
      if (false === filter_var($objet["phone"], FILTER_VALIDATE_INT)) return 'El telefono no es correcto';
      if ('' == $objet["url_img"] && '' == $objet["Base64"]) return 'La imagen no es correcto';      
      return null;
    }
    
    /**
     * Undocumented function
     *
     * @param [type] $token
     * @return void
     */
    public function getUserSession($token)
    {
        $sql = "
            SELECT User.* FROM User 
            WHERE 
                User.remember_token=? AND 
                User.active=1
            LIMIT 1";
        $user = $this->db->query($sql, array($token))->result_array();
        if (!is_null($user) && sizeof($user) > 0) {
            $sql = "
                SELECT company_role.* FROM User 
                LEFT JOIN company_user ON User.id = company_user.id_user 
                LEFT JOIN company_role ON company_role.id = company_user.id_role 
                WHERE 
                    User.id=? AND 
                    User.active=1 AND
                    (company_user.active IS NULL OR company_user.active=1 ) AND 
                    (company_role.active IS NULL OR company_role.active=1 ) AND 
                    (company_role.start IS NULL OR company_role.start < NOW()) AND 
                    (company_role.end IS NULL OR company_role.end > NOW())
                ORDER BY company_user.created_at ASC
                LIMIT 1";
            $user = $user[0];
            $user['roles'] = $this->db->query($sql, array($user['id']))->result_array();
            return $user;
        }
    }



    public function filterColumn($objet, $all = false) {
        $ret = [];
        if ($this->noneFilter) {
            foreach ($this->fillable as $key) {
                if (isset($objet[$key]) && $objet[$key] != null ) $ret[$key] = $objet[$key];
            }
            if ($all) {
                foreach ($this->hidden as $key) {
                    if (isset($objet[$key]) && $objet[$key] != null ) $ret[$key] = $objet[$key];
                }
            }
        }
        else $ret = $objet;
        return $ret;
    }



    public function validUser($email)
    {
        $sql = "SELECT * FROM $this->table WHERE email=?";
        return (sizeof(
                    $this->db->query(
                        $sql,
                        array($email)
                    )->result_array()
                ) == 0);
    }

    public function getUser($id = null, $noneFilter = false)
    {
        $this->noneFilter = $noneFilter;
        if ($id == null) {
            $sql = "SELECT * FROM $this->table WHERE active=1";
            return array_map( array('Model_user', 'filterColumn'), $this->db->query($sql)->result_array());
        } else {
            $sql = "SELECT * FROM $this->table WHERE id=? AND active=1";
            return array_map( array('Model_user', 'filterColumn'), $this->db->query($sql, array($id))->result_array());
        }
    }

    public function post($data)
    {
        return $this->db->insert($this->table,$this->filterColumn($data, true));
    }

    public function put($data = null)
    {
        $this->noneFilter = true;
        $this->db->set(
            $this->filterColumn($data, false)
        );
        $this->db->where('id', $data['id']);
        return $this->db->update($this->table); 
    }

    public function delete($id)
    {
        $this->db->set( 'active' , 0);
        $this->db->where('id', $id);
        return $this->db->update($this->table); 
    }

    public function login($user)
    {
        $user['remember_token'] = bin2hex(random_bytes(64));
        $this->db->set('remember_token' , $user['remember_token']);
        $this->db->where('id', $user['id']);
        return $this->db->update($this->table) ?  
        [
            'status' => true,
            'message' => $user
        ] : [
            'status' => false,
            'message' => 'Error al intentear ingresar el estado en el servidor'
        ];
    }

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM $this->table WHERE email=? and active=1";
        $lUser = $this->db->query(
                    $sql,
                    array($email)
                )->result_array();
        return sizeof($lUser) == 1 ? $lUser[0] : null; 
    }

    
    
}
