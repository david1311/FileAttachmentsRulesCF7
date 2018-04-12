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
use WP_Query;

class fileAttachmentsCF7beforeSend
{
    public static function getItemAfterCheckIfConditionsAreValid(&$cf7)
    {
        $attachments = self::getValueByShortcode($cf7->mail['attachments']);

        foreach($attachments as $attachment) {
            $item = json_decode($attachment->meta_value)->item;

            if(self::allowOrDenyMailSendByConditionValue($item) === true) {
               $items[] = self::getEmbedPath($item);
            }
        }

        return isset($items) ? $items : [];
    }


    private static function allowOrDenyMailSendByConditionValue($item): bool
    {
        if (self::isRuleValid($item->condition, $_POST) === true || empty($item->condition)) {
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
        if(is_int(strpos($shortcode, 'multiple'))) {
            $multipleValues = $wpdb->get_results(
                $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta where meta_key like %s","%" . $shortcode . "%")
            );

            return $multipleValues;
        }

        $metaValue = $wpdb->get_results(
            $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta where meta_key = %s", $shortcode)
        );

        return [$metaValue[0]];
    }

    private static function isRuleValid($rule, $values): bool
    {
        if(is_null($rule) || empty($rule) )  {
            return true;
        }

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