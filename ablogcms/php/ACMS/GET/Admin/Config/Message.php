<?php
/**
 * ACMS_GET_Admin_Config_Message
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Config_Message extends ACMS_GET
{
    function get()
    {
        $Tpl = new Template($this->tpl, new ACMS_Corrector());
        $config = $this->Post->get('config');
        
        if ( !empty($config) ) {
            $Tpl->add(null, array(
                'config_name' => $config,
            ));
        }
        
        return $Tpl->get();
    }
}
