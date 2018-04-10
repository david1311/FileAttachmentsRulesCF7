<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 10/04/18
 * Time: 11:48
 */

namespace FileAttachments;


use Hoa\Ruler\Ruler;

class fileAttachmentsCF7beforeSend
{
    public static function updateValuesCF7beforeSend(&$wpcf7_data) {
        $itemID = self::getValueByShortcode($wpcf7_data->mail['attachments']);
        $item = json_decode($itemID)->item;

        if(isset($item->condition) && self::isRuleValid($item->condition, $wpcf7_data)) {
            $wpcf7_data->mail['attachments'] = get_post($item)->guid;
            $wpcf7_data->skip_mail = true;
        }
    }

    private static function getValueByShortcode($shortcode) {
        global $wpdb;
        $shortcode = preg_replace("/\[(.+)]/", "$1", $shortcode);

        $metaValue = $wpdb->get_results(
            $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta where meta_key = %s", $shortcode)
        );

       return $metaValue[0]->meta_value;
    }

    private static function isRuleValid($rule, $cf7) {
        $ruler = new Ruler();
        $context['subject'] = $cf7->subject;

        return $ruler->assert($rule, $context);
    }
}