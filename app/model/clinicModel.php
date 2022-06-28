<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class clinicModel extends model {
    public function get_clinic($where = array(), $para = '*', $table = 'Clinic') {
        // $where['hidden'] = '0';
        $where['ORDER'] = array('clinic_id' => 'DESC');
        return $this->select($table, $para, $where);
    }

    public function insert_clinic($para = array(), $table = 'Clinic') {
        if($this->has($table, array('account' => $para['account']))) {
            return -1;
        } else {
            return $this->insert($table,$para);
        }
    }

    public function update_all_clinic($para = array(), $where = array(), $table = 'Clinic') {
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

    public function update_clinic($para = array(), $where = array(), $table = 'Clinic') {
        $this->update($table, $para, $where);
        $err = $this->error;
        if($err == null) {
            return 1;
        } else {
            return 0;
        }
    }
}
