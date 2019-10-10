<?php

class Model_company extends CI_Model
{
  
    public $noneFilter = true;
    public $table = "company";
    public $fillable = array(
        'rut',
        "active", 
        'title', 
        'bps', 
        'description', 
        'debts', 
        'personal', 
        'x', 
        'y', 
        'id_user', 
        'addres', 
        'phone', 
        'email', 
        'date', 
        'dateStart',
        'apoyoGoviernoDepartamental',
        'apoyoANII',
        'apoyoANR',
        'apoyoOtro',
        
        'web',
        'facebook',
        'instagram',
        'twitter',
        'branchactivity',
        
        'startactivitytime',
        'endactivitytime',
    );
    
    public $hidden = array(

    );

    function __contruct(){
        parent::__construct();
    }
    
    function valid($objet){    
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

    public function get($id = null,$filter = null)
    {
        if ($id == null) {
            if ($filter != null) {
                $sql = "SELECT * FROM $this->table WHERE $filter AND active=1";
            } else {
                $sql = "SELECT * FROM $this->table WHERE active=1";
            }
            return $this->db->query($sql)->result_array();
        } else {
            $sql = "SELECT * FROM $this->table WHERE id=? AND active=1";
            $com = $this->db->query($sql, array($id))->result_array();
            if ($com != null && sizeof($com) > 0) {
                $com = $com[0];
                $sql = "SELECT sector.id,sector.name, sector.img, company_sector.active FROM sector left JOIN company_sector ON sector.id = company_sector.id_sector AND company_sector.id_company=?  WHERE sector.active=1";
                $com['sector'] = $this->db->query($sql, array($id))->result_array();
                $sql = "SELECT * FROM company_ods WHERE id_company=?";
                $com['ODS'] = $this->db->query($sql, array($id))->result_array();
                $sql = "SELECT * FROM company_role WHERE id_company=?";
                $com['role'] = $this->db->query($sql, array($id))->result_array();
                return $com;
            }
            return null;
        }
    }
    

    public function post($data,$id_user)
    {
        try {
            $this->db->trans_start();
            $this->db->set($this->filterColumn($data, true));
            $this->db->insert($this->table);
            $id_company = $this->db->insert_id();
            
            $id_role = $this->addRole( 
                $id_company, 
                'Fundador', 
                null, 
                null, 
                bin2hex(random_bytes(64)), 
                true, 
                true, 
                true, 
                true, 
                true, 
                true 
            );
            $id_role = $this->addUser( 
                $id_company, 
                $id_user, 
                $id_role
            );

            foreach ($data['sector'] as $sector) {
                $this->addSector($sector, $id_company);
            }

            for ($i=0; $i < sizeof($data['ods']); $i++) { 
                $this->addOds($i, $id_company,$data['ods'][$i]);
            }
            
            $this->db->trans_complete();

        } catch (\Throwable $th) {
            $this->db->trans_rollback();

            throw $th;
        }
    }

    public function put($data = null)
    {
        try {
            $this->db->trans_start();
            $setData = $this->filterColumn($data);
            $setData['updated_at'] = date("j/n/Y");
            
            $this->db->set(
                $setData
            );
            $this->db->where('id', $data['id']);
            $this->db->update($this->table);
            if (isset($data['sector']) && $data['sector'] != null) {
                $this->resetSector($data['id']);
                foreach ($data['sector'] as $sector) {
                    $this->addSector($sector, $data['id']);
                }
            }
            if (isset($data['ods']) && $data['ods'] != null) {
            $this->resetods($data['id']);
                for ($i=0; $i < sizeof($data['ods']); $i++) { 
                    $this->addOds($i, $data['id'],$data['ods'][$i]);
                }
            }
            $this->db->trans_complete();
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            throw $th;
        }
    }

    public function delete($id)
    {
        $this->db->set( 'active' , false);
        $this->db->where('id', $id);
        return $this->db->update($this->table); 
    }

    public function changeState($id, $status)
    {
        $this->db->set( 'status' , $status);
        $this->db->where('id', $id);
        return $this->db->update($this->table); 
    }

    public function getbyUser($id = null)
    {
        $sql = "SELECT distinct $this->table . * FROM $this->table join company_user on company_user.id_company = $this->table . id WHERE company_user.id_user=? AND $this->table .ACTIVE=1 AND company_user.active=1";
        return $this->db->query($sql, array($id))->result_array();
    }

    public function addUser($id_company, $id_user, $id_role)
    {
        $this->db->set(array(
            'id_company'  => $id_company, 
            'id_user'  => $id_user, 
            'id_role'  => $id_role
        ));
        $this->db->insert('company_user');
        return $this->db->insert_id();
 
    }

    public function removeUser($id_company, $id_user, $id_role)
    {
        $this->db->set( 'active' , false);
        $this->db->where(array(
            'id_company'  => $id_company, 
            'id_user'  => $id_user, 
            'id_role'  => $id_role
        ));
        return $this->db->update('company_user'); 
    }


    public function addRole( 
        $id_company, 
        $name, 
        $start = null,
        $end = null, 
        $key_add = false, 
        $edit_info = false, 
        $delete_company = false, 
        $remove_user = false, 
        $edit_role = false, 
        $add_user = false, 
        $see_role = false 
        )
    {
        $this->db->set(array(
            'id_company' => $id_company, 
            'name' => $name, 
            'start' => $start, 
            'end' => $end, 
            'key_add' => $key_add, 
            'edit_info' => $edit_info, 
            'delete_company' => $delete_company, 
            'remove_user' => $remove_user, 
            'edit_role' => $edit_role, 
            'add_user' => $add_user, 
            'see_role' => $see_role
        ));
        $this->db->insert('company_role');
        return $this->db->insert_id();
    }

    public function editRole( 
        $id, 
        $name, 
        $start = null,
        $end = null, 
        $key_add = false, 
        $edit_info = false, 
        $delete_company = false, 
        $remove_user = false, 
        $edit_role = false, 
        $add_user = false, 
        $see_role = false 
        )
    {
        $this->db->set(array( 
            'name' => $name, 
            'start' => $start, 
            'end' => $end, 
            'key_add' => $key_add, 
            'edit_info' => $edit_info, 
            'delete_company' => $delete_company, 
            'remove_user' => $remove_user, 
            'edit_role' => $edit_role, 
            'add_user' => $add_user, 
            'see_role' => $see_role
        ));
        $this->db->where(array(
            'id'  => $id
        ));
        return $this->db->update('company_role');
    }

    public function removeRole($id)
    {
        $this->db->set( 'active' , false);
        $this->db->where(array(
            'id'  => $id
        ));
        return $this->db->update('company_user'); 
    }
    
    public function sector()
    {
        $sql = "SELECT * FROM sector WHERE active=1";
        return $this->db->query($sql)->result_array();
    }
    function resetSector($id) {
        $this->db->set( 'active' , false);
        $this->db->where(array(
            'id_company' => $id
        ));
        return $this->db->update('company_sector');
    }
    function addSector($data,$id){
        $sql = "SELECT * FROM company_sector WHERE id_sector=? AND id_company=?";
        $objet = $this->db->query($sql, array($data['id'], $id))->result_array();
        if (is_null($objet) || sizeof($objet) == 0) {
            $this->db->set(array(
                'active' => $data['status'],
                'id_sector' => $data['id'],
                'id_company' => $id
            ));
            return $this->db->insert('company_sector');
        } else {
            $this->db->set( 'active' , $data['status']);
            $this->db->where(array(
                'id_sector' => $data['id'],
                'id_company' => $id
            ));
            return $this->db->update('company_sector');
        }
    }
    function resetods($id) {
        $this->db->set( 'active' , false);
        $this->db->where(array(
            'id_company' => $id
        ));
        return $this->db->update('company_ods');
    }
    function addOds($i,$id,$s){
        $sql = "SELECT * FROM company_ods WHERE ods=? AND id_company=?";
        $objet = $this->db->query($sql, array($i, $id))->result_array();
        if (is_null($objet) || sizeof($objet) == 0) {
            $this->db->set(array(
                'active' => $s,
                'ods' => $i,
                'id_company' => $id
            ));
            return $this->db->insert('company_ods');
        } else {
            $this->db->set( 'active' , $s);
            $this->db->where(array(
                'ods' => $i,
                'id_company' => $id
            ));
            return $this->db->update('company_ods');
        }
    }

    public function getCompanyByKey($key, $id){
        try {
            $sql = "SELECT * FROM company_role WHERE key_add=?";
            $companyRole = $this->db->query($sql,array($key))->result_array();
            if ($companyRole == null || sizeof($companyRole) == 0) return [
                'status' => FALSE,
                'message' => 'La key no pertenece a ninguna empresa'
            ];
            $companyRole = $companyRole[0];
            $id_role = $this->addUser( 
                $companyRole['id_company'], 
                $id, 
                $companyRole['id']
            );
            return [
                'status' => True,
                'message' => $id_role
            ];
        } catch (\Throwable $th) {
            return [
                'status' => FALSE,
                'message' => $th
            ];
        }

    }
    /** FILTROS */
    
    public function getColumns($data){
        return $this->fillable;
    }
    public function addFilter($data){
        $this->db->set('name' ,$data['name'] );
        $this->db->insert('filter');
        $id_filter = $this->db->insert_id();
        foreach ($data['configuration'] as $oConfig) {
            $this->db->set(array(
                'filter_id' => $id_filter,
                'column' => $data['column'],
                'comparation' => $data['comparation'],
                'default'=> $data['default']
            ));
            $this->db->insert('filterConfiguration');
        }
    }
    
    public function getAllFilter($data){
        $sql = "SELECT * FROM filter WHERE active=1";
        return $this->db->query($sql)->result_array();
    }

    public function getFilter($id){
        $sql = "SELECT * FROM filterConfiguration WHERE filter_id=? AND active=1";
        return $this->db->query($sql, array('id' => $id))->result_array();
    }
    public function getWeareFilter($id){
        $sql = "SELECT * FROM filterConfiguration WHERE filter_id=? AND active=1";
        return $this->db->query($sql, array('id' => $id))->result_array();
    }


    


}
