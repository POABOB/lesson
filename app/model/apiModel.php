<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class apiModel extends model {
    //登入
    public function login( $where = array(), $para = array(), $table = 'Users') {
        if($this->has($table, $where)) {
            $this->update($table, array('last_login' => date('Y-m-d H:i:s')), $where);
            return true;
        }
        return false;
    }

    //更新密碼
    public function update_password( $where = array(), $para = '*', $table = 'Users') {
        $data = $this->get($table, $para, array('account' => $where[0], 'password' => $where[1]));
        if($data !== null) {
            $this->update($table, array('password' => $where[2]), array('account' => $where[0], 'password' => $where[1]));
            $err = $this->error;
            if($err == null) {
                return 2;
            } else {
                return 1;
            }
        } else {
            return 0;
        }
    }

    //index
    public function index($where = 1, $para = '*', $table = 'Home') {
        return $this->select($table, $para, $where);
    }
    
    public function insertOrUpdate_index($para = array(), $where = array(), $table = 'Home') {
        if(count($where) == 0) {
            //獲取orders最大值
            $data = $this->max($table, 'orders', 1);
            $data = intval($data) + 1;
            $para['orders'] = $data;
            return $this->insert($table,$para);
        } else {
            if($this->has($table, $where)){
                $this->update($table, $para, $where);
                $err = $this->error;
                if($err == null) {
                    return 2;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }

    public function delete_index($where = array(), $table = 'Home') {
        return $this->delete($table, $where);
    }

    //projects_type
    public function projects_type($where = 1, $para = '*', $table = 'Projects_type') {
        return $this->select($table, $para, $where);
    }
    
    public function insertOrUpdate_projects_type($para = array(), $where = array(), $table = 'Projects_type') {
        if(count($where) == 0) {
            //獲取orders最大值
            $data = $this->max($table, 'orders', 1);
            $data = intval($data) + 1;
            $para['orders'] = $data;
            return $this->insert($table,$para);
        } else {
            if($this->has($table, $where)){
                $this->update($table, $para, $where);
                $err = $this->error;
                if($err == null) {
                    return 2;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }

    public function delete_projects_type($where = array(), $table = 'Projects_type') {
        return $this->delete($table, $where);
    }

    //projects_detail
    public function projects_detail($where = 1, $para = '*', $table = 'Projects_detail') {
        return $this->select($table, $para, $where);
    }
    
    public function insertOrUpdate_projects_detail($para = array(), $where = array(), $table = 'Projects_detail') {
        if(count($where) == 0) {
            //獲取orders最大值
            $data = $this->max($table, 'orders', 1);
            $data = intval($data) + 1;
            $para['orders'] = $data;
            return $this->insert($table,$para);
        } else {
            if($this->has($table, $where)){
                $this->update($table, $para, $where);
                $err = $this->error;
                if($err == null) {
                    return 2;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }

    public function delete_projects_detail($where = array(), $table = 'Projects_detail') {
        return $this->delete($table, $where);
    }

    //aboutus_intro
    public function aboutus_intro($where = array('id' => 1), $para = '*', $table = 'About') {
        return $this->get($table, $para, $where);
    }

    public function insertOrUpdate_aboutus_intro($para = array(), $where = array(), $table = 'About') {
        if(count($where) == 0) {
            return $this->insert($table,$para);
        } else {
            if($this->has($table, $where)){
                $this->update($table, $para, $where);
                $err = $this->error;
                if($err == null) {
                    return 2;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }

        //aboutus_member
        public function aboutus_member($where = 1, $para = '*', $table = 'Team') {
            return $this->select($table, $para, $where);
        }
        
        public function insertOrUpdate_aboutus_member($para = array(), $where = array(), $table = 'Team') {
            if(count($where) == 0) {
                //獲取orders最大值
                $data = $this->max($table, 'orders', 1);
                $data = intval($data) + 1;
                $para['orders'] = $data;
                return $this->insert($table,$para);
            } else {
                if($this->has($table, $where)){
                    $this->update($table, $para, $where);
                    $err = $this->error;
                    if($err == null) {
                        return 2;
                    } else {
                        return 1;
                    }
                } else {
                    return 0;
                }
            }
        }
    
        public function delete_aboutus_member($where = array(), $table = 'Team') {
            return $this->delete($table, $where);
        }

    //service
    public function service($where = 1, $para = '*', $table = 'Service') {
        return $this->select($table, $para, $where);
    }
    
    public function insertOrUpdate_service($para = array(), $where = array(), $table = 'Service') {
        if(count($where) == 0) {
            //獲取orders最大值
            $data = $this->max($table, 'orders', 1);
            $data = intval($data) + 1;
            $para['orders'] = $data;
            return $this->insert($table,$para);
        } else {
            if($this->has($table, $where)){
                $this->update($table, $para, $where);
                $err = $this->error;
                if($err == null) {
                    return 2;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }

    public function delete_service($where = array(), $table = 'Service') {
        return $this->delete($table, $where);
    }

    //contactus
    public function contactus($where = 1, $para = '*', $table = 'Contact') {
        return $this->select($table, $para, $where);
    }
    
    public function insertOrUpdate_contactus($para = array(), $where = array(), $table = 'Contact') {
        if(count($where) == 0) {
            //獲取orders最大值
            $data = $this->max($table, 'orders', 1);
            $data = intval($data) + 1;
            $para['orders'] = $data;
            return $this->insert($table,$para);
        } else {
            if($this->has($table, $where)){
                $this->update($table, $para, $where);
                $err = $this->error;
                if($err == null) {
                    return 2;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        }
    }

    public function delete_contactus($where = array(), $table = 'Contact') {
        return $this->delete($table, $where);
    }
}
