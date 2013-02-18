<?php
/**
 * ACMS_User_GET_Touch_SpecialCategory
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_User_GET_Touch_SpecialCategory extends ACMS_GET
{
    var $_scope = array(
        'cid'   => 'global',
    );
    function get()
    {
		// 限定したいカテゴリのID
		$ary_category = array(1,2,3);
        return ( in_array( $this->cid, $ary_category ) ) ? $this->tpl : false;
    }
}
