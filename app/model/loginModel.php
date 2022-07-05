<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class loginModel extends model {

    public function login($where = array(), $para = array('user_id', 'account', 'name', 'roles', 'active', 'clinic_id'), $table = 'Users') {
        $user = $this->get($table,$para,$where);
        if($user !== null) {
            if($user['active'] == '1') {
                if($user['roles'] !== '999') {
                    // USER
                    $clinic = $this-> get_clinic_parent(array('clinic_id' => $user['clinic_id']));
                    if(count($clinic) !== 0 && $clinic[0]['active'] == '1') {
                        $user['clinic_name'] = $clinic[0]['name'];
                        if($clinic[0]['parent_id'] !== 0) {
                            $user['parent_id'] = $clinic[0]['parent_id'];
                            $user['parent_name'] = $clinic[0]['parent_name'];
                        } else {
                            $user['parent_id'] = 0;
                            $user['parent_name'] = "";
                        }
                        return $user;
                    }               
                    // 診所未啟用
                    return -2;
                } else {
                    // ADMIN
                    return $user;
                }
            }
            // 使用者未啟用
            return -1;
        }
        // 帳號或密碼錯誤
        return 0;
    }

    // 獲取診所的上級診所
    public function get_clinic_parent($where = array(), $para = array('clinic_id', 'name', 'active', 'parent_id'), $table = 'Clinic') {
        return $this->query("
            SELECT  a.clinic_id AS clinic_id, a.name AS name, 
                    a.active AS active, a.parent_id AS parent_id,
                    b.name AS parent_name
            FROM    Clinic a, Clinic b
            WHERE   b.clinic_id = a.parent_id AND a.clinic_id = {$where['clinic_id']}
            LIMIT 1;
        ")->fetchAll();
    }
}
