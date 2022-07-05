<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class clinicModel extends model {
    public function get_clinic($where = array(), $para = '*', $table = 'Clinic') {
        // return $this->select($table, $para, $where);
        return $this->query("
            SELECT  c.clinic_id AS clinic_id, c.name AS name, 
                    c.active AS active, c.parent_id AS parent_id,
                    COUNT(l.lesson_id) AS lesson_count
            FROM    Clinic c
            LEFT JOIN Lessons l
            ON c.clinic_id = l.request_clinic_id AND c.clinic_id > {$where['clinic_id']}
            GROUP BY (c.clinic_id)
            LIMIT {$where['LIMIT']};
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function insert_clinic($para = array(), $table = 'Clinic') {
        // 判斷是否有下級
        if(intval($para['parent_id']) !== 0) {
            $parent = $this->get($table, '*', array('clinic_id' => $para['parent_id']));
            if($parent == null) {
                return -2;   // 當他指定不存在的clinic_id
            } else if($parent['parent_id'] !== 0) {
                return -1;   // 當他指定的上級診所已有上級
            }
        }

        $this->insert($table,$para);
        return  0;
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
        $self = $this->get($table, '*', array('clinic_id' => $where['clinic_id']));
        if(intval($self['parent_id']) !== intval($para['parent_id'])) {
            if(intval($para['parent_id']) !== 0) {
                $parent = $this->get($table, '*', array('clinic_id' => $para['parent_id']));
                if($parent == null) {
                    return -4;   // 當他指定不存在的clinic_id
                } else if(intval($parent['parent_id']) !== 0) {
                    return -3;   // 當他指定的上級診所已有上級
                }
            }

            if($this->has($table, array('parent_id' => $where['clinic_id']))) {
                return -2;  //診所有下級
            }

            if($this->has('Lessons', array('request_clinic_id' => $where['clinic_id']))) {
                return -1;  // 診所有課程
            }
        }

        $this->update($table, $para, $where);
        $err = $this->error;
        if($err == null) {
            return 1;
        } else {
            return 0;
        }
    }
}
