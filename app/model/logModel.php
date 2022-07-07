<?php
namespace app\model;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\model;
class logModel extends model {
    public function get_log($where = array(), $para = array(), $table = 'Logs') {
      $p = ($where['page'] - 1) * $where['pageNums'];
      return $this->query("
        SELECT  lo.log_id AS log_id, lo.user_id as user_id,
                lo.name AS name, lo.verb as verb,
                lo.lesson_id AS lesson_id, l.customer_id AS customer_id,
                l.customer_name AS customer_name, l.lesson_sn AS lesson_sn,
                l.lesson_name AS lesson_name, l.lesson_nums AS lesson_nums,
                l.request_datetime AS request_datetime, l.expired_datetime AS expired_datetime,
                l.request_clinic_id AS request_clinic_id, c1.name AS request_clinic_name,
                l.response_clinic_id AS response_clinic_id, c2.name AS response_clinic_name,
                l.status AS status
        FROM Logs lo
        LEFT JOIN Lessons l
          ON l.lesson_id = lo.lesson_id
        LEFT JOIN Clinic c1
          ON c1.clinic_id = l.request_clinic_id
        LEFT JOIN Clinic c2
          ON c2.clinic_id = l.response_clinic_id
        WHERE   lo.clinic_id = {$where['clinic_id']}
        ORDER BY lo.log_id DESC
        LIMIT {$p}, {$where['pageNums']};
      ")->fetchAll(\PDO::FETCH_ASSOC);
    }
}
