<?php

namespace core\lib;

if ( ! defined('PPP')) exit('非法入口');

use core\lib\config;

class log
{
	static $class;
	/**
	 * 1. 確定日誌存放位置及方式
	 * 2. 寫日誌
	 * 3. 緩存配置
	 */

	static public function init() {
		//確定日誌存放位置及方式
		$drive = config::get('DRIVE', 'log');
		$class = '\core\lib\drive\log\\'.$drive;
		self::$class = new $class;
	}

	static public function log($name, $file = '.log') {
		self::$class->log($name, $file);
	}
	static public function ip() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			return $_SERVER['HTTP_CLIENT_IP'];
		 }else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		 }else{
			return $_SERVER['REMOTE_ADDR'];
		 }
	}
}