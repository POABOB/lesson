<?php
namespace app\controller;
if ( ! defined('PPP')) exit('非法入口');
use app\model\clinicModel;
use core\lib\resModel;
use core\lib\Validator;

class clinicController extends \core\PPP {

    /**
     * @OA\Get(
     *      path="/api/clinic", 
     *      tags={"後台診所管理"},
     *      summary="後台獲取診所",
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
     *          example="100"
     *      ),
     *      @OA\Response(
     *          response="200", 
     *          description="獲取診所",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", example="null"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="flat", type="array",
     *                     @OA\Items(type="object",
     *                          @OA\Property(property="clinic_id", type="int(11)", example="1"),
     *                          @OA\Property(property="clinic_sn", type="string(10)", example="XXXXXXXXXX"),
     *                          @OA\Property(property="name", type="string(128)", example="XX診所"),
     *                          @OA\Property(property="active", type="string(1)", example="1"),
     *                          @OA\Property(property="parent_id", type="int(11)", example="0"),
     *                          @OA\Property(property="parent_name", type="string(64)", example=""),
     *                          @OA\Property(property="is_parent_editable", type="string(1)", example="0"),
     *                      )
     *                  ),
     *                  @OA\Property(property="parent_children", type="array",
     *                     @OA\Items(type="object",
     *                          @OA\Property(property="clinic_id", type="int(11)", example="1"),
     *                          @OA\Property(property="clinic_sn", type="string(10)", example="XXXXXXXXXX"),
     *                          @OA\Property(property="name", type="string(128)", example="XX診所"),
     *                          @OA\Property(property="active", type="string(1)", example="1"),
     *                          @OA\Property(property="parent_id", type="int(11)", example="0"),
     *                          @OA\Property(property="parent_name", type="string(64)", example=""),
     *                          @OA\Property(property="is_parent_editable", type="string(1)", example="0"),
     *                          @OA\Property(property="children", type="array",
     *                              @OA\Items(type="object",
     *                                  @OA\Property(property="clinic_id", type="int(11)", example="1"),
     *                                  @OA\Property(property="clinic_sn", type="string(10)", example="XXXXXXXXXX"),
     *                                  @OA\Property(property="name", type="string(128)", example="XX診所"),
     *                                  @OA\Property(property="active", type="string(1)", example="1"),
     *                                  @OA\Property(property="parent_id", type="int(11)", example="0"),
     *                                  @OA\Property(property="parent_name", type="string(64)", example=""),
     *                                  @OA\Property(property="is_parent_editable", type="string(1)", example="0"),
     *                              )
     *                          )
     *                      )
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response="400", description="獲取失敗")
     * )
     */
    public function index_() {
        $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
        $pageNums = (isset($_GET['pageNums']) && is_numeric($_GET['pageNums'])) ? $_GET['pageNums'] : 100;
        $database = new clinicModel();
        $data = $database->get_clinic(
            array(
                // 'ORDER' => array('clinic_id'=> 'DESC'),
                // 優化LIMIT
                'clinic_id' => (($page - 1) * $pageNums),
                'LIMIT' => $pageNums
            )
        );
        $flat_data = array();
        $parent_children_data = array();
        if($data !== null || count($data) !== 0) {
            // 尋找上級診所
            foreach ($data as $key => $value) {
                if($data[$key]['lesson_count'] == 0) {
                    $data[$key]['is_parent_editable'] = "1";  
                } else {
                    $data[$key]['is_parent_editable'] = "0";  
                }

                if(intval($data[$key]['parent_id']) !== 0) {
                    // 上級
                    $parent = array_find(
                        $data,
                        function($val) use ($data, $key) {
                            return $val['clinic_id'] == $data[$key]['parent_id'];
                        }
                    );

                    if($parent !== null) {
                        $data[$key]['parent_name'] = $parent['name'];
                    } else {
                        $data[$key]['parent_name'] = "";
                    }
                    array_push($flat_data, $data[$key]);
                    
                    $data[$key]['children'] = [];                    
                }
            }
            foreach ($data as $key => $value) {
                if(intval($data[$key]['parent_id']) == 0) {
                    // 下級
                    $children = array_filter(
                        $data,
                        function($val) use ($data, $key) {
                            return $val['parent_id'] == $data[$key]['clinic_id'];
                        }
                    );
                    
                    if(count($children) > 0) {
                        $data[$key]['is_parent_editable'] = "0";
                    }
                    $data[$key]['parent_name'] = "";
                    
                    array_push($flat_data, $data[$key]);
                    $data[$key]['children'] = $children;

                    array_push($parent_children_data, $data[$key]);
                }
            }
        }
        json(new resModel(200, array('flat' => $flat_data, 'parent_children' => $parent_children_data)));
    }

    /**
     * @OA\Post(
     *     path="/api/clinic", 
     *     tags={"後台診所管理"},
     *     summary="後台新增診所(parent_id為指定上級診所，診所最多為兩級，所以不能指定已有parent_id的診所!)",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"name", "clinic_sn", "parent_id",},
     *                  @OA\Property(property="name", type="string(64)", example="診所A"),
     *                  @OA\Property(property="clinic_sn", type="string(10)", example="XXXXXXXXXX"),
     *                  @OA\Property(property="parent_id", type="int(11)", example="0"),
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
     *      @OA\Response(response="400", description="指定非0以外不存在的診所ID | 該診所已有上級，無法指定"),
     *      @OA\Response(response="401", description="提交格式有誤"),
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
                '診所機構代碼' => $post['clinic_sn'],
                '上級診所' => $post['parent_id'],
            ),
            array(
                '診所' => array('required', 'maxLen' => 64),
                '診所機構代碼' => array('required', 'maxLen' => 10),
                '上級診所' => array('required', 'maxLen' => 11),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }


        $database = new clinicModel();
        $data = $database->insert_clinic(
            array(
                'name' => $post['name'],
                'clinic_sn' => $post['clinic_sn'],
                'parent_id' => $post['parent_id'],
            )
        );
        if($data == -2) {
            json(new resModel(400, '指定非0以外不存在的診所ID'));
        } else if($data == -1) {
            json(new resModel(400, '該診所已有上級，無法指定'));
        } else {
            json(new resModel(200, '新增成功'));
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/clinic", 
     *     tags={"後台診所管理"},
     *     summary="後台更新診所(parent_id在兩種情況下不能被修改，1. 該診所以有課程操作紀錄 2. 該診所有下級診所)",
     *     security={{"Authorization":{}}}, 
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  required={"clinic_id", "clinic_sn", "name", "parent_id"},
     *                  @OA\Property(property="clinic_id", type="int(11)", example="2"),
     *                  @OA\Property(property="clinic_sn", type="string(10)", example="XXXXXXXXXX"),
     *                  @OA\Property(property="name", type="string(64)", example="診所A"),
     *                  @OA\Property(property="parent_id", type="int(11)", example="0"),
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
     *      @OA\Response(response="400", description="指定非0以外不存在的診所ID | 該診所已有上級，無法指定"),
     *      @OA\Response(response="402", description="診所已有下級，不能指定其他診所為上級 | 診所已有課程操作，不能指定其他診所為上級"),
     *      @OA\Response(response="401", description="提交格式有誤"),
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
                '診所機構代碼' => $post['clinic_sn'],
                '診所' => $post['name'],
                '上級診所' => $post['parent_id'],
            ),
            array(
                '診所ID' => array('required', 'maxLen' => 11),
                '診所機構代碼' => array('required', 'maxLen' => 11),
                '診所' => array('required', 'maxLen' => 64),
                '上級診所' => array('required', 'maxLen' => 11),
            )
        );

        if($v->error()) {
            json(new resModel(401, $v->error(), '提交格式有誤'));
            return;
        }


        $database = new clinicModel();
        $data = $database->update_clinic(
            array(
                'name' => $post['name'],
                'clinic_sn' => $post['clinic_sn'],
                'parent_id' => $post['parent_id'],
            ),
            array('clinic_id' => $post['clinic_id'])
        );
        
        if($data == -4) {
            json(new resModel(400, '指定非0以外不存在的診所ID'));
        } else if($data == -3) {
            json(new resModel(400, '該診所已有上級，無法指定'));
        } else if($data == -2) {
            json(new resModel(402, '診所已有下級，不能指定其他診所為上級'));
        } else if($data == -1) {
            json(new resModel(402, '診所已有課程操作，不能指定其他診所為上級'));
        } else {
            json(new resModel(200, '更新成功'));
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/clinic", 
     *     tags={"後台診所管理"},
     *     summary="後台停用診所(讓該診所的員工不能登入操作，但不限制其他診所)",
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
     *     path="/api/clinic/back", 
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
}