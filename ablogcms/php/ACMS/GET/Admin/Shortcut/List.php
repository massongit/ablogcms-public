<?php
/**
 * ACMS_GET_Admin_Shortcut_List
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Shortcut_List extends ACMS_GET_Admin
{
    function get()
    {
        if ( !sessionWithContribution() ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('dashboard');
        $SQL->addWhereOpr('dashboard_key', 'shortcut_%', 'LIKE');
        $SQL->addWhereOpr('dashboard_blog_id', BID);
        $SQL->setOrder('dashboard_sort');
        if ( !$all = $DB->query($SQL->get(dsn()), 'all') ) return '';

        $Tmp    = array();
        foreach ( $all as $row ) {
            if ( !preg_match('@^shortcut_(bid|uid|cid|eid|rid|mid|fmid|mbid|scid|null)_(\d+|null)_(.+)_([^_]+)$@', $row['dashboard_key'], $match) ) continue;
            $Tmp[$match[1].','.$match[2].','.$match[3]][$match[4]]  = $row['dashboard_value'];
        }

        //------
        // auth
        $aryAuth    = array();
        if ( sessionWithContribution() ) $aryAuth[] = 'contribution';
        if ( sessionWithCompilation() ) $aryAuth[]  = 'compilation';
        if ( sessionWithAdministration() ) $aryAuth[]   = 'administration';

        $_Tmp   = $Tmp;
        $Tmp    = array();
        foreach ( $_Tmp as $hash => $data ) {
            if ( !in_array($data['auth'], $aryAuth) ) continue;
            $Tmp[$hash] = $data;
        }
        if ( empty($Tmp) ) return '';

        foreach ( $Tmp as $hash => $data ) {
            list($idKey, $id, $admin) = explode(',', $hash);
            if ( 'null' == $idKey ) $idKey = null;
            if ( 'null' == $id ) $id = null;
            $name   = $data['name'];
            $auth   = $data['auth'];
            $action = $data['action'];

            if ( $idKey === 'mid' && !empty($id) ) {
                $admin = 'module_edit';
            }

            //-----
            // url
            $Url    = array(
                'bid'   => BID,
                'admin' => $admin,
            );
            if ( in_array($idKey, array('rid', 'mid', 'fmid', 'mbid', 'scid')) ) {
                $Url['query']   = array(
                    $idKey  => $id,
                );
            } else {
                $Url[$idKey]    = $id;
            }

            if ( $admin === 'blog_edit' ) {
                $admin = 'blog';
            }

            $Tpl->add('shortcut:loop', array(
                'admin' => $admin,
                'name'  => $data['name'],
                'url'   => acmsLink($Url),
            ));
        }

        //$Tpl->add(null, $this->buildField($this->Post, $Tpl));
        return $Tpl->get();

    }
}
