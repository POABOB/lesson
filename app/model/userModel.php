<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class userModel extends model {
    public function get_user($where = array(), $para = array('user_id', 'account', 'name', 'roles', 'active', 'clinic_id'), $table = 'Users') {
        return $this->select($table, $para, $where);
    }

    public function insert_user($para = array(), $table = 'Users') {
        if(!$this->has('Clinic', array('clinic_id' => $para['clinic_id']))) {
          return -2;
        }

        if($this->has($table, array('account' => $para['account']))) {
          return -1;
        }

        $this->insert($table,$para);
        return  0;
    }

    public function update_all_clinic($para = array(), $where = array(), $table = 'Users') {
        if($this->has($table, array('account' => $para['account'], 'clinic_id[!]' => $where['clinic_id']))) {
            return -1;
        } else {
            $this->update($table, $para, $where);
            $err = $this->error;
            if($err == null) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    public function update_user($para = array(), $where = array(), $table = 'Users') {
      if($this->has($table, array('account' => $para['account'], 'user_id[!]' => $where['user_id']))) {
        return -1;
      }

        $this->update($table, $para, $where);
        $err = $this->error;
        if($err == null) {
            return 1;
        } else {
            return 0;
        }
    }

    public function update_user_password($para = array(), $where = array(), $table = 'Users') {
        $this->update($table, $para, $where);
        $err = $this->error;
        if($err == null) {
            return 1;
        } else {
            return 0;
        }
    }

    public function update_user_active($para = array(), $where = array(), $table = 'Users') {
      $this->update($table, $para, $where);
      $err = $this->error;
      if($err == null) {
          return 1;
      } else {
          return 0;
      }
  }
}
