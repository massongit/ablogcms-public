<?php
/**
 * ACMS_GET_Admin_Form_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Form_Edit extends ACMS_GET_Admin_Edit
{
    function edit(& $Tpl)
    {
        $Form   =& $this->Post->getChild('form');

        if ( empty($this->step) and ($fmid = intval($this->Get->get('fmid'))) ) {
            $DB     = DB::singleton(dsn());
            $SQL    = SQL::newSelect('form');
            $SQL->addWhereOpr('form_id', $fmid);
            if ( $row = $DB->query($SQL->get(dsn()), 'row') ) {
                $Form->set('code', $row['form_code']);
                $Form->set('name', $row['form_name']);
                $Form->set('scope', $row['form_scope']);
                $Form->overload(unserialize($row['form_data']), true);
            }
        }

        $Mail   = $Form->getChild('mail');
        if ( !$Mail->isNull() ) {
            $vars   = array();
            foreach ( $Mail->listFields() as $fd ) {
                $vars[$fd]  = join(', ', $Mail->getArray($fd));
                
                // 2013/01/21
                if( $fd == 'AdminAttachment' && $Mail->get($fd) == 'on' ){
                    $vars[ $fd . ":checked#" . $Mail->get($fd) ]  = config('attr_checked');
                    $Tpl->add(array($fd . ":checked#" . $Mail->get($fd), 'mail'), NULL);
                }
            }
            $Tpl->add(array('mail'), $vars);
        } else {
            $vars   = array(
                'Charset'       => 'ISO-2022-JP',
                'CharsetHTML'   => 'UTF-8',
            );
            $Tpl->add(array('mail'), $vars);
        }
        $Option = $Form->getChild('option');

        if ( !$Option->isNull() ) {
            foreach ( $Option->getArray('field') as $i => $fd ) {

                if ( empty($fd) ) continue;
                if ( !$method = $Option->get('method', '', $i) ) continue;

                $value  = $Option->get('value', '', $i);
                $Tpl->add(array('method:touch#'.$method));
                $Tpl->add(array('option:loop'), array(
                    'field'     => $fd,
                    'value'     => $value,
                    'method:selected#'.$method  => config('attr_selected'),
                ));
            }
        }

        for ( $i=0; $i<3; $i++ ) {
            $Tpl->add(array('option:loop'));
        }

        return true;
    }
}
