<?php
/**
 * ACMS_GET_User_Search
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_User_Search extends ACMS_GET
{
    var $_scope = array(
        'uid'   => 'global',
        'field' => 'global',
        'page'  => 'global',
    );

    function get ( )
    {
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('user');
        $SQL->addWhereOpr('user_pass', '', '<>');
        $SQL->addLeftJoin('blog', 'blog_id', 'user_blog_id');

        // blog axis
        ACMS_Filter::blogTree($SQL, $this->bid, $this->blogAxis());
        ACMS_Filter::blogStatus($SQL);

        // field
        if ( !empty($this->Field) ) {
            ACMS_Filter::userField($SQL, $this->Field);
        }

        // keyword
        if ( !empty($this->keyword) ) {
            ACMS_Filter::userKeyword($SQL, $this->keyword);
        }

        // indexing
        if ( 'on' === config('user_search_indexing') ) {
            $SQL->addWhereOpr('user_indexing', 'on');
        }

        // auth
        if ( configArray('user_search_auth') ) {
            $SQL->addWhereIn('user_auth', configArray('user_search_auth'));
        }

        // status 2013/02/08 
        if ( configArray('user_search_status') ) {
            $SQL->addWhereIn('user_status', configArray('user_search_status'));
        }

        // mail_magazine 2013/02/08 
        if ( $ary_mailmagazine = configArray('user_search_mail_magazine') ) {
			if( is_array( $ary_mailmagazine ) && count( $ary_mailmagazine ) > 0 ){
				foreach( $ary_mailmagazine as $key_mailmagazine => $val_mailmagazine ){
					switch( $val_mailmagazine ){
						case 'pc':
							$SQL->addWhereOpr('user_mail_magazine', 'on');
							$SQL->addWhereOpr('user_mail', '', '<>');
							break;
						case 'mobile':
							$SQL->addWhereOpr('user_mail_mobile_magazine', 'on');
							$SQL->addWhereOpr('user_mail_mobile', '', '<>');
							break;
					}
				}
			}
        }

        // uid
        if ( $uid = intval($this->uid) ) {
            $SQL->addWhereOpr('user_id', $uid);
            $SQL->setLimit(1);
        }

        // amount
        $Amount = new SQL_Select($SQL);
        $Amount->setSelect('*', 'user_amount', null, 'count');
        $itemsAmount    = intval($DB->query($Amount->get(dsn()), 'one'));

        // tpl
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        // no data
        if ( empty($itemsAmount) ) {
            $Tpl->add('notFound');
            return $Tpl->get();
        }

        // order, limit
        if ( empty($uid) ) {
            ACMS_Filter::userOrder($SQL, config('user_search_order'));
            $limit  = intval(config('user_search_limit'));
            $from   = ($this->page - 1) * $limit;
            $SQL->setLimit($limit, $from);
        }

        //-----------
        // user:loop
        $q      = $SQL->get(dsn());
        foreach ( $DB->query($q, 'all') as $i => $row ) {
            unset($row['user_pass']);
            unset($row['user_pass_reset']);
            unset($row['user_generated_datetime']);

            $vars   = $this->buildField(loadUserField(intval($row['user_id'])), $Tpl);
            $vars['i']  = $i;
            foreach ( $row as $key => $value ) {
                if ( strpos($key, 'user_') !== 0 ) continue;
                $vars[substr($key, strlen('user_'))]    = $value;
            }
            $uid = intval($row['user_id']);
            $vars['icon']       = loadUserIcon($uid);
            if ( $large = loadUserLargeIcon($uid) ) {
                $vars['largeIcon']  = $large;
            }

            $Tpl->add('user:loop', $vars);
        }

        // pager
        if ( empty($uid) and 'random' <> config('user_search_order') ) {
            $Tpl->add(null, $this->buildPager($this->page, 
                config('user_search_limit'), $itemsAmount, 
                config('user_search_pager_delta'), 
                config('user_search_pager_cur_attr'), $Tpl)
            );
        }

        return $Tpl->get();
    }
}
