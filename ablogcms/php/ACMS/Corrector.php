<?php
/**
 * ACMS_Corrector
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
require_once LIB_DIR.'Markdown.php';

class ACMS_Corrector
{
    var $userCorrect;
    var $const = array();

    function ACMS_Corrector()
    {
        $this->userCorrect = class_exists('ACMS_User_Corrector');
    }

    function correct($txt, $opt, $name)
    {
        if ( !( 0
            or 'selected' == $name
            or 'checked' == $name
            or 'disabled' == $name
            or 'class' == $name
            or 'attr' == $name
            or is_int(strpos($name, ':selected'))
            or is_int(strpos($name, ':checked'))
        ) ) {
            if ( config('corrector_replace') === 'on' ) {
                if ( empty($opt) ) {
                    $opt = '|escape';
                } else {
                    $correct_regex = configArray('corrector_regex');
                    $correct_match = configArray('corrector_match');
                    foreach ( $correct_regex as $i => $regex ) {
                        if ( preg_match($regex, $opt) ) {
                            $opt = isset($correct_match[$i]) ? $correct_match[$i] : '';
                            break;
                        }
                    }
                }
            } else {
                if ( empty($opt) ) {
                    $opt = '|escape';
                }
            }
        }
        if ( !empty($opt) ) {
            $opt    = '|'.$opt;
            while ( preg_match('@\s*\|\s*([^(|\s]+)\s*(\()?\s*(.*)@', $opt, $match) ) {
                $method = $match[1];
                $opt    = $match[3];
                $args   = array();
                if ( !empty($match[2]) ) {
                    while ( preg_match('@'.'(?:'
                        .'([\d.]+)'.'|'
                        .'"((?:[^"]|\\\")*)"'.'|'
                        ."'((?:[^']|\\\')*)'"
                    .')'.'\s*(,|\))\s*(.*)@', $opt, $match) ) {
                        $args[] = $match[1] | $match[2] | $match[3];
                        $opt    = $match[5];
                        if ( ')' == $match[4] ) { break; }
                    }
                }
                if ( 'list' == $method ) { $method = 'acms_corrector_list'; }

                if ( $this->userCorrect === true && is_callable(array('ACMS_User_Corrector', $method)) ) {
                    $txt    = ACMS_User_Corrector::$method($txt, $args);
                } else if ( method_exists($this, $method) ) {
                    $txt    = $this->$method($txt, $args);
                } else {
                    continue;
                }
            }
        }

        return $txt;
    }

    function nl2br($txt)
    {
        return nl2br($txt);
    }

    function nl2br4html($txt)
    {
        return nl2br($txt, false);
    }

    function delnl($txt)
    {
        return preg_replace("/(\xe2\x80[\xa8-\xa9]|\xc2\x85|\r\n|\r|\n)/", "", $txt);
    }

    function escape($txt, $args=array())
    {
        if ( !empty($args) and is_array($args) ) {
            $rep    = array(
                '&' => '&amp;',
                '<' => '&lt;',
                '>' => '&gt;',
                '"' => '&quot;',
                "'" => '&#039;',
            );
            foreach ( $args as $val ) {
                if ( !isset($rep[$val]) ) continue;
                unset($rep[$val]);
            }
            return str_replace(array_keys($rep), array_values($rep), $txt);
        } else {
            if ( is_array($txt) ) {
                $txt = implode($txt);
            }
            return  htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
        }
    }

    function escvars($txt)
    {
        return str_replace(array('{', '}'), array('&#123;', '&#125;'), $txt);
    }

    function escquot($txt)
    {
        return preg_replace('@"@', '""', $txt);
    }

    function trim($txt, $args=array())
    {
        if ( !isset($args[0]) ) return $txt;
        $width  = intval($args[0]);
        $marker = isset($args[1]) ? $args[1] : '';

        return mb_strimwidth($txt, 0, $width, $marker);
    }

    function mb_trim($txt, $args=array())
    {
        if ( !isset($args[0]) ) return $txt;
        $width  = intval($args[0]);
        $marker = isset($args[1]) ? $args[1] : '';

        if ( $width < mb_strlen($txt) ) {
            return mb_substr($txt, 0, $width).$marker;
        }
        return $txt;
    }

    function trim4ext($txt, $args=array())
    {
        if ( !empty($args[0]) && is_array($args) && preg_match('@^.(.*)$@si', $args[0], $match) ) {
            return str_replace($args[0], '', $txt);
        } else {
            return $txt;
        }
    }

    function table($csv, $args=array())
    {
        if ( empty($csv) ) return $csv;

        //-----------
        // overwrite
        $i  = 0;
        $m  = array();
        foreach ( array(
            'column'    => ',',
            'row'       => '[\r\n]+',
            'enclosure' => '"',
            'head'      => '#',
            'align'     => '\|',
            'nowrapS'   => '\[',
            'nowrapE'   => '\]',
            'regex'     => '@',
            'rspan'     => '\^\d+',
            'cspan'     => '\>\d+',
        ) as $key => $val ) {
            $m[$key]    = !empty($args[$i]) ? $args[$i] : $val;
            $i++;
        }

        //--------
        // double
        $doubleCheck    = array_unique($m);
        if ( count($m) !== count($doubleCheck) ) return $csv;

        //-----
        // ptn
        $ptn    = $m['regex']
            .'(^|'.$m['column'].'|'.$m['row'].')'
            .'((?:\t| |　|'.$m['rspan'].'|'.$m['cspan'].'|'.$m['head'].'|'.$m['nowrapS'].'|'.$m['align'].')*)'
            .'(?:(?:'.$m['enclosure'].'((?:[^'.$m['enclosure'].']|'.$m['enclosure'].$m['enclosure'].')*)'.$m['enclosure'].')|([^'.$m['column'].']*?))'
            .'(?=([[:blank:]'.$m['nowrapE'].$m['align'].']*+)(?:'.$m['column'].'|'.$m['row'].'|$))'
        .$m['regex'];

        preg_match_all($ptn, $csv, $matches, PREG_SET_ORDER);
        
        $html   = '<tr>';
        
        while ( !!($match = array_shift($matches)) ) {
            //------------------
            // 1 : delimiter
            // 2 : option left
            // 3 : cell escaped
            // 4 : cell
            // 5 : option right

            $delimiter  = $match[1];
            $optionL    = $match[2];
            $cell       = $match[3] | $match[4];
            $optionR    = !empty($match[5]) ? $match[5] : '';

            if ( preg_match('@^'.$m['row'].'$@', $delimiter) ) {
                $html   .= "</tr>\n<tr>";
            }

            $attr   = '';
            
            //-------
            // align
            if ( false !== strpos($optionL, '|') ) {
                if ( false !== strpos($optionR, '|') ) {
                    $attr   .= ' style="text-align:center"';
                } else {
                    $attr   .= ' style="text-align:left"';
                }
            } else if ( false !== strpos($optionR, '|') ) {
                $attr   .= ' style="text-align:right"';
            }
            
            //-------
            // span
            if ( preg_match('@\>(\d+)@', $optionL, $ncol) ) {
                $col    = intval($ncol[1]);
                $attr   .= ' colspan="'.$col.'"';
            }
            if ( preg_match('@\^(\d+)@', $optionL, $nrow) ) {
                $row    = intval($nrow[1]);
                $attr   .= ' rowspan="'.$row.'"';
            }

            //--------
            // nowrap
            if ( false !== strpos($optionL, '[') and false !== strpos($optionR, ']') ) {
                $attr   .= ' nowrap="nowrap"';
            }
            $optionL    = '_'.$optionL.'_';
            $tag        = (preg_match('/^_[^#]{0,}?#[^#]{0,}?_/', $optionL)) ? 'th' : 'td';
            $cell       = preg_match('/^_[#]{2}?_/', $optionL) ? '#'.$cell : $cell;
            $html       .= '<'.$tag.$attr.'>'.nl2br(str_replace('""', '"', $cell)).'</'.$tag.'>';
        }
        $html   .= "</tr>";

        return $html;
    }

    function definition_list($txt, $args=array())
    {
        if ( $lis = preg_split('@( |　|\t)*\r?\n@', $txt, -1, PREG_SPLIT_NO_EMPTY) ) {
            $txt = "\n";
            foreach ( $lis as $dval ) {
                if ( preg_match('/^#[^#]/', $dval) ) {
                    $txt .= "<dt>".preg_replace('@^#( |　|\t)*@', '', $dval)."</dt>\n";
                } else {
                    $txt .= "<dd>".preg_replace('/##/', '#', $dval)."</dd>\n";
                }
            }
        }
        return $txt;
    }

    function acms_corrector_list($txt)
    {
        if ( $lis = preg_split('@( |　|\t)*\r?\n@', $txt, -1, PREG_SPLIT_NO_EMPTY) ) {
            $txt    = "\n<li>".join("</li>\n<li>", $lis)."</li>\n";
        }
        return $txt;
    }

    function markdown($txt, $args=array())
    {
        $lv = intval(isset($args[0]) ? $args[0] : 0);
        if ( 0 < $lv ) {
            $_txt   = $txt;
            $txt    = '';
            foreach ( preg_split('@^@m', $_txt) as $token ) {
                if ( '#' == substr($token, 0, 1) ) {
                    $token  = str_repeat('#', $lv).$token;
                }
                $txt    .= $token;
            }
        }

        return Markdown($txt);
    }

    function striptags($txt)
    {
        return strip_tags($txt);
    }

    function urldecode($txt)
    {
        return urldecode($txt);
    }

    function urlencode($txt)
    {
        // RFC3986
        return str_replace('%7E', '~', rawurlencode($txt));
    }

    function html_entity_decode($txt)
    {
        return html_entity_decode($txt);
    }

    function md5($txt)
    {
        return md5($txt);
    }

    function base64($txt)
    {
        return base64_encode($txt);
    }

    function number_format($txt)
    {
        if ( !empty($txt) && is_numeric($txt) ) {
            return number_format($txt);
        } else {
            return $txt;
        }
    }

    function str4script($txt)
    {
        return preg_replace(array('@\'|"@', '@\r|\n@'), array('\\\$0', ''), $txt);
    }

    function tax($txt, $args=array())
    {
        $args[0] = is_numeric($args[0]) ? $args[0] : intval($args[0]);
        return floor($txt * $args[0]);
    }

    function convert($txt, $args=array())
    {
        return !empty($args[0]) ? mb_convert_kana($txt, strval($args[0])) : $txt ;
    }

    function camelcase_to_hyphen($txt)
    {
        return preg_replace("/([^_])([A-Z])/", "$1-$2", $txt);
    }

    function symbolfont_path($txt)
    {
        if ( in_array($txt, array(
            'Blog_Field', 'Category_Field', 'Entry_Field', 'User_Field'
        )) ) {
            return 'entry_body';
        }

        return $this->lowercase($this->camelcase_to_hyphen($txt));
    }

    function wareki($txt, $args=array())
    {
        $dt  = strtotime($this->fixChars($txt));
        if ( !$dt ) return $txt;
        $ymd = date('Ymd', $dt);
        $y   = substr($ymd, 0, 4);

        if ( $ymd <= '19120729' ) {
            $era    = '明治';
            $year   = $y - 1867;
        } elseif ( $ymd >= '19120730' && $ymd <= '19261224' ) {
            $era    = '大正';
            $year   = $y - 1911;
        } elseif ( $ymd >= '19261225' && $ymd <= '19890107' ) {
            $era    = '昭和';
            $year   = $y - 1925;
        } elseif ( $ymd >= '19890108' ) {
            $era    = '平成';
            $year   = $y - 1988;
        }

        if ( substr($year, 0, 1) == 0 ) $year = preg_replace('@^0+@', '', $year);
        $result = $era.$year;
        if ( isset($args[0]) ) {
            $result .= $this->datetime($txt, $args);
        }
        return $result;
    }

    function age($txt)
    {
        $dt  = false !== ($dt = strtotime($this->fixChars($txt))) ? $dt : $txt;
        $ymd = date('Ymd', $dt);
        $txt = intval((date('Ymd') - strval($ymd))/10000);
        return $txt;
    }

    function date($txt, $args=array())
    {
        return $this->datetime($txt, $args);
    }

    function datetime($txt, $args=array())
    {
        if ( !isset($args[0]) ) return $txt;
        $dt  = ( false !== ($dt = strtotime($this->fixChars($txt))) )? $dt : $txt;
        if( $dt === '0000-00-00 00:00:00'){
            return '';
        }
        $txt = date($args[0], $dt);
        if ( $txt === date($args[0],strtotime(null)) && isset($args[1]) ) $txt = $args[1];
        return $txt;
    }

    function resizeImg($src, $args=array())
    {
        if ( !isset($args[0]) ) return $src;

        $width      = empty($args[0]) ? 0 : intval($args[0]);
        $height     = (isset($args[1]) && !empty($args[1])) ? intval($args[1]) : 0;
        $color      = isset($args[2]) ? strtolower($args[2]) : 'ffffff';
        $color_r = $color_g = $color_b = 0;

        $pfx        = '';
        if ( !empty($width) ) {
            $pfx    .= 'w'.$width;
        }
        if ( !empty($height) ) {
            if ( !empty($pfx) ) $pfx .= '_';
            $pfx    .= 'h'.$height;
        }
        if ( $color !== 'ffffff' ) {
            $pfx    .= '_'.$color;
        }

        $srcPath        = '';
        $destPath       = '';
        $destPathVars   = '';

        foreach ( array('', REQUEST_URL, ARCHIVES_DIR, REVISON_ARCHIVES_DIR) as $archive_dir ) {
            $tmpPath        = $archive_dir.normalSizeImagePath($src);
            $destPath       = trim(dirname($tmpPath), '/').'/'.$pfx.'-'.basename($tmpPath);
            $destPathVars   = trim(dirname($src), '/').'/'.$pfx.'-'.basename($tmpPath);
            $largePath      = otherSizeImagePath($tmpPath, 'large'); // large path

            if ( is_readable($destPath) ) {
                return $destPathVars;
            }
            if ( is_readable($largePath) ) {
                $srcPath = $largePath;
                break;
            }
            if ( is_readable($tmpPath) ) {
                $srcPath = $tmpPath;
                break;
            }
        }
        if ( empty($srcPath) ) {
            return $src;
        }

        if ( !$xy = @getimagesize($srcPath) ) {
            return $src;
        }
        if ( preg_match('/^([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/', $color, $matches) ) {
            $color_r    = hexdec($matches[1]);
            $color_g    = hexdec($matches[2]);
            $color_b    = hexdec($matches[3]);
        }
        if ( 0
            || $color_r > 255 || $color_r < 0
            || $color_g > 255 || $color_g < 0
            || $color_b > 255 || $color_b < 0
        ) {
            return $src;
        }

        $mime   = $xy['mime'];
        $exts   = array(
            'image/gif'         => 'gif',
            'image/png'         => 'png',
            'image/vnd.wap.wbmp'=> 'bmp',
            'image/xbm'         => 'xbm',
            'image/jpeg'        => 'jpg',
        );
        $ext    = isset($exts[$mime]) ? $exts[$mime] : 'jpg';
        if ( 'gif' == $ext ) {
            $srcImg     = imagecreatefromgif($srcPath);
        } else if ( 'png' == $ext ) {
            $srcImg     = imagecreatefrompng($srcPath);
        } else if ( 'bmp' == $ext ) {
            $srcImg     = imagecreatefromwbmp($srcPath);
        } else if ( 'xbm' == $ext ) {
            $srcImg     = imagecreatefromxbm($srcPath);
        } else {
            $srcImg     = imagecreatefromjpeg($srcPath);
        }

        //--------------
        // resize image
        $srcW       = imagesx($srcImg);
        $srcH       = imagesy($srcImg);
        $srcX = $srcY = $destW = $destH = $destX = $destY = $canvasW = $canvasH = 0;

        if ( !empty($width) && !empty($height) ) {
            $canvasW    = $width;
            $canvasH    = $height;

            $srcRatio   = $srcW / $srcH;
            $destRatio  = $canvasW / $canvasH;

            // アスペクト比が縦に伸びる
            if ( $srcRatio > $destRatio ) {
                if ( $height < $srcH ) {
                    $destH  = $height;
                    $ratio  = $height / $srcH;
                    $tmpW   = ceil($srcW * $ratio);
                    if ( $width < $tmpW ) {
                        $destW  = $width;
                        $srcX   = ceil(($tmpW - $width) / 2 / $ratio);
                        $srcW   = ceil($srcW - ($srcX * 2));
                    } else {
                        $destW  = $tmpW;
                        $destX  = ceil(($width - $tmpW) / 2);
                    }
                } else {
                    $destH  = $srcH;
                    $destY  = ceil(($height - $srcH) / 2);
                    if ( $width < $srcW ) {
                        $destW  = $width;
                        $srcX   = ceil(($srcW - $width) / 2);
                        $srcW   = ceil($srcW - ($srcX * 2));
                    } else {
                        $destW  = $srcW;
                        $destX  = ceil(($width - $srcW) / 2);
                    }
                }
            // アスペクト比が横に伸びる
            } else {
                if ( $width < $srcW ) {
                    $destW  = $width;
                    $ratio  = $width / $srcW;
                    $tmpH   = ceil($srcH * $ratio);
                    if ( $height < $tmpH ) {
                        $destH  = $height;
                        $srcY   = ceil(($tmpH - $height) / 2 / $ratio);
                        $srcH   = ceil($srcH - ($srcY * 2));
                    } else {
                        $destH  = $tmpH;
                    }
                } else {
                    $destW  = $srcW;
                    $destX  = ceil(($width - $srcW) / 2);
                    if ( $height < $srcH ) {
                        $destH  = $height;
                        $srcY   = ceil(($srcH - $height) / 2);
                        $srcH   = ceil($srcH - ($srcY * 2));
                    } else {
                        $destH  = $srcH;
                        $destY  = ceil(($height - $srcH) / 2);
                    }
                }
            }

        } else if ( !empty($width) ) {
            $canvasW    = $width;
            $ratio      = $width / $srcW;

            if ( $width < $srcW ) {
                $destW      = $width;
                $destH      = ceil($srcH * $ratio);
                $canvasH    = $destH;
            } else {
                $destW      = $srcW;
                $destH      = $srcH;
                $canvasH    = $srcH;
                $destX      = ceil(($width - $srcW) / 2);
            }
        } else if ( !empty($height) ) {
            $canvasH    = $height;
            $ratio      = $height / $srcH;

            if ( $height < $srcH ) {
                $destH      = $height;
                $destW      = ceil($srcW * $ratio);
                $canvasW    = $destW;
            } else {
                $destW      = $srcW;
                $destH      = $srcH;
                $canvasW    = $srcW;
                $destY      = ceil(($height - $srcH) / 2);
            }
        } else {
            return $src;
        }

        $destImg    = imagecreatetruecolor($canvasW, $canvasH);

        if ( 0 <= ($idx = imagecolortransparent($srcImg)) ) {
            @imagetruecolortopalette($destImg, true, 256);
            $rgb    = @imagecolorsforindex($srcImg, $idx);
            $idx    = imagecolorallocate($destImg, $rgb['red'], $rgb['green'], $rgb['blue']);
            imagefill($destImg, 0, 0, $idx);
            imagecolortransparent($destImg, $idx);
        } else {
            imagealphablending($destImg, false);
            imagefill($destImg, 0, 0, imagecolorallocatealpha($destImg, $color_r, $color_g, $color_b, 127));
            imagesavealpha($destImg, true);
        }

        imagecopyresampled($destImg, $srcImg, $destX, $destY, $srcX, $srcY, $destW, $destH, $srcW, $srcH);
        if ( 'gif' == $ext ) {
            imagegif($destImg, $destPath);
        } else if ( 'png' == $ext ) {
            imagepng($destImg, $destPath);
        } else if ( 'bmp' == $ext ) {
            imagewbmp($destImg, $destPath);
        } else if ( 'xbm' == $ext ) {
            imagexbm($destImg, $destPath);
        } else {
            imagejpeg($destImg, $destPath, intval(config('image_jpeg_quality')));
        }

        return $destPathVars;
    }

    function fixChars($txt)
    {
        $needle      = array('年', '月', '日', '時', '分', '秒', '　');
        $replacement = array('', '', '', '', '', '', '');
        $txt    = mb_convert_kana(str_replace($needle, $replacement, $txt), 'a');
        return $txt;
    }

    function br4alnum($txt, $args=array())
    {
        if ( 0
            or !isset($args[0]) 
            or !($len = intval($args[0]))
        ) {
            return $txt;
        }
        $ptn    = '@[[:alnum:]]{'.$len.'}(?=[[:alnum:]])@';
        $br     = isset($args[1]) ? $args[1] : '<br />';

        $newText    = '';
        while ( preg_match($ptn, $txt, $match, PREG_OFFSET_CAPTURE) ) {
            $pos        = strlen($match[0][0]) + $match[0][1];
            $newText    .= substr($txt, 0, $pos).$br;
            $txt        = substr($txt, $pos);
        }
        $newText    .= $txt;

        return $newText;
    }

    function weekEN2JP($txt, $args=array())
    {
        $en    = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $jp    = configArray('week_label');
        foreach ( $en as $i => $val ) {
            $txt    = str_replace($val, $jp[$i], $txt);
        }
        return $txt;
    }

    function basename($txt)
    {
        return basename($txt);
    }

    function dirname($txt)
    {
        return dirname($txt);
    }

    function zero_padding($txt, $args)
    {
        $length = isset($args[0]) ? intval($args[0]) : 4;
        return str_pad($txt, $length, '0', STR_PAD_LEFT);
    }

    function convert_bytes($txt, $args)
    {
        if ( !empty($args[0]) && preg_match('/^[gmkGMK]$/', $args[0]) ) {
            return convert_bytes($txt, $args[0], isset($args[1]) ? intval($args[1]) : 2);
        } else {
            return $txt;
        }
    }

    function align2label($txt)
    {
        $dict = array(
            'auto'   => 'おまかせ',
            'center' => '中央',
            'right'  => '右寄せ',
            'left'   => '左寄せ',
            'hidden' => '非表示',
        );
        return isset($dict[$txt]) ? $dict[$txt] : $txt;
    }

    function del_pictogram($txt)
    {
        if ( !!($path = config('const_file_path')) && empty($this->const) ) {
            include SCRIPT_DIR.$path;
            $this->const = $const;
        }
        return str_replace(array_keys($this->const), '', $txt);
    }

    function split($txt, $args=array())
    {
        if ( !isset($args[1]) ) return $txt;

        $count      = intval($args[1]);
        $pattern    = isset($args[0]) ? $args[0] : '';
        $data       = preg_split('@'.$pattern.'@', $txt);
        if ( !isset($data[$count]) ) return $txt;

        return $data[$count];
    }

    function lowercase($txt)
    {
        return strtolower($txt);
    }

    function uppercase($txt)
    {
        return strtoupper($txt);
    }

    function buildGlobalVars($txt)
    {
        return setGlobalVars($txt);
    }

    function buildModule($txt)
    {
        return build($txt, Field_Validation::singleton('post'));
    }

    function buildTpl($txt)
    {
        return $this->buildModule($this->buildGlobalVars($txt));
    }
}

