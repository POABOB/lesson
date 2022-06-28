<?php
namespace core;
if ( ! defined('PPP')) exit('非法入口');
class ppp
{
	//紀錄類，避免重複加載
	public static $classMap = array();
	public $assign;

	//執行
	static public function run()
	{

		//session開啟
		session_start([
			//session for 3 days
			'cookie_lifetime' => 259200,
        ]);

        $router = new \core\lib\Router();
        $router->setNamespace('\app\controller');
        require APP.'/routes.php';
        $router->set404(function() {
            if(DEBUG) {
                throw new \Exception('Router no match');
                exit();
            }
            header('HTTP/1.1 404 Not Found');
        });
        $router->run();
        //log
        if(!DEBUG) {
            \core\lib\log::init();
            \core\lib\log::log(date('Y-m-d H:i:s').' -- '.$_SERVER['REQUEST_METHOD'].' -- '.$_SERVER['REQUEST_URI'].' -- '.\core\lib\log::ip().' -- '.$_SERVER['HTTP_USER_AGENT']);
        }
	}

	static public function load($class)
	{
		//如果Map裡頭已經加載，就返回true
		if(isset($classMap[$class])) {
			return true;
		} else {
			//替換\\成/
			$class = str_replace('\\', '/', $class);
			$file = PPP.'/'.$class.'.php';
			//如果有這個php，直接加載
			if(is_file($file)) {
				include $file;
				//因為為靜態陣列，使用self來call，再加入Map中
				self::$classMap[$class] = $class;
			} else {
				return false;
			}
		}	
	}

	//前後端分離不需要使用這些功能
	//賦值給視圖
	public function assign($name, $value)
	{
		$this->assign[$name] = $value;
	}
	//顯示html或是php
	public function display($file)
	{
		//原先view
		$file = PPP . '/static/'. $file;
		if(is_file($file)) {
			extract($this->assign ? $this->assign : []);
			include $file;
		}
	}
}