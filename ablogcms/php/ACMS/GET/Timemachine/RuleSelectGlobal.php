<?php
/**
 * ACMS_GET_Timemachine_RuleSelectGlobal
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Timemachine_RuleSelectGlobal extends ACMS_GET
{
    function get()
    {
        if ( !timemachineMode() ) return false;

        $Tpl        = new Template($this->tpl, new ACMS_Corrector());
        $DB         = DB::singleton(dsn());
        $Session    =& Field::singleton('session');
        $rootVars   = array();
        $rule       = $Session->get('timemachine_rule');
        $rule       = intval($rule);

        $SQL    = SQL::newSelect('rule');
        $SQL->addLeftJoin('blog', 'blog_id', 'rule_blog_id');
        ACMS_Filter::blogTree($SQL, BID, 'ancestor-or-self');

        $Where  = SQL::newWhere();
        $Where->addWhereOpr('rule_blog_id', BID, '=', 'OR');
        $Where->addWhereOpr('rule_scope', 'global', '=', 'OR');
        $SQL->addWhere($Where);
        $SQL->addWhereOpr('rule_status', 'open');

        $SQL->setOrder('rule_sort');
        $all    = $DB->query($SQL->get(dsn()), 'all');

        $sort   = 1;
        while ( $row = array_shift($all) ) {
            $rid            = intval($row['rule_id']);
            $query['rid']   = $rid;
            $vars           = array(
                'rid'   => $rid,
                'label' => $row['rule_name'],
            );
            if ( $rid === intval($rule) ) {
                $vars['selected']   = config('attr_selected');
            }
            $Tpl->add('rule:loop', $vars);

            $sort++;
        }
        $Tpl->add(null, $rootVars);

        return $Tpl->get();
    }
}
