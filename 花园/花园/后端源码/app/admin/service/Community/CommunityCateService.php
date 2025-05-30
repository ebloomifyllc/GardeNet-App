<?php 
/*
 module:		分类管理
 create_time:	2022-01-16 12:24:51
 author:		
 contact:		
*/

namespace app\admin\service\Community;
use app\admin\model\Community\CommunityCate;
use think\exception\ValidateException;
use xhadmin\CommonService;

class CommunityCateService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = CommunityCate::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['rows'=>$res['data'],'total'=>$res['total']];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$res = CommunityCate::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->community_cate_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$res = CommunityCate::update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res;
	}




}

