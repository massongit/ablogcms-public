<?php
/**
 * ACMS_GET_Admin_Schedule_Labels
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Schedule_Labels extends ACMS_GET_Admin
{
    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $scid   = $this->Get->get('scid');

        $labels     = configArray('schedule_label@'.$scid);
        $takeover   = $this->Post->getChild('schedule');
        $isNull     = $takeover->listFields();
        $add  = 3;
        $sort = 0;
        $max  = count($labels) + 1 + $add;

        if ( is_array($labels) && !empty($labels) ) {

            foreach ( $labels as $sort => $label ) {
                $sort++;
                $_label = explode(config('schedule_label_separator'), $label);

                for ( $i=1; $i < $max; $i++ ) {
                    $vars   = array('i' => $i);
                    if ( $i == $sort ) $vars['selected'] = config('attr_selected');
                    $Tpl->add(array('sort:loop', 'label:loop'), $vars);
                }

                $Tpl->add('label:loop', array(
                    'sort'  => $sort,
                    'name'  => $_label[0],
                    'key'   => $_label[1],
                    'class' => @$_label[2],
                    )
                );
            }

            for ( $n=0; $n<$add; $n++ ) {
                $sort++;
                for ( $i=1; $i < $max; $i++ ) {
                    $vars   = array('i' => $i);
                    if ( $i == $sort ) $vars['selected'] = config('attr_selected');
                    $Tpl->add(array('sort:loop', 'label:loop'), $vars);
                }
                $Tpl->add('label:loop');
            }
        } else if ( $this->Get->get('edit') == 'update' ) {
            for ( $n=0; $n<$add; $n++ ) {
                $sort++;
                for ( $i=1; $i < $max; $i++ ) {
                    $vars   = array('i' => $i);
                    if ( $i == $sort ) $vars['selected'] = config('attr_selected');
                    $Tpl->add(array('sort:loop', 'label:loop'), $vars);
                }
                $Tpl->add('label:loop');
            }
        } else {
            $Tpl->add('notFound');
        }

        return $Tpl->get();
    }
}