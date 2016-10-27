<?php
/**
 * ACMS_GET_Admin_Media_Tag
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Media_Tag extends ACMS_GET
{
    var $_axis  = array(
        'bid'   => 'self',
    );

    function get()
    {
        // if ( config('media_library') !== 'on' ) return '';

        $SQL    = SQL::newSelect('media_tag');
        $SQL->addSelect('media_tag_name');
        $SQL->addSelect('media_tag_name', 'tag_amount', null, 'count');
        $SQL->addLeftJoin('blog', 'blog_id', 'media_tag_blog_id');
        ACMS_Filter::blogTree($SQL, $this->bid, $this->blogAxis());
        ACMS_Filter::blogStatus($SQL);
        $SQL->addGroup('media_tag_name');
        $SQL->addOrder('tag_amount', 'DESC');
        $q  = $SQL->get(dsn());

        $DB     = DB::singleton(dsn());
        $all    = $DB->query($q, 'all');

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        foreach ( configArray('column_media_size_label') as $i => $label ) {
            $sizeAry  = array(
                'value'     => config('column_media_size', '', $i),
                'label'     => $label,
            );
            $Tpl->add('size:loop', $sizeAry);
        }

        if ( !$cnt = count($all) ) {
            return $Tpl->get();
        }

        $tags       = array();
        $amounts    = array();
        foreach ( $all as $row ) {
            $tag        = $row['media_tag_name'];
            $amount     = $row['tag_amount'];
            $tags[$tag] = $amount;
            $amounts[]  = $amount;
        }
        $min    = min($amounts);
        $max    = max($amounts);

        $c  = ($max <> $min) ? (24 / (sqrt($max) - sqrt($min))) : 1;
        $x  = ceil(sqrt($min) * $c);

        $i      = 0;
        foreach ( $tags as $tag => $amount ) {
            if ( !empty($i) ) $Tpl->add('glue');
            $vars = array(
                'amount'    => $amount,
                'name'      => $tag,
            );
            if ( $tag === TAG ) {
                $vars['selected'] = config('attr_selected');
            }
            $Tpl->add('tag:loop', $vars);
            $i++;
        }

        return $Tpl->get();
    }
}
