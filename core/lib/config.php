<?php

namespace core\lib;

if ( ! defined('PPP')) exit('非法入口');

class config
{
	static public $config = array();
	//引入單個配置
	static public function get($name, $file) {
		/**
		 * 1. 判斷CONFIG是否存在
		 * 2. 判斷CONFIG是否有配置
		 * 3. 緩存配置
		 */
		// p(self::$config);
		if(isset(self::$config[$file])) {
			return self::$config[$file][$name];
		} else {
			// p(1);
			$path = PPP.'/core/config/'.$file.'.php';
			if(is_file($path)) {
				$config = include $path;
				if(isset($config[$name])) {
					self::$config[$file] = $config;
					return $config[$name];
				} else {
					throw new \Exception('沒有這個配置項目'.$name);
				}
			} else {
				throw new \Exception('沒有這個配置文件'.$file);
			}
		}


		
	}
	//引入整個配置
	static public function all($file) {
		if(isset(self::$config[$file])) {
			return self::$config[$file];
		} else {
			// p(1);
			$path = PPP.'/core/config/'.$file.'.php';
			if(is_file($path)) {
				$config = include $path;
				self::$config[$file] = $config;
				return $config;;
				} else {
				throw new \Exception('沒有這個配置文件'.$file);
			}
		}
	}
}