<?php
/**
 * ACMS_GET_Admin_Indexing
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Indexing extends ACMS_GET_Admin
{
    function get()
    {
        if ( !sessionWithContribution() ) return '';

        $Tpl = new Template($this->tpl, new ACMS_Corrector());

        if ( !!EID ) {
            $this->addBlock($Tpl, ACMS_RAM::entryIndexing(EID), 'entry');
        }

        if ( !!CID ) {
            $this->addBlock($Tpl, ACMS_RAM::categoryIndexing(CID), 'category');
        }

        $this->addBlock($Tpl, ACMS_RAM::blogIndexing(BID), 'blog');
        return $Tpl->get();
    }

    function addBlock(& $Tpl, $indexing, $block)
    {
        if ( $indexing === 'on' ) {
            $Tpl->add('index:'.$block);
        } else {
            $Tpl->add('not_index:'.$block);
        }
    }
}
