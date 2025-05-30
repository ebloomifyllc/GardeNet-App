<?php
/**
 * 数据表中间件
 * ============================================================================
 * * COPYRIGHT 2016-2019 xhadmin.com , and all rights reserved.
 * * WEBSITE: http://www.xhadmin.com;
 * ----------------------------------------------------------------------------
 * This is not a free software!You have not used for commercial purposes in the
 * premise of the program code to modify and use; and publication does not allow
 * any form of code for any purpose.
 * ============================================================================
 * Author: 寒塘冷月 QQ：274363574
 */

namespace app\admin\controller\Sys\middleware;
use app\admin\controller\Sys\model\Application;
use app\admin\controller\Admin;
use think\facade\Db;

class SetTable extends Admin
{
	
    public function handle($request, \Closure $next)
    {	

		$data = $request->param();
		
		if($data['table_status'] && $data['table_name'] && $data['pk_id']){
			try{
				$data['table_name'] = strtolower(trim($data['table_name']));
				$data['pk_id'] = strtolower(trim($data['pk_id']));
				$connect = $data['connect'] ? $data['connect'] : config('database.default');
				
				//创建数据表
				$sql=" CREATE TABLE IF NOT EXISTS `".config('database.connections.'.$connect.'.prefix')."".$data['table_name']."` ( ";
				$sql .= '
					`'.$data['pk_id'].'` int(11) NOT NULL AUTO_INCREMENT ,
					PRIMARY KEY (`'.$data['pk_id'].'`)
					) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
				';
				
				Db::connect($connect)->execute($sql); 
				
				//如果是cms应用 则数据表创建content_id 字段
				$applicationInfo = Application::find($data['app_id']);
				if($applicationInfo['app_type'] == 3){
					$propertyField = \app\admin\controller\Sys\service\FieldSetService::propertyField();
					$property = $propertyField[2];
					$sql = "ALTER TABLE ".config('database.connections.'.$connect.'.prefix')."{$data['table_name']} ADD content_id {$property['name']}({$property['maxlen']}{$property['decimal']}) DEFAULT NULL";
				}
				
				Db::connect($connect)->execute($sql); 
			}catch(\Exception $e){
				return json(['status'=>'01','msg'=>$e->getMessage()]);
			}
		}
        
		return $next($request);
    }
}