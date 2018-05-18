<?php

namespace App\Http\Controllers\Admin;

use App\Model\UserComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserCommentController extends Controller
{
    //列表
    public function showList(Request $request){
        if (\Route::input('action')){
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $whereArray[] = ['pid','=','0'];
            $orWhereArray = [];
            if ($request->input('startTime')){
                $whereArray[] = ['time','>=',strtotime($request->input('startTime'))];
            }
            if ($request->input('endTime')){
                $whereArray[] = ['time','<=',strtotime($request->input('endTime'))+86400];
            }
            if ($request->input('keyWord')){
                $orWhereArray[] = ['user_account','like','%'.$request->input('keyWord').'%'];
            }
            $return['count'] = UserComment::where($whereArray)
                ->where(function ($query) use ($orWhereArray){
                    foreach ($orWhereArray as $item) {
                        $query->orWhere($item[0],$item[1],$item[2]);
                    }
                })
                ->count();
            $return['data'] = UserComment::where($whereArray)
                ->where(function ($query) use ($orWhereArray){
                    foreach ($orWhereArray as $item) {
                        $query->orWhere($item[0],$item[1],$item[2]);
                    }
                })
                ->orderBy('time','desc')
                ->paginate($request->input('limit'))
                ->toArray()['data'];
            foreach ($return['data'] as $key => $value){
                $return['data'][$key]['count_zi'] = UserComment::where('pid','=',$value['id'])->count();
            }
            return $return;
        }
        return view('Admin.UserComment.showList');
    }

    public function showHuiFuList(Request $request){
        $id = \Route::input('id');
        if (\Route::input('action')){
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $whereArray[] = ['pid','=',$id];
            $orWhereArray = [];
            if ($request->input('startTime')){
                $whereArray[] = ['time','>=',strtotime($request->input('startTime'))];
            }
            if ($request->input('endTime')){
                $whereArray[] = ['time','<=',strtotime($request->input('endTime'))+86400];
            }
            if ($request->input('keyWord')){
                $orWhereArray[] = ['user_account','like','%'.$request->input('keyWord').'%'];
            }
            $return['count'] = UserComment::where($whereArray)
                ->where(function ($query) use ($orWhereArray){
                    foreach ($orWhereArray as $item) {
                        $query->orWhere($item[0],$item[1],$item[2]);
                    }
                })
                ->count();
            $return['data'] = UserComment::where($whereArray)
                ->where(function ($query) use ($orWhereArray){
                    foreach ($orWhereArray as $item) {
                        $query->orWhere($item[0],$item[1],$item[2]);
                    }
                })
                ->orderBy('time','asc')
                ->paginate($request->input('limit'))
                ->toArray()['data'];
            return $return;
        }
        return view('Admin.UserComment.showHuiFuList')->with('id',$id);
    }

    //删除
    public function ajaxDel(){
        $res = array(
            'status' => false,
            'echo'  => ''
        );
        $ids = $_GET['id'];
        $ids = trim($ids,',');
        UserComment::destroy(explode(',',$ids));
        UserComment::whereIn('pid',explode(',',$ids))->delete();
        $res['status'] = true;
        $res['echo'] = '删除成功';
        return $res;
    }
}
