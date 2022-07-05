<?php
namespace core\lib;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\config;
/**
 * PHP實現jwt
 */
class Jwt {

    //頭部
    private static $header=array(
        'alg'=>'HS256', //生成signature的演算法
        'typ'=>'JWT'    //型別
    );

    //使用HMAC生成資訊摘要時所使用的金鑰
    private static $key='fdfgdfkgjo23r39419edsfgfgnfnoniweffesd';

    /**
     * 獲取jwt token
     * @param array $payload jwt載荷   格式如下非必須
     * [
     *  'iss'=>'jwt_admin',  //該JWT的簽發者
     *  'iat'=>time(),  //簽發時間
     *  'exp'=>time()+259200,  //過期時間
     *  'nbf'=>time()+60,  //該時間之前不接收處理該Token
     *  'sub'=>'www.admin.com',  //面向的使用者
     *  'jti'=>md5(uniqid('JWT').time())  //該Token唯一標識
     * ]
     * @return bool|string
     */
    public static function getToken(array $payload)
    {
        if(is_array($payload))
        {
            $base64header=self::base64UrlEncode(json_encode(self::$header,JSON_UNESCAPED_UNICODE));
            $base64payload=self::base64UrlEncode(json_encode($payload,JSON_UNESCAPED_UNICODE));
            $token=$base64header.'.'.$base64payload.'.'.self::signature($base64header.'.'.$base64payload,self::$key,self::$header['alg']);
            return $token;
        }else{
            return false;
        }
    }

    public static function getHeaders() {
        $headers = apache_request_headers();
        // $headers = get_HTTP_request_headers();
        $token = "";
        if(isset($headers['Authorization'])) {
            $token = explode(" ", $headers['Authorization']);
            if($token[0] !== 'Bearer') {
                $token = $headers['Authorization'];
            } else {
                $token = $token[1];
            }
        } else if (isset($headers['Authorizations'])) {
            $token = explode(" ", $headers['Authorizations']);
            if($token[0] !== 'Bearer') {
                $token = $headers['Authorizations'];
            } else {
                $token = $token[1];
            }
        }
        return $token;
    }


    /**
     * 驗證token是否有效,預設驗證exp,nbf,iat時間
     * @param string $Token 需要驗證的token
     * @return bool|string
     */
    public static function verifyToken(string $Token)
    {
        $tokens = explode('.', $Token);
        if (count($tokens) != 3)
            return false;

        list($base64header, $base64payload, $sign) = $tokens;

        //獲取jwt演算法
        $base64decodeheader = json_decode(self::base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64decodeheader['alg']))
            return false;

        //簽名驗證
        if (self::signature($base64header . '.' . $base64payload, self::$key, $base64decodeheader['alg']) !== $sign)
            return false;

        $payload = json_decode(self::base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);

        //簽發時間大於當前伺服器時間驗證失敗
        if (isset($payload['iat']) && $payload['iat'] > time())
            return false;

        //過期時間小宇當前伺服器時間驗證失敗
        if (isset($payload['exp']) && $payload['exp'] < time())
            return false;

        //該nbf時間之前不接收處理該Token
        if (isset($payload['nbf']) && $payload['nbf'] > time())
            return false;

        return $payload;
    }




    /**
     * base64UrlEncode   https://jwt.io/  中base64UrlEncode編碼實現
     * @param string $input 需要編碼的字串
     * @return string
     */
    private static function base64UrlEncode(string $input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64UrlEncode  https://jwt.io/  中base64UrlEncode解碼實現
     * @param string $input 需要解碼的字串
     * @return bool|string
     */
    private static function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * HMACSHA256簽名   https://jwt.io/  中HMACSHA256簽名實現
     * @param string $input 為base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg   演算法方式
     * @return mixed
     */
    private static function signature(string $input, string $key, string $alg = 'HS256')
    {
        $alg_config=array(
            'HS256'=>'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key,true));
    }
}