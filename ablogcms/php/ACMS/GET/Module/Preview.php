<?php
/**
 * ACMS_GET_Module_Preview
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Module_Preview extends ACMS_GET_Layout
{
    function get()
    {
        $DB     = DB::singleton(dsn());
        $mid    = $this->Get->get('mid');
        $tpl    = $this->Get->get('tpl');

        $SQL    = SQL::newSelect('module');
        $SQL->addSelect('module_id');
        $SQL->addSelect('module_identifier');
        $SQL->addSelect('module_name');
        $SQL->addWhereOpr('module_id', $mid);
        $module = $DB->query($SQL->get(dsn()), 'row');

        return $this->spreadModule($module['module_name'], $module['module_identifier'], $tpl);
    }
}
