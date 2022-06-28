<?php
namespace app\controller;
if ( ! defined('PPP')) exit('非法入口');
use app\model\clinicModel;
use core\lib\resModel;
use core\lib\Validator;

class adminController extends \core\PPP {

    /**
     * @OA\Get(
     *     path="/api/admin/clinic", 
     *     tags={"後台診所管理"},
     *     summary="後台獲取診所",
     *     security={{"Authorization":{}}}, 
     *      @OA\Response(
     *          response="200", 
     *          description="獲取診所",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", example="null"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="judger_id", type="int(11)", example="1"),
     *                      @OA\Property(property="name", type="string(128)", example="王診所"),
     *                      @OA\Property(property="ID", type="string(10)", example="A12345678"),
     *                      @OA\Property(property="password", type="string(64)", example="password"),
     *                      @OA\Property(property="phone", type="string(15)", example="0912345678"),
     *                      @OA\Property(property="right", type="string(1)", example="1"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="獲取失敗")
     * )
     */
    public function index_() {
        $database = new clinicModel();
        $data = $database->get_clinic(array('roles[!]' => "999"));
        if($data !== null || count($data) !== 0) {
            foreach ($data as $key => $value) {
                $data[$key]['allowed_ip'] = json_decode($data[$key]['allowed_ip']);
            }
        }
        json(new resModel(200, $data));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/clinic", 
     *     tags={"後台診所管理"},
     *     summary="後台新增診所",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"name", "account", "password", "allowed_ip", "roles"},
     *                  @OA\Property(property="name", type="string(64)", example="診所A"),
     *                  @OA\Property(property="account", type="string(128)", example="A12345678"),
     *                  @OA\Property(property="password", type="string(256)", example="password"),
     *                  @OA\Property(property="roles", type="string(3)", example="3"),
     *                  @OA\Property(property="allowed_ip", type="array",
     *                      @OA\Items(type="string", example="*.*.*.*")
     *                  )
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
     *      @OA\Response(response="400", description="帳號已被使用..."),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function insert_() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array(
                '診所' => $post['name'],
                '帳號' => $post['account'],
                '密碼' => $post['password'],
                '權限' => $post['roles']
            ),
            array(
                '診所' => array('required', 'maxLen' => 64),
                '帳號' => array('required', 'maxLen' => 128),
                '密碼' => array('required', 'maxLen' => 256),
                '權限' => array('required', 'maxLen' => 3),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        // IP驗證
        if($post['allowed_ip'] == null || count($post['allowed_ip']) == 0) {
            json(new resModel(401, $v->error(), 'ip提交格式有誤'));
            return;
        }
        foreach ($post['allowed_ip'] as $key => $value) {
            if(gettype($post['allowed_ip'][$key]) !== 'string') {
                json(new resModel(401, $v->error(), 'ip提交格式有誤'));
                return;
            }
        }

        $database = new clinicModel();
        $data = $database->insert_clinic(
            array(
                'name' => $post['name'],
                'account' => $post['account'],
                'allowed_ip' => (($post['allowed_ip'] == null) ? json_encode(["*.*.*.*"]) : json_encode($post['allowed_ip'])),
                'password' => md5($post['password']),
                'roles' => (string)$post['roles']
            )
        );

        if($data !== -1) {
            json(new resModel(200, '新增成功'));
        } else {
            json(new resModel(400, '帳號已被使用...'));
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/clinic", 
     *     tags={"後台診所管理"},
     *     summary="後台更新診所",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"clinic_id", "name", "account", "allowed_ip", "roles", "active"},
     *                  @OA\Property(property="clinic_id", type="int(11)", example="2"),
     *                  @OA\Property(property="name", type="string(64)", example="診所A"),
     *                  @OA\Property(property="account", type="string(128)", example="A12345678"),
     *                  @OA\Property(property="roles", type="string(3)", example="3"),
     *                  @OA\Property(property="active", type="string(1)", example="1"),
     *                  @OA\Property(property="allowed_ip", type="array",
     *                      @OA\Items(type="string", example="*.*.*.*")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="更新成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="更新成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="更新失敗/account帳號重複"),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function update_() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array(
                '診所ID' => $post['clinic_id'],
                '診所' => $post['name'],
                '帳號' => $post['account'],
                '權限' => $post['roles'],
                '狀態' => $post['active'],
            ),
            array(
                '診所ID' => array('required', 'maxLen' => 11),
                '診所' => array('required', 'maxLen' => 64),
                '帳號' => array('required', 'maxLen' => 128),
                '權限' => array('required', 'maxLen' => 3),
                '狀態' => array('required', 'maxLen' => 1)
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        // IP驗證
        if($post['allowed_ip'] == null || count($post['allowed_ip']) == 0) {
            json(new resModel(401, $v->error(), 'ip提交格式有誤'));
            return;
        }
        foreach ($post['allowed_ip'] as $key => $value) {
            if(gettype($post['allowed_ip'][$key]) !== 'string') {
                json(new resModel(401, $v->error(), 'ip提交格式有誤'));
                return;
            }
        }


        $database = new clinicModel();
        $return = $database->update_all_clinic(
            array(
                'name' => $post['name'],
                'account' => $post['account'],
                'allowed_ip' => (($post['allowed_ip'] == null) ? json_encode(["*.*.*.*"]) : json_encode($post['allowed_ip'])),
                'roles' => (string)$post['roles'],
                'active' => (string)$post['active'],
            ),
            array('clinic_id' => $post['clinic_id'])
        );

        if($return == 1) {
            json(new resModel(200, '更新成功'));
        } else if($return == -1) {
            json(new resModel(400, '帳號已被使用...'));
        } else if($return == 0) {
            json(new resModel(400, $database->error));
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/admin/clinic", 
     *     tags={"後台診所管理"},
     *     summary="後台停用診所",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"clinic_id"},
     *                  @OA\Property(property="clinic_id", type="int(11)", example="2"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="停用成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="停用成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="停用失敗"),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function delete_() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('診所ID' => $post['clinic_id']),
            array('診所ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new clinicModel();
        $return = $database->update_clinic(
            array('active' => '0'),
            array('clinic_id' => $post['clinic_id'])
        );


        if($return == 1) {
            json(new resModel(200, '停用成功'));
        } else if($return == 0) {
            json(new resModel(400, '停用失敗'));
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/clinic/back", 
     *     tags={"後台診所管理"},
     *     summary="後台啟用診所",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"clinic_id"},
     *                  @OA\Property(property="clinic_id", type="int(11)", example="2"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="啟用成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="啟用成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="啟用失敗"),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function delete_back() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('診所ID' => $post['clinic_id']),
            array('診所ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new clinicModel();
        $return = $database->update_clinic(
            array('active' => '1'),
            array('clinic_id' => $post['clinic_id'])
        );


        if($return == 1) {
            json(new resModel(200, '啟用成功'));
        } else if($return == 0) {
            json(new resModel(400, '啟用失敗'));
        }
    }

        /**
     * @OA\Patch(
     *     path="/api/admin/clinic/password", 
     *     tags={"後台診所管理"},
     *     summary="後台更新診所密碼",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"clinic_id", "password"},
     *                  @OA\Property(property="clinic_id", type="int(11)", example="2"),
     *                  @OA\Property(property="password", type="string(256)", example="123456"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="更新成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="更新成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="更新失敗"),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function update_password() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('診所ID' => $post['clinic_id'], '密碼' => $post['password']),
            array('診所ID' => array('required', 'maxLen' => 11), '密碼' => array('required', 'maxLen' => 256))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new clinicModel();
        $return = $database->update_clinic(
            array('password' => md5($post['password'])),
            array('clinic_id' => $post['clinic_id'])
        );


        if($return == 1) {
            json(new resModel(200, '更新成功'));
        } else if($return == 0) {
            json(new resModel(400, '更新失敗'));
        }
    }
}