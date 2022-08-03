<?php
namespace app\controller;
if ( ! defined('PPP')) exit('非法入口');
use app\model\lessonModel;
use core\lib\resModel;
use core\lib\Validator;
use core\common\auth;
use core\lib\JWT;

class lessonController extends \core\PPP {
    /**
     * @OA\Get(
     *      path="/api/lessons", 
     *      tags={"診所扣課管理"},
     *      summary="獲取診所所有扣課紀錄",
     *      security={{"Authorization":{}}}, 
     *      @OA\Parameter(
     *          name="page",
     *          description="分頁",
     *          in = "query",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *          example="1"
     *      ),
     *      @OA\Parameter(
     *          name="pageNums",
     *          description="每頁資料量",
     *          in = "query",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *          example="50"
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="獲取診所扣課紀錄",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", example="null"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="request", type="array",
     *                    @OA\Items(type="object",
     *                      @OA\Property(property="lesson_id", type="int(11)", example="1"),
     *                      @OA\Property(property="customer_info", type="string(64)", example="顧客姓名或手機"),
     *                      @OA\Property(property="lesson_info", type="string(2000)", example="課程JSON"),
     *                      @OA\Property(property="request_clinic_id", type="int(11)", example="2"),
     *                      @OA\Property(property="request_clinic_name", type="string(64)", example="診所A"),
     *                      @OA\Property(property="request_parent_id", type="int(11)", example="1"),
     *                      @OA\Property(property="request_parent_name", type="string(64)", example="XX診所"),
     *                      @OA\Property(property="response_clinic_id", type="int(11)", example="1"),
     *                      @OA\Property(property="response_clinic_name", type="string(64)", example="XX診所"),
     *                      @OA\Property(property="request_datetime", type="string(20)", example="2022-08-06 12:42:08"),
     *                      @OA\Property(property="expired_datetime", type="string(20)", example="2022-08-07 23:59:59"),
     *                      @OA\Property(property="status", type="string(20)", example="待核准/已取消/已核准/已拒絕/已過期"),
     *                      @OA\Property(property="download_status", type="string(20)", example="/可下載/已下載"),
     *                    ), 
     *                  ),
     *                  @OA\Property(property="response", type="array",
     *                    @OA\Items(type="object",
     *                      @OA\Property(property="lesson_id", type="int(11)", example="1"),
     *                      @OA\Property(property="customer_info", type="string(64)", example="顧客姓名或手機"),
     *                      @OA\Property(property="lesson_info", type="string(2000)", example="課程JSON"),
     *                      @OA\Property(property="lesson_price", type="int(11)", example="9999"),
     *                      @OA\Property(property="request_clinic_id", type="int(11)", example="2"),
     *                      @OA\Property(property="request_clinic_name", type="string(64)", example="診所A"),
     *                      @OA\Property(property="request_parent_id", type="int(11)", example="1"),
     *                      @OA\Property(property="request_parent_name", type="string(64)", example="XX診所"),
     *                      @OA\Property(property="response_clinic_id", type="int(11)", example="1"),
     *                      @OA\Property(property="response_clinic_name", type="string(64)", example="XX診所"),
     *                      @OA\Property(property="request_datetime", type="string(20)", example="2022-08-06 12:42:08"),
     *                      @OA\Property(property="expired_datetime", type="string(20)", example="2022-08-07 23:59:59"),
     *                      @OA\Property(property="status", type="string(20)", example="待核准/已取消/已核准/已拒絕/已過期"),
     *                      @OA\Property(property="download_status", type="string(20)", example="/可下載/已下載"),
     *                    ),
     *                  ),
     *                  @OA\Property(property="response_list", type="array",
     *                      @OA\Items(type="object",
     *                        @OA\Property(property="clinic_id", type="int(11)", example="1"),
     *                        @OA\Property(property="clinic_sn", type="string(10)", example="xxxxx"),
     *                        @OA\Property(property="name", type="string(64)", example="顧客A"),
     *                        @OA\Property(property="active", type="string(1)", example="1"),
     *                        @OA\Property(property="parent_id", type="int(11)", example="2"),
     *                      )
     *                  ), 
     *                  @OA\Property(property="request_total_page", type="int(11)", example="5"), 
     *                  @OA\Property(property="response_total_page", type="int(11)", example="10"), 
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response="401", description="提交格式有誤"),
     *      @OA\Response(response="403", description="ADMIN不能操作"),
     * )
     */
    public function index_() {
        auth::factory()->no_admin();
        $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
        $pageNums = (isset($_GET['pageNums']) && is_numeric($_GET['pageNums'])) ? $_GET['pageNums'] : 50;
        $payload = JWT::verifyToken(JWT::getHeaders());
        $database = new lessonModel();
        $data = $database->get_lesson(
          array(
            'clinic_id' => $payload['clinic_id'],
            'parent_id' => $payload['parent_id'],
            'page' => $page,
            'pageNums' => $pageNums,
          )
        );

        $count = $database->count_lesson(
          array(
            'clinic_id' => $payload['clinic_id'],
            'parent_id' => $payload['parent_id'],
            'page' => $page,
            'pageNums' => $pageNums,
          )
        );

        foreach ($data['request'] as $key => $value) {
          if(strtotime($data['request'][$key]['expired_datetime']) < strtotime(date('Y-m-d H:i:s')) && $data['request'][$key]['status'] == '已核准') {
            $data['request'][$key]['status'] = '已過期';
          }
        }

        foreach ($data['response'] as $key => $value) {
          if(strtotime($data['response'][$key]['expired_datetime']) < strtotime(date('Y-m-d H:i:s')) && $data['response'][$key]['status'] == '已核准') {
            $data['response'][$key]['status'] = '已過期';
          }
        }

        $data['response_list'] = array_filter($data['response_list'], function($val) use ($payload) {
          return intval($val['clinic_id']) !== intval($payload['clinic_id']);
        });

        $data['request_total_page'] = floor($count['request_total_page'] / $pageNums + 1);
        $data['response_total_page'] = floor($count['response_total_page'] / $pageNums + 1);

        json(new resModel(200, $data));
    }

    /**
     * @OA\Post(
     *     path="/api/lessons/request", 
     *     tags={"診所扣課管理"},
     *     summary="診所新增扣課請求",
     *     security={{"Authorization":{}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"customer_info", "lesson_info", "response_clinic_id"},
     *                  @OA\Property(property="customer_info", type="string(64)", example="顧客姓名或手機"),
     *                  @OA\Property(property="lesson_info", type="string(2000)", example="課程JSON"),
     *                  @OA\Property(property="response_clinic_id", type="string(20)", example="3"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="新增成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="新增成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="請求診所ID不存在 | 請求診所ID在不同集合內"),
     *      @OA\Response(response="401", description="提交格式有誤"),
     *      @OA\Response(response="403", description="ADMIN不能操作"),
     * )
     */
    public function insert_() {
        auth::factory()->no_admin();
        $payload = JWT::verifyToken(JWT::getHeaders());
        $post = array();
        $post = post_json();
        //Validation
        $v = new Validator();
        $v->validate(
            array(
                '顧客資訊' => $post['customer_info'],
                '申請診所ID' => $post['response_clinic_id'],
                '課程資訊' => $post['lesson_info'],
            ),
            array(
                '顧客資訊' => array('required', 'maxLen' => 64),
                '申請診所ID' => array('required', 'maxLen' => 11),
                '課程資訊' => array('required', 'maxLen' => 2000),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new lessonModel();
        $data = $database->insert_lesson(
            array(
              'customer_info' => $post['customer_info'],
              'lesson_info' => $post['lesson_info'],
              'request_datetime' => date('Y-m-d H:i:s'),
              'expired_datetime' => "9999-12-31 00:00:00",
              'request_clinic_id' => $payload['clinic_id'],
              'response_clinic_id' => $post['response_clinic_id'],
              'request_parent_id' => $payload['parent_id'],
              'status' => '待核准',
            ),
            array(
              'user_id' => $payload['user_id'],
              'name' => $payload['name'],
              'clinic_id' => $payload['clinic_id'],
              'parent_id' => $payload['parent_id'],
            )
        );

        
        if($data == -2) {
            json(new resModel(400, '申請診所ID不存在'));
        } else if($data == -1) {
            json(new resModel(400, '申請診所ID在不同集合內'));
        } else {
            json(new resModel(200, '新增成功'));
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/lessons/request", 
     *     tags={"診所扣課管理"},
     *     summary="診所取消扣課請求",
     *     security={{"Authorization":{}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"lesson_id"},
     *                  @OA\Property(property="lesson_id", type="int(11)", example="1"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="取消成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="取消成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="只能取消自己提出的扣課請求"),
     *      @OA\Response(response="401", description="提交格式有誤"),
     *      @OA\Response(response="403", description="ADMIN不能操作"),
     * )
     */
    public function cancel_() {
        auth::factory()->no_admin();
        $payload = JWT::verifyToken(JWT::getHeaders());
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('課程ID' => $post['lesson_id']),
            array('課程ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new lessonModel();
        $return = $database->cancel_lesson(
            array('status' => '已取消'),
            array(
              'lesson_id' => $post['lesson_id'],
              'request_clinic_id' => $payload['clinic_id'],
            ),
            array(
              'user_id' => $payload['user_id'],
              'name' => $payload['name'],
              'clinic_id' => $payload['clinic_id'],
            )
        );

        if($return == -3) {
          json(new resModel(400, '該請求已被核准/拒絕'));
        } else if($return == -2) {
          json(new resModel(400, '只能取消自己提出的扣課請求'));
        } else if($return == -1) {
          json(new resModel(400, $database->error));
      } else {
          json(new resModel(200, '取消成功'));
        }
    }


    /**
     * @OA\Patch(
     *     path="/api/lessons/response", 
     *     tags={"診所扣課管理"},
     *     summary="診所核准扣課申請",
     *     security={{"Authorization":{}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"lesson_id"},
     *                  @OA\Property(property="lesson_id", type="int(11)", example="2"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="核准成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="核准成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="查無扣課請求 | 扣課請求已過期 | 扣課請求尚未被指定 | 扣課請求已取消"),
     *      @OA\Response(response="401", description="提交格式有誤"),
     *      @OA\Response(response="403", description="ADMIN不能操作"),
     * )
     */
    public function approve_() {
        auth::factory()->no_admin();
        $payload = JWT::verifyToken(JWT::getHeaders());
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('課程ID' => $post['lesson_id']),
            array('課程ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new lessonModel();
        $return = $database->approve_lesson(
            array(
              'status' => '已核准',
              'expired_datetime' => date('Y-m-d H:i:s', strtotime('4 hour')),
            ),
            array('lesson_id' => $post['lesson_id']),
            array(
              'user_id' => $payload['user_id'],
              'name' => $payload['name'],
              'clinic_id' => $payload['clinic_id'],
            )
        );

      if($return == -5) {
        json(new resModel(400, '查無扣課請求'));
      } else if($return == -4) {
          json(new resModel(400, '扣課請求已過期'));
      } else if($return == -3) {
          json(new resModel(400, '扣課請求尚未被指定'));
      } else if($return == -2) {
          json(new resModel(400, '扣課請求已取消'));
      } else if($return == -1) {
          json(new resModel(400, $database->error));
      } else {
          json(new resModel(200, '核准成功'));
      }
    }

    /**
     * @OA\Delete(
     *     path="/api/lessons/response", 
     *     tags={"診所扣課管理"},
     *     summary="診所拒絕扣課申請",
     *     security={{"Authorization":{}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"lesson_id"},
     *                  @OA\Property(property="lesson_id", type="int(11)", example="2"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="拒絕成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="拒絕成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="查無扣課請求 | 扣課請求已過期 | 扣課請求尚未被指定 | 扣課請求已取消"),
     *      @OA\Response(response="401", description="提交格式有誤"),
     *      @OA\Response(response="403", description="ADMIN不能操作"),
     * )
     */
    public function reject_() {
        auth::factory()->no_admin();
        $payload = JWT::verifyToken(JWT::getHeaders());
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('課程ID' => $post['lesson_id']),
            array('課程ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new lessonModel();
        $return = $database->reject_lesson(
            array('status' => '已拒絕'),
            array('lesson_id' => $post['lesson_id']),
            array(
              'user_id' => $payload['user_id'],
              'name' => $payload['name'],
              'clinic_id' => $payload['clinic_id'],
            )
        );



        if($return == -5) {
          json(new resModel(400, '查無扣課請求'));
        } else if($return == -4) {
            json(new resModel(400, '扣課請求已過期'));
        } else if($return == -3) {
            json(new resModel(400, '扣課請求尚未被指定'));
        } else if($return == -2) {
            json(new resModel(400, '扣課請求已取消'));
        } else if($return == -1) {
            json(new resModel(400, $database->error));
        } else {
            json(new resModel(200, '拒絕成功'));
        }
    }
}