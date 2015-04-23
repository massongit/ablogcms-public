<?php
/**
 * ACMS_User_GET_Convert
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
/**
 * php/ACMS/User/GET/Convert.php
 *
 * テンプレート上では、標準のGETモジュールと同様に、
 * '<!-- BEGIN_MODULE Convert --><!--END_MODULE Convert -->' で呼び出されます。
 */
class ACMS_User_GET_Convert extends ACMS_GET
{
    function convertCategory($cid)
    {
        $table = array(
            "1" => "50",
            "2" => "51",
            "3" => "52",
            "4" => "53",
        );

        $cid = strval($cid);
        if ( isset($table[$cid]) ) {
            return $table[$cid];
        }
        return $cid;
    }

    function get()
    {
        set_time_limit(0);
        $old    = dsn(array('name' => 'DBkeeper2'));
        $BID    = 12;

        $DB_Src = DB::singleton($old);
        $DB_Dst = DB::singleton(dsn());

        $SQL    = SQL::newSelect('entry');
        $SQL->setOrder('entry_id', 'ASC');
        $q      = $SQL->get($old);
        $DB_Src->query($q, 'fetch');

        while ( $entry = $DB_Src->fetch($q) ) {
            $eid    = $entry['entry_id'];
            $neid   = intval($DB_Dst->query(SQL::nextval('entry_id', dsn()), 'seq'));

            //-------
            // Entry
            $entry['entry_id']            = $neid;
            $entry['entry_category_id']   = $this->convertCategory($entry['entry_category_id']);
            $entry['entry_blog_id']       = $BID;

            $SQL    = SQL::newInsert('entry');
            foreach ( $entry as $key => $val ) {
                $SQL->addInsert($key, $val);
            }
            $DB_Dst->query($SQL->get(dsn()), 'exec');

            //-------
            // Unit
            $SQL    = SQL::newSelect('column');
            $SQL->addWhereOpr('column_entry_id', $eid);
            $all    = $DB_Src->query($SQL->get($old), 'all');
            foreach ( $all as $unit ) {
                $ncid = intval($DB_Dst->query(SQL::nextval('column_id', dsn()), 'seq'));

                $unit['column_id']          = $ncid;
                $unit['column_entry_id']    = $neid;
                $unit['column_blog_id']     = $BID;

                $SQL    = SQL::newInsert('column');
                foreach ( $unit as $key => $val ) {
                    $SQL->addInsert($key, $val);
                }
                $DB_Dst->query($SQL->get(dsn()), 'exec');
            }

            //---------
            // Comment
            $SQL    = SQL::newSelect('comment');
            $SQL->addWhereOpr('comment_entry_id', $eid);
            $all    = $DB_Src->query($SQL->get($old), 'all');
            foreach ( $all as $comment ) {
                $ncid = intval($DB_Dst->query(SQL::nextval('comment_id', dsn()), 'seq'));

                $comment['comment_id']          = $ncid;
                $comment['comment_entry_id']    = $neid;
                $comment['comment_blog_id']     = $BID;

                $SQL    = SQL::newInsert('comment');
                foreach ( $comment as $key => $val ) {
                    $SQL->addInsert($key, $val);
                }
                $DB_Dst->query($SQL->get(dsn()), 'exec');
            }

            //---------
            // Comment
            $SQL    = SQL::newSelect('tag');
            $SQL->addWhereOpr('tag_entry_id', $eid);
            $all    = $DB_Src->query($SQL->get($old), 'all');
            foreach ( $all as $tag ) {
                $tag['tag_entry_id']    = $neid;
                $tag['tag_blog_id']     = $BID;

                $SQL    = SQL::newInsert('tag');
                foreach ( $tag as $key => $val ) {
                    $SQL->addInsert($key, $val);
                }
                $DB_Dst->query($SQL->get(dsn()), 'exec');
            }
        }

        die('ok');
    }
}
