<?php
/**
 * ACMS_GET_Js
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Js extends ACMS_GET
{
    function get()
    {
        $Session    =& Field::singleton('session');
        $delStorage = $Session->get('webStorageDeleteKey');

        $jquery = '';
        jsModule('domains', HTTP_HOST);
        jsModule('offset', DIR_OFFSET);
        jsModule('jsDir', JS_DIR);
        jsModule('themesDir', '/'.DIR_OFFSET.THEMES_DIR);
        jsModule('bid', BID);
        jsModule('aid', AID);
        jsModule('uid', UID);
        jsModule('cid', CID);
        jsModule('eid', EID);
        jsModule('bcd', ACMS_RAM::blogCode(BID));
        jsModule('rid', $this->Get->get('rid', null));
        jsModule('mid', $this->Get->get('mid', null));
        jsModule('layout', LAYOUT_EDIT);
        jsModule('yahooApiKey', config('yahoo_search_api_key'));
        jsModule('jQuery', config('jquery_version'));
        jsModule('jQueryMigrate', config('jquery_migrate', 'off'));
        jsModule('delStorage', $delStorage);

        jsModule('umfs', ini_get('upload_max_filesize'));
        jsModule('pms',  ini_get('post_max_size'));
        jsModule('mfu',  ini_get('max_file_uploads'));
        jsModule('lgImg', config('image_size_large_criterion').':'.preg_replace('/[^0-9]/', '', config('image_size_large')));
        jsModule('jpegQuality', config('image_jpeg_quality', 95));

        $Session->delete('webStorageDeleteKey');

        //----------
        // category
        if ( $cid = CID ) {
            $ccds   = array(ACMS_RAM::categoryCode($cid));
            while ( $cid = ACMS_RAM::categoryParent($cid) ) {
                if ( 'on' == ACMS_RAM::categoryIndexing($cid) ) {
                    $ccds[] = ACMS_RAM::categoryCode($cid);
                }
            }
            jsModule('ccd', join('/', array_reverse($ccds)));
        }

        //---------
        // session
        jsModule('sid', SID);
        jsModule('admin', ADMIN);
        jsModule('rid', RID);
        jsModule('ecd', ACMS_RAM::entryCode(EID));
        jsModule('session', SID ? SESSION_NAME : null);
        jsModule('keyword', htmlspecialchars(str_replace('　', ' ', KEYWORD), ENT_QUOTES));
        jsModule('scriptRoot', '/'.DIR_OFFSET.(REWRITE_ENABLE ? '' : SCRIPT_FILENAME.'/'));
        $jsModules  = array();
        foreach ( jsModule() as $key => $value ) {
            if ( empty($value) ) continue;
            $jsModules[]    = $key.(!is_bool($value) ? '='.$value : '');
        }
        $jquery = join('&', $jsModules);

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        if ( !empty($jquery) ) $Tpl->add(null, array(
            'arguments' => '?'.$jquery,
        ));

        return $Tpl->get();
    }
}
