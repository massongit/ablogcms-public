<?php
/**
 * ACMS_GET_Admin_Media_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Media_Index extends ACMS_GET_Entry
{
    function get()
    {
        if ( config('media_library') !== 'on' ) return '';
        if ( roleAvailableUser() ) {
            if ( !roleAuthorization('media_upload', BID) && !roleAuthorization('media_edit', BID) ) return false;
        } else {
            if ( !sessionWithContribution() ) return false;
        }

        $order  = ORDER ? ORDER : 'upload_date-desc';
        $limits = configArray('admin_limit_option');
        $limit  = LIMIT ? LIMIT : $limits[config('admin_limit_default')];

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $vars   = array();

        //---------
        // refresh
        if ( !$this->Post->isNull() ) {
            $Tpl->add('refresh');
            $vars['notice_mess'] = 'show';
            $notice = true;
        }

        //----------
        // init SQL
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('media');

        //-----
        // bid
        $target_bid = $this->Get->get('_bid', BID);

        //------
        // axis
        $axis   = $this->Get->get('axis', 'self');
        if ( 1 < ACMS_RAM::blogRight($target_bid) - ACMS_RAM::blogLeft($target_bid) ) {
            $Tpl->add('axis', array(
                'axis:checked#'.$axis => config('attr_checked')
            ));
        } else {
            $axis   = 'self';
        }

        //-------
        // tag
        if ( TAG ) {
            $SQL->addLeftJoin('media_tag', 'media_tag_media_id', 'media_id');
            $SQL->addWhereOpr('media_tag_name', TAG, '=', 'AND');
        }

        //-------
        // order
        $vars['order:selected#'.$order]  = config('attr_selected');

        //-------
        // limit
        foreach ( $limits as $val ) {
            $_vars  = array('limit' => $val);
            if ( $limit == $val ) $_vars['selected'] = config('attr_selected');
            $Tpl->add('limit:loop', $_vars);
        }

        $SQL->addLeftJoin('blog', 'blog_id', 'media_blog_id');
        ACMS_Filter::blogTree($SQL, $target_bid, $axis);
        ACMS_Filter::blogStatus($SQL);

        $Pager  = new SQL_Select($SQL);
        $Pager->setSelect('*', 'media_amount', null, 'count');
        if ( !$pageAmount = intval($DB->query($Pager->get(dsn()), 'one')) ) {
            $Tpl->add('index#notFound');
            $vars['notice_mess'] = 'show';
            $Tpl->add(null, $vars);
            return $Tpl->get();
        }

        $vars   += $this->buildPager(PAGE, $limit, $pageAmount
            , config('admin_pager_delta'), config('admin_pager_cur_attr'), $Tpl, array(), array('admin' => ADMIN)
        );

        $SQL->setLimit($limit, (PAGE - 1) * $limit);
        ACMS_Filter::mediaOrder($SQL, $order);

        $q  = $SQL->get(dsn());
        $DB->query($q, 'fetch');

        while ( $row = $DB->fetch($q) ) {
            $mid    = $row['media_id'];
            $type   = $row['media_type'];
            $path   = $row['media_path'];
            $name   = $row['media_file_name'];
            $size   = $row['media_file_size'];
            $date   = $row['media_upload_date'];
            $bid    = $row['media_blog_id'];

            $permalink = BASE_URL.MEDIA_LIBRARY_DIR.$row['media_path'];
            if ( ARCHIVES_CACHE_SERVER ) {
                $permalink  = ARCHIVES_CACHE_SERVER.'/'.DIR_OFFSET.MEDIA_LIBRARY_DIR.$row['media_path'];
            }

            $this->buildTag($Tpl, $mid, 'media:loop');

            $_vars  = array();
            $ext    = ite(pathinfo($path), 'extension');

            if ( !empty($row['media_thumbnail']) ) {
                $_vars['thumbnail'] = $row['media_thumbnail'];
            }

            $_vars   += array(
                'mid'       => $mid,
                'bid'       => $bid,
                'path'      => $path,
                'datetime'  => $row['media_upload_date'],
                'name'      => $row['media_file_name'],
                'size'      => $row['media_file_size'],
                'type'      => $row['media_type'],
                'permalink' => $permalink,
                'editUrl'   => acmsLink(array(
                    'bid'   => $bid,
                    'admin' => 'media_edit',
                    'query' => array(
                        '_mid'  => $mid,
                    ),
                )),
            );
            if ( is_file(pathIcon($ext)) ) {
                $_vars['icon']  = pathIcon($ext);
            }
            $Tpl->add('media:loop', $_vars);
        }

        $Tpl->add(null, $vars);
        return $Tpl->get();
    }

    function buildTag(& $Tpl, $mid, $block=null)
    {
        $block  = empty($block) ? array() : (is_array($block) ? $block : array($block));

        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('media_tag');
        $SQL->addSelect('media_tag_name');
        $SQL->addSelect('media_tag_blog_id');
        $SQL->addWhereOpr('media_tag_media_id', $mid);
        $SQL->addOrder('media_tag_sort');

        $q  = $SQL->get(dsn());

        do {
            if ( !$DB->query($q, 'fetch') ) break;
            if ( !$row = $DB->fetch($q) ) break;
            $stack  = array();
            array_push($stack, $row);
            array_push($stack, $DB->fetch($q));
            while ( $row = array_shift($stack) ) {
                if ( !empty($stack[0]) ) $Tpl->add(array_merge(array('glue', 'tag:loop'), $block));
                $Tpl->add(array_merge(array('tag:loop'), $block), array(
                    'name'  => $row['media_tag_name'],
                    'url'   => acmsLink(array(
                        'bid'   => $row['media_tag_blog_id'],
                        'tag'   => $row['media_tag_name'],
                    )),
                ));
                array_push($stack,$DB->fetch($q));
            }
        } while ( false );
    }
}
