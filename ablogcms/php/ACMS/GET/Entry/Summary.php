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
    protected $config;
    protected $entries;
    protected $amount;
    protected $eids;

    protected $blogSubQuery;
    protected $categorySubQuery;

    var $_axis = array(
        'bid'   => 'self',
        'cid'   => 'self',
    );

    /**
     * コンフィグの取得
     *
     * @return array
     */
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
            'simplePagerOn'         => config('entry_summary_simple_pager_on'),
            'mainImageOn'           => config('entry_summary_image_on'),
            'detailDateOn'          => config('entry_summary_date'),
            'fullTextOn'            => config('entry_summary_fulltext'),
            'fulltextWidth'         => config('entry_summary_fulltext_width'),
            'fulltextMarker'        => config('entry_summary_fulltext_marker'),
            'tagOn'                 => config('entry_summary_tag'),
            'hiddenCurrentEntry'    => config('entry_summary_hidden_current_entry'),
            'loop_class'            => config('entry_summary_loop_class'),
            'relational'            => config('entry_summary_relational'),
        );
    }

    /**
     * 起動
     *
     * @return string
     */
    function get()
    {
        if ( !$this->setConfig() ) return '';

        $DB = DB::singleton(dsn());
        $Tpl = new Template($this->tpl, new ACMS_Corrector());

        $this->setRelational();
        $this->buildModuleField($Tpl);

        $SQL = $this->buildQuery();
        $this->entries = $DB->query($SQL->get(dsn()), 'all');

        $this->buildSimplePager($Tpl);
        $this->buildEntries($Tpl);
        if ( $this->buildNotFound($Tpl) ) {
            return $Tpl->get();
        }
        if ( empty($this->entries) ) {
            return '';
        }
        $vars = $this->getRootVars();
        $vars += $this->buildFullspecPager($Tpl);
        $Tpl->add(null, $vars);

        return $Tpl->get();
    }

    /**
     * sqlの組み立て
     *
     * @return SQL_Select
     */
    function buildQuery()
    {
        $SQL = SQL::newSelect('entry');

        $SQL->addLeftJoin('blog', 'blog_id', 'entry_blog_id');
        $SQL->addLeftJoin('category', 'category_id', 'entry_category_id');

        $this->filterQuery($SQL);
        $this->setAmount($SQL); // limitする前のクエリから全件取得のクエリを準備しておく
        $this->orderQuery($SQL);
        $this->limitQuery($SQL);

        return $SQL;
    }

    /**
     * orderクエリ組み立て
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function orderQuery(& $SQL)
    {
        if ( 1
            and isset($config['relational']) && $config['relational'] === 'on'
            and count($this->eids) > 0
        ) {
            $SQL->setFieldOrder('entry_id', $this->eids);
            return;
        }
        if ( $sortFd = ACMS_Filter::entryOrder($SQL, $this->config['order'], $this->uid, $this->cid) ) {
            $SQL->setGroup($sortFd);
        }
        $SQL->addGroup('entry_id');
    }

    /**
     * エントリー数取得sqlの準備
     *
     * @param SQL_Select $SQL
     * @return void
     */
    function setAmount($SQL)
    {
        $this->amount = new SQL_Select($SQL);
        $this->amount->setSelect('DISTINCT(entry_id)', 'entry_amount', null, 'COUNT');
    }

    /**
     * limitクエリ組み立て
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function limitQuery(& $SQL)
    {
        $from   = ($this->page - 1) * $this->config['limit'] + $this->config['offset'];
        $limit  = $this->config['limit'] + 1;

        $SQL->setLimit($limit, $from);
    }

    /**
     * 絞り込みクエリ組み立て
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function filterQuery(& $SQL)
    {
        ACMS_Filter::entrySpan($SQL, $this->start, $this->end);
        ACMS_Filter::entrySession($SQL);

        $this->relationalFilterQuery($SQL);

        $multi = false;
        $multi = $multi || $this->categoryFilterQuery($SQL);
        $multi = $multi || $this->userFilterQuery($SQL);
        $multi = $multi || $this->entryFilterQuery($SQL);
        $this->blogFilterQuery($SQL, $multi);

        $this->tagFilterQuery($SQL);
        $this->keywordFilterQuery($SQL);
        $this->fieldFilterQuery($SQL);

        $this->filterSubQuery($SQL);
        $this->otherFilterQuery($SQL);
    }

    /**
     * 関連エントリーの絞り込み
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function relationalFilterQuery(& $SQL)
    {
        if ( isset($this->config['relational']) && $this->config['relational'] === 'on' ) {
            $SQL->addWhereIn('entry_id', $this->eids);
        }
    }

    /**
     * カテゴリーの絞り込み
     *
     * @param SQL_Select & $SQL
     * @return bool
     */
    function categoryFilterQuery(& $SQL)
    {
        $multi = false;
        if ( !empty($this->cid) ) {
            $this->categorySubQuery = SQL::newSelect('category');
            $this->categorySubQuery->setSelect('category_id');
            if ( is_int($this->cid) ) {
                ACMS_Filter::categoryTree($this->categorySubQuery, $this->cid, $this->categoryAxis());
            } else if ( strpos($this->cid, ',') !== false ) {
                $this->categorySubQuery->addWhereIn('category_id', explode(',', $this->cid));
                $multi = true;
            }
            ACMS_Filter::categoryStatus($this->categorySubQuery);
        } else {
            ACMS_Filter::categoryStatus($SQL);
        }
        return $multi;
    }

    /**
     * ユーザーの絞り込み
     *
     * @param SQL_Select & $SQL
     * @return bool
     */
    function userFilterQuery(& $SQL)
    {
        $multi = false;
        if ( !empty($this->uid) ) {
            if ( is_int($this->uid) ) {
                $SQL->addWhereOpr('entry_user_id', $this->uid);
            } else if ( strpos($this->uid, ',') !== false ) {
                $SQL->addWhereIn('entry_user_id', explode(',', $this->uid));
                $multi = true;
            }
        }
        return $multi;
    }

    /**
     * エントリーの絞り込み
     *
     * @param SQL_Select & $SQL
     * @return bool
     */
    function entryFilterQuery(& $SQL)
    {
        $multi = false;
        if ( !empty($this->eid) ) {
            if ( is_int($this->eid) ) {
                $SQL->addWhereOpr('entry_id', $this->eid);
            } else if ( strpos($this->eid, ',') !== false ) {
                $SQL->addWhereIn('entry_id', explode(',', $this->eid));
                $multi = true;
            }
        }
        return $multi;
    }

    /**
     * ブログの絞り込み
     *
     * @param SQL_Select & $SQL
     * @param bool $multi
     * @return void
     */
    function blogFilterQuery(& $SQL, $multi)
    {
        if ( !empty($this->bid) ) {
            $this->blogSubQuery = SQL::newSelect('blog');
            $this->blogSubQuery->setSelect('blog_id');
            if ( is_int($this->bid) ) {
                if ( $multi ) {
                    ACMS_Filter::blogTree($this->blogSubQuery, $this->bid, 'descendant-or-self');
                } else {
                    ACMS_Filter::blogTree($this->blogSubQuery, $this->bid, $this->blogAxis());
                }
            } else if ( strpos($this->bid, ',') !== false ) {
                $this->blogSubQuery->addWhereIn('blog_id', explode(',', $this->bid));
            }
            if ( 'on' === $this->config['secret'] ) {
                ACMS_Filter::blogDisclosureSecretStatus($this->blogSubQuery);
            } else {
                ACMS_Filter::blogStatus($this->blogSubQuery);
            }
        } else {
            ACMS_Filter::blogStatus($SQL);
        }
    }

    /**
     * タグの絞り込み
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function tagFilterQuery(& $SQL)
    {
        if ( !empty($this->tags) ) {
            ACMS_Filter::entryTag($SQL, $this->tags);
        }
    }

    /**
     * キーワードの絞り込み
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function keywordFilterQuery(& $SQL)
    {
        if ( !empty($this->keyword) ) {
            ACMS_Filter::entryKeyword($SQL, $this->keyword);
        }
    }

    /**
     * フィールドの絞り込み
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function fieldFilterQuery(& $SQL)
    {
        if ( !$this->Field->isNull() ) {
            ACMS_Filter::entryField($SQL, $this->Field);
        }
    }

    /**
     * サブクエリの組み立て
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function filterSubQuery(& $SQL)
    {
        $DB = DB::singleton(dsn());
        if ( $this->blogSubQuery ) {
            $SQL->addWhereIn('entry_blog_id', $DB->subQuery($this->blogSubQuery));
        }
        if ( $this->categorySubQuery ) {
            $SQL->addWhereIn('entry_category_id', $DB->subQuery($this->categorySubQuery));
        } else if ( empty($this->cid) and null !== $this->cid ) {
            $SQL->addWhereOpr('entry_category_id', null);
        }
    }

    /**
     * その他の絞り込み
     *
     * @param SQL_Select & $SQL
     * @return void
     */
    function otherFilterQuery(& $SQL)
    {
        if ( 'on' === $this->config['indexing'] ) {
            $SQL->addWhereOpr('entry_indexing', 'on');
        }
        if ( 'on' <> $this->config['noimage'] ) {
            $SQL->addWhereOpr('entry_primary_image', null, '<>');
        }
        if ( !!$this->eid && 'on' === $this->config['hiddenCurrentEntry'] ) {
            $SQL->addWhereOpr('entry_id', $this->eid, '<>');
        }
    }

    /**
     * 関連エントリーの取得
     *
     * @return bool
     */
    function setRelational()
    {
        if ( isset($this->config['relational']) && $this->config['relational'] === 'on' ) {
            if ( !EID ) return false;
            $this->eids = loadRelatedEntries(EID);
        }
        return true;
    }

    /**
     * シンプルページャーの組み立て
     *
     * @param Template & $Tpl
     * @return void
     */
    function buildSimplePager(& $Tpl)
    {
        $next_page = false;
        if ( count($this->entries) > $this->config['limit'] ) {
            array_pop($this->entries);
            $next_page = true;
        }
        if ( !isset($this->config['simplePagerOn']) || $this->config['simplePagerOn'] !== 'on' ) {
            return;
        }
        // prev page
        if ( $this->page > 1 ) {
            $Tpl->add('prevPage', array(
                'url'    => acmsLink(array(
                    'page' => $this->page - 1,
                ), true),
            ));
        } else {
            $Tpl->add('prevPageNotFound');
        }
        // next page
        if ( $next_page ) {
            $Tpl->add('nextPage', array(
                'url'    => acmsLink(array(
                    'page' => $this->page + 1,
                ), true),
            ));
        } else {
            $Tpl->add('nextPageNotFound');
        }
    }

    /**
     * コンフィグのセット
     *
     * @return bool
     */
    function setConfig()
    {
        $this->config = $this->initVars();
        if ( $this->config === false ) {
            return false;
        }
        return true;
    }

    /**
     * フルスペックページャーの組み立て
     *
     * @param Template & $Tpl
     * @return array
     */
    function buildEntries(& $Tpl)
    {
        $gluePoint = count($this->entries);
        foreach ( $this->entries as $i => $row ) {
            $i++;
            $this->buildSummary($Tpl, $row, $i, $gluePoint, $this->config);
        }
    }

    /**
     * NotFound時のテンプレート組み立て
     *
     * @param Template & $Tpl
     * @return bool
     */
    function buildNotFound(& $Tpl)
    {
        if ( !empty($this->entries) ) return false;
        if ( 'on' !== $this->config['notfound'] ) return false;

        $Tpl->add('notFound');
        $Tpl->add(null, $this->getRootVars());
        if ( isset($this->config['notfoundStatus404']) && 'on' === $this->config['notfoundStatus404'] ) {
            httpStatusCode('404 Not Found');
        }
        return true;
    }

    /**
     * ルート変数の取得
     *
     * @return array
     */
    function getRootVars()
    {
        $blogName   = ACMS_RAM::blogName($this->bid);
        $vars = array(
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
        return $vars;
    }

    /**
     * フルスペックページャーの組み立て
     *
     * @param Template & $Tpl
     * @return array
     */
    function buildFullspecPager(& $Tpl)
    {
        $vars = array();
        if ( 'random' === $this->config['order'] ) {
            return $vars;
        }
        if ( !isset($this->config['pagerOn']) || $this->config['pagerOn'] !== 'on' ) {
            return $vars;
        }
        $DB = DB::singleton(dsn());
        $itemsAmount = intval($DB->query($this->amount->get(dsn()), 'one'));
        $itemsAmount -= $this->config['offset'];
        $vars += $this->buildPager($this->page, $this->config['limit'], $itemsAmount, $this->config['pagerDelta'], $this->config['pagerCurAttr'], $Tpl);

        return $vars;
    }
}