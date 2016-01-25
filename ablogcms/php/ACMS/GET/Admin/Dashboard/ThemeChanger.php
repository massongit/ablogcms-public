<?php
/**
 * ACMS_GET_Admin_Dashboard_ThemeChanger
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Dashboard_ThemeChanger extends ACMS_GET
{
    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $vars   = array();
        
        $thmPath = SCRIPT_DIR.THEMES_DIR;
        $curThm = config('theme');
        
        if ( is_dir($thmPath) ) {
            $dh = opendir($thmPath);
            while (false != ($dir = readdir($dh))) {
                $vars = array('theme' => $dir, 'label' => $dir);
                
                if (!is_dir($thmPath.$dir)) {
                    continue;
                } elseif ($dir == 'system') {
                    continue;
                } elseif ($dir == '.' || $dir == '..') {
                    continue;
                } elseif ($dir == $curThm) {
                    $vars['selected'] = 'selected="selected"';
                }
                
                $Tpl->add('theme:loop',$vars);
            }
        }
        
        return $Tpl->get();
    }
}