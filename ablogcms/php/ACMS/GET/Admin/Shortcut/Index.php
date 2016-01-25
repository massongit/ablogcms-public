<?php
/**
 * ACMS_GET_Admin_Shortcut_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Shortcut_Index extends ACMS_GET_Admin
{
    function get()
    {
        if ( !sessionWithAdministration() ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('dashboard');
        $SQL->addWhereOpr('dashboard_key', 'shortcut_%', 'LIKE');
        $SQL->addWhereOpr('dashboard_blog_id', BID);
        $SQL->setOrder('dashboard_sort');
        if ( !$all = $DB->query($SQL->get(dsn()), 'all') ) {
            $Tpl->add('index#notFound');
            $Tpl->add(null, array('notice_mess' => 'show'));
            return $Tpl->get();
        }

        $Tmp    = array();
        foreach ( $all as $row ) {
            if ( !preg_match('@^shortcut_(bid|uid|cid|eid|rid|mid|fmid|mbid|scid|null)_(\d+|null)_(.+)_([^_]+)$@', $row['dashboard_key'], $match) ) continue;
            $Tmp[$match[1].','.$match[2].','.$match[3]][$match[4]]  = $row['dashboard_value'];
        }

        $amount = count($Tmp);
        $i      = 1;
        foreach ( $Tmp as $hash => $data ) {
            list($idKey, $id, $admin) = explode(',', $hash);
            if ( 'null' == $idKey ) $idKey = null;
            if ( 'null' == $id ) $id = null;
            $name   = $data['name'];
            $auth   = $data['auth'];
            $action = $data['action'];

            //------
            // sort
            for ( $j=1; $j<=$amount; $j++) {
                $_vars  = array(
                    'value' => $j,
                    'label' => $j,
                );
                if ( $i === $j ) $_vars['selected'] = config('attr_selected');
                $Tpl->add(array('sort:loop', 'shortcut:loop'), $_vars);
            }

            //------
            // auth
            $Tpl->add(array('auth#'.$data['auth'], 'shortcut:loop'));

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

            $Tpl->add('shortcut:loop', array(
                'name'  => $data['name'],
                'url'   => acmsLink($Url),
                'itemUrl'   => acmsLink(array(
                    'bid'   => BID,
                    'admin' => 'shortcut_edit',
                    'query' => array(
                        'action'    => $action,
                        'admin'     => $admin,
                        $idKey      => $id,
                    ),
                )),
            ));

            $i++;
        }

        $Tpl->add(null, $this->buildField($this->Post, $Tpl));
        return $Tpl->get();

    }
}
