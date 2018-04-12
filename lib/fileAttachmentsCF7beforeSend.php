<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 10/04/18
 * Time: 11:48
 */

namespace FileAttachments;

use Hoa\Ruler\Context;
use Hoa\Ruler\Ruler;

class fileAttachmentsCF7beforeSend
{
    public static function getItemAfterCheckIfConditionsAreValid(&$cf7)
    {
        $itemID = self::getValueByShortcode($cf7->mail['attachments']);
        $item   = json_decode($itemID)->item;

        return self::allowOrDenyMailSendByConditionValue($item) === true ? self::getEmbedPath($item) : false;
    }


    private static function allowOrDenyMailSendByConditionValue($item): bool
    {
        if ( ! empty($item->condition) && self::isRuleValid($item->condition, $_POST) == true || empty($item->condition)) {
            return true;
        }

        return false;
    }

    private static function getEmbedPath($item) {
        return get_post($item)->guid;
    }


    private static function getValueByShortcode($shortcode)
    {
        global $wpdb;
        $shortcode = preg_replace("/\[(.+)]/", "$1", $shortcode);
        $metaValue = $wpdb->get_results(
            $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta where meta_key = %s", $shortcode)
        );

        return $metaValue[0]->meta_value;
    }

    private static function isRuleValid($rule, $values): bool
    {
        $ruler   = new Ruler();
        $context = new Context();

        foreach ($values as $key => $value) {
            if (is_int(strpos($rule, $key))) {
                $context[$key] = $value;
            }
        }

        $ruler->assert($rule, $context);
    }
}