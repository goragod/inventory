<?php

/**
 * @filesource modules/repair/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Repair\Settings;

use Kotchasan\Html;

/**
 * module=repair-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ตั้งค่าโมดูล
     *
     * @return string
     */
    public function render()
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/repair/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Module settings}',
        ));
        // repair_first_status
        $fieldset->add('select', array(
            'id' => 'repair_first_status',
            'labelClass' => 'g-input icon-tools',
            'itemClass' => 'item',
            'label' => '{LNG_Initial repair status}',
            'options' => \Repair\Status\Model::create()->toSelect(),
            'value' => isset(self::$cfg->repair_first_status) ? self::$cfg->repair_first_status : 1,
        ));
        // repair_job_no
        $fieldset->add('text', array(
            'id' => 'repair_job_no',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'item',
            'label' => '{LNG_Job No.}',
            'placeholder' => '{LNG_number format such as %04d (%04d means the number on 4 digits, up to 11 digits)}',
            'comment' => '{LNG_Leave empty for generate auto}',
            'value' => isset(self::$cfg->repair_job_no) ? self::$cfg->repair_job_no : 'job%04d',
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
