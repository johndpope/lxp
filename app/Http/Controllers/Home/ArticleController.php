<?php

namespace App\Http\Controllers\Home;

use App\Model\About;
use App\Model\Article;
use App\Model\Category;
use App\Model\ArticleComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    //列表
    public function articleList(){
        //关键字
        $keyWordsInfo = About::find(3);
        //描述
        $descriptionInfo = About::find(4);
        //关于博客
        $blogInfo = About::find(2);
        //分类
        $categoryList = Category::where('status','=','1')
            ->get();
        $keyWord = '';
        $category = 0;
        //设置title
        if (intval(\Route::input('category'))){
            $categoryName = Category::find(intval(\Route::input('category')));
            $titleName = $categoryName->name;
            $category = intval(\Route::input('category'));
        }else if(trim(\Route::input('keyWord')) !== ''){
            $titleName = trim(\Route::input('keyWord'));
            $keyWord = trim(\Route::input('keyWord'));
        }else{
            $titleName = '文章专栏';
        }
        //作者推荐
        $isRecommendList = Article::where('status','=','1')
            ->where('isRecommend','=','1')
            ->orderBy('sort','asc')
            ->orderBy('addTime','desc')
            ->select('id','title')
            ->paginate(8);
            // ->get();
        //随便看看
        $suijiList = Article::inRandomOrder()
            ->select('id','title')
            ->take(8)
            ->get();
        return view('Home.Article.articleList')->with('blogInfo',$blogInfo)
            ->with('categoryList',$categoryList)
            ->with('isRecommendList',$isRecommendList)
            ->with('suijiList',$suijiList)
            ->with('keyWordsInfo',$keyWordsInfo)
            ->with('descriptionInfo',$descriptionInfo)
            ->with('titleName',$titleName)
            ->with('keyWord',$keyWord)
            ->with('category',$category)
            ->with('controllerName','Article');
    }

    //ajax获取流数据
    public function getData(){
        $whereArray = [];
        $whereArray[] = ['status','=','1'];
        if (intval(\Route::input('category'))){
            $whereArray[] = ['category_id','=',intval(\Route::input('category'))];
        }
        if (trim(\Route::input('keyWord'))){
            $whereArray[] = ['title','like','%'.trim(\Route::input('keyWord')).'%'];
        }
        $count = Article::where($whereArray)->count();
        if ($count){
            $pageCount = ceil($count/8);
            $list = Article::where($whereArray)
                ->orderBy('sort','asc')
                ->orderBy('addTime','desc')
                ->paginate(8)
                ->toArray()['data'];
        }else{
            $pageCount = 0;
            $list = Article::inRandomOrder()
                ->where('status','=','1')
                ->take(8)
                ->get();
        }
        foreach ($list as $key => $value){
            $list[$key]['commentCount'] = ArticleComment::where('article_id','=',$value['id'])->count();
        }
        exit(json_encode(array('data'=>$list,'pageCount'=>$pageCount)));
    }
}
