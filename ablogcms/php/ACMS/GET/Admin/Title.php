<?php
/**
 * ACMS_GET_Admin_Title
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Title extends ACMS_GET_Admin
{
    function get()
    {
        if ( !SUID ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $aryAdmin   = array();
        if ( 'form_log' == ADMIN ) {
            $aryAdmin[] = 'form_index';
            $aryAdmin[] = 'form_edit';
            $aryAdmin[] = 'form_log';
        } else if ( 'shop' == substr(ADMIN, 0, strlen('shop')) )  {
            if ( 'shop_menu' != ADMIN ) $aryAdmin[] = 'shop_menu';
            if ( preg_match('@_edit$@', ADMIN) ) {
                $aryAdmin[] = str_replace('_edit', '_index', ADMIN);
            }
            $aryAdmin[] = ADMIN;
        } else if ( 'schedule' == substr(ADMIN, 0, strlen('schedule')) )  {
            if ( 'schedule_index' != ADMIN ) $aryAdmin[] = 'schedule_index';
            $aryAdmin[] = ADMIN;
        } else if ( 'config' == substr(ADMIN, 0, strlen('config')) ) {
            if ( !!$this->Get->get('rid') ) {
                $aryAdmin[] = 'rule_index';
                $aryAdmin[] = 'rule_edit';
            } else if ( !!$this->Get->get('mid') ) {
                $aryAdmin[] = 'module_index';
                $aryAdmin[] = 'module_edit';
            }
            if ( !$this->Get->get('mid') )$aryAdmin[] = 'config_index';
            if ( 'config_index' <> ADMIN ) {
                $aryAdmin[] = ADMIN;
            }
        } else if ( preg_match('@_edit$@', ADMIN) ) {
            if ( !('user_edit' == ADMIN and !sessionWithContribution()) ) {
                $aryAdmin[] = str_replace('_edit', '_index', ADMIN);
                
            }

            if ( 'blog_edit' <> ADMIN ) {
                $aryAdmin[] = ADMIN;
            }
        } else if ( 'import' == substr(ADMIN, 0, strlen('import')) ) {
            if ( 'import_index' != ADMIN ) $aryAdmin[] = 'import_index';
            $aryAdmin[] = ADMIN;
        } else {
            $aryAdmin[] = ADMIN;
        }
        
        $Tpl->add(array_pop($aryAdmin));

        return $Tpl->get();
    }
}
