<?php
/**
 * ACMS_GET_Admin_Schedule_View
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Schedule_View extends ACMS_GET_Plugin_Schedule
{
    function get()
    {
        // POSTから年月日を取得
        if ( !empty($_POST['dateArgs']) ) {
            $dateArgs = explode('-', $_POST['dateArgs']);
        } elseif ( !empty($_POST['yearArg']) && !empty($_POST['monthArg']) ) {
            $dateArgs = array();
            $dateArgs[0] = $_POST['yearArg'];
            $dateArgs[1] = $_POST['monthArg'];
        } elseif ( !empty($_POST['year']) && !empty($_POST['month']) ) {
            $dateArgs = array();
            $dateArgs[0] = $_POST['year'];
            $dateArgs[1] = $_POST['month'];
        }

        $this->year     = !empty($dateArgs[0])     ? intval($dateArgs[0])     : date('Y');
        $this->month    = !empty($dateArgs[1])     ? intval($dateArgs[1])     : date('n');
        $this->day      = !empty($dateArgs[2])     ? intval($dateArgs[2])     : 1;

        // リストモード固定＆コンフィグの代入
        $this->listmode = true;
        $this->sep      = config('schedule_label_separator');
        $this->plan     = config('schedule_multi_plan');
        $this->forwardM = 0; //config('schedule_forwardM');
        $this->backM    = 0; //config('schedule_backM');
        $this->forwardD = 0; //config('schedule_forwardD');
        $this->backD    = 0; //config('schedule_backD');
        $this->formatY  = config('schedule_formatY');
        $this->formatM  = config('schedule_formatM');
        $this->formatD  = config('schedule_formatD');
        $this->formatD  = config('schedule_formatD');
        $this->formatW  = config('schedule_formatW');
        $this->key      = $this->Get->get('scid');
        $this->labels   = configArray('schedule_label@'.$this->key);

        $Tpl = new Template($this->tpl, new ACMS_Corrector());

        $this->monthView($Tpl);

        return $Tpl->get();
    }
}