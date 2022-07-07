<?php
namespace app\controller;
if ( ! defined('PPP')) exit('非法入口');
use app\model\hisModel;
use core\lib\resModel;
use core\lib\Validator;
use core\common\auth;
use core\lib\JWT;

class hisController extends \core\PPP {
    /**
     * @OA\Get(
     *      path="/api/his/{clinic_sn}", 
     *      tags={"診所HIS"},
     *      summary="獲取診所所有申請紀錄",
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
     *      @OA\Parameter(
     *          name="clinic_sn",
     *          description="診所機構代碼(10碼)",
     *          in = "path",
     *          required=true,
     *          @OA\Schema(type="string"),
     *          example="AAAAAAAAAA"
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="獲取診所所有申請紀錄",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", example="null"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="lesson_id", type="int(11)", example="1"),
     *                      @OA\Property(property="customer_id", type="string(20)", example="xxxxx"),
     *                      @OA\Property(property="customer_name", type="string(64)", example="顧客A"),
     *                      @OA\Property(property="lesson_sn", type="string(20)", example="yyyyy"),
     *                      @OA\Property(property="lesson_name", type="string(128)", example="課程A"),
     *                      @OA\Property(property="lesson_nums", type="string(10)", example="2"),
     *                      @OA\Property(property="request_clinic_id", type="int(11)", example="2"),
     *                      @OA\Property(property="request_clinic_name", type="string(64)", example="診所A"),
     *                      @OA\Property(property="response_clinic_id", type="int(11)", example="1"),
     *                      @OA\Property(property="response_clinic_name", type="string(64)", example="XX診所"),
     *                      @OA\Property(property="request_datetime", type="string(20)", example="2022-08-06 12:42:08"),
     *                      @OA\Property(property="expired_datetime", type="string(20)", example="2022-08-07 23:59:59"),
     *                      @OA\Property(property="status", type="string(20)", example="待核准/已取消/已核准/已拒絕/已過期"),
     *                  ), 
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response="401", description="提交格式有誤"),
     * )
     */
    public function index_($clinic_sn) {
        $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
        $pageNums = (isset($_GET['pageNums']) && is_numeric($_GET['pageNums'])) ? $_GET['pageNums'] : 50;
        $database = new hisModel();
        $data = $database->get_his(
          array(
            'clinic_sn' => $clinic_sn,
            'page' => $page,
            'pageNums' => $pageNums,
          )
        );
        json(new resModel(200, $data));
    }
}