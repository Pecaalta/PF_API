<?php

class Model_news extends CI_Model
{
  
    public $noneFilter = true;
    public $table = "news";
    public $fillable = array(
        "active", 
        "html", 
        "description", 
        "title", 
        "url_img", 
        "id_User", 
        "created_at", 
        "updated_at"
    //    "active", 
    );
    
    public $hidden = array(

    );

    function __contruct(){
        parent::__construct();
    }
    
    function valid($objet){
      if ('' == $objet["html"]) return 'Es ovligatorio el contenido';
      if ('' == $objet["description"]) return 'Es ovligatorio la description';
      if ('' == $objet["title"]) return 'Es ovligatorio el titulo';
      if ('' == $objet["url_img"] && '' == $objet["Base64"]) return 'La imagen no es correcto';      
      return null;
    }
    
    public function filterColumn($objet, $all = false) {
        $ret = [];
        if ($this->noneFilter) {
            foreach ($this->fillable as $key) {
                if (isset($objet[$key])) $ret[$key] = $objet[$key];
            }
            if ($all) {
                foreach ($this->hidden as $key) {
                    if (isset($objet[$key])) $ret[$key] = $objet[$key];
                }
            }
        }
        else $ret = $objet;
        return $ret;
    }

    public function get($id = null)
    {
        if ($id == null) {
            $sql = "SELECT * FROM $this->table WHERE active=1";
            return $this->db->query($sql)->result_array();
        } else {
            $sql = "SELECT * FROM $this->table WHERE id=? AND active=1";
            return $this->db->query($sql, array($id))->result_array();
        }
    }

    public function post($data)
    {

        $this->db->set($this->filterColumn($data, true));
        return $this->db->insert($this->table);
    }

    public function put($data = null)
    {
        $this->db->set(
            $this->filterColumn($data)
        );
        $this->db->where('id', $data['id']);
        return $this->db->update($this->table); 
    }

    public function delete($id)
    {
        $this->db->set( 'active' , false);
        $this->db->where('id', $id);
        return $this->db->update($this->table); 
    }


    
    
}
