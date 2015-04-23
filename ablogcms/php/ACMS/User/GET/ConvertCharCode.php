<?php
/**
 * ACMS_User_GET_ConvertCharCode
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
 * '<!-- BEGIN_MODULE ConvertCharCode --><!--END_MODULE ConvertCharCode -->' で呼び出されます。
 */
class ACMS_User_GET_ConvertCharCode extends ACMS_GET
{
    function get()
    {
        set_time_limit(0);
        $old    = dsn(array(
            'name'      => 'DBconvertEUC',
            'charset'   => 'eucjp',
            'prefix'    => 'PRESIDENT_',
        ));
        $new    = dsn(array(
            'name'      => 'DBconvertUTF8_Pr',
            'charset'   => 'utf8',
            'prefix'    => 'PRESIDENT_',
        ));

        $DB_Src = DB::singleton($old);
        $DB_Dst = DB::singleton($new);

        $table  = 'user'; // news, newsText, comment, tag

        $SQL    = SQL::newSelect($table);
        $q      = $SQL->get($old);
        $DB_Src->query($q, 'fetch');

        while ( $news = $DB_Src->fetch($q) ) {
            $SQL    = SQL::newInsert($table);
            foreach ( $news as $key => $val ) {
                //---------------------------
                // euc-jp → sjis-win → utf-8
                $val = mb_convert_encoding(
                    mb_convert_encoding($val, "sjis-win", "EUC-JP"),
                    "UTF-8",
                    "sjis-win"
                );
                $SQL->addInsert($key, $val);
            }
            $q2  = $SQL->get($new);
            $DB_Dst->query($q2, 'exec');
        }

        die('ok');
    }
}
