<?php
/**
 * ACMS_GET_Api_Twitter
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
require_once ACMS_LIB_DIR.'Services/Twitter.php';

class ACMS_GET_Api_Twitter extends ACMS_GET_Api
{
    const WEB_URL = 'http://twitter.com/';

    function largeImageUrl($url)
    {
        return preg_replace('@normal(\.gif|\.jpg|\.png|\.jpeg|\.JPG|\.GIF|\.PNG|\.JPEG|\.bmp|\.BMP)$@', 'bigger$1', $url);
    }

    function miniImageUrl($url)
    {
        return preg_replace('@normal(\.gif|\.jpg|\.png|\.jpeg|\.JPG|\.GIF|\.PNG|\.JPEG|\.bmp|\.BMP)$@', 'mini$1', $url);
    }
}
