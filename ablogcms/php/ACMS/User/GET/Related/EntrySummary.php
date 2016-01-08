<?php
/**
 * ACMS_User_GET_Related_EntrySummary
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_User_GET_Related_EntrySummary extends ACMS_GET_Entry_Summary
{
    var $_axis = array(
        'bid'   => 'self',
        'cid'   => 'self',
    );

    var $_scope = array(
        'eid'       => 'global',
    );

    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        
        if ( !$this->eid ) {
            $Tpl->add('notFound');
            return $Tpl->get();
        }

        $config = $this->initVars();

        $this->initVars();

        $DB     = DB::singleton(dsn());
        $this->buildModuleField($Tpl);

        $SQL    = SQL::newSelect('entry');

        $SQL->addLeftJoin('blog', 'blog_id', 'entry_blog_id');
        $SQL->addLeftJoin('category', 'category_id', 'entry_category_id');

        ACMS_Filter::entrySpan($SQL, $this->start, $this->end);
        ACMS_Filter::entrySession($SQL);

        //----------------------
        // get related articles
        $Field = loadEntryField($this->eid);
        $relatedEids = $Field->getArray('relatedEids');

        if ( empty($relatedEids) ) {
            $Tpl->add('notFound');
            return $Tpl->get();
        }
        $SQL->addWhereIn('entry_id', $relatedEids);
        $SQL->addWhereOpr('entry_indexing', 'on');

        $SQL->setLimit(999);
        $SQL->setGroup('entry_id');
        $SQL->setFieldOrder('entry_id', $relatedEids);

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
            $Tpl->add(null, $vars);
            return $Tpl->get();
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

        $Tpl->add(null, $vars);

        return $Tpl->get();
    }
}