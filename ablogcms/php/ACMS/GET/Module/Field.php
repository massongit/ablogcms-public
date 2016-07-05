<?php
/**
 * ACMS_GET_Module_Field
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Module_Field extends ACMS_GET
{
    function get()
    {
        if ( !$this->mid ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $vars = $this->buildField(loadModuleField($this->mid), $Tpl);
        $Tpl->add(null, $vars);

        return $Tpl->get();
    }
}
