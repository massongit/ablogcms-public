<?php
/**
 * ACMS_GET_Admin_Rule_Name
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Rule_Name extends ACMS_GET
{
    function get()
    {
        if ( !$rid = idval($this->Get->get('rid')) ) return '';
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('rule');
        $SQL->setSelect('rule_name');
        $SQL->addWhereOpr('rule_id', $rid);
        if ( !$name = $DB->query($SQL->get(dsn()), 'one') ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $Tpl->add(null, array('rid' => $rid, 'name' => $name));
        return $Tpl->get();
    }
}
