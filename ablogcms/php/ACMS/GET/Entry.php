<?php
/**
 * ACMS_GET_Entry
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Entry extends ACMS_GET
{
    function buildColumn(& $Column, & $Tpl, $eid, $preAlign = null, $renderGroup = true)
    {
        $entry          = ACMS_RAM::entry($eid);
        $rootBlock      = array('unit:loop');

        $columnAmount   = count($Column) - 1;
        $currentGroup   = null;
        $squareImgSize  = config('image_size_square');
        $showInvisible  = ( 1
            and roleEntryAuthorization(BID, $entry, false)
            and 'on' == config('entry_edit_inplace_enable')
            and 'on' == config('entry_edit_inplace')
            and ( !enableApproval() || sessionWithApprovalAdministrator() )
            and $entry['entry_approval'] !== 'pre_approval'
        );
        $unitGroupEnable= (config('unit_group') === 'on');

        foreach ( $Column as $k => $data ) {
            $type   = $data['type'];
            $align  = $data['align'];
            $sort   = $data['sort'];
            $group  = $data['group'];
            $utid   = $data['clid'];

            // 特定指定子を含むユニットタイプ
            $actualType = $type;
            // 特定指定子を除外した、一般名のユニット種別
            $type = detectUnitTypeSpecifier($type);

            if ( !$showInvisible && 'hidden' === $align ) {
                continue;
            }
            //-------
            // group
            if ( 1
                and $unitGroupEnable
                and $group !== ''
                and $renderGroup === true
            ) {
                $class = $group;

                // close rear
                if ( !!$currentGroup ) {
                    $Tpl->add(array('unitGroup#rear', 'unit:loop'));
                }

                // open front
                $grVars = array('class' => $class);
                if ( $currentGroup === $class ) {
                    $count += 1;
                    $grVars['i'] = $count;
                } else {
                    $count = 1;
                    $grVars['i'] = $count;
                }

                if ( $class === config('unit_group_clear', 'acms-column-clear') ) {
                    $currentGroup = null;
                } else {
                    $Tpl->add(array_merge(array('unitGroup#front'), $rootBlock), $grVars);
                    $currentGroup = $class;
                }
            }

            //-------
            // clear
            if ( 'break' <> $type ) {
                do {
                    if ( empty($preAlign) ) break;
                    if ( 'left' == $align and 'left' == $preAlign ) break;
                    if ( 'rigth' == $align and 'right' == $preAlign ) break;
                    if ( 'auto' == $align ) {
                        if ( 'left' == $preAlign ) break;
                        if ( 'right' == $preAlign ) break;
                        if ( 'auto' == $preAlign and 'text' == $type ) break;
                    }
                    $Tpl->add(array_merge(array('clear'), $rootBlock));
                } while ( false );

                if ( 'auto' == $align and 'text' <> $type ) {
                    $data['align']  = !empty($preAlign) ? $preAlign : 'auto';
                }
                $preAlign   = $align;
            }

            //------
            // text
            if ( 'text' == $type ) {
                if ( empty($data['text']) ) continue;
                $vars   = array(
                    'text'          => $data['text'],
                    'extend_tag'    => $data['extend_tag'],
                );
                buildUnitData($vars['text'], $vars, 'text');

                if ( !empty($data['attr']) ) {
                    $vars['attr']   = $data['attr'];
                    $vars['class']  = $data['attr']; // legacy
                }

                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $vars['extend_tag'] = $data['extend_tag'];
                $Tpl->add(array_merge(array($data['tag'], 'unit#'.$actualType), $rootBlock), $vars);
                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), array(
                    'align' => $data['align'],
                ));

            //-------
            // image
            } else if ( 'image' == $type ) {
                if ( empty($data['path']) ) continue;

                $vars       = array();
                $pathAry    = explodeUnitData($data['path']);

                $pattern        = '@^\.\./'.REVISON_ARCHIVES_DIR.'@';
                $revisonPath    = preg_match($pattern, $data['path']) ? '../'.REVISON_ARCHIVES_DIR : '';

                foreach ( $pathAry as $i => $path_ ) {
                    if ( empty($i) ) {
                        $i = '';
                    } else {
                        $i++;
                        $path_  = $revisonPath.$path_;
                    }
                    $path   = ARCHIVES_DIR.$path_;
                    $xy     = @getimagesize($path);

                    $vars['path'.$i]   = $path;
                    $vars['x'.$i]      = $xy[0];
                    $vars['y'.$i]      = $xy[1];
                }

                $vars['alt']    = $data['alt'];

                if ( !empty($data['display_size']) ) {
                    $dsize = $data['display_size'];
                    if ( is_numeric($dsize) && intval($dsize) > 0 ) {
                        $vars['display_size']   = ' style="width: '.$data['display_size'].'%"';
                    } else {
                        $viewClass = $data['display_size'];
                        $viewClass = ltrim($viewClass, '.');
                        $vars['display_size_class'] = ' js_notStyle '.$viewClass;
                    }
                }

                if ( !empty($data['caption']) ) {
                    $vars['caption'] = $data['caption'];
                }

                $vars['align']  = $data['align'];
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];

                $linkAry        = explodeUnitData($data['link']);
                foreach ( $pathAry as $i => $path_ ) {
                    if ( empty($i) ) $j = '';
                    else $j = $i + 1;
                    
                    $link_ = isset($linkAry[$i]) ? $linkAry[$i] : '';
                    if ( empty($link_) ) {
                        if ( isset($pathAry[$i]) ) {
                            $path   = ARCHIVES_DIR.$pathAry[$i];
                        } else {
                            $path   = ARCHIVES_DIR.$data['path'];
                        }
                        $name   = basename($path);
                        $large  = substr($path, 0, strlen($path) - strlen($name)).'large-'.$name;

                        if ( $xy = @getimagesize($large) ) {
                            $Tpl->add(array_merge(array('link'.$j.'#front', 'unit#'.$actualType), $rootBlock), array(
                                'url'.$j    => BASE_URL.$large,
                                'viewer'.$j => str_replace('{unit_eid}', $eid, config('entry_body_image_viewer')),
                            ));
                            $Tpl->add(array_merge(array('link'.$j.'#rear', 'unit#'.$actualType), $rootBlock));
                        }
                    } else {
                        $Tpl->add(array_merge(array('link'.$j.'#front', 'unit#'.$actualType), $rootBlock), array(
                            'url'.$j  => $link_,
                        ));
                        $Tpl->add(array_merge(array('link'.$j.'#rear', 'unit#'.$actualType), $rootBlock));
                    }
                }

                $tiny   = otherSizeImagePath($path, 'tiny');
                if ( $xy = @getimagesize($tiny) ) {
                    $vars['tinyPath']   = $tiny;
                    $vars['tinyX']      = $xy[0];
                    $vars['tinyY']      = $xy[1];
                }
                
                $square = otherSizeImagePath($path, 'square');
                if ( @is_file($square) ) {
                    $vars['squarePath']   = $square;
                    $vars['squareX']      = $squareImgSize;
                    $vars['squareY']      = $squareImgSize;
                }

                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;

                foreach ( $vars as $key => $val ) {
                     buildUnitData($val, $vars, $key);
                }
                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //------
            // file
            } else if ( 'file' == $type ) {
                if ( empty($data['path']) ) continue;

                $pathAry    = explodeUnitData($data['path']);
                $vars       = array();

                foreach ( $pathAry as $i => $val ) {
                    if ( empty($i) ) $fx = '';
                    else $fx = $i + 1;

                    $path   = ARCHIVES_DIR.$val;
                    $ext    = ite(pathinfo($path), 'extension');
                    $icon   = pathIcon($ext);
                    if ( !file_exists($icon) ) {
                        continue;
                    }
                    $xy     = getimagesize($icon);
                    $vars   += array(
                        'path'.$fx  => $path,
                        'icon'.$fx  => $icon,
                        'x'.$fx     => $xy[0],
                        'y'.$fx     => $xy[1],
                    );
                }
                if ( !empty($data['caption']) ) $vars['caption'] = $data['caption'];
                buildUnitData($data['caption'], $vars, 'caption');

                $vars['align']  = $data['align'];
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];

                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //-----
            // map
            } else if ( 'map' == $type ) {
                if ( empty($data['lat']) ) continue;
                list($x, $y) = explode('x', $data['size']);
                $msg    = str_replace(array(
                    '"', '<', '>', '&'
                ), array(
                    '[[:quot:]]', '[[:lt:]]', '[[:gt:]]', '[[:amp:]]'
                ), $data['msg']);
                $vars   = array(
                    'lat'   => $data['lat'],
                    'lng'   => $data['lng'],
                    'zoom'  => $data['zoom'],
                    'msg'   => $msg,
                    'msgRaw'    => $data['msg'],
                    'x'     => $x,
                    'y'     => $y,
                    'align' => $data['align'],
                );
                if ( !empty($data['display_size']) ) {
                    $dsize = $data['display_size'];
                    if ( is_numeric($dsize) && intval($dsize) > 0 ) {
                        $vars['display_size']   = ' style="width: '.$data['display_size'].'%"';
                    } else {
                        $viewClass = $data['display_size'];
                        $viewClass = ltrim($viewClass, '.');
                        $vars['display_size_class'] = ' js_notStyle '.$viewClass;
                    }
                }
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];
                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //------
            // yolp
            } else if ( 'yolp' == $type ) {
                if ( empty($data['lat']) ) continue;
                list($x, $y) = explode('x', $data['size']);
                $msg    = str_replace(array(
                    '"', '<', '>', '&'
                ), array(
                    '[[:quot:]]', '[[:lt:]]', '[[:gt:]]', '[[:amp:]]'
                ), $data['msg']);
                $layer = $data['layer'];
                if ( in_array($layer, array('railway', 'monotone', 'bold', 'midnight')) ) {
                    $mode   = 'map';
                    $style  = 'base:'.$layer;
                } else {
                    $mode   = $layer;
                    $style  = '';
                }

                $vars   = array(
                    'lat'   => $data['lat'],
                    'lng'   => $data['lng'],
                    'zoom'  => $data['zoom'],
                    'mode'  => $mode,
                    'style' => $style,
                    'msg'   => $msg,
                    'msgRaw'    => $data['msg'],
                    'x'     => $x,
                    'y'     => $y,
                    'align' => $data['align'],
                );
                if ( !empty($data['display_size']) ) {
                    $dsize = $data['display_size'];
                    if ( is_numeric($dsize) && intval($dsize) > 0 ) {
                        $vars['display_size']   = ' style="width: '.$data['display_size'].'%"';
                    } else {
                        $viewClass = $data['display_size'];
                        $viewClass = ltrim($viewClass, '.');
                        $vars['display_size_class'] = ' js_notStyle '.$viewClass;
                    }
                }
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];
                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //---------
            // youtube
            } else if ( 'youtube' == $type ) {
                if ( empty($data['youtube_id']) ) continue;
                list($x, $y) = explode('x', $data['size']);
                $vars   = array(
                    'youtubeId' => $data['youtube_id'],
                    'x' => $x,
                    'y' => $y,
                    'align' => $data['align'],
                );
                buildUnitData($data['youtube_id'], $vars, 'youtubeId');

                if ( !empty($data['display_size']) ) {
                    $dsize = $data['display_size'];
                    if ( is_numeric($dsize) && intval($dsize) > 0 ) {
                        $vars['display_size']   = ' style="width: '.$data['display_size'].'%"';
                    } else {
                        $viewClass = $data['display_size'];
                        $viewClass = ltrim($viewClass, '.');
                        $vars['display_size_class'] = ' js_notStyle '.$viewClass;
                    }
                }
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];
                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //---------
            // video
            } else if ( 'video' == $type ) {
                if ( empty($data['video_id']) ) continue;
                list($x, $y) = explode('x', $data['size']);
                $vars   = array(
                    'videoId' => $data['video_id'],
                    'x' => $x,
                    'y' => $y,
                    'align' => $data['align'],
                );
                buildUnitData($data['video_id'], $vars, 'videoId');

                if ( !empty($data['display_size']) ) {
                    $dsize = $data['display_size'];
                    if ( is_numeric($dsize) && intval($dsize) > 0 ) {
                        $vars['display_size']   = ' style="width: '.$data['display_size'].'%"';
                    } else {
                        $viewClass = $data['display_size'];
                        $viewClass = ltrim($viewClass, '.');
                        $vars['display_size_class'] = ' js_notStyle '.$viewClass;
                    }
                }
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];
                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //---------
            // eximage
            } else if ( 'eximage' == $type ) {
                if ( empty($data['normal']) ) continue;
                list($x, $y) = explode('x', $data['size']);

                $normalAry  = explodeUnitData($data['normal']);
                $linkAry    = explodeUnitData($data['link']);
                $largeAry   = explodeUnitData($data['large']);
                foreach ( $normalAry as $i => $normal ) {
                    if ( empty($i) ) $j = '';
                    else $j = $i + 1;

                    $link_  = isset($linkAry[$i]) ? $linkAry[$i] : '';
                    $large_ = isset($largeAry[$i]) ? $largeAry[$i] : '';

                    $url    = !empty($link_) ? $link_ : (!empty($large_) ? $large_ : null);
                    if ( !empty($url) ) {
                        $vars   = array(
                            'url'.$j    => $url,
                        );
                        if ( empty($link_) ) $vars['viewer'.$j] = str_replace('{unit_eid}', $eid, config('entry_body_image_viewer'));
                        $Tpl->add(array_merge(array('link'.$j.'#front', 'unit#'.$actualType), $rootBlock), $vars);
                        $Tpl->add(array_merge(array('link'.$j.'#rear', 'unit#'.$actualType), $rootBlock));
                    }
                }

                $vars   = array(
                    'normal'    => $data['normal'],
                    'x'         => $x,
                    'y'         => $y,
                    'alt'       => $data['alt'],
                    'large'     => $data['large'],
                    'caption'   => '',
                );

                if ( !empty($data['display_size']) ) {
                    $dsize = $data['display_size'];
                    if ( is_numeric($dsize) && intval($dsize) > 0 ) {
                        $vars['display_size']   = ' style="width: '.$data['display_size'].'%"';
                    } else {
                        $viewClass = $data['display_size'];
                        $viewClass = ltrim($viewClass, '.');
                        $vars['display_size_class'] = ' js_notStyle '.$viewClass;
                    }
                }
                if ( !empty($data['caption']) ) $vars['caption'] = $data['caption'];

                $vars['align']      = $data['align'];
                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];

                buildUnitData($vars['normal'], $vars, 'normal');
                buildUnitData($x, $vars, 'x');
                buildUnitData($y, $vars, 'y');
                buildUnitData($vars['alt'], $vars, 'alt');
                buildUnitData($vars['large'], $vars, 'large');
                buildUnitData($vars['caption'], $vars, 'caption');

                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //-------
            // quote
            } else if ( 'quote' == $type ) {
                if ( empty($data['quote_url']) ) continue;
                $url    = $data['quote_url'];
                $vars   = array(
                    'quote_url' => $url,
                );
                buildUnitData($vars['quote_url'], $vars, 'quote_url');

                if ( !empty($data['html']) ) {
                    $vars['quote_html']         = $data['html'];
                    buildUnitData($vars['quote_html'], $vars, 'quote_html');
                }
                if ( !empty($data['site_name']) ) {
                    $vars['quote_site_name']    = $data['site_name'];
                    buildUnitData($vars['quote_site_name'], $vars, 'quote_site_name');
                }
                if ( !empty($data['author']) ) {
                    $vars['quote_author']       = $data['author'];
                    buildUnitData($vars['quote_author'], $vars, 'quote_author');
                }
                if ( !empty($data['title']) ) {
                    $vars['quote_title']        = $data['title'];
                    buildUnitData($vars['quote_title'], $vars, 'quote_title');
                }
                if ( !empty($data['description']) ) {
                    $vars['quote_description']  = $data['description'];
                    buildUnitData($vars['quote_description'], $vars, 'quote_description');
                }
                if ( !empty($data['image']) ) {
                    $vars['quote_image']        = $data['image'];
                    buildUnitData($vars['quote_image'], $vars, 'quote_image');
                }

                $vars['align']      = $data['align'];
                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                if ( !empty($data['attr']) ) $vars['attr'] = $data['attr'];

                $Tpl->add(array('unit#'.$actualType), $vars);

            //-------
            // media
            } else if ( 'media' == $type ) {
                if ( empty($data['media_id']) ) continue;

                $DB     = DB::singleton(dsn());

                $midAry = explodeUnitData($data['media_id']);
                foreach ( $midAry as $i => $mid ) {
                    if ( empty($i) ) $fx = '';
                    else $fx = $i + 1;
                    
                    $vars   = array();

                    $SQL    = SQL::newSelect('media');
                    $SQL->addWhereOpr('media_id', $mid);
                    if ( !($media = $DB->query($SQL->get(dsn()), 'row')) ) {
                        continue;
                    }

                    $path   = MEDIA_LIBRARY_DIR.$media['media_path'];
                    $vars['path'.$fx]   = $path;
                    $vars['alt'.$fx]    = $media['media_field_3'];
                    if ( !empty($media['media_field_1']) ) {
                        $vars['caption'.$fx] = $media['media_field_1'];
                    }
                    if ( !empty($media['media_field_4']) ) {
                        $vars['text'.$fx] = $media['media_field_4'];
                    }

                    if ( $media['media_type'] == 'image' ) {
                        $vars['x'.$fx] = $data['size'];

                        $name   = basename($path);
                        $large  = substr($path, 0, strlen($path) - strlen($name)).'large-'.$name;
                        if ( !empty($media['media_field_2']) ) {
                            $url    = $media['media_field_2'];
                        } else if ( $xy = @getimagesize($large) ) {
                            $url    = BASE_URL.$large;
                        }

                        if ( !empty($url) ) {
                            $varsLink   = array(
                                'url'.$fx   => $url,
                            );
                            if ( empty($media['media_field_2']) ) $varsLink['viewer'.$fx] = str_replace('{unit_eid}', $eid, config('entry_body_image_viewer'));
                            $Tpl->add(array_merge(array('link'.$fx.'#front', 'type'.$fx.'#'.$media['media_type'], 'unit'.$fx.'#'.$actualType), $rootBlock), $varsLink);
                            $Tpl->add(array_merge(array('link'.$fx.'#rear', 'type'.$fx.'#'.$media['media_type'], 'unit'.$fx.'#'.$actualType), $rootBlock));
                        }

                    } else if ( $media['media_type'] == 'file' ) {
                        if ( empty($media['media_thumbnail']) ) {
                            $ext    = ite(pathinfo($path), 'extension');
                            $icon   = pathIcon($ext);
                            $xy     = getimagesize($icon);
                            $vars   += array(
                                'icon'.$fx  => $icon,
                                'x'.$fx     => $xy[0],
                                'y'.$fx     => $xy[1],
                            );
                        } else {
                            $xy     = getimagesize(ARCHIVES_DIR.$media['media_thumbnail']);
                            $vars   += array(
                                'thumbnail'.$fx => $media['media_thumbnail'],
                                'x'.$fx         => $xy[0],
                                'y'.$fx         => $xy[1],
                            );
                        }
                    }
                    $Tpl->add(array_merge(array('type'.$fx.'#'.$media['media_type'], 'unit#'.$actualType), $rootBlock), $vars);
                }

                if ( !empty($data['display_size']) ) {
                    $dsize = $data['display_size'];
                    if ( is_numeric($dsize) && intval($dsize) > 0 ) {
                        $varsRoot['display_size']   = ' style="width: '.$data['display_size'].'%"';
                    } else {
                        $viewClass = $data['display_size'];
                        $viewClass = ltrim($viewClass, '.');
                        $varsRoot['display_size_class'] = ' js_notStyle '.$viewClass;
                    }
                }

                $varsRoot['align']      = $data['align'];
                $varsRoot['utid']       = $utid;
                $varsRoot['unit_eid']   = $eid;

                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $varsRoot);
            
            //-------
            // break
            } else if ( 'break' == $type ) {

                if ( empty($data['label']) ) continue;
                $vars   = array(
                    'label'  => $data['label'],
                );

                if ( !empty($data['attr']) ) {
                    $vars['attr']   = $data['attr'];
                    $vars['class']  = $data['attr']; // legacy
                }

                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $vars['align']      = $data['align'];

                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //--------
            // module
            } else if ( 'module' == $type ) {

                if ( empty($data['mid']) ) continue;
                
                // ToDo: モジュールのテンプレートを構築
                $mid    = $data['mid'];
                $tpl    = $data['tpl'];
                if ( !empty($mid) ) {
                    $module     = loadModule($mid);
                    $name       = $module->get('name');
                    $identifier = $module->get('identifier');
                    $vars['view'] = ACMS_GET_Layout::spreadModule($name, $identifier, $tpl);
                }

                if ( !empty($data['attr']) ) {
                    $vars['attr']   = $data['attr'];
                    $vars['class']  = $data['attr']; // legacy
                }

                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $vars['align']      = $data['align'];

                $Tpl->add(array_merge(array('unit#'.$actualType), $rootBlock), $vars);

            //--------
            // custom
            } else if ( 'custom' == $type ) {
                if ( empty($data['field']) ) continue;

                $vars  = array();
                if ( !empty($data['attr']) ) {
                    $vars['attr']   = $data['attr'];
                    $vars['class']  = $data['attr']; // legacy
                }

                $vars['utid']       = $utid;
                $vars['unit_eid']   = $eid;
                $vars['align']      = $data['align'];

                $Field      = acmsUnserialize($data['field']);
                $block      = array_merge(array('unit#'.$actualType), $rootBlock);
                $vars       += $this->buildField($Field, $Tpl, $block);
                $Tpl->add($block, $vars);

            } else {
                continue;
            }

            //--------------
            // edit inplace
            if ( 1
                and VIEW == 'entry'
                and 'on' == config('entry_edit_inplace_enable')
                and 'on' == config('entry_edit_inplace')
                and ( !enableApproval() || sessionWithApprovalAdministrator() )
                and $entry['entry_approval'] !== 'pre_approval'
                and !ADMIN
                and ( 0
                    or roleEntryAuthorization(BID, $entry, false)
                    or ( 1
                        and sessionWithContribution()
                        and SUID == ACMS_RAM::entryUser($eid)
                    )
                )
            ) {
                $vars  = array();
                $vars['unit:loop.type']     = $actualType;
                $vars['unit:loop.utid']     = $utid;
                $vars['unit:loop.unit_eid'] = $eid;
                $vars['unit:loop.sort']     = $sort;
                $vars['unit:loop.align']    = $align;
                $Tpl->add(array_merge(array('inplace#front'), $rootBlock), $vars);
                $Tpl->add(array_merge(array('inplace#rear'), $rootBlock));
            }

            //-------------
            // close group
            if ( $k === $columnAmount && $currentGroup !== null ) {
                $Tpl->add(array_merge(array('unitGroup#last'), $rootBlock));
            }

            $Tpl->add($rootBlock);
        }

        // ユニットグループでかつ最後の要素が非表示だった場合
        $lastUnit = array_pop($Column);
        if ( !$showInvisible && $lastUnit['align'] == 'hidden' && $currentGroup !== null ) {
            $Tpl->add(array_merge(array('unitGroup#last'), $rootBlock));
            $Tpl->add($rootBlock);
        }
        return true;
    }
}
