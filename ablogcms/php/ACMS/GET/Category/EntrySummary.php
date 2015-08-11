<?php
/**
 * ACMS_GET_Category_EntrySummary
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Category_EntrySummary extends ACMS_GET_Category_EntryList
{
    var $_axis = array(
        'bid'   => 'self',
        'cid'   => 'self',
    );
    
    var $_endGluePoint = null;
    
    function initVars()
    {
        $config = array(
            'categoryOrder'             => config('category_entry_summary_category_order'),
            'categoryEntryListLevel'    => config('category_entry_summary_level'),
            'categoryIndexing'          => config('category_entry_summary_category_indexing'),
            'entryAmountZero'           => config('category_entry_summary_entry_amount_zero'),
            'order'                     => config('category_entry_summary_order'),
            'limit'                     => intval(config('category_entry_summary_limit')),
            'offset'                    => intval(config('category_entry_summary_offset')),
            'indexing'                  => config('category_entry_summary_indexing'),
            'secret'                    => config('category_entry_summary_secret'),
            'notfound'                  => config('mo_category_entry_summary_notfound'),
            'noimage'                   => config('category_entry_summary_noimage'),
            'unit'                      => config('category_entry_summary_unit'),
            'newtime'                   => config('category_entry_summary_newtime'),
            'imageX'                    => intval(config('category_entry_summary_image_x')),
            'imageY'                    => intval(config('category_entry_summary_image_y')),
            'imageTrim'                 => config('category_entry_summary_image_trim'),
            'imageZoom'                 => config('category_entry_summary_image_zoom'),
            'imageCenter'               => config('category_entry_summary_image_center'),
            'mainImageOn'               => config('category_entry_summary_image_on'),
            'entryFieldOn'              => config('category_entry_summary_entry_field_on'),
            'categoryFieldOn'           => config('category_entry_summary_category_field_on'),
            'categoryLoopClass'         => config('category_entry_summary_category_loop_class'),
            'entryLoopClass'            => config('category_entry_summary_entry_loop_class'),
            'fulltextWidth'             => config('category_entry_summary_fulltext_width'),
            'fulltextMarker'            => config('category_entry_summary_fulltext_marker'),
        );
        if(!empty($this->order)){$config['order'] = $this->order;}
        
        return $config;
    }
    
    function buildQuery($cid, &$Tpl)
    {
        $BlogSub        = null;
        $CategorySub    = null;

        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('entry');
        $SQL->addLeftJoin('category', 'category_id', 'entry_category_id');
        $SQL->addLeftJoin('blog', 'blog_id', 'entry_blog_id');
        $SQL->addWhereOpr('entry_category_id', $cid);

        if ( !empty($this->bid) ) {
            $BlogSub = SQL::newSelect('blog');
            $BlogSub->setSelect('blog_id');
            ACMS_Filter::blogTree($BlogSub, $this->bid, $this->blogAxis());

            if ( 'on' === $this->_config['secret'] ) {
                ACMS_Filter::blogDisclosureSecretStatus($BlogSub);
            } else {
                ACMS_Filter::blogStatus($BlogSub);
            }
        }

        if ( !empty($this->cid) ) {
            $CategorySub = SQL::newSelect('category');
            $CategorySub->setSelect('category_id');
            ACMS_Filter::categoryTree($CategorySub, $this->cid, $this->categoryAxis());
            ACMS_Filter::categoryStatus($CategorySub);
        }
        
        if ( $uid = intval($this->uid) ) {
            $SQL->addWhereOpr('entry_user_id', $this->uid);
        }

        if ( !empty($this->eid) ) {
            $SQL->addWhereOpr('entry_id', $this->eid);
        }
        ACMS_Filter::entrySpan($SQL, $this->start, $this->end);
        ACMS_Filter::entrySession($SQL);

        if ( !empty($this->tags) ) {
            ACMS_Filter::entryTag($SQL, $this->tags);
        }
        if ( !empty($this->keyword) ) {
            ACMS_Filter::entryKeyword($SQL, $this->keyword);
        }
        if ( !empty($this->Field) ) {
            ACMS_Filter::entryField($SQL, $this->Field);
        }
        if ( 'on' == $this->_config['indexing'] ) {
            $SQL->addWhereOpr('entry_indexing', 'on');
        }
        if ( 'on' <> $this->_config['noimage'] ) {
            $SQL->addWhereOpr('entry_primary_image', null, '<>');
        }

        //-------------------------
        // filter (blog, category) 
        if ( $BlogSub ) {
            $SQL->addWhereIn('entry_blog_id', $DB->subQuery($BlogSub));
        }
        if ( $CategorySub ) {
            $SQL->addWhereIn('entry_category_id', $DB->subQuery($CategorySub));
        } else if ( empty($this->cid) and null !== $this->cid ) {
            $SQL->addWhereOpr('entry_category_id', null);
        }
        
        $limit  = $this->_config['limit'];
        $offset = intval($this->_config['offset']);
        if ( 1 > $limit ) return '';

        $sortFd = ACMS_Filter::entryOrder($SQL, $this->_config['order'], $this->uid, $this->cid);
        $SQL->setLimit($limit, $offset);

        if ( !empty($sortFd) ) {
            $SQL->setGroup($sortFd);
        }
        $SQL->addGroup('entry_id');

        $q  = $SQL->get(dsn());

        $all    = $DB->query($q, 'all');
        if ( empty($all) ) {
            if ( 'on' == $this->_config['notfound'] ) {
                $Tpl->add('notFound');
            }
            return false;
        }
        $this->_endGluePoint = count($all);
        
        return $q;
    }
    
    function buildUnit($eRow, &$Tpl, $cid, $level, $count = 0)
    {
        $this->buildSummary($Tpl, $eRow, $count, $this->_endGluePoint, $this->_config);
    }
}
