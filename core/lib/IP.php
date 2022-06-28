<?php
namespace core\lib;
use core\lib\resModel;
use core\lib\JWT;
if ( ! defined('PPP')) exit('非法入口');
class IP {
  //工廠方法
  public static function factory() { 
    return new self; 
  } 

  /**
   * 檢測訪問的ip是否為規定的允許的ip
   */ 
  public function check_ip($allow = array()){
    if($allow == null || count($allow) == 0) {
      $token = JWT::getHeaders();
      $payload = JWT::verifyToken($token);
      $ALLOWED_IP = $payload['allowed_ip'];
    } else {
      $ALLOWED_IP = $allow;
    }

    $IP = $this->getIP(); 
    $check_ip_arr = explode('.',$IP);//要檢測的ip拆分成陣列 
    
    #限制IP 
    if(!in_array($IP,$ALLOWED_IP)) { 
      foreach ($ALLOWED_IP as $val) { 
        if(strpos($val,'*') !== false) {//發現有*號替代符 
          $arr = array();// 
          $arr = explode('.', $val); 
          $bl = true;//用於記錄迴圈檢測中是否有匹配成功的 
          for($i = 0;$i < 4;$i++) { 
              if($arr[$i] != '*'){//不等於*  就要進來檢測,如果為*符號替代符就不檢查 
                  if($arr[$i] != $check_ip_arr[$i]){ 
                      $bl = false; 
                      break;//終止檢查本個ip 繼續檢查下一個ip 
                  } 
              } 
          }//end for  
          if($bl) {//如果是true則找到有一個匹配成功的就返回 
            return true; 
          } 
        } 
      }//end foreach 
      json(new resModel(403, 'IP位址' . $IP . '不在白名單之內!'));
      exit();
    }
    return true; 
  } 

  /**
  * 獲得訪問的IP
  */ 
  private function getIP() { 
    return isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : (isset($_SERVER["HTTP_CLIENT_IP"]) ? $_SERVER["HTTP_CLIENT_IP"] : $_SERVER["REMOTE_ADDR"]); 
  }
}