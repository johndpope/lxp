<?php

namespace App\Http\Controllers\Admin;

use App\Model\Article;
use App\Model\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    //列表
    public function showList(Request $request){
        if (\Route::input('action')){
            //返回数据格式
            $return =array('code'=>0,'msg'=>'','count'=>0,'data'=>array());
            $whereArr = [];
            if ($request->input('keyWord')){
                $whereArr[] = ['title','like','%'.$request->input('keyWord').'%'];
            }
            if ($request->input('category_id')){
                $whereArr[] = ['category_id','=',$request->input('category_id')];
            }
            $return['count'] = Article::where($whereArr)->count();
            $return['data'] = Article::where($whereArr)
                ->orderBy('sort','asc')
                ->orderBy('addTime','desc')
                ->paginate($request->input('limit'))
                ->toArray()['data'];
            foreach ($return['data'] as $key => $value) {
                $return['data'][$key]['addTime'] = date('Y-m-d H:i:s',$value['addTime']);
            }
            exit(json_encode($return));
        }
        $categoryArr = Category::where('status','=',1)
            ->orderBy('sort','asc')
            ->get();
        return view('Admin.Article.showList')->with('categoryArr',$categoryArr);
    }

    //添加页
    public function add(){
        $categoryArr = Category::where('status','=',1)
            ->orderBy('sort','asc')
            ->get();
        return view('Admin.Article.add')->with('categoryArr',$categoryArr);
    }

    //执行添加
    public function ajaxAdd(Request $request){
        $res = array(
            'status' => false,
            'echo'  => ''
        );
        $articleOrm = new Article();
        if ($request->file('img')){
            $path = $request->file('img')->store('article/'.date('Y-m-d'),'myUploads');
            $articleOrm->img = $path;
        }
        $articleOrm->addTime = time();
        $articleOrm->author = session()->get('adminInfo')['name'];
        $articleOrm->showNum = 0;
        $articleOrm->category_id = $request->input('category_id');
        $articleOrm->category_name = '';
        $articleOrm->title = $request->input('title');
        $articleOrm->sort = $request->input('sort');
        $articleOrm->outline = $request->input('outline');
        $articleOrm->status = $request->input('status');
        $articleOrm->isHome = $request->input('isHome');
        $articleOrm->isRecommend = $request->input('isRecommend');
        if ($request->input('content')){
            $articleOrm->content = $request->input('content');
        }else{
            $articleOrm->content = '';
        }
        $articleOrm->save();
        $res['status'] = true;
        $res['echo'] = '添加成功';
        exit(json_encode($res));
    }

    //修改页
    public function edit(){
        $id = \Route::input('id');
        $articleInfo = Article::find($id);
        $categoryArr = Category::where('status','=',1)
            ->orderBy('sort','asc')
            ->get();
        return view('Admin.Article.edit')->with('categoryArr',$categoryArr)
            ->with('articleInfo',$articleInfo);
    }

    //执行修改
    public function ajaxEdit(Request $request){
        $id = $request->input('id');
        $articleOrm = Article::find($id);
        if ($request->file('img')){
            @unlink(public_path('uploads/').$articleOrm->img);
            $path = $request->file('img')->store('article/'.date('Y-m-d'),'myUploads');
            $articleOrm->img = $path;
        }
        if ($request->input('category_id')){
            $articleOrm->category_id = $request->input('category_id');
            $articleOrm->category_name = '';
        }
        if ($request->input('title')){
            $articleOrm->title = $request->input('title');
        }
        if ($request->input('sort')){
            $articleOrm->sort = $request->input('sort');
        }
        if ($request->input('outline')){
            $articleOrm->outline = $request->input('outline');
        }
        if ($request->input('status') !== null){
            $articleOrm->status = $request->input('status');
        }
        if ($request->input('isHome') !== null){
            $articleOrm->isHome = $request->input('isHome');
        }
        if ($request->input('isRecommend') !== null){
            $articleOrm->isRecommend = $request->input('isRecommend');
        }
        if ($request->input('content')){
            $articleOrm->content = $request->input('content');
        }
        if ($request->input('showNum')){
            $articleOrm->showNum = $request->input('showNum');
        }
        $articleOrm->save();
        $res['status'] = true;
        $res['echo'] = '修改成功';
        exit(json_encode($res));
    }

    //删除
    public function ajaxDel(){
        $res = array(
            'status' => false,
            'echo'  => ''
        );
        $ids = $_GET['id'];
        $ids = trim($ids,',');
        $articleOrm = Article::whereIn('id',explode(',',$ids))->get();
        foreach ($articleOrm as $item) {
            @unlink(public_path('uploads/').$item->img);
        }
        Article::destroy(explode(',',$ids));
        $res['status'] = true;
        $res['echo'] = '删除成功';
        exit(json_encode($res));
    }
}