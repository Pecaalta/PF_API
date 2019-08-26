<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;


class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}
        
    const MODEL = 'App\model\User';

    function login(Request $request){
        $pass = $request->input('pass');
        $user = $request->input('user');

        $mUser = self::MODEL;
        $oUser = $mUser::where([
            ['active',1],
            ['email',$user]
        ])->first();
        if (!is_null($oUser)){
            if (Crypt::decrypt($oUser->password) != $pass) {
                return $this->respond(Response::HTTP_FOUND,'Contraseña incorecta');
            } else {
                $oUser->remember_token = str_random(64);
                $oUser->save();
                return $this->respond(Response::HTTP_OK, $oUser );
            }
        } else {
            return $this->respond(Response::HTTP_FOUND,'Usuario no encontrado');
        }
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
        $oUsers = self::MODEL;
        $this->validate($request,[]);
        $oNewUser = $request->all();
        $oNewUser['password'] = Crypt::encrypt($oNewUser['password']);
        return $this->respond(Response::HTTP_CREATED, $oUsers::create($oNewUser));
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
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)){
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $model->active = false;
        $model->save();
        return $this->respond(Response::HTTP_OK);
    }

    function respond($status, $data = [])
    {
        return response()->json($data, $status);
    }

}
