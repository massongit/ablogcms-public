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
            if ( isset($this->Post->_aryChild['field']) ) {
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
        if ( EID ) {
            $entry  = ACMS_RAM::entry(EID);
            $fmid   = $entry['entry_form_id'];

            $DB     = DB::singleton(dsn());
            $SQL    = SQL::newSelect('form');
            $SQL->addSelect('form_code');
            $SQL->addWhereOpr('form_id', $fmid);
            $SQL->addWhereOpr('form_blog_id', BID);
            $fcode  = $DB->query($SQL->get(dsn()), 'one');

            $this->Post->add('form_id', $fcode);
        }
        $Tpl->add($Block, $this->buildField($this->Post, $Tpl, $Block, ''));
        return $Tpl->get();
    }
}
