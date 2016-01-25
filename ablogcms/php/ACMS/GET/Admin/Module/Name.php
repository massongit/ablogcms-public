<?php
/**
 * ACMS_GET_Admin_Module_Name
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Module_Name extends ACMS_GET_Admin_Module
{
    function get()
    {
        if ( !$mid = idval($this->Get->get('mid')) ) return '';

        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('module');
        $SQL->addWhereOpr('module_id', $mid);
        $SQL->addWhereOpr('module_blog_id', BID);
        if ( !$row = $DB->query($SQL->get(dsn()), 'row') ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $Tpl->add(null, array(
            'mid'   => $mid,
            'name'  => $row['module_name'],
            'label' => $row['module_label'],
            'identifier'    => $row['module_identifier'],
        ));
        return $Tpl->get();
    }
}
