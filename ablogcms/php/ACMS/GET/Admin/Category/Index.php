<?php
/**
 * ACMS_GET_Admin_Category_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Category_Index extends ACMS_GET_Admin
{
    function get()
    {
        if ( roleAvailableUser() ) {
            if ( !roleAuthorization('category_edit', BID) ) return false;
        } else {
            if ( !sessionWithCompilation() ) return false;
        }

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $vars   = array();

        $layered = true;
        if ( 0
            || config('admin_category_layered') === 'on'
            || TPL === 'ajax/arg/cid-reference.html'
        ) {
            $layered = false;
        }

        $_cid   = $this->Get->get('_cid', 0);

        //---------
        // refresh
        if ( !$this->Post->isNull() ) {
            $Tpl->add('refresh');
            $vars['notice_mess'] = 'show';
        }

        //-------
        // order
        $order  = ORDER ? ORDER : 'sort-asc';
        $vars['order:selected#'.$order] = config('attr_selected');
        if ( $order === 'sort-asc' ) {
            $vars['sortable'] = 'on';
        } else {
            $vars['sortable'] = 'off';
        }

        //-----
        // bid
        $target_bid = $this->Get->get('_bid', BID);

        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('category', 'master');
        $SQL->addLeftJoin('blog', 'blog_id', 'category_blog_id');
        ACMS_Filter::blogTree($SQL, $target_bid, 'ancestor-or-self');

        $SQL->addSelect('category_id', null, 'master');
        $SQL->addSelect('category_name', null, 'master');
        $SQL->addSelect('category_parent', null, 'master');
        $SQL->addSelect('category_left', null, 'master');
        $SQL->addSelect('category_sort', null, 'master');
        $SQL->addSelect('category_code', null, 'master');
        $SQL->addSelect('category_status', null, 'master');
        $SQL->addSelect('category_scope', null, 'master');
        $SQL->addSelect('category_blog_id', null, 'master');

        if ( $layered ) {
            $SQL->addLeftJoin('category', 'category_parent', 'category_id', 'count', 'master');
            $SQL->addSelect('category_id', 'child', 'count', 'COUNT');
            $SQL->addWhereOpr('category_parent', $_cid, '=', 'AND', 'master');
            $SQL->addGroup('category_id', 'master');
        }

        $Where  = SQL::newWhere();
        $Where->addWhereOpr('category_blog_id', $target_bid, '=', 'OR', 'master');
        $Where->addWhereOpr('category_scope', 'global', '=', 'OR', 'master');
        $SQL->addWhere($Where);

        //--------
        // amount
        $Amount = new SQL_Select($SQL);
        $Amount->addLeftJoin('entry', 'entry_category_id', 'category_id', 'entry', 'master');
        $Amount->addSelect('entry_id', 'category_entry_amount', 'entry', 'COUNT');
        $Amount->addGroup('category_id', 'master');
        $Amount->addWhereOpr('entry_status', 'trash', '<>', 'AND', 'entry');
        $cnts = $DB->query($Amount->get(dsn()), 'all');

        foreach ( $cnts as $cnt ) {
            $total[$cnt['category_id']] = $cnt['category_entry_amount'];
        }
        ACMS_Filter::categoryOrder($SQL, $order);

        $q  = $SQL->get(dsn());
        if ( !$DB->query($q, 'fetch') or !($row = $DB->fetch($q)) ) {
            $Tpl->add('index#notFound');
            $vars['notice_mess'] = 'show';
        }

        $all    = array();
        $amount = array();
        $parent = array();
        $last   = array();
        do {
            $bid    = intval($row['category_blog_id']);
            $cid    = intval($row['category_id']);
            $pid    = intval($row['category_parent']);
            $all[$pid][]    = $row;
            $parent[$cid]   = $pid;
            $last[$pid]     = $cid;
            if ( !isset($amount[$bid][$pid]) ) $amount[$bid][$pid]  = 0;
            $amount[$bid][$pid]   += 1;
        } while ( $row = $DB->fetch($q) );

        $stack = array();
        if ( $layered ) {
            $stack  = isset($all[$_cid]) ? $all[$_cid] : array();
        } else {
            $stack  = $all[0];
        }
        unset($all[0]);
        $last   = array_flip($last);

        while ( $row = array_shift($stack) ) {
            $bid    = intval($row['category_blog_id']);
            $cid    = intval($row['category_id']);
            $pid    = intval($row['category_parent']);
            $sort   = intval($row['category_sort']);
            $Tpl->add('status#'.$row['category_status']);

            if ( BID !== intval($row['category_blog_id']) ) {
                $row['category_scope'] = 'parental';
                $disabled              = config('attr_disabled');
            } else {
                $disabled              = '';
            }

            for ( $i=1; $i<=$amount[$bid][$pid]; $i++ ) {

                $_vars  = array(
                    'value' => $i,
                    'label' => $i,
                );
                if ( $sort == $i ) $_vars['selected'] = config('attr_selected');
                $Tpl->add('sort:loop', $_vars);
            }

            $blocks = array();
            if ( !empty($parent[$cid]) ) {
                $blocks[]   = isset($last[$cid]) ? 'child#last' : 'child';
                $_pid   = $cid;
                while ( $_pid = $parent[$_pid] ) {
                    if ( empty($parent[$_pid]) ) break;
                    $blocks[]   = isset($last[$_pid]) ? 'descendant#last' : 'descendant';
                }
            }

            $level = 0;
            foreach ( array_reverse($blocks) as $block ) {
                $Tpl->add($block);
                $Tpl->add('indent:loop');
                $level++;
            }

            $Tpl->add('scope:touch#'.$row['category_scope']);

            $cvars  = array(
                'cid'       => $cid,
                'pcid'      => $pid,
                'name'      => $row['category_name'],
                'code'      => $row['category_code'],
                'scope'     => $row['category_scope'],
                'amount'    => empty($total[$cid]) ? 0 : $total[$cid],//$row['category_entry_amount'],
                'disabled'  => $disabled,
                'level'     => $level,
            );

            $cbid   = intval($row['category_blog_id']);
            if ( BID === $cbid ) {
                $Tpl->add('mine', array(
                    'itemLink'  => acmsLink(array(
                        'bid'   => BID,
                        'cid'   => $cid,
                        'admin' => 'category_edit',
                ))));
            } else if ( 0
                or ( roleAvailableUser() && roleAuthorization('category_edit', $cbid) )
                or sessionWithAdministration($cbid)
            ) {
                $Tpl->add('notMinePermit', array(
                    'itemLink'  => acmsLink(array(
                        'bid'   => $cbid,
                        'cid'   => $cid,
                        'admin' => 'category_edit',
                )))); 
            } else {
                $Tpl->add('notMine');
            }

            //-------
            // field
            $cvars  += $this->buildField(loadCategoryField($cid), $Tpl, 'category:loop');

            if ( 1
                && $layered
                && isset($row['child'])
                && $row['child'] > 0
            ) {
                $Tpl->add(array('childLink', 'category:loop'), array(
                    'parent_cid'    => $cid,
                    'pre_cid'       => $_cid,
                ));
            }
            $Tpl->add('category:loop', $cvars);

            if ( isset($all[$cid]) ) {
                while ( $_row = array_pop($all[$cid]) ) array_unshift($stack, $_row);
                unset($all[$cid]);
            }
        }

        //----------------------
        // category create auth
        if ( 0
            || !roleAvailableUser()
            || ( roleAvailableUser() && roleAuthorization('category_create', BID) )
        ) {
            $Tpl->add('action#categoryInsert');
        }

        $Tpl->add(null, $vars);
        return $Tpl->get();
    }
}

if ( roleAvailableUser() ) {
            if ( !roleAuthorization('category_edit', BID) ) return false;
        } else {
            if ( !sessionWithCompilation() ) return false;
        }
