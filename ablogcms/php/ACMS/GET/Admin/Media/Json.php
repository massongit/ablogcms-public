<?php
/**
 * ACMS_GET_Admin_Media_Json
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Media_Json extends ACMS_GET_Entry
{
    function get()
    {
        if ( !sessionWithContribution() ) return '';

        $order  = ORDER ? ORDER : 'upload_date-desc';
        $limit  = 30;
        $mediaList  = array();

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
        if ( 1 >= ACMS_RAM::blogRight($target_bid) - ACMS_RAM::blogLeft($target_bid) ) {
            $axis   = 'self';
        }

        //-------
        // tag
        $SQL->addLeftJoin('media_tag', 'media_tag_media_id', 'media_id');
        if ( TAG ) {
            $SQL->addWhereOpr('media_tag_name', TAG, '=', 'AND');
        }

        $SQL->addLeftJoin('blog', 'blog_id', 'media_blog_id');
        ACMS_Filter::blogTree($SQL, $target_bid, $axis);
        ACMS_Filter::blogStatus($SQL);

        $COUNT  = new SQL_Select($SQL);
        $COUNT->setSelect('*', 'media_amount', null, 'count');
        $amount = $DB->query($COUNT->get(dsn()), 'one');
        if ( $amount > PAGE * $limit ) {
            $next   = PAGE + 1;
        } else {
            $next   = 0;
        }

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

            $_vars  = array();
            $ext    = ite(pathinfo($path), 'extension');

            if ( !empty($row['media_thumbnail']) ) {
                $_vars['thumbnail'] = $row['media_thumbnail'];
            }

            $_vars   += array(
                'mid'       => $mid,
                'bid'       => $bid,
                'datetime'  => $row['media_upload_date'],
                'name'      => $row['media_file_name'],
                'label'     => $row['media_tag_name'],
                'ext'       => $ext,
                'size'      => $row['media_file_size'],
                'type'      => $row['media_type'],
                'path'      => $path,
                'icon'      => pathIcon($ext),
                'normalPath'=> '/'.DIR_OFFSET.MEDIA_LIBRARY_DIR.$path,
                'tinyPath'  => otherSizeImagePath($path, 'tiny'),
                'permalink' => $permalink,
                'editUrl'   => acmsLink(array(
                    'bid'   => $bid,
                    'admin' => 'media_edit',
                    'query' => array(
                        '_mid'  => $mid,
                    ),
                )),
            );

            $large  = MEDIA_LIBRARY_DIR.preg_replace('@(.*?)([^/]+)$@', '$1large-$2', $row['media_path']);
            $square = MEDIA_LIBRARY_DIR.preg_replace('@(.*?)([^/]+)$@', '$1square-$2', $row['media_path']);

            if ( is_readable($large) ) {
                $_vars['largePath'] = '/'.DIR_OFFSET.$large;
            }
            if ( is_readable($square) ) {
                $_vars['squarePath'] = '/'.DIR_OFFSET.$square;
            }

            $mediaList[]    = $_vars;
        }
        return json_encode(array(
            'MediaList' => $mediaList,
            'Amount'    => $amount,
            'NextPage'  => $next,
        ));
    }
}
