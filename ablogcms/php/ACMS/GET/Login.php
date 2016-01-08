<?php
/**
 * ACMS_GET_Login
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Login extends ACMS_GET
{
    function get()
    {
        if ( SUID ) { return false; }

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $block  = ALT ? ALT : 'auth';
        $Login  =& $this->Post->getChild('login');

        //-----------
        // blog index
        if ( $Login->get('loginIndex') == 'yes' ) {
            $block  = 'select';
            $bidAry = $Login->getArray('bid');
            foreach ( $bidAry as $bid ) {
                $Tpl->add(array('selectBlog:loop', $block), array(
                    'bid'   => $bid,
                    'name'  => ACMS_RAM::blogName($bid),
                    'url'   => acmsLink(array(
                        'bid'       => $bid,
                        'sid'       => $Login->get('sid'),
                        '_protocol' => 'http'
                    ), false),
                ));
            }
        //-----------
        // subscribe
        } else if ( 'on' == config('subscribe') ) {
            $Tpl->add(array('subscribeLink', $block));
        } else {
            if ( 'subscribe' == ALT ) $block = 'auth';
        }

        $vars   = array();

        if ( $this->Post->isNull() ) {
            $Tpl->add(array('sendMsg#before', $block));
            $Tpl->add(array('submit', $block));
            $vars   += array(
                'mail'  => $this->Get->get('reset', $this->Get->get('subscribe')),
                'step'  => 'step',
            );
        } else {
            if ( $this->Post->isValidAll() ) {
                $Tpl->add(array('sendMsg#after', $block));
                $vars   += array(
                    'step'  => 'result',
                );
            } else {
                $Tpl->add(array('submit', $block));
                $vars   += array(
                    'step'  => 'reapply',
                );
            }
        }

        $vars   += $this->buildField($this->Post, $Tpl, $block, 'login');

        //------------
        // if expired
        if ( !IS_LICENSED && $block == 'auth' ) {
            $Tpl->add(array('expired', $block));
        }

        $Tpl->add($block, $vars);

        return $Tpl->get();
    }
}
