<?php 
/*
 module:		会员管理
 create_time:	2021-01-12 14:44:38
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Member;
use think\exception\ValidateException;
use xhadmin\CommonService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MemberService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Member::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
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
			validate(\app\admin\validate\Member::class)->scene('add')->check($data);
			$data['password'] = md5($data['password'].config('my.password_secrect'));
			$data['create_time'] = strtotime($data['create_time']);
			$res = Member::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->member_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			validate(\app\admin\validate\Member::class)->scene('update')->check($data);
			$data['create_time'] = strtotime($data['create_time']);
			$res = Member::update($data);
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


	/*
 	* @Description  导出
 	*/
	public static function dumpData($list,$field){
		ob_clean();
		try{
			$map['menu_id'] = 724;
			$map['field'] = $field;
			$fieldList = db("field")->where($map)->order('sortid asc')->select()->toArray();

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			//excel表头
			foreach($fieldList as $key=>$val){
				$sheet->setCellValue(getTag($key+1).'1',$val['name']);
			}
			//excel表主体内容
			foreach($list as $k=>$v){
				foreach($fieldList as $m=>$n){
					if(in_array($n['type'],[7,12,25]) && $v[$n['field']]){
						$v[$n['field']] = !empty($v[$n['field']]) ? date(getTimeFormat($n),$v[$n['field']]) : '';
					}
					if(in_array($n['type'],[2,3,4,23,27,29]) && !empty($n['config'])){
						$v[$n['field']] = getFieldVal($v[$n['field']],$n['config']);
					}
					if($n['type'] == 17){
						foreach(explode('|',$n['field']) as $q){
							$v[$n['field']] .= $v[$q].'-';
						}
						$v[$n['field']] = rtrim($v[$n['field']],'-');
					}
					$sheet->setCellValueExplicit(getTag($m+1).($k+2),$v[$n['field']],\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
					$v[$n['field']] = '';
				}
			}
			
			$filename = date('YmdHis');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename='.$filename.'.'.config('my.import_type')); 
			header('Cache-Control: max-age=0');
			$writer = new Xlsx($spreadsheet); 
			$writer->save('php://output');
			exit;
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
	}
	/*
 	* @Description  修改密码
 	*/
	public static function uppass($data){
		try{
			validate(\app\admin\validate\Member::class)->scene('uppass')->check($data);
			$data['password'] = md5($data['password'].config('my.password_secrect'));
			$res = Member::update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

