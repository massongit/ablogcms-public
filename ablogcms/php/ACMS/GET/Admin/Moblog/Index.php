<?php
/**
 * ACMS_GET_Admin_Moblog_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Moblog_Index extends ACMS_GET_Admin
{
    function get()
    {
        if ( !IS_LICENSED ) { return ''; }
        if ( 'moblog_index' <> ADMIN ) { return ''; }
        if ( !sessionWithAdministration() ) { return ''; }

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('moblog');
        $SQL->addWhereOpr('moblog_blog_id', BID);
        $SQL->addOrder('moblog_user');
        $SQL->addOrder('moblog_server');
        $q  = $SQL->get(dsn());

        if ( !!$DB->query($q, 'fetch') and !!($row = $DB->fetch($q)) ) { do {
            $mbid   = intval($row['moblog_id']);
            $Tpl->add('status#'.$row['moblog_status']);
            $Tpl->add('moblog:loop', array(
                'mbid'      => $mbid,
                'mail'      => $row['moblog_mail'],
                'itemUrl'   => acmsLink(array(
                    'bid'   => BID,
                    'admin' => 'moblog_edit',
                    'query' => array(
                        'mbid'  => $mbid,
                    ),
                ), false),
            ));
        } while( !!($row = $DB->fetch($q)) ); } else {
            $Tpl->add('index#notFound');
            $Tpl->add(null, array('notice_mess' => 'show'));
            return $Tpl->get();
        }

        return $Tpl->get();
    }
}
