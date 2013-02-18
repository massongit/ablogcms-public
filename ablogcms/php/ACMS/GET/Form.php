<?php
/**
 * ACMS_GET_Form
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Form extends ACMS_GET
{
    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $step   = $this->Get->get('step');
        if ( $this->Post->isValidAll() ) {
            $step   = $this->Post->get('step', $step);
        } else {
            $Errors = array();
            $Field  = $this->Post->_aryChild['field'];
            foreach ( $Field->_aryV as $key => $val ) {
                foreach ( $val as $valid ) {
                    if ( 1
                        and isset($valid[0])
                        and $valid[0] === false
                    ) {
                        $Errors[]   = $key;
                    } 
                }
            }
            if ( !empty($Errors) ) {
                $Tpl->add('error', array(
                    'formID'    => $this->Post->get('id'),
                    'errorKey'  => implode(',', $Errors),
                ));
            }
        }
        $Block  = !(empty($step) or is_bool($step)) ? 'step#'.$step : 'step';
        $this->Post->delete('step');
        $Tpl->add($Block, $this->buildField($this->Post, $Tpl, $Block, ''));
        return $Tpl->get();
    }
}
