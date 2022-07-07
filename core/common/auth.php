<?php
namespace core\common;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\resModel;
use core\lib\JWT;

class auth {
    //工廠方法
    public static function factory() { 
        return new self; 
    } 

    // 驗證ADMIN TOKEN
	public function admin($msg = 'Permission denied') {
        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);

        if($payload == false || intval($payload['roles']) !== 999) {
            json(new resModel(403, $msg));
            exit();
        }
    }

    // 驗證USERS TOKEN
    public function users($msg = 'Permission denied') {
        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);
        if($payload == false || intval($payload['roles']) < 2 || intval($payload['active']) == 0) {
            json(new resModel(403, $msg));
            exit();
        }
    }

    public function user_info($msg = 'Permission denied') {
        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);
        if($payload == false) {
            json(new resModel(403, $msg));
            exit();
        } else {
            json(new resModel(200, $payload));
            exit();
        }
    }


    //ADMIN可以任意操作
    public function roles_auth($clinic_id) {
        if($clinic_id == null) {
            json(new resModel(401, "無法獲取診所ID"));
            exit();
        }

        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);

        if(intval($payload['roles']) !== 999) {
            if($payload['clinic_id'] !== $clinic_id) {
                json(new resModel(400, "無法操作其他診所"));
                exit();
            }
        }
    }

    public function auth($min = 3) {
        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);
        if(intval($payload['roles']) < $min) {
            json(new resModel(403, "Permission Denied"));
            exit();
        }
    }

    public function check_self($min = 3, $user_id = 0) {
        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);
        if(intval($payload['roles']) < $min && intval($user_id) !== intval($payload['user_id'])) {
            json(new resModel(403, "只能操作自己的資料"));
            exit();
        }
    }

    public function no_admin() {
        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);
        if(intval($payload['roles']) == 999) {
            json(new resModel(403, "ADMIN不能操作"));
            exit();
        }
    }
}