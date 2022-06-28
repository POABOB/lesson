<?php
namespace app\controller;
if ( ! defined('PPP')) exit('非法入口');
use app\model\judgerModel;
use core\lib\resModel;
use core\lib\Validator;
use core\lib\JWT;

class clinicController extends \core\PPP {



    /**
     * @OA\Post(
     *     path="/api/admin/judger", 
     *     tags={"後台裁判管理"},
     *     summary="後台新增裁判",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"name", "ID", "password", "phone", "right"},
     *                  @OA\Property(property="name", type="string(128)", example="王裁判"),
     *                  @OA\Property(property="ID", type="string(10)", example="A12345678"),
     *                  @OA\Property(property="password", type="string(64)", example="password"),
     *                  @OA\Property(property="phone", type="string(15)", example="0912345678"),
     *                  @OA\Property(property="right", type="string(1)", example="1"),
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
     *      @OA\Response(response="400", description="新增失敗"),
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
                '姓名' => $post['name'],
                'ID' => $post['ID'],
                '電話' => $post['phone'],
                '密碼' => $post['password'],
                '權限' => $post['right']
            ),
            array(
                '姓名' => array('required', 'maxLen' => 128),
                'ID' => array('required', 'maxLen' => 10),
                '電話' => array('required', 'maxLen' => 15),
                '密碼' => array('required', 'maxLen' => 64),
                '權限' => array('required', 'maxLen' => 1),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new judgerModel();
        $data = $database->insert_judger(
            array(
                'name' => $post['name'],
                'ID' => $post['ID'],
                'phone' => $post['phone'],
                'password' => md5($post['password']),
                'right' => (string)$post['right']
            )
        );

        if($data) {
            json(new resModel(200, '新增成功'));
        } else {
            json(new resModel(400, '新增失敗'));
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/judger", 
     *     tags={"後台裁判管理"},
     *     summary="後台更新裁判",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"judger_id", "name", "ID", "password", "phone", "right"},
     *                  @OA\Property(property="judger_id", type="int(11)", example="1"),
     *                  @OA\Property(property="name", type="string(128)", example="王裁判"),
     *                  @OA\Property(property="ID", type="string(10)", example="A12345678"),
     *                  @OA\Property(property="password", type="string(64)", example="password"),
     *                  @OA\Property(property="phone", type="string(15)", example="0912345678"),
     *                  @OA\Property(property="right", type="string(1)", example="1"),
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
    public function update_() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array(
                '裁判ID' => $post['judger_id'],
                '姓名' => $post['name'],
                'ID' => $post['ID'],
                '電話' => $post['phone'],
                '權限' => $post['right']
            ),
            array(
                '裁判ID' => array('required', 'maxLen' => 11),
                '姓名' => array('required', 'maxLen' => 128),
                'ID' => array('required', 'maxLen' => 10),
                '電話' => array('required', 'maxLen' => 15),
                '權限' => array('required', 'maxLen' => 1),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new judgerModel();
        $return = $database->update_judger(
            array(
                'name' => $post['name'],
                'ID' => $post['ID'],
                'phone' => $post['phone'],
                'right' => (string)$post['right']
            ),
            array('judger_id' => $post['judger_id'])
        );


        if($return == 1) {
            json(new resModel(200, '更新成功'));
        } else if($return == 0) {
            json(new resModel(400, '更新失敗'));
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/admin/judger", 
     *     tags={"後台裁判管理"},
     *     summary="後台刪除裁判",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"judger_id"},
     *                  @OA\Property(property="judger_id", type="int(11)", example="1"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="刪除成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="刪除成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="刪除失敗"),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function delete_() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('裁判ID' => $post['judger_id']),
            array('裁判ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new judgerModel();
        $return = $database->update_judger(
            array('hidden' => '1'),
            array('judger_id' => $post['judger_id'])
        );


        if($return == 1) {
            json(new resModel(200, '刪除成功'));
        } else if($return == 0) {
            json(new resModel(400, '刪除失敗'));
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/judger/back", 
     *     tags={"後台裁判管理"},
     *     summary="後台恢復裁判",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"judger_id"},
     *                  @OA\Property(property="judger_id", type="int(11)", example="1"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="恢復成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="恢復成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="恢復失敗"),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function delete_back() {
        $post = array();
        $post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('裁判ID' => $post['judger_id']),
            array('裁判ID' => array('required', 'maxLen' => 11))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new judgerModel();
        $return = $database->update_judger(
            array('hidden' => '0'),
            array('judger_id' => $post['judger_id'])
        );


        if($return == 1) {
            json(new resModel(200, '恢復成功'));
        } else if($return == 0) {
            json(new resModel(400, '恢復失敗'));
        }
    }

        /**
     * @OA\Patch(
     *     path="/api/admin/judger/password", 
     *     tags={"後台裁判管理"},
     *     summary="後台更新裁判密碼",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"judger_id", "password"},
     *                  @OA\Property(property="judger_id", type="int(11)", example="1"),
     *                  @OA\Property(property="password", type="string(64)", example="123456"),
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
            array('裁判ID' => $post['judger_id'], '密碼' => $post['password']),
            array('裁判ID' => array('required', 'maxLen' => 11), '密碼' => array('required', 'maxLen' => 64))
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }

        $database = new judgerModel();
        $return = $database->update_judger(
            array('password' => md5($post['password'])),
            array('judger_id' => $post['judger_id'])
        );


        if($return == 1) {
            json(new resModel(200, '更新成功'));
        } else if($return == 0) {
            json(new resModel(400, '更新失敗'));
        }
    }
}