<?php
/**
 * Created by PhpStorm.
 * User: blirx
 * Date: 2018/3/19
 * Time: 11:19
 */

namespace App\Handlers;


use GuzzleHttp\Client;
use Overtrue\Pinyin\Pinyin;

class SlugTranslateHandler {
    public function translate($text) {

        $http = new Client;

        // 初始化配置信息
        $api       = 'http://openapi.youdao.com/api?';
        $appkey    = config('services.youdao_translate.appkey');
        $appsecret = config('services.youdao_translate.appsecret');

        $salt = time();

        // 如果没有配置有道翻译，自动使用兼容的拼音方案
        if (empty($appkey)) {
            return $this->pinyin($text);
        }

        // 根据文档，生成 sign
        $sign = md5($appkey . $text . $salt . $appsecret);

        // 构建请求参数
        $query = http_build_query([
            "q"      => $text,
            "from"   => "zh-CHS",
            "to"     => "EN",
            "appKey" => $appkey,
            "salt"   => $salt,
            "sign"   => $sign,
        ]);
        // 发送 HTTP Get 请求
        $response = $http->get($api . $query);

        $result = json_decode($response->getBody(), true);

        // 尝试获取获取翻译结果
        if (isset($result['translation'][0])) {
            return str_slug($result['translation'][0]);
        } else {
            // 如果翻译没有结果，使用拼音作为后备计划。
            return $this->pinyin($text);
        }
    }

    public function pinyin($text) {
        return str_slug(app(Pinyin::class)->permalink($text));
    }
}