<?php
/**
 * ACMS_GET_Admin_Config_Import
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Config_Import extends ACMS_GET_Admin_Config
{
    function get()
    {
        if ( 'config_import' <> ADMIN ) return false;
//        if ( !sessionWithAdministration() ) return false;

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        if ( !($rid = intval($this->Get->get('rid'))) ) { $rid = null; }
        if ( !($mid = intval($this->Get->get('mid'))) ) { $mid = null; }

        $vars   = $this->buildField($this->Post, $Tpl);
        $vars['indexUrl']       = $this->getIndexUrl($rid, $mid);
        $vars['shortcutUrl']    = acmsLink(array(
                'bid'   => BID,
                'admin' => 'shortcut_edit',
                'query' => array(
                    'action' => 'Config',
                    'admin'  => ADMIN,
                    'edit'   => 'add',
                    'step'   => 'reapply',
                    'rid'   => $rid,
                    'mid'   => $mid,
                )
        ));
        $Tpl->add(null, $vars);

        return $Tpl->get();
    }
}
