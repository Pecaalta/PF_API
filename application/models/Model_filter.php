<?php

class Model_filter extends CI_Model
{
  
    public $noneFilter = true;
    public $table = "user";
    public $fillable = array(
        "name",
        "id"
    );
    
    public $hidden = array(
    );

    function __contruct(){
        parent::__construct();
    }
    
    function valid($objet){
      if ('' == $objet["name"]) return 'El nombre no es correcto';     
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
        if ($id == null ) {
            $sql = "SELECT * FROM filter LEFT JOIN filter_config ON filter_config.id_filter = filter.id WHERE filter.active=1 AND filter_config.active=1  ORDER BY filter_config.id_filter Asc";
            $filters = $this->db->query($sql)->result_array();
            $retfilters = [];
            if (sizeof($filters) == 0) return null;
            $temfilters = array(
                'id' => $filters[0]['id_filter'],
                'name' => $filters[0]['name'],
                'configuration' => []
            );
            foreach ($filters as $oFilter) {
                if ($oFilter['id_filter'] != $temfilters['id']) {
                    $retfilters[] = $temfilters;
                    $temfilters = array('id' => $oFilter['id_filter'], 'name' => $oFilter['name'] ,'configuration' => [] );
                }
                $temfilters['configuration'][] = array(
                    'id_filter' => $oFilter['id_filter'],
                    'comparador' => $oFilter['comparador'],
                    'columna' => $oFilter['columna'],
                    'default' => $oFilter['default']
                );
            }
            $retfilters[] = $temfilters;
            return $retfilters;
        } else {
            $sql = "SELECT * FROM filter WHERE id=? and active=1";
            $filters = $this->db->query($sql,array($id))->result_array();
            if (sizeof($filters) == 0) return null;
            $filters = $filters[0];
            $sql = "SELECT * FROM filter_config WHERE active=1 and id_filter=?";
            $filters['configuration'] = $this->db->query($sql,array($filters['id']))->result_array();
            return $filters;

        }
    }

    public function post($data)
    {
        $this->db->insert('filter',$this->filterColumn($data, true));
        $id = $this->db->insert_id();

        foreach ($data['configuration'] as $conf) {
            $this->addConf($id, $conf['comparador'], $conf['columna'], $conf['default']);
        }
    }

    public function put($data = null)
    {
        $this->db->set(
            $this->filterColumn($data, true)
        );
        $this->db->where('id', $data['id']);
        $this->db->update('filter'); 
        $this->resetConf($data['id']);
        foreach ($data['configuration'] as $conf) {
            $this->addConf($data['id'], $conf['comparador'], $conf['columna'], $conf['default']);
        }
        
    }

    function resetConf($id) {
        $this->db->set( 'active' , false);
        $this->db->where(array(
            'id_filter' => $id
        ));
        return $this->db->update('filter_config');
    }



    function addConf($id_filter,$comparador,$columna,$default){
        $sql = "SELECT * FROM filter_config WHERE id_filter=? AND comparador=? AND columna=?";
        $objet = $this->db->query($sql, array($id_filter, $comparador,$columna))->result_array();
        if (is_null($objet) || sizeof($objet) == 0) {
            $this->db->set(array(
                'active' => 1,
                'id_filter' => $id_filter,
                'comparador' => $comparador,
                'columna' => $columna,
                'default' => $default,
            ));
            return $this->db->insert('filter_config');
        } else {
            $this->db->set(array(
                'active' => 1,
                'default' => $default,
            ));
            $this->db->where(array(
                'id_filter' => $id_filter,
                'comparador' => $comparador,
                'columna' => $columna
            ));
            return $this->db->update('filter_config');
        }
    }


    public function delete($id)
    {
        $this->db->set( 'active' , 0);
        $this->db->where('id', $id);
        return $this->db->update('filter'); 
    }


    
    
}
