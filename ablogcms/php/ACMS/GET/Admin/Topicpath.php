<?php
/**
 * ACMS_GET_Admin_Topicpath
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Topicpath extends ACMS_GET_Admin
{
    function get()
    {
        if ( !SUID ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        //-----------
        // blog tree
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('blog');
        $SQL->addSelect('blog_id');
        $SQL->addSelect('blog_name');

        // [CMS-971] ブログ作成時に管理トピックパスがおかしい → ブログ作成時のキャッシュを解除
        ACMS_RAM::blog(BID, array());
        ACMS_RAM::blog(SBID, array());

        $fromLeft   = ACMS_RAM::blogLeft(BID);
        $fromRight  = ACMS_RAM::blogRight(BID);
        $toLeft     = ACMS_RAM::blogLeft(SBID);
        $toRight    = ACMS_RAM::blogRight(SBID);
        $SQL->addWhereBw('blog_left', $toLeft, $fromLeft);
        $SQL->addWhereBw('blog_right', $fromRight, $toRight);
        $SQL->setOrder('blog_left');
        $q  = $SQL->get(dsn());
        $DB->query($q, 'fetch');
        $i  = 0;
        while ( $row = $DB->fetch($q) ) {
            $bid    = intval($row['blog_id']);
            if ( !empty($i) ) $Tpl->add('glue');
            $Tpl->add('topic:loop', array(
                'url'   => acmsLink(array(
                    'bid'   => $bid,
                    'admin' => (BID == $bid) ? 'top' : 'blog_edit',
                )),
                'label'  => $row['blog_name'],
            ));
            $i++;
        }

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
        } else if ( 0
            || 'config' == substr(ADMIN, 0, strlen('config'))
            || 'module' == substr(ADMIN, 0, strlen('module'))
        ) {
            if ( !!$this->Get->get('rid') ) {
                $aryAdmin[] = 'rule_index';
                $aryAdmin[] = 'rule_edit';
            }
            if ( !!$this->Get->get('mid') ) {
                $aryAdmin[] = 'module_index';
            }
            if ( 1
                && !$this->Get->get('mid')
                && 'module_index' <> ADMIN 
            ) {
                $aryAdmin[] = 'config_index';
            }
            if ( 'config_index' <> ADMIN ) {
                $aryAdmin[] = ADMIN;
            }
        } else if ( 'fix' == substr(ADMIN, 0, strlen('fix')) ) {

            $aryAdmin[] = 'fix_index';
            if ( 'fix_index' <> ADMIN  ) {
                $aryAdmin[] = ADMIN;
            }
        } else if ( preg_match('@_edit$@', ADMIN) ) {
            if ( !('user_edit' == ADMIN and !sessionWithContribution()) ) {
                if ( 'blog_edit' !== ADMIN ) {
                    $aryAdmin[] = str_replace('_edit', '_index', ADMIN);
                }
            }
            $aryAdmin[] = ADMIN;
        } else if ( 'import' == substr(ADMIN, 0, strlen('import')) ) {
            if ( 'import_index' != ADMIN ) $aryAdmin[] = 'import_index';
            $aryAdmin[] = ADMIN;
        } else {
            $aryAdmin[] = ADMIN;
        }

        foreach ( $aryAdmin as $admin ) {
            $Tpl->add('glue');
            $Tpl->add($admin);
            if ( preg_match('@_edit$@', $admin) ) {
                $url    = acmsLink(array(
                    'bid'   => BID,
                    'uid'   => UID,
                    'cid'   => CID,
                    'eid'   => EID,
                    'tag'   => TAG,
                    'admin' => $admin,
                    'query' => Field::singleton('get'),
                ));
            } else {
                $url    = acmsLink(array(
                    'bid'   => BID,
                    'admin' => $admin,
                    'query' => array(
                        'rid'   => $this->Get->get('rid'),
                        'mid'   => $this->Get->get('mid'),
                        'fmid'  => $this->Get->get('fmid'),
                    ),
                ));
            }
            $Tpl->add('topic:loop', array(
                'url'   => $url,
            ));
        }

        return $Tpl->get();
    }
}
