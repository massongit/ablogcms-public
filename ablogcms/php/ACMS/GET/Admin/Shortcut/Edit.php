<?php
/**
 * ACMS_GET_Admin_Shortcut_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Shortcut_Edit extends ACMS_GET_Admin_Edit
{
    function edit()
    {
        if ( 'shortcut_edit' <> ADMIN ) { return false; }
        if ( !$this->Get->get('admin') ) { return false; }
        if ( !$this->Get->get('action') ) { return false; }

        $admin  = $this->Get->get('admin');
        foreach ( array('bid', 'uid', 'cid', 'eid', 'rid', 'mid', 'fmid', 'mbid', 'scid') as $idKey ) {
            $id = $this->Get->get($idKey);
            if ( empty($id) or is_bool($id) ) continue;
            $id = intval($id);
            break;
        }
        if ( !is_int($id) ) {
            $idKey  = 'null';
            $id     = 'null';
        }

        $Shortcut   =& $this->Post->getChild('shortcut');

        if ( 'add' == $this->edit ) {
            $DB     = DB::singleton(dsn());
            $SQL    = SQL::newSelect('dashboard');
            $SQL->setSelect('dashboard_key');
            $SQL->addWhereOpr('dashboard_key', 'shortcut_'.$idKey.'_'.$id.'_'.$admin.'_%', 'LIKE');
            $SQL->addWhereOpr('dashboard_blog_id', BID);
            $SQL->setLimit(1);
            if ( !!$DB->query($SQL->get(dsn()), 'one') ) {
                foreach ( array('action', 'auth', 'name') as $key ) {
                    $SQL    = SQL::newSelect('dashboard');
                    $SQL->setSelect('dashboard_value');
                    $SQL->addWhereOpr('dashboard_key', 'shortcut_'.$idKey.'_'.$id.'_'.$admin.'_'.$key);
                    $SQL->addWhereOpr('dashboard_blog_id', BID);
                    $Shortcut->set($key, $DB->query($SQL->get(dsn()), 'one'));
                }
                if ( $Shortcut->isNull() ) return false;
                $this->edit = 'update';
            } else {
                $this->edit = 'insert';
            }
        } else if ( $this->edit !== 'delete' ) {
            $DB     = DB::singleton(dsn());
            foreach ( array('action', 'auth', 'name') as $key ) {
                $SQL    = SQL::newSelect('dashboard');
                $SQL->setSelect('dashboard_value');
                $SQL->addWhereOpr('dashboard_key', 'shortcut_'.$idKey.'_'.$id.'_'.$admin.'_'.$key);
                $SQL->addWhereOpr('dashboard_blog_id', BID);
                $Shortcut->set($key, $DB->query($SQL->get(dsn()), 'one'));
            }
            if ( $Shortcut->isNull() ) return false;
        }

        if ( $idKey === 'mid' && !empty($id) ) {
            $admin = 'module_edit';
        }

        $Url    = array(
            'bid'   => BID,
            'admin' => $admin,
        );
        if ( 'null' == $idKey ) {
            
        } else if ( in_array($idKey, array('rid', 'mid', 'fmid', 'mbid', 'scid')) ) {
            $Url['query']   = array(
                $idKey  => $id,
            );
        } else {
            $Url[$idKey]    = $id;
        }

        $Shortcut->set('url', acmsLink($Url));

        return true;
    }
}
