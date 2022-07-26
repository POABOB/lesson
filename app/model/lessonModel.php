<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class lessonModel extends model {
    public function get_lesson($where = array(), $para = array(), $table = 'Lessons') {
      $p = ($where['page'] - 1) * $where['pageNums'];
      $data['request'] = $this->query("
        SELECT  l.lesson_id AS lesson_id, l.customer_id AS customer_id,
                l.customer_name AS customer_name, l.lesson_sn AS lesson_sn,
                l.lesson_name AS lesson_name, l.lesson_nums AS lesson_nums,
                l.lesson_type AS lesson_type, l.lesson_note AS lesson_note,
                l.lesson_each_price AS lesson_each_price, l.lesson_price AS lesson_price,
                l.request_datetime AS request_datetime, l.expired_datetime AS expired_datetime,
                l.request_clinic_id AS request_clinic_id, c1.name AS request_clinic_name,
                l.response_clinic_id AS response_clinic_id, c2.name AS response_clinic_name,
                l.request_parent_id AS request_parent_id, c3.name AS request_parent_name,
                l.status AS status, l.download_status AS download_status
        FROM    Lessons l
        LEFT JOIN Clinic c1
          ON c1.clinic_id = l.request_clinic_id
        LEFT JOIN Clinic c2
          ON c2.clinic_id = l.response_clinic_id
        LEFT JOIN Clinic c3
          ON c3.clinic_id = l.request_parent_id
        WHERE   l.request_clinic_id = {$where['clinic_id']}
        ORDER BY l.lesson_id DESC
        LIMIT {$p}, {$where['pageNums']};
      ")->fetchAll(\PDO::FETCH_ASSOC);
      $data['response'] = $this->query("
        SELECT  l.lesson_id AS lesson_id, l.customer_id AS customer_id,
                l.customer_name AS customer_name, l.lesson_sn AS lesson_sn,
                l.lesson_name AS lesson_name, l.lesson_nums AS lesson_nums,
                l.lesson_type AS lesson_type, l.lesson_note AS lesson_note,
                l.lesson_each_price AS lesson_each_price, l.lesson_price AS lesson_price,
                l.request_datetime AS request_datetime, l.expired_datetime AS expired_datetime,
                l.request_clinic_id AS request_clinic_id, c1.name AS request_clinic_name,
                l.response_clinic_id AS response_clinic_id, c2.name AS response_clinic_name,
                l.request_parent_id AS request_parent_id, c3.name AS request_parent_name,
                l.status AS status, l.download_status AS download_status
        FROM    Lessons l
        LEFT JOIN Clinic c1
          ON c1.clinic_id = l.request_clinic_id
        LEFT JOIN Clinic c2
          ON c2.clinic_id = l.response_clinic_id
        LEFT JOIN Clinic c3
          ON c3.clinic_id = l.request_parent_id
        WHERE   l.response_clinic_id = {$where['clinic_id']}
        ORDER BY l.lesson_id DESC
        LIMIT {$p}, {$where['pageNums']};
      ")->fetchAll(\PDO::FETCH_ASSOC);
      $data['response_list'] = $this->select('Clinic', '*', 
        array(
          "OR" => array(
            'clinic_id' => $where['parent_id'],
            'parent_id' => $where['parent_id'],
          ),
          
        )
      );
      return $data;
    }

    public function insert_lesson($para = array(), $log = array(), $table = 'Lessons') {
      try{
        // 判斷插入ID是否為集合內ID
        $clinic = $this->get('Clinic', '*', array('clinic_id' => $para['response_clinic_id']));
        if($clinic == null) {
          return -2;
        } else if(
          intval($clinic['parent_id']) !== intval($log['parent_id']) &&
          intval($clinic['clinic_id']) !== intval($log['parent_id'])
        ) {
          return -1;
        }
        
        $this->pdo->beginTransaction();
        // LESSON
        $this->insert($table,$para);
        $lesson_id = $this->id();
        
        // LOG
        $this->insert('Logs', array(
          'user_id' => $log['user_id'],
          'name' => $log['name'],
          'lesson_id' => $lesson_id,
          'verb' => '申請',
          'clinic_id' => $log['clinic_id'],
        ));

        $this->pdo->commit(); 
        return 0;
      }catch(Exception $e){
        // ACID回滾
        $this->pdo->rollBack();
        return -1;
      }
    }

    public function cancel_lesson($para = array(), $where = array(), $log = array(), $table = 'Lessons') {
      try{
        if(
          $this->has($table, 
            array(
              'status' => array('已核准', '已拒絕'),
              'lesson_id' => $where['lesson_id'], 
            )
          )
        ) {
          return -3;
        } else if(!$this->has($table, $where)) {
          return -2;
        }
        $this->pdo->beginTransaction();

        // LESSON
        $this->update($table, $para, $where);

        // LOG
        $this->insert('Logs', array(
          'user_id' => $log['user_id'],
          'name' => $log['name'],
          'lesson_id' => $where['lesson_id'],
          'verb' => '取消',
          'clinic_id' => $log['clinic_id'],
        ));

        $err = $this->error;
        if($err == null) {
          $this->pdo->commit(); 
          return 0;
        } else {
          $this->pdo->rollBack();
          return -1;
        }
      }catch(Exception $e){
        // ACID回滾
        $this->pdo->rollBack();
        return -1;
      }
    }

    public function approve_lesson($para = array(), $where = array(), $log = array(), $table = 'Lessons') {
      try{
        $lesson = $this->get($table, '*', array('lesson_id' => $where['lesson_id']));
        if($lesson == null) {
          // 查無lesson
          return -5;
        } else if (strtotime($lesson['expired_datetime']) < strtotime(date('Y-m-d H:i:s'))) {
          // 申請已過期
          return -4;
        } else if ($lesson['response_clinic_id'] !== $log['clinic_id']) {
          // 未被指定
          return -3;
        } else if($lesson['status'] == '已取消') {
          return -2;
        }
        $this->pdo->beginTransaction();

        // LESSON
        $this->update($table, $para, $where);

        // LOG
        $this->insert('Logs', array(
          'user_id' => $log['user_id'],
          'name' => $log['name'],
          'lesson_id' => $where['lesson_id'],
          'verb' => '核准',
          'clinic_id' => $log['clinic_id'],
        ));

        $err = $this->error;
        if($err == null) {
          $this->pdo->commit(); 
          return 0;
        } else {
          $this->pdo->rollBack();
          return -1;
        }
      }catch(Exception $e){
        // ACID回滾
        $this->pdo->rollBack();
        return -1;
      }
    }

    public function reject_lesson($para = array(), $where = array(), $log = array(), $table = 'Lessons') {
      try{
        $lesson = $this->get($table, '*', array('lesson_id' => $where['lesson_id']));
        if($lesson == null) {
          // 查無lesson
          return -5;
        } else if (strtotime($lesson['expired_datetime']) < strtotime(date('Y-m-d H:i:s'))) {
          // 申請已過期
          return -4;
        } else if ($lesson['response_clinic_id'] !== $log['clinic_id']) {
          // 未被指定
          return -3;
        } else if($lesson['status'] == '已取消') {
          return -2;
        }
        $this->pdo->beginTransaction();

        // LESSON
        $this->update($table, $para, $where);

        // LOG
        $this->insert('Logs', array(
          'user_id' => $log['user_id'],
          'name' => $log['name'],
          'lesson_id' => $where['lesson_id'],
          'verb' => '拒絕',
          'clinic_id' => $log['clinic_id'],
        ));

        $err = $this->error;
        if($err == null) {
          $this->pdo->commit(); 
          return 0;
        } else {
          $this->pdo->rollBack();
          return -1;
        }
      }catch(Exception $e){
        // ACID回滾
        $this->pdo->rollBack();
        return -1;
      }
    }
}
