<?php
/**
 * ACMS_GET_Ajax_Unit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Ajax_Unit extends ACMS_GET
{
    function get()
    {
        if ( !($column = $this->Get->get('column')) ) { return false; }
        list($pfx, $type)   = explode('-', $column, 2);

        // typeで参照できるラベルの連想配列
        $aryTypeLabel    = array();
        foreach ( configArray('column_add_type') as $i => $_type ) {
            $aryTypeLabel[$_type]    = config('column_add_type_label', '', $i);
        }

        // 特定指定子を含むユニットタイプ
        $actualType = $type;
        // 特定指定子を除外した、一般名のユニット種別
        $type = detectUnitTypeSpecifier($type);

        $Config = new Field(Field::singleton('config'));
        if ( $rid = intval($this->Get->get('rid')) ) {
            $Config->overload(loadConfig(BID, $rid));
        }

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $Column = new Field();
        $Column->setField('pfx', $pfx);
        switch ( $type ) {
            case 'text':
                foreach ( $Config->getArray('column_text_tag') as $i => $tag ) {
                    $Tpl->add(array('textTag:loop', $type), array(
                        'value' => $tag,
                        'label' => $Config->get('column_text_tag_label', '', $i),
                    ));
                }
                break;
            case 'image':
                foreach ( $Config->getArray('column_image_size') as $j => $size ) {
                    $Tpl->add(array('size:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_image_size_label', '', $j),
                    ));
                }
                break;
            case 'file':
                break;
            case 'map':
                foreach ( $Config->getArray('column_map_size') as $j => $size ) {
                    $Tpl->add(array('size:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_map_size_label', '', $j),
                    ));
                }
                break;
            case 'yolp':
                foreach ( $Config->getArray('column_map_size') as $j => $size ) {
                    $Tpl->add(array('size:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_map_size_label', '', $j),
                    ));
                }
                foreach ( $Config->getArray('column_map_layer_type') as $j => $layer ) {
                    $Tpl->add(array('layer:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_map_layer_type_label', '', $j),
                    ));
                }
                break;
            case 'youtube':
                foreach ( $Config->getArray('column_youtube_size') as $j => $size ) {
                    $Tpl->add(array('size:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_youtube_size_label', '', $j),
                    ));
                }
                break;
            case 'video':
                foreach ( $Config->getArray('column_video_size') as $j => $size ) {
                    $Tpl->add(array('size:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_video_size_label', '', $j),
                    ));
                }
                break;
            case 'eximage':
                foreach ( $Config->getArray('column_eximage_size') as $j => $size ) {
                    $Tpl->add(array('size:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_eximage_size_label', '', $j),
                    ));
                }
                break;
            case 'quote':
                
                break;
            case 'media':
                foreach ( $Config->getArray('column_media_size') as $j => $size ) {
                    $Tpl->add(array('size:loop', $type), array(
                        'value' => $size,
                        'label' => $Config->get('column_media_size_label', '', $j),
                    ));
                }
                break;
            case 'break':
                break;
            case 'custom':
                break;
            default:
                return '';
        }

        if ( 'on' === config('unit_group') ) {
            $classes = configArray('unit_group_class');
            $labels  = configArray('unit_group_label');
            foreach ( $labels as $i => $label ) {
                $Tpl->add(array('group:loop', 'group:veil', $type), array(
                     'group.value'     => $classes[$i],
                     'group.label'     => $label,
                     'group.selected'  => ($classes[$i] === $Config->get('group')) ? config('attr_selected') : '',
                ));
            }
            $Tpl->add(array('group:veil', $type), array(
                'group.pfx' => $Column->get('pfx'),
            ));
        }

        $vars   = $this->buildField($Column, $Tpl, $type, 'column');
        $vars  += array(
            'actualType'  => $actualType,
            'actualLabel' => $aryTypeLabel[$actualType],
        );

        $Tpl->add($type, $vars);
        return $Tpl->get();
    }
}
