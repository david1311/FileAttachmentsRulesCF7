<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 10/04/18
 * Time: 11:04
 */
namespace FileAttachments;

class fileAttachmentsRulesCF7
{
    public function __construct()
    {
        add_filter('wpcf7_editor_panels', [$this,'setNewSectionCF7']);
        add_action('admin_footer', [$this, 'testUpload']);
        add_action("wpcf7_before_send_mail", [$this, 'initCF7beforeSend']);
        add_action('save_post', [$this, 'saveAttachmentCF7']);
        add_action('save_post', [$this, 'saveConditionAttachmentCF7']);
    }

    private $kinds = [
            'multiple',
            'single'
    ];

    private function getMetaValueDecoded($id, $name) {
        $serialize = array_shift($id);
        $item = json_decode($serialize)->{$name};

        return $item;
    }

    private function getAttachmentURL($id) {
        $item = $this->getMetaValueDecoded($id, 'item');
        return get_post($item)->guid;
    }

    private function getAttachmentCondition($id) {
        $condition = $this->getMetaValueDecoded($id, 'condition');
        return $condition;
    }

    private function setFormsToSaveImages($kind) {
        fileAttachmentsUpload::getUploadImageGalleryOption($kind);
    }

    public function initCF7beforeSend($wcf7) {
        fileAttachmentsCF7beforeSend::updateValuesCF7beforeSend($wcf7);
    }

    private function filterByPrefix($keys, $prefix) {
        foreach(array_keys($keys) as $key) {
            if( strpos($key, $prefix) === 0) {
                $filtered[] = $key;
            }
        }

        return isset($filtered) ? $filtered : [];
    }

    public function setNewSectionCF7($panels) {
            $panels['file-attachments'] = array(
                'title' => __( 'File Attachments', 'wpcf7cf' ),
                'callback' => [$this, 'fileAttachmentsRulesPage']
            );
            return $panels;
    }


    private function getAttachments($prefix) {
        $keys =  get_post_meta($_GET['post'], '',false);
        $names = $this->filterByPrefix($keys, $prefix);

        foreach($names as $name):
            $condition = $this->getAttachmentCondition($keys[$name])
            ?>
            <tr>
                <th>[<?= $name ?>]</th>
                <th><?= $this->getAttachmentURL($keys[$name]) ?></th>
                <?= $prefix === 'multiple' ? "<th><input type='text' name='condition_$name' value='$condition'></th>" : '' ?>
            </tr>
            <?php
        endforeach;
    }

    public function saveConditionAttachmentCF7() {
        if(isset($_POST)) {
            $filtered = array_filter(array_keys($_POST), function ($key) {
                return strpos($key, 'condition_') === 0;
            });

        }

        isset($filtered) ? $this->updateConditionsByPostID($filtered, $_POST['post_ID']) : [];
    }

    public function saveAttachmentCF7() {
        foreach($this->kinds as $kind) {
            if(isset($_POST["saveAttachmentCF7_$kind"]) && !empty($_POST["saveAttachmentCF7_$kind"])) {
                $arguments = [
                    'item' =>  $_POST["saveAttachmentCF7_$kind"],
                    'condition' => ''
                ];

                update_post_meta($_POST['post_ID'], $_POST["saveAttachmentCF7type_$kind"].'_attachment_cf7'. rand(), json_encode($arguments));
            }
        }
    }

    private function updateConditionsByPostID($filtered, $id) {
        foreach($filtered as $key) {
            $metaKey = str_replace('condition_', '', $key);
            $meta = json_decode(get_post_meta($id, $metaKey)[0]);

            $meta->condition = $_POST[$key];

            if(!empty($_POST[$key])) {
                update_post_meta($id, $metaKey, json_encode($meta));
            }
        }
    }

    public function fileAttachmentsRulesPage() {
        ?>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th>Shortcode</th>
                <th>Url</th>
            </tr>
            </thead>
            <?php $this->getAttachments('single'); ?>
        </table>
        <?php
        $this->setFormsToSaveImages('single');
        ?>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th>Shortcode</th>
                <th>Url</th>
                <th>Condition</th>
            </tr>
            </thead>
            <?php $this->getAttachments('multiple'); ?>
        </table>
        <?php
        $this->setFormsToSaveImages('multiple');
    }
}