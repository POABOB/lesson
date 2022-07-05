<?php
namespace app\controller;
if ( ! defined('PPP')) exit('非法入口');
use app\model\loginModel;
use core\lib\resModel;
use core\lib\Validator;
use core\lib\JWT;
use core\lib\IP;
/**
 * @OA\Info(title="LESSON API", version="1.0", description="
 * roles=999，系統ADMIN，可以對任何資料進行操作<br>
 * 帳密：admin/admin1111password<br>
 * roles=3，診所管理者，只由ADMIN新增，可以操作自己診所的任何事情<br>
 * 帳密：user1/user1<br>
 * roles=2，診所使用者，可由ADMIN、管理者新增，只可以修改自己的資料、密碼，操作扣課<br>
 * 帳密：user2/user2")
 * @OA\OpenApi(tags={
 *      {"name"="登入登出", "description"="登入登出 API"},
 *      {"name"="後台診所管理", "description"="後台診所管理 API"},
 *      {"name"="診所使用者管理", "description"="診所使用者管理 API<br>(roles=999可以操作所有user，roles=3可以操作自己診所的users，roles=2只能優改自己的基本資料和密碼)"},
 *      {"name"="診所扣課管理", "description"="診所扣課管理 API"},
 *      {"name"="診所LOG", "description"="診所LOG API"},
 * })
 * @OA\SecurityScheme(
 *      securityScheme="Authorization",
 *      in="header",
 *      name="Authorization",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 * ),
 */

class loginController extends \core\PPP {
    /**
     * @OA\Post(
     *      path="/api/login", 
     *      tags={"登入登出"},
     *      summary="登入",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"ID", "password"},
     *                  @OA\Property(property="account", type="string(15)", example="admin"),
     *                  @OA\Property(property="password", type="string(64)", example="admin1111password"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="登入成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="登入成功"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="token", type="string", example="<JWT-token>"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="帳號或密碼錯誤"),
     *      @OA\Response(response="401", description="提交格式有誤")
     * )
     */
    public function login_() {
        $post = array();
		$post = post_json();

        //Validation
        $v = new Validator();
        $v->validate(
            array('account' => $post['account'], 'password' => $post['password']),
            array('account' => array('required'),'password' => array('required'))
        );

		if($v->error()) {
			json(new resModel(401, $v->error(), '提交格式有誤'));
			return;
		}

        $database = new loginModel();
		$data = $database->login(array('account' => $post['account'],'password' => md5($post['password'])));

		//有則加入SESSION，沒有就返回Error
		if($data == -2) {
			json(new resModel(400, '該診所未啟用!'));
        } else if($data == -1) {
			json(new resModel(400, '該使用者未啟用!'));
        } else if($data == 0) {
			json(new resModel(400, '帳號或密碼錯誤!'));
        } else {
            $payload=array_merge($data, array('iat'=>time(),'exp'=>time()+60*60*24*30,'nbf'=>time()));
            $token = JWT::getToken($payload);
            $_SESSION['user'] = $token;
            json(new resModel(200, array('token' => $token), '登入成功!'));
		}
	}


    /**
     * @OA\Get(
     *      path="/api/logout", 
     *      tags={"登入登出"},
     *      summary="登出",
     *      @OA\Response(
     *          response="200", 
     *          description="登出成功",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="登出成功"),
     *              @OA\Property(property="data", example="null"),
     *          ),
     *      ),
     * )
     */
    public function logout_() {
        $_SESSION['user'] = false;
		json(new resModel(200, '登出成功'));
	}
}