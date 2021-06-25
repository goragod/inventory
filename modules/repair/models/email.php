<?php

/**
 * @filesource modules/repair/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Repair\Email;

use Kotchasan\Date;
use Kotchasan\Language;

/**
 * ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ส่งอีเมลแจ้งการทำรายการ
     *
     * @param int $id
     */
    public static function send($id)
    {
        // ตรวจสอบรายการที่ต้องการ
        $order = \Kotchasan\Model::createQuery()
            ->from('repair R')
            ->join('inventory_items I', 'LEFT', array('I.product_no', 'R.product_no'))
            ->join('inventory V', 'LEFT', array('V.id', 'I.inventory_id'))
            ->join('user U', 'LEFT', array('U.id', 'R.customer_id'))
            ->where(array('R.id', $id))
            ->first('R.job_id', 'R.product_no', 'V.topic', 'R.job_description', 'R.create_date', 'U.username', 'U.name');
        if ($order) {
            $ret = array();
            // ข้อความ
            $content = array(
                '{LNG_Repair} ' . $order->job_id,
                '{LNG_Serial/Registration No.} ' . $order->product_no,
                '{LNG_Equipment} ' . $order->topic,
                '{LNG_problems and repairs details} ' . $order->job_description,
                '{LNG_Date} ' . Date::format($order->create_date, 'd M Y'),
                '{LNG_Informer} ' . $order->name,
            );
            $msg = Language::trans(implode("\n", $content));
            $admin_msg = $msg . "\nURL: " . WEB_URL . 'index.php?module=repair-setup';
            if (self::$cfg->noreply_email != '') {
                // หัวข้ออีเมล
                $subject = '[' . self::$cfg->web_title . '] ' . Language::get('Repair');
                // ส่งอีเมลไปยังผู้ทำรายการเสมอ
                $err = \Kotchasan\Email::send($order->name . '<' . $order->username . '>', self::$cfg->noreply_email, $subject, nl2br($msg));
                if ($err->error()) {
                    $ret[] = strip_tags($err->getErrorMessage());
                }
                // อีเมลของผู้ดูแล
                $query = \Kotchasan\Model::createQuery()
                    ->select('username', 'name')
                    ->from('user')
                    ->where(array(
                        array('social', 0),
                        array('active', 1),
                        array('username', '!=', $order->username),
                    ))
                    ->andWhere(array(
                        array('status', 1),
                        array('permission', 'LIKE', '%,can_manage_repair,%'),
                    ), 'OR')
                    ->cacheOn();
                $emails = array();
                foreach ($query->execute() as $item) {
                    $emails[$item->username] = $item->name . '<' . $item->username . '>';
                }
                if (!empty($emails)) {
                    $err = \Kotchasan\Email::send(implode(',', $emails), self::$cfg->noreply_email, $subject, nl2br($admin_msg));
                    if ($err->error()) {
                        $ret[] = strip_tags($err->getErrorMessage());
                    }
                }
            }
            if (!empty(self::$cfg->line_api_key)) {
                // ส่งไลน์
                $err = \Gcms\Line::send($admin_msg);
                if ($err != '') {
                    $ret[] = $err;
                }
            }
            // คืนค่า
            if (self::$cfg->noreply_email != '' || !empty(self::$cfg->line_api_key)) {
                return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", $ret);
            } else {
                return Language::get('Saved successfully');
            }
        }
        // not found
        return Language::get('Unable to complete the transaction');
    }
}
