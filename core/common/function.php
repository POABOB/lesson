<?php

if ( ! defined('PPP')) exit('非法入口');

// show msg like var_dump
function p($var)
{
	if (is_bool($var)) {
		var_dump($var);
	} else if (is_null($var)) {
		var_dump(NULL);
	} else {
		echo "<pre>".print_r($var, true)."</pre>";
	}
}

/**
 *@param $name 對應值
 *@param $default 默認值
 *@param $fitt 過濾方法 'int'
 */
function get($name, $default = false, $fitt = false)
{
	if (isset($_GET[$name])) {
		if($fitt) {
			switch ($fitt) {
				case 'int':
					if(is_numeric($_GET[$name])) {
						return $_GET[$name];
					} else {
						return $default;
					}
					break;
				default:
					# code...
					break;
			}
		} else {
			return $_GET[$name];
		}
	} else {
		return $default;
	}
}

// //判斷方法
// function http_method()
// {
//     if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
//         return 'POST';
//     } else {
//         return 'GET';
//     }
// }

//判斷方法
function http_method($method = 'GET')
{
    if (!(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == $method)) {
		show404();
    } 
}

//顯示404
function show404()
{
    header('HTTP/1.1 404 Not Found');
    header("status: 404 Not Found");
    exit();
}

//返回json格式資料
function json($array)
{
    header('Content-Type:application/json; charset=utf-8');
    echo json_encode($array, true);
}

//獲取json
function post_json(){
	$json = file_get_contents('php://input');
	return json_decode($json, true);
}

function get_chinese_weekday($datetime)
{
    $weekday = date('w', strtotime($datetime));
    return '星期' . ['日', '一', '二', '三', '四', '五', '六'][$weekday];
}

function get_weekday($datetime)
{
    $weekday = date('w', strtotime($datetime));
    return ['日', '一', '二', '三', '四', '五', '六'][$weekday];
}
/**
 *@param $name 對應值
 *@param $default 默認值
 *@param $fitt 過濾方法 'int'
 */
 //json 傳遞不需要
// function post($name, $default = false, $fitt = false)
// {
// 	if (isset($_POST[$name])) {
// 		if($fitt) {
// 			switch ($fitt) {
// 				case 'int':
// 					if(is_numeric($_POST[$name])) {
// 						return $_POST[$name];
// 					} else {
// 						return $default;
// 					}
// 					break;
// 				default:
// 					# code...
// 					break;
// 			}
// 		} else {
// 			return $_POST[$name];
// 		}
// 	} else {
// 		return $default;
// 	}
// }

function getweek_fday_lday($thisday){
    //取得thisday 為禮拜幾 0-6
    $weekday = date("w", strtotime($thisday));
    //該週的第一天
    $week_fday = date("Y-m-d", strtotime("$thisday -".$weekday." days"));
    //該週的最後一天
    $week_lday = date("Y-m-d", strtotime("$week_fday +6 days"));
    //回傳 日期,該日期當週的第一天,該日期當週的最後一天
    return array('this_day'=>$thisday,'week_first_day'=>$week_fday,'week_last_day'=>$week_lday);
}

function unique_multidim_array($array, $key) {
    $temp_array = array();
    $i = 0;
    $key_array = array();
   
    foreach($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
}

function sortByDate($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
}

//像是js的find()
function array_find(array $array, callable $callback) {
    foreach ($array as $key => $value) {
        if ($callback($value, $key, $array)) {
            return $value;
        }
    }
    return null;
}

function blob_etag($file) {
    // ETAG LAST-MODYFIED
    $last_modified_time  = filemtime($file);
    $etag = md5_file($file);

    header( "Last-Modified: ".gmdate( "D, d M Y H:i:s", $last_modified_time )." GMT" );
    header( "Etag: ".$etag );
    header('Cache-Control: public');    // if last modified date is same as "HTTP_IF_MODIFIED_SINCE", send 304 then exit
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
            header( "HTTP/1.1 304 Not Modified" );
            exit;
        }
    }
}

function generatorPassword($password_len) {
    $password_len = 7;
    $password = '';

    // remove o,0,1,l
    $word = 'abcdefghijkmnpqrstuvwxyz!@#$ABCDEFGHIJKLMNPQRSTUVWXYZ23456789';
    $len = strlen($word);

    for ($i = 0; $i < $password_len; $i++) {
        $password .= $word[rand() % $len];
    }

    return $password;
}

function base_url($string = '/')
{
	if(strlen($string) != 1) {
		if((substr($string, 0, 1)) != '/') {
			$string = '/'.$string;
		}
	}
	return HTTP.$_SERVER['HTTP_HOST'].URL.$string;
}

function site_url($string = '/')
{
	if(strlen($string) != 1) {
		if((substr($string, 0, 1)) != '/') {
			$string = '/'.$string;
		}
	}
	return HTTP.$_SERVER['HTTP_HOST'].URL.'/app/views'.$string;
}

function replaceEnterWithP($str) {
    $string = $str;
    $string = str_replace("\n", '</p><p class="p_">', $string);
    $string = str_replace("\r", '</p><p class="p_">', $string);
    return '<p class="p_">' . $string . '</p>';
}

function scandirFolder($path) {
    $list = [];
    $temp_list = scandir($path);
    foreach ($temp_list as $file) {
        //排除根目錄
        if ($file != ".." && $file != ".") {
            if (is_dir($path . "/" . $file)) {
                //子資料夾，進行遞回
                $list[$file] = scandirFolder($path . "/" . $file);
            }
            else {
                //根目錄下的檔案
                $list[] = '/' . $file;
            }
        }
    }
    return $list;
}

function delTree($dir) { 
    $files = array_diff(scandir($dir), array('.', '..')); 

    foreach ($files as $file) { 
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    }

    return rmdir($dir); 
}

function scandirFolderWithoutFolder($path) {
    $list = [];
    $temp_list = scandir($path);
    foreach ($temp_list as $file) {
        //排除根目錄
        if ($file != ".." && $file != ".") {
            if (!is_dir($path . "/" . $file)) {
                //根目錄下的檔案
                $list[] = '/' . $file;
            }
        }
    }
    return $list;
}

if( !function_exists('apache_request_headers') ) {

    function apache_request_headers() {
      $arh = array();
      $rx_http = '/\AHTTP_/';
      foreach($_SERVER as $key => $val) {
        if( preg_match($rx_http, $key) ) {
          $arh_key = preg_replace($rx_http, '', $key);
          $rx_matches = array();
          // do some nasty string manipulations to restore the original letter case
          // this should work in most cases
          $rx_matches = explode('_', $arh_key);
          if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
            foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
            $arh_key = implode('-', $rx_matches);
          }
          $arh[$arh_key] = $val;
        }
      }
      return( $arh );
      }
}
function get_HTTP_request_headers() {
    $HTTP_headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $single_header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $HTTP_headers[$single_header] = $value;
    }
    return $HTTP_headers;
}