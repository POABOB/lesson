<?php
namespace app\controller;
if ( ! defined('PPP')) exit('非法入口');
use app\model\userModel;
use core\lib\resModel;
use core\lib\Validator;
use core\common\auth;
use core\lib\JWT;

class userController extends \core\PPP {


    /**
     * @OA\Get(
     *     path="/api/users/info", 
     *     tags={"診所使用者管理"},
     *     summary="獲取使用者登入資訊",
     *     security={{"Authorization":{}}}, 
     *      @OA\Response(
     *          response="200", 
     *          description="獲取使用者登入資訊",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", example="null"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="user_id", type="int(11)", example="1"),
     *                  @OA\Property(property="account", type="string(128)", example="user1"),
     *                  @OA\Property(property="name", type="string(64)", example="USER1"),
     *                  @OA\Property(property="clinic_id", type="int(11)", example="1"),
     *                  @OA\Property(property="clinic_name", type="string(64)", example="診所A"),
     *                  @OA\Property(property="active", type="string(1)", example="1"),
     *                  @OA\Property(property="roles", type="string(3)", example="3"),
     *                  @OA\Property(property="parent_id", type="int(11)", example="0"),
     *                  @OA\Property(property="parent_name", type="string(64)", example=""),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="獲取失敗")
     * )
     */
    public function info_() {
        auth::factory()->user_info('Session 過期，請重新再登入');
    }

    /**
     * @OA\Get(
     *      path="/api/users/{clinic_id}", 
     *      tags={"診所使用者管理"},
     *      summary="獲取診所所有使用者",
     *      security={{"Authorization":{}}}, 
     *      @OA\Parameter(
     *          name="clinic_id",
     *          description="診所ID",
     *          in = "path",
     *          required=true,
     *          example="1",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="獲取診所使用者",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", example="null"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="user_id", type="int(11)", example="1"),
     *                      @OA\Property(property="account", type="string(128)", example="user1"),
     *                      @OA\Property(property="name", type="string(128)", example="使用者A"),
     *                      @OA\Property(property="active", type="string(1)", example="1"),
     *                      @OA\Property(property="roles", type="string(1)", example="3"),
     *                      @OA\Property(property="clinic_id", type="int(11)", example="1"),
     *                  ), 
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="無法操作其他診所"),
     *      @OA\Response(response="401", description="無法獲取診所ID | 提交格式有誤"),
     * )
     */
    public function index_($clinic_id = null) {
        auth::factory()->roles_auth($clinic_id);
        $database = new userModel();
        $data = $database->get_user(
            array(
                'ORDER' => array('clinic_id' => 'DESC'),
                'clinic_id' => $clinic_id
        ));
        json(new resModel(200, $data));
    }

    /**
     * @OA\Post(
     *     path="/api/users/{clinic_id}", 
     *     tags={"診所使用者管理"},
     *     summary="診所新增使用者",
     *     security={{"Authorization":{}}}, 
     *      @OA\Parameter(
     *          name="clinic_id",
     *          description="診所ID",
     *          in = "path",
     *          required=true,
     *          example="1",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"account", "name", "password", "roles"},
     *                  @OA\Property(property="account", type="string(128)", example="user1"),
     *                  @OA\Property(property="name", type="string(64)", example="使用者A"),
     *                  @OA\Property(property="password", type="string(256)", example="password"),
     *                  @OA\Property(property="roles", type="string(1)", example="3"),
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
     *      @OA\Response(response="400", description="無法操作其他診所 | 帳號已被使用.."),
     *      @OA\Response(response="401", description="診所ID不存在 | 無法獲取診所ID | 提交格式有誤"),
     *      @OA\Response(response="403", description="Permission Denied"),
     * )
     */
    public function insert_($clinic_id = null) {
        auth::factory()->roles_auth($clinic_id);
        auth::factory()->auth(3);
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array(
                '使用者' => $post['name'],
                '帳號' => $post['account'],
                '密碼' => $post['password'],
                '權限' => $post['roles']
            ),
            array(
                '使用者' => array('required', 'maxLen' => 64),
                '帳號' => array('required', 'maxLen' => 128),
                '密碼' => array('required', 'maxLen' => 256),
                '權限' => array('required', 'maxLen' => 3),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new userModel();
        $data = $database->insert_user(
            array(
                'name' => $post['name'],
                'account' => $post['account'],
                'password' => md5($post['password']),
                'roles' => (string)$post['roles'],
                'clinic_id' => $clinic_id,
            )
        );

        if($data !== -2) {
            json(new resModel(401, '診所ID不存在'));
        } else if($data !== -1) {
            json(new resModel(400, '帳號已被使用...'));
        } else {
            json(new resModel(200, '新增成功'));
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{clinic_id}", 
     *     tags={"診所使用者管理"},
     *     summary="診所更新使用者",
     *     security={{"Authorization":{}}}, 
     *      @OA\Parameter(
     *          name="clinic_id",
     *          description="診所ID",
     *          in = "path",
     *          required=true,
     *          example="1",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"account", "name", "user_id", "roles"},
     *                  @OA\Property(property="user_id", type="int(11)", example="1"),
     *                  @OA\Property(property="account", type="string(128)", example="user1"),
     *                  @OA\Property(property="name", type="string(64)", example="使用者A"),
     *                  @OA\Property(property="roles", type="string(1)", example="3"),
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
     *      @OA\Response(response="400", description="無法操作其他診所 | 帳號已被使用..."),
     *      @OA\Response(response="401", description="無法獲取診所ID | 提交格式有誤"),
     *      @OA\Response(response="403", description="只能操作自己的資料"),
     * )
     */
    public function update_($clinic_id = null) {
        $token = JWT::getHeaders();
        $payload = JWT::verifyToken($token);
        auth::factory()->roles_auth($clinic_id);
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array(
                '使用者ID' => $post['user_id'],
                '使用者' => $post['name'],
                '帳號' => $post['account'],
                '權限' => $post['roles']
            ),
            array(
                '使用者ID' => array('required', 'maxLen' => 11),
                '使用者' => array('required', 'maxLen' => 64),
                '帳號' => array('required', 'maxLen' => 128),
                '權限' => array('required', 'maxLen' => 3),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        
        auth::factory()->check_self(3, $post['user_id']);
        if(intval($payload['roles']) == 2) {
            $post['roles'] = '2';
        }


        $database = new userModel();
        $return = $database->update_user(
            array(
                'name' => $post['name'],
                'account' => $post['account'],
                'roles' => (string)$post['roles'],
            ),
            array(
                'user_id' => $post['user_id'],
                'clinic_id' => $clinic_id
            )
        );

        if($return == -1) {
            json(new resModel(400, '帳號已被使用...'));
        } else if($return == 0) {
            json(new resModel(400, $database->error));
        } else {
            json(new resModel(200, '更新成功'));
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/users/{clinic_id}", 
     *     tags={"診所使用者管理"},
     *     summary="診所停用使用者",
     *     security={{"Authorization":{}}},
     *      @OA\Parameter(
     *          name="clinic_id",
     *          description="診所ID",
     *          in = "path",
     *          required=true,
     *          example="1",
     *          @OA\Schema(type="integer"),
     *      ), 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"user_id"},
     *                  @OA\Property(property="user_id", type="int(11)", example="2"),
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
     *      @OA\Response(response="400", description="無法操作其他診所"),
     *      @OA\Response(response="401", description="無法獲取診所ID | 提交格式有誤"),
     *      @OA\Response(response="403", description="Permission Denied"),
     * )
     */
    public function delete_($clinic_id = null) {
        auth::factory()->roles_auth($clinic_id);
        auth::factory()->auth(3);
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('使用者ID' => $post['user_id']),
            array('使用者ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new userModel();
        $return = $database->update_user_active(
            array('active' => '0'),
            array(
                'user_id' => $post['user_id'],
                'clinic_id' => $clinic_id
            )
        );


        if($return == 1) {
            json(new resModel(200, '停用成功'));
        } else if($return == 0) {
            json(new resModel(400, '停用失敗'));
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/users/back/{clinic_id}", 
     *     tags={"診所使用者管理"},
     *     summary="診所啟用使用者",
     *     security={{"Authorization":{}}}, 
     *      @OA\Parameter(
     *          name="clinic_id",
     *          description="診所ID",
     *          in = "path",
     *          required=true,
     *          example="1",
     *          @OA\Schema(type="integer"),
     *      ), 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"user_id"},
     *                  @OA\Property(property="user_id", type="int(11)", example="2"),
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
     *      @OA\Response(response="400", description="無法操作其他診所"),
     *      @OA\Response(response="401", description="無法獲取診所ID | 提交格式有誤"),
     *      @OA\Response(response="403", description="Permission Denied"),
     * )
     */
    public function delete_back($clinic_id = null) {
        auth::factory()->roles_auth($clinic_id);
        auth::factory()->auth(3);
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('使用者ID' => $post['user_id']),
            array('使用者ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new userModel();
        $return = $database->update_user_active(
            array('active' => '1'),
            array(
                'user_id' => $post['user_id'],
                'clinic_id' => $clinic_id
            )
        );


        if($return == 1) {
            json(new resModel(200, '啟用成功'));
        } else if($return == 0) {
            json(new resModel(400, '啟用失敗'));
        }
    }

        /**
     * @OA\Patch(
     *     path="/api/users/password/{clinic_id}", 
     *     tags={"診所使用者管理"},
     *     summary="診所更新使用者密碼",
     *     security={{"Authorization":{}}}, 
     *      @OA\Parameter(
     *          name="clinic_id",
     *          description="診所ID",
     *          in = "path",
     *          required=true,
     *          example="1",
     *          @OA\Schema(type="integer"),
     *      ), 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"user_id", "password"},
     *                  @OA\Property(property="user_id", type="int(11)", example="2"),
     *                  @OA\Property(property="password", type="string(256)", example="password"),
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
     *      @OA\Response(response="400", description="無法操作其他診所"),
     *      @OA\Response(response="401", description="無法獲取診所ID | 提交格式有誤"),
     *      @OA\Response(response="403", description="只能操作自己的資料"),
     * )
     */
    public function update_password($clinic_id = null) {
        auth::factory()->roles_auth($clinic_id);
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('使用者ID' => $post['user_id'], '密碼' => $post['password']),
            array('使用者ID' => array('required', 'maxLen' => 11), '密碼' => array('required', 'maxLen' => 256))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        auth::factory()->check_self(3, $post['user_id']);

        $database = new userModel();
        $return = $database->update_user_password(
            array('password' => md5($post['password'])),
            array(
                'user_id' => $post['user_id'],
                'clinic_id' => $clinic_id
            )
        );


        if($return == 0) {
            json(new resModel(400, $database->error));
        } else {
            json(new resModel(200, '更新成功'));
        }
    }
}