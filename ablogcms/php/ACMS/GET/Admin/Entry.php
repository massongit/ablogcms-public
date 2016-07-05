<?php
/**
 * ACMS_GET_Admin_Entry
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Entry extends ACMS_GET_Admin
{
    function getColumnDefinition($mode, $type, $i)
    {
        $pfx    = 'column_def_'.$mode.'_';

        // 特定指定子を除外した、一般名のユニット種別
        $type = detectUnitTypeSpecifier($type);

        if ( 'text' == $type ) {
            return array(
                'text'          => config($pfx.'field_1', '', $i),
                'tag'           => config($pfx.'field_2', '', $i),
                'extend_tag'    => '',
            );
        } else if ( 'image' == $type ) {
            return array(
                'caption'   => config($pfx.'field_1', '', $i),
                'path'      => config($pfx.'field_2', '', $i),
                'link'      => config($pfx.'field_3', '', $i),
                'alt'       => config($pfx.'field_4', '', $i),
            );
        } else if ( 'file' == $type ) {
            return array(
                'caption'   => config($pfx.'field_1', '', $i),
                'path'      => config($pfx.'field_2', '', $i),
            );
        } else if ( 'map' == $type ) {
            return array(
                'msg'   => config($pfx.'field_1', '', $i),
                'lat'   => config($pfx.'field_2', '35.185574', $i),
                'lng'   => config($pfx.'field_3', '136.899066', $i),
                'zoom'  => config($pfx.'field_4', '10', $i),
            );
        } else if ( 'yolp' == $type ) {
            return array(
                'msg'   => config($pfx.'field_1', '', $i),
                'lat'   => config($pfx.'field_2', '35.185574', $i),
                'lng'   => config($pfx.'field_3', '136.899066', $i),
                'zoom'  => config($pfx.'field_4', '10', $i),
                'layer' => config($pfx.'field_5', 'map', $i),
            );
        } else if ( 'youtube' == $type ) {
            return array(
                'youtube_id'    => config($pfx.'field_2', '', $i),
            );
        } else if ( 'video' == $type ) {
            return array(
                'video_id'    => config($pfx.'field_2', '', $i),
            );
        } else if ( 'eximage' == $type ) {
            return array(
                'caption'   => config($pfx.'field_1', '', $i),
                'normal'    => config($pfx.'field_2', '', $i),
                'large'     => config($pfx.'field_3', '', $i),
                'link'      => config($pfx.'field_4', '', $i),
                'alt'       => config($pfx.'field_5', '', $i),
            );
        } else if ( 'quote' == $type ) {
            return array(
                'quote_url' => config($pfx.'field_6', '', $i),
                'html'      => config($pfx.'field_7', '', $i),
                'site_name' => config($pfx.'field_1', '', $i),
                'author'    => config($pfx.'field_2', '', $i),
                'title'     => config($pfx.'field_3', '', $i),
                'description' => config($pfx.'field_4', '', $i),
                'image'     => config($pfx.'field_5', '', $i),
            );
        } else if ( 'media' == $type ) {
            return array(
                'media_id'  => config($pfx.'field_1', '', $i),
            );
        } else if ( 'break' == $type ) {
            return array(
                'label' => config($pfx.'field_1', '', $i),
            );
        } else if ( 'module' == $type ) {
            return array(
                'mid'   => config($pfx.'field_1', '', $i),
                'tpl'   => config($pfx.'field_2', '', $i),
            );
        } else if ( 'custom' == $type ) {
            return array(
                'field' => config($pfx.'field_6', '', $i),
            );
        } else {
            return array();
        }
    }

    function buildColumn($data, & $Tpl, $rootBlock=array())
    {
        $rootBlock  = empty($rootBlock) ? array() : 
            (is_array($rootBlock) ? $rootBlock : array($rootBlock))
        ;

        $id     = $data['id'];
        $clid   = ite($data, 'clid');
        $typeS  = $data['type'];
        $size   = $data['size'];

        // 特定指定子を除外した、一般名のユニット種別
        $type = detectUnitTypeSpecifier($typeS);

        //------
        // text
        if ( 'text' == $type ) {
            $suffix = '';
            if ( preg_match('@(?:id="([^"]+)"|class="([^"]+)")@', $data['attr'], $match) ) {
                if ( !empty($match[1]) ) $suffix .= '#'.$match[1];
                if ( !empty($match[2]) ) $suffix .= '.'.$match[2];
            }
            foreach ( configArray('column_text_tag') as $i => $tag ) {
                $vars  = array(
                    'value'     => $tag,
                    'label'     => config('column_text_tag_label', '', $i),
                    'extend'    => config('column_text_tag_extend_label', '', $i),
                );
                if ( $data['tag'].$suffix === $tag) {
                    $vars['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('textTag:loop', $type), $rootBlock), $vars);
            }

            $textVars = array(
                'id'            => $id,
                'extend_tag'    => $data['extend_tag'],
            );
            buildUnitData($data['text'], $textVars, 'text');
            $Tpl->add(array_merge(array($type), $rootBlock), $textVars);

        //-------
        // image
        } else if ( 'image' == $type ) {
            foreach ( configArray('column_image_size') as $i => $_size ) {
                $vars  = array(
                    'value'     => $_size,
                    'label'     => config('column_image_size_label', '', $i),
                    'display'   => config('column_image_display_size', '', $i),
                );
                if ( $size == $_size ) {
                    $vars['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('size:loop', $type), $rootBlock), $vars);
            }
            $vars  = array(
                'old'       => $data['path'],
                'caption'   => $data['caption'],
                'link'      => $data['link'],
                'alt'       => $data['alt'],
                'id'        => $id,
            );

            buildUnitData($vars['caption'], $vars, 'caption');
            buildUnitData($vars['link'], $vars, 'link');
            buildUnitData($vars['alt'], $vars, 'alt');
            buildUnitData($data['path'], $vars, 'old');

            if ( isset($data['edit']) ) {
                $edit = $data['edit'];
                $vars['edit:selected#'.$edit] = config('attr_selected');
            }

            //----------------
            // tiny and large
            if ( !empty($data['path']) ) {
                $nXYAry     = array();
                $tXYAry     = array();
                $tinyAry    = array();
                $lXYAry     = array();

                foreach ( explodeUnitData($data['path']) as $normal ) {
                    $nXY   = @getimagesize(ARCHIVES_DIR.$normal);
                    $tiny  = preg_replace('@[^/]+$@', 'tiny-$0', $normal);
                    $large = preg_replace('@[^/]+$@', 'large-$0', $normal);
                    $tXY   = @getimagesize(ARCHIVES_DIR.$tiny);
                    if ( $lXY = @getimagesize(ARCHIVES_DIR.$large) ) {
                        $lXYAry['x'][]  = $lXY[0];
                        $lXYAry['y'][]  = $lXY[1];
                        $largeAry[]     = $large;
                    } else {
                        $lXYAry['x'][]  = '';
                        $lXYAry['y'][]  = '';
                        $largeAry[]     = '';
                    }

                    $nXYAry['x'][] = $nXY[0];
                    $nXYAry['y'][] = $nXY[1];
                    $tXYAry['x'][] = $tXY[0];
                    $tXYAry['y'][] = $tXY[1];
                    
                    $tinyAry[]  = $tiny;
                }

                $vars   += array(
                    'tiny'  => implodeUnitData($tinyAry),
                    'tinyX' => implodeUnitData($tXYAry['x']),
                    'tinyY' => implodeUnitData($tXYAry['y']),
                    'popup' => $data['path'],
                    'normalX'   => implodeUnitData($nXYAry['x']),
                    'normalY'   => implodeUnitData($nXYAry['y']),
                    'largeX'    => implodeUnitData($lXYAry['x']),
                    'largeY'    => implodeUnitData($lXYAry['y']),
                );

                buildUnitData($vars['tiny'], $vars, 'tiny');
                buildUnitData($vars['tinyX'], $vars, 'tinyX');
                buildUnitData($vars['popup'], $vars, 'popup');
                buildUnitData($vars['normalX'], $vars, 'normalX');
                buildUnitData($vars['normalY'], $vars, 'normalY');
                buildUnitData($vars['largeX'], $vars, 'largeX');
                buildUnitData($vars['largeY'], $vars, 'largeY');

                foreach ( $vars as $key => $val ) {
                    if ( $val == '' ) {
                        unset($vars[$key]);
                    }
                }

            } else {
                $Tpl->add(array_merge(array('preview#none', $type), $rootBlock));
            }

            //------
            // size
//            if ( empty($size) ) {
//                $vars['size:selected#none'] = config('attr_selected');
//            }

            //-------
            // rotate
            if ( function_exists('imagerotate') ) {
                $count = count(explodeUnitData($data['path']));
                for ( $i=0; $i<$count; $i++ ) {
                    if ( empty($i) ) $n = '';
                    else $n = $i + 1;
                    $Tpl->add(array_merge(array('rotate'.$n, $type), $rootBlock));
                }
            }

            //---------------
            // primary image
            if ( array_key_exists('primaryImage', $data) ) {
                $vars['primaryImageId'] = $id;
                if ( !empty($clid) and $data['primaryImage'] == $clid ) {
                    $vars['primaryImageChecked']    = config('attr_checked');
                }
            }

            $Tpl->add(array_merge(array($type), $rootBlock), $vars);

        //------
        // file
        } else if ( 'file' == $type ) {
            $vars  = array(
                'id'        => $id,
            );
            if ( !empty($data['path']) ) {
                $vars['old']      = $data['path'];
                $length = count(explodeUnitData($data['path']));
                buildUnitData($vars['old'], $vars, 'old');

                for ( $i=0; $i<$length; $i++ ) {
                    if ( empty($i) ) $fx = '';
                    else $fx = $i + 1;

                    if ( !isset($vars['old'.$fx]) ) {
                        continue;
                    }
                    $path   = $vars['old'.$fx];
                    $vars['basename'.$fx] = basename($path);

                    $e    = preg_replace('@.*\.(?=[^.]+$)@', '', $path);
                    $t   = null;
                    if ( in_array($e, configArray('file_extension_document')) ) {
                        $t   = 'document';
                    } else if ( in_array($e, configArray('file_extension_archive')) ) {
                        $t   = 'archive';
                    } else if ( in_array($e, configArray('file_extension_movie')) ) {
                        $t   = 'movie';
                    } else if ( in_array($e, configArray('file_extension_audio')) ) {
                        $t   = 'audio';
                    }
                    $cwd    = getcwd();
                    chdir(THEMES_DIR.'system/'.IMAGES_DIR.'fileicon/');
                    $icon   = glob($e.'.*') ? $e : $t;
                    chdir($cwd);

                    $vars['icon'.$fx]   = $icon;
                    $vars['type'.$fx]   = $icon;
                }

                $vars['caption']  = $data['caption'];
                $vars['deleteId'] = $id;

                buildUnitData($vars['caption'], $vars, 'caption');
            }
            $Tpl->add(array_merge(array($type), $rootBlock), $vars);

        //-----
        // map
        } else if ( 'map' == $type ) {
            foreach ( configArray('column_map_size') as $i => $size ) {
                $vars  = array(
                    'value'   => $size,
                    'label'   => config('column_map_size_label', '', $i),
                    'display' => config('column_map_display_size', '', $i),
                );
                if ( $data['size'] == $size ) {
                    $vars['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('size:loop', $type), $rootBlock), $vars);
            }

            $Tpl->add(array_merge(array($type), $rootBlock), array(
                'lat'   => $data['lat'],
                'lng'   => $data['lng'],
                'zoom'  => $data['zoom'],
                'msg'   => $data['msg'],
                'id'    => $id,
            ));

        //-------
        // yolp
        } else if ( 'yolp' == $type ) {
            foreach ( configArray('column_map_size') as $i => $size ) {
                $vars  = array(
                    'value'   => $size,
                    'label'   => config('column_map_size_label', '', $i),
                    'display' => config('column_map_display_size', '', $i),
                );
                if ( $data['size'] == $size ) {
                    $vars['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('size:loop', $type), $rootBlock), $vars);
            }
            foreach ( configArray('column_map_layer_type') as $j => $layer ) {
                $vars  = array(
                    'value' => $layer,
                    'label' => config('column_map_layer_type_label', '', $j),
                );
                if ( $data['layer'] == $layer ) {
                    $vars['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('layer:loop', $type), $rootBlock), $vars);
            }
            $Tpl->add(array_merge(array($type), $rootBlock), array(
                'lat'   => $data['lat'],
                'lng'   => $data['lng'],
                'layer' => $data['layer'],
                'zoom'  => $data['zoom'],
                'msg'   => $data['msg'],
                'id'    => $id,
            ));

        //---------
        // youtube
        } else if ( 'youtube' == $type ) {
            foreach ( configArray('column_youtube_size') as $i => $size ) {
                $vars  = array(
                    'value'   => $size,
                    'label'   => config('column_youtube_size_label', '', $i),
                    'display' => config('column_youtube_display_size', '', $i),
                );
                if ( $data['size'] == $size ) {
                    $vars['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('size:loop', $type), $rootBlock), $vars);
            }
            $vars = array('id' => $id);
            buildUnitData($data['youtube_id'], $vars, 'youtubeId');

            $Tpl->add(array_merge(array($type), $rootBlock), $vars);

        //---------
        // video
        } else if ( 'video' == $type ) {
            foreach ( configArray('column_video_size') as $i => $size ) {
                $vars  = array(
                    'value'   => $size,
                    'label'   => config('column_video_size_label', '', $i),
                    'display' => config('column_video_display_size', '', $i),
                );
                if ( $data['size'] == $size ) {
                    $vars['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('size:loop', $type), $rootBlock), $vars);
            }
            $vars = array('id' => $id);
            buildUnitData($data['video_id'], $vars, 'videoId');

            $Tpl->add(array_merge(array($type), $rootBlock), $vars);

        //---------
        // eximage
        } else if ( 'eximage' == $type ) {
            if ( !empty($size) and ($xy = explode('x', $size)) ) {
                $x  = intval($xy[0]);
                $y  = intval(ite($xy, 1));
                $size   = max($x, $y);
            }

            $match  = false;
            foreach ( configArray('column_eximage_size') as $i => $_size ) {
                $vars  = array(
                    'value'   => $_size,
                    'label'   => config('column_eximage_size_label', '', $i),
                    'display' => config('column_eximage_display_size', '', $i),
                );
                if ( $size == $_size ) {
                    $vars['selected']  = config('attr_selected');
                    $match  = true;
                }
                $Tpl->add(array_merge(array('size:loop', $type), $rootBlock), $vars);
            }
            $vars  = array(
                'caption'   => $data['caption'],
//                'normal'    => $data['normal'],
                'large'     => $data['large'],
                'link'      => $data['link'],
                'alt'       => $data['alt'],
                'id'        => $id,
            );
            if ( !empty($data['normal']) ) $vars['normal']  = $data['normal'];

            if ( !$match ) $vars['size:selected#none'] = config('attr_selected');

            buildUnitData($data['caption'], $vars, 'caption');
            buildUnitData($data['normal'], $vars, 'normal');
            buildUnitData($data['large'], $vars, 'large');
            buildUnitData($data['link'], $vars, 'link');
            buildUnitData($data['alt'], $vars, 'alt');

            $Tpl->add(array_merge(array($type), $rootBlock), $vars);

        //---------
        // quote
        } else if ( 'quote' == $type ) {
            $vars = array(
                'quote_url' => $data['quote_url'],
                'html'      => isset($data['html']) ? $data['html'] : '',
                'site_name' => isset($data['site_name']) ? $data['site_name'] : '',
                'author'    => isset($data['author']) ? $data['author'] : '',
                'title'     => isset($data['title']) ? $data['title'] : '',
                'description'   => isset($data['description']) ? $data['description'] : '',
                'image'     => isset($data['image']) ? $data['image'] : '',
                'id'        => $id,
            );
            buildUnitData($vars['quote_url'], $vars, 'quote_url');
            buildUnitData($vars['html'], $vars, 'html');
            buildUnitData($vars['site_name'], $vars, 'site_name');
            buildUnitData($vars['author'], $vars, 'author');
            buildUnitData($vars['title'], $vars, 'title');
            buildUnitData($vars['description'], $vars, 'description');
            buildUnitData($vars['image'], $vars, 'image');

            $Tpl->add(array_merge(array($type), $rootBlock), $vars);

        //---------
        // media
        } else if ( 'media' == $type ) {
            $DB     = DB::singleton(dsn());

            $midAry = explodeUnitData($data['media_id']);
            $vars   = array();
            foreach ( $midAry as $i => $mid ) {
                if ( empty($i) ) $fx = '';
                else $fx = $i + 1;

                $SQL    = SQL::newSelect('media');
                $SQL->addWhereOpr('media_id', $mid);
                $media = $DB->query($SQL->get(dsn()), 'row');

                $path   = $media['media_path'];
                $ext    = ite(pathinfo($path), 'extension');
                $vars   += array(
                    'id'            => $id,
                    'media_id'.$fx  => $mid,
                    'caption'.$fx   => $media['media_field_1'],
                    'link'.$fx      => $media['media_field_2'],
                    'alt'.$fx       => $media['media_field_3'],
                    'text'.$fx      => $media['media_field_4'],
                    'type'.$fx      => $media['media_type'],
                    'path'.$fx      => $path,
                    'tiny'.$fx      => otherSizeImagePath($path, 'tiny'),
                );
                if ( !empty($ext) ) {
                    $vars['icon'.$fx]   = pathIcon($ext);
                }
                if ( !empty($media['media_thumbnail']) ) {
                    $vars['thumbnail'.$fx] = $media['media_thumbnail'];
                }
            }

            foreach ( configArray('column_media_size') as $i => $size ) {
                $sizeAry  = array(
                    'value'   => $size,
                    'label'   => config('column_media_size_label', '', $i),
                    'display' => config('column_media_display_size', '', $i),
                );
                if ( $data['size'] == $size ) {
                    $sizeAry['selected']  = config('attr_selected');
                }
                $Tpl->add(array_merge(array('size:loop', $type), $rootBlock), $sizeAry);
            }
            $Tpl->add(array_merge(array($type), $rootBlock), $vars);
    
        //-------
        // break
        } else if ( 'break' == $type ) {
            $Tpl->add(array_merge(array($type), $rootBlock), array(
                'label' => $data['label'],
                'id'    => $id,
            ));

        //--------
        // module
        } else if ( 'module' == $type ) {
            $mid    = $data['mid'];
            $tpl    = $data['tpl'];
            $vars   = array(
                'mid'   => $mid,
                'tpl'   => $tpl,
                'id'    => $id,
            );
            if ( !empty($mid) ) {
                $module     = loadModule($mid);
                $name       = $module->get('name');
                $identifier = $module->get('identifier');
                $vars['view'] = ACMS_GET_Layout::spreadModule($name, $identifier, $tpl);
            }
            $Tpl->add(array_merge(array($type), $rootBlock), $vars);

        //--------
        // custom
        } else if ( 'custom' == $type ) {
            if ( !empty($data['field']) ) {
                $Field  = acmsUnserialize($data['field']);
                if ( !method_exists($Field, 'listFields') ) $Field = null;
            }
            $block      = array_merge(array($typeS), $rootBlock);
            $vars       = array('id' => $id);
            if ( isset($Field) ) {
                $vars   += $this->buildField($Field, $Tpl, $block);
            }
            $Tpl->add($block, $vars);

        } else {
            return false;
        }

        return true;
    }

    function buildFormColumn($data, & $Tpl, $rootBlock=array())
    {
        $rootBlock  = empty($rootBlock) ? array() : 
            (is_array($rootBlock) ? $rootBlock : array($rootBlock))
        ;
        $id     = $data['id'];
        $type   = $data['type'];

        //----------------
        // text, textarea
        if ( in_array($type, array('text', 'textarea')) ) {
            
        //-------------------------
        // radio, select, checkbox
        } else if ( in_array($type, array('radio', 'select', 'checkbox')) ) {
            if ( 1
                && isset($data['values']) 
                && $values = acmsUnserialize($data['values'])
            ) {
                if ( is_array($values) ) {
                    foreach ( $values as $val ) {
                        if ( !empty($val) ) {
                            $Tpl->add(array_merge(array($type.'_value:loop'), $rootBlock), array(
                                'value' => $val,
                                'id'    => $id,
                            ));
                        }
                    }
                }
            }
        } else {
            return false;
        }

        $data = array_merge(array(
            'type'              => '',
            'label'             => '',
            'caption'           => '',
            'validator'         => array(),
            'validator-value'   => array(),
            'validator-message' => array(),
        ), $data);

        //---------------
        // label caption
        $Tpl->add(array_merge(array($type), $rootBlock), array(
            'label'             => $data['label'],
            'caption'           => $data['caption'],
            'id'                => $id,
        ));
        //------------
        // validator
        if ( isset($data['validatorSet']) ) {
            $validatorSet   = acmsUnserialize($data['validatorSet']);
            if ( is_array($validatorSet) ) {
                $validator      = $validatorSet['validator'];
                $validator_val  = $validatorSet['validator-value'];
                $validator_mess = $validatorSet['validator-message'];
            } else {
                $validator      = array();
                $validator_val  = array();
                $validator_mess = array();
            }
        } else {
            $validator      = $data['validator'];
            $validator_val  = $data['validator-value'];
            $validator_mess = $data['validator-message'];
        }
        
        foreach ( $validator as $j => $val ) {
            if ( !empty($val) ) {
                $Tpl->add(array_merge(array('option:loop'), $rootBlock), array(
                    'validator'                 => $val,
                    'validator:selected#'.$val  => config('attr_selected'),
                    'validator-value'           => $validator_val[$j],
                    'validator-message'         => $validator_mess[$j],
                    'id'                        => $id,
                ));
            }
        }
        return true;
    }
}
