<?php
/**
 * ACMS_GET_Admin_Fix_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Fix_Index extends ACMS_GET_Admin
{
    function get()
    {
        if ( 'fix_index' <> ADMIN ) return false;
        if ( !sessionWithAdministration() ) return false;

        $Tpl = new Template($this->tpl, new ACMS_Corrector());
        $aryAdmin = array(
            'fix_image',
            'fix_unit-size',
            'fix_unit-group',
            'fix_sequence',
            'fix_fulltext',
            'fix_ngram',
            'fix_tag',
        );

        if ( editionWithProfessional() ) {
            $aryAdmin[] = 'fix_replacement';
        }

        foreach ( $aryAdmin as $admin ) {
            $AP     = array(
                'bid'   => BID,
                'admin' => $admin,
            );
            $Tpl->add($admin, array(
                'url'   => acmsLink($AP),
            ));
        }

        return $Tpl->get();
    }
}
