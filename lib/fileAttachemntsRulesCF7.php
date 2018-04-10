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
        add_action( 'admin_enqueue_scripts', [$this,'mediaScrips' ]);
    }

    public function setNewSectionCF7($panels) {
            $panels['file-attachments'] = array(
                'title' => __( 'File Attachments', 'wpcf7cf' ),
                'callback' => [$this, 'fileAttachmentsRulesPage']
            );
            return $panels;
    }


    public function mediaScrips() {
        wp_enqueue_media();
    }

    public function initCF7beforeSend($wcf7) {
        return fileAttachmentsCF7beforeSend::updateValuesCF7beforeSend($wcf7);
    }

    public function fileAttachmentsRulesPage() {
        ?>
        <table class="wp-list-table widefat fixed striped posts">
            <tr>
                <th>Shortcode</th>
                <th>Url</th>
            </tr>
            <?php $this->getAttachments('single'); ?>
        </table>
        <?php
        $this->setFormsToSaveImages('single');
        ?>
        <table class="wp-list-table widefat fixed striped posts">
            <tr>
                <th>Shortcode</th>
                <th>Url</th>
                <th>Condition</th>
            </tr>
            <?php $this->getAttachments('multiple'); ?>
        </table>
        <?php
        $this->setFormsToSaveImages('multiple');
    }

    private function filterByPrefix($keys, $prefix) {
        foreach(array_keys($keys) as $key) {
            if( strpos($key, $prefix) === 0) {
                $filtered[] = $key;
            }
        }

        return isset($filtered) ? $filtered : [];
    }

    private function getAttachments($prefix) {
        $keys =  get_post_meta($_GET['post'], '',false);
        $names = $this->filterByPrefix($keys, $prefix);

        foreach($names as $name):

            ?>
            <tr>
                <th>[<?= $name ?>]</th>
                <th><?= $this->getAttachmentURL($keys[$name]) ?></th>
                <?= $prefix === 'multiple' ? '<th><input type="text"></th>' : '' ?>
            </tr>
            <?php
        endforeach;
    }

    private function getAttachmentURL($id) {
        $serialize = array_shift($id);
        $item = json_decode($serialize)->item;

        return get_post($item)->guid;
    }

    private function setFormsToSaveImages($kind) {
        return fileAttachmentsUpload::getUploadImageGalleryOption($kind);
    }

    public function saveAttachmentCF7() {
        if(isset($_POST['saveAttachmentCF7'])) {
            $arguments = [
                'item' =>  $_POST['saveAttachmentCF7'],
                'condition' => ''
            ];

           update_post_meta($_POST['post_ID'], $_POST['saveAttachmentCF7type'].'_attachment_cf7'. rand(), json_encode($arguments));
        }
    }
}