<?php
/**
 * ACMS_GET_Entry_Summary
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Entry_Summary extends ACMS_GET_Entry
{
    var $_axis = array(
        'bid'   => 'self',
        'cid'   => 'self',
    );

    function initVars()
    {
        return array(
            'order'                 => $this->order ? $this->order : config('entry_summary_order'),
            'limit'                 => intval(config('entry_summary_limit')),
            'offset'                => intval(config('entry_summary_offset')),
            'indexing'              => config('entry_summary_indexing'),
            'secret'                => config('entry_summary_secret'),
            'notfound'              => config('mo_entry_summary_notfound'),
            'notfoundStatus404'     => config('entry_summary_notfound_status_404'),
            'noimage'               => config('entry_summary_noimage'),
            'pagerDelta'            => config('entry_summary_pager_delta'),
            'pagerCurAttr'          => config('entry_summary_pager_cur_attr'),

            'unit'                  => config('entry_summary_unit'),
            'newtime'               => config('entry_summary_newtime'),
            'imageX'                => intval(config('entry_summary_image_x')),
            'imageY'                => intval(config('entry_summary_image_y')),
            'imageTrim'             => config('entry_summary_image_trim'),
            'imageZoom'             => config('entry_summary_image_zoom'),
            'imageCenter'           => config('entry_summary_image_center'),

            'entryFieldOn'          => config('entry_summary_entry_field'),
            'categoryInfoOn'        => config('entry_summary_category_on'),
            'categoryFieldOn'       => config('entry_summary_category_field_on'),
            'userInfoOn'            => config('entry_summary_user_on'),
            'userFieldOn'           => config('entry_summary_user_field_on'),
            'blogInfoOn'            => config('entry_summary_blog_on'),
            'blogFieldOn'           => config('entry_summary_blog_field_on'),
            'pagerOn'               => config('entry_summary_pager_on'),
            'mainImageOn'           => config('entry_summary_image_on'),
            'detailDateOn'          => config('entry_summary_date'),
            'fullTextOn'            => config('entry_summary_fulltext'),
            'fulltextWidth'         => config('entry_summary_fulltext_width'),
            'fulltextMarker'        => config('entry_summary_fulltext_marker'),
            'tagOn'                 => config('entry_summary_tag'),
            'hiddenCurrentEntry'    => config('entry_summary_hidden_current_entry'),
            'loop_class'            => config('entry_summary_loop_class'),
        );
    }

    function get()
    {
        $config = $this->initVars();

        $order  = $config['order'];
        $this->initVars();
        if ( !empty($order) ) { $config['order'] = $order; }

        $DB     = DB::singleton(dsn());
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $this->buildModuleField($Tpl);

        $SQL    = SQL::newSelect('entry');

        $BlogSub        = null;
        $CategorySub    = null;

        $SQL->addLeftJoin('blog', 'blog_id', 'entry_blog_id');
        $SQL->addLeftJoin('category', 'category_id', 'entry_category_id');

        ACMS_Filter::entrySpan($SQL, $this->start, $this->end);
        ACMS_Filter::entrySession($SQL);
        
        $multiId = false;
        if ( !empty($this->cid) ) {
            $CategorySub = SQL::newSelect('category');
            $CategorySub->setSelect('category_id');
            if ( is_int($this->cid) ) {
                ACMS_Filter::categoryTree($CategorySub, $this->cid, $this->categoryAxis());
            } else if ( strpos($this->cid, ',') !== false ) {
                $CategorySub->addWhereIn('category_id', explode(',', $this->cid));
                $multiId = true;
            }
            ACMS_Filter::categoryStatus($CategorySub);
        }

        if ( !empty($this->uid) ) {
            if ( is_int($this->uid) ) {
                $SQL->addWhereOpr('entry_user_id', $this->uid);
            } else if ( strpos($this->uid, ',') !== false ) {
                $SQL->addWhereIn('entry_user_id', explode(',', $this->uid));
                $multiId = true;
            }
        }

         if ( !empty($this->eid) ) {
            if ( is_int($this->eid) ) {
                $SQL->addWhereOpr('entry_id', $this->eid);
            } else if ( strpos($this->eid, ',') !== false ) {
                $SQL->addWhereIn('entry_id', explode(',', $this->eid));
                $multiId = true;
            }
        }

        if ( !empty($this->bid) ) {
            $BlogSub = SQL::newSelect('blog');
            $BlogSub->setSelect('blog_id');
            if ( is_int($this->bid) ) {
                if ( $multiId ) {
                    ACMS_Filter::blogTree($BlogSub, $this->bid, 'descendant-or-self');
                } else {
                    ACMS_Filter::blogTree($BlogSub, $this->bid, $this->blogAxis());
                }
            } else if ( strpos($this->bid, ',') !== false ) {
                $BlogSub->addWhereIn('blog_id', explode(',', $this->bid));
            }
            if ( 'on' === $config['secret'] ) {
                ACMS_Filter::blogDisclosureSecretStatus($BlogSub);
            } else {
                ACMS_Filter::blogStatus($BlogSub);
            }
        }

        if ( !empty($this->tags) ) {
            ACMS_Filter::entryTag($SQL, $this->tags);
        }
        if ( !empty($this->keyword) ) {
            ACMS_Filter::entryKeyword($SQL, $this->keyword);
        }
        if ( !$this->Field->isNull() ) {
            ACMS_Filter::entryField($SQL, $this->Field);
        }

        if ( 'on' === $config['indexing'] ) {
            $SQL->addWhereOpr('entry_indexing', 'on');
        }
        if ( 'on' <> $config['noimage'] ) {
            $SQL->addWhereOpr('entry_primary_image', null, '<>');
        }
        if ( !!EID && 'on' === $config['hiddenCurrentEntry'] ) {
            $SQL->addWhereOpr('entry_id', EID, '<>');
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

        $Amount = new SQL_Select($SQL);
        $Amount->setSelect('*', 'entry_amount', null, 'count');

        $from   = ($this->page - 1) * $config['limit'] + $config['offset'];
        $limit  = $config['limit'];

        $sortFd = ACMS_Filter::entryOrder($SQL, $config['order'], $this->uid, $this->cid);
        $SQL->setLimit($limit, $from);

        if ( !empty($sortFd) ) {
            $SQL->setGroup($sortFd);
        }
        $SQL->addGroup('entry_id');

        $q      = $SQL->get(dsn());
        $all    = $DB->query($q, 'all');

        //------------------
        // build summary tpl
        $gluePoint = count($all);
        foreach ( $all as $i => $row ) {
            $i++;
            $this->buildSummary($Tpl, $row, $i, $gluePoint, $config);
        }

        if ( empty($all) ) {
            if ( 'on' == $config['notfound'] ) {
                $Tpl->add('notFound');
                $blogName   = ACMS_RAM::blogName($this->bid);
                $vars   = array(
                    'indexUrl'  => acmsLink(array( 
                        'bid'   => $this->bid,
                        'cid'   => $this->cid,
                    )),
                    'indexBlogName' => $blogName,
                    'blogName'      => $blogName,
                    'blogCode'      => ACMS_RAM::blogCode($this->bid),
                    'blogUrl'       => acmsLink(array(
                        'bid'   => $this->bid,
                    )),
                );
                if ( !empty($this->cid) ) {
                    $categoryName   = ACMS_RAM::categoryName($this->cid);
                    $vars['indexCategoryName']  = $categoryName;
                    $vars['categoryName']       = $categoryName;
                    $vars['categoryCode']       = ACMS_RAM::categoryCode($this->cid);
                    $vars['categoryUrl']        = acmsLink(array(
                        'bid'   => $this->bid,
                        'cid'   => $this->cid,
                    ));
                }
                $Tpl->add(null, $vars);
                if ( 'on' == $config['notfoundStatus404'] ) {
                    httpStatusCode('404 Not Found');
                }
                return $Tpl->get();
            } else {
                return false;
            }
        }

        $blogName   = ACMS_RAM::blogName($this->bid);
        $vars   = array(
            'indexUrl'  => acmsLink(array(
                'bid'   => $this->bid,
                'cid'   => $this->cid,
            )),
            'indexBlogName' => $blogName,
            'blogName'      => $blogName,
            'blogCode'      => ACMS_RAM::blogCode($this->bid),
            'blogUrl'       => acmsLink(array(
                'bid'   => $this->bid,
            )),
        );
        if ( !empty($this->cid) ) {
            $categoryName   = ACMS_RAM::categoryName($this->cid);
            $vars['indexCategoryName']  = $categoryName;
            $vars['categoryName']       = $categoryName;
            $vars['categoryCode']       = ACMS_RAM::categoryCode($this->cid);
            $vars['categoryUrl']        = acmsLink(array(
                'bid'   => $this->bid,
                'cid'   => $this->cid,
            ));
        }

        if ( 'random' <> $config['order'] ) {
            //-------
            // pager
            if ( isset($config['pagerOn']) and $config['pagerOn'] === 'on' ) {
                $itemsAmount = intval($DB->query($Amount->get(dsn()), 'one'));

                $itemsAmount -= $config['offset'];
                $vars += $this->buildPager($this->page, $config['limit'], $itemsAmount, $config['pagerDelta'], $config['pagerCurAttr'], $Tpl);
            }
        }

        $Tpl->add(null, $vars);

        return $Tpl->get();
    }
}