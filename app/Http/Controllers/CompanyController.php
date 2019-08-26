<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;


class CompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}
        
    const MODEL = 'App\model\Company';
    const SECTOR = 'App\model\Sector';
    const COMPANY_ROLE = 'App\model\Companyrole';
    const COMPANY_SECTOR = 'App\model\companysector';
    const COMPANY_USER = 'App\model\Companyuser';
    const COMPANY_ODS = 'App\model\Companyods';

    function getSector(Request $request){
        $oSector = self::SECTOR;
        return $this->respond(
            Response::HTTP_OK, 
            $oSector::where('active',1)->get()
        );
    }
    

    function getAll(Request $request){
        $oUsers = self::MODEL;
        return $this->respond(
            Response::HTTP_OK, 
            $oUsers::where('active',1)->get()
        );
    }

    function get(Request $request){
        $nId = $request->input('id');
        $oUsers = self::MODEL;
        return $this->respond(
            Response::HTTP_OK, 
            $oUsers::where([
                ['active',1],
                ['id',$nId]
            ])
        );
    }

    function post(Request $request){

            $oCompany = self::MODEL;
            $oNewCompany = $request->all();
            
            $oModel = $oCompany::where([
                ['rut',$oNewCompany['rut']]
            ])->first();
            if (!is_null($oModel)) {
                return $this->respond(Response::HTTP_CONFLICT,'La el rut ya a sido asignado');
            }
            
            $oNewCompanyData = array();
            if(isset($oNewCompany['name'])) $oNewCompanyData['name'] = $oNewCompany['name'];
            if(isset($oNewCompany['rut'])) $oNewCompanyData['rut'] = $oNewCompany['rut'];
            if(isset($oNewCompany['description'])) $oNewCompanyData['description'] = $oNewCompany['description'];
            if(isset($oNewCompany['debts'])) $oNewCompanyData['debts'] = $oNewCompany['debts'];
            if(isset($oNewCompany['personal'])) $oNewCompanyData['personal'] = $oNewCompany['personal'];
            else $oNewCompanyData['personal'] = 1;
            if(isset($oNewCompany['x'])) $oNewCompanyData['x'] = $oNewCompany['x'];
            if(isset($oNewCompany['y'])) $oNewCompanyData['y'] = $oNewCompany['y'];

            $oDbCompany = $oCompany::create($oNewCompanyData);

            foreach ($oNewCompany['sector'] as $sector) {
                $this->addSector($sector, $oDbCompany['id']);
            }

            for ($i=0; $i < sizeof($oNewCompany['ods']); $i++) { 
                $this->addOds($i, $oDbCompany['id'],$oNewCompany['ods'][$i]);
            }

            return $this->respond(Response::HTTP_CREATED);

    }


    function addSector($data,$id){
        $mModel = self::COMPANY_SECTOR;
        $oModel = $mModel::where([
            ['id_sector',$data['id']],
            ['id_company',$id]
        ])->first();
        if (is_null($oModel)) {
            $mModel::create(array(
                'active' => 1,
                'id_sector' => $data['id'],
                'id_company' => $id
            ));
            return true;
        } else {
            $oModel->active = true;
            $oUser->save();
            return true;
        }
    }

    function addOds($i,$id,$s){
        $mModel = self::COMPANY_ODS;
        $oModel = $mModel::where([
            ['ods',$i],
            ['id_company',$id]
        ])->first();
        if (is_null($oModel)) {
            $mModel::create(array(
                'active' => $s,
                'ods' => $i,
                'id_company' => $id
            ));
            return true;
        } else {
            $oModel->active = $s;
            $oUser->save();
            return true;
        }
    }

    function put(Request $request,$nId){
        $m = self::MODEL;
        $this->validate($request, []);
        $model = $m::find($id);
        if(is_null($model)){
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $model->update($request->all());
        return $this->respond(Response::HTTP_OK, $model);
    }

    function delete(Request $request,$nId){

    }

    function respond($status, $data = [])
    {
        return response()->json($data, $status);
    }
    
    function myCompany(Request $request){
        $oUsers = self::MODEL;
        return $this->respond(
            Response::HTTP_OK, 
            $oUsers::where([
                ['active',1]
            ])->get()
        );
    }

}
