<?php
/**
 * ACMS_User_Hook
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_User_Hook
{
    /**
     * GETモジュール処理前
     * 解決前テンプレートの中間処理など
     *
     * @param string   &$tpl
     * @param ACMS_GET $thisModule
     */
    public function beforeGetFire(&$tpl, $thisModule)
    {

    }

    /**
     * GETモジュール処理後
     * 解決済みテンプレートの中間処理など
     *
     * @param string   &$res
     * @param ACMS_GET $thisModule
     */
    public function afterGetFire(&$res, $thisModule)
    {

    }

    /**
     * POSTモジュール処理前
     * $thisModuleのプロパティを参照・操作するなど
     *
     * @param ACMS_POST $thisModule
     */
    public function beforePostFire($thisModule)
    {

    }

    /**
     * POSTモジュール処理後
     * $thisModuleのプロパティを参照・操作するなど
     *
     * @param ACMS_POST $thisModule
     */
    public function afterPostFire($thisModule)
    {

    }

    /**
     * ビルド前（GETモジュール解決前）
     *
     * @param $tpl &$tpl テンプレート文字列
     */
    public function beforeBuild(&$tpl)
    {

    }

    /**
     * ビルド後（GETモジュール解決後）
     * ※ 空白の除去・文字コードの変換・POSTモジュールに対するSIDの割り当てなどはこの後に行われます
     *
     * @param string &$res レスポンス文字列
     */
    public function afterBuild(&$res)
    {

    }

    /**
     * フォーム Submit時
     *
     * @param array $mail 自動返信メール
     * @param array $mailAdmin 管理者宛メール
     */
    public function formSubmit($mail, $mailAdmin)
    {

    }

    /**
     * 承認通知
     *
     * @param array $data 通知データ
     * @param bool falseを設定するとデフォルトのメールが飛ばないように設定
     */
    public function approvalNotification($data, &$send=true)
    {

    }

    /**
     * 処理の一番最後のシャットダウン時
     *
     *
     */
    public function beforeShutdown()
    {

    }

    /**
     * グローバル変数の拡張
     *
     * @param array $globalVars
     */
    public function extendsGlobalVars($globalVars)
    {
        // $globalVars->set('key', 'var');
    }

    /**
     * 引用ユニット拡張
     * @param string $url 引用URL
     * @param string &$html 整形後HTML
     */
    public function extendsQuoteUnit($url, &$html)
    {
        // $parsed_url = parse_url($url);
        // if ( $parsed_url['host'] === 'sampleple.com' ) {
        //     $html  = '';
        // }
    }

    /**
     * ビデオユニット拡張
     * @param string $url URL
     * @param string &$id Video ID
     */
    public function extendsVideoUnit($url, &$id)
    {
        // $parsed_url = parse_url($url);
        // if ( !empty($parsed_url['path']) ) {
        //     $id = preg_replace('@/@', '', $parsed_url['path']);
        // }
    }

    /**
     * キャッシュのリフレッシュ時
     *
     */
    public function cacheRefresh()
    {

    }

    /**
     * キャッシュのクリア時
     *
     */
    public function cacheClear()
    {

    }

    /**
     * キャッシュの削除時
     *
     */
    public function cacheDelete()
    {

    }

    /**
     * メディアデータ作成
     * @param string $path 作成先パス
     *
     */
    public function mediaCreate($path)
    {
        // include_once 'HTTP/Request.php';

        // $url = "http://localhost/media/create.php";
        // $req =& new HTTP_Request($url);
        // $req->setMethod(HTTP_REQUEST_METHOD_POST);
        // $req->addHeader("Content-Type", "multi-part/form-data");
        // $req->addFile("mediaFile", $path);
        // $req->addPostData("mediaPath", $path);
        // // $req->setBasicAuth("user", "pass");
        // $req->sendRequest();
    }

    /**
     * メディアデータ削除
     * @param string $path 削除パス
     *
     */
    public function mediaDelete($path)
    {
        // $url = "http://localhost/media/delete.php";
        // $req =& new HTTP_Request($url);
        // $req->setMethod(HTTP_REQUEST_METHOD_POST);
        // $req->addPostData("mediaPath", $path);
        // // $req->setBasicAuth("user", "pass");
        // $req->sendRequest();
    }
}
