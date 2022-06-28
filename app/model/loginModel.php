<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class loginModel extends model {

    public function login($where = array(), $para = array('clinic_id', 'account', 'name', 'roles', 'active', 'allowed_ip'), $table = 'Clinic') {
        return $this->get($table,$para,$where);
    }
}
