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

    public function fileAttachmentsRulesPage() {
        $this->getAttachments();
        $this->setFormsToSaveImages();
    }

    private function filterByPrefix($keys) {
        $filtered = array_filter(array_keys($keys), function($key) {
            return strpos($key, 'single_') === 0;
        });

        return $filtered;
    }
    private function getAttachments() {
        $keys =  get_post_meta($_GET['post'], '',false);
        $names = $this->filterByPrefix($keys);

        foreach($names as $name) {
            ?>
            <p><?= $name ?>,<?= $this->getAttachmentURL($keys[$name]) ?></p>
            <?php
        }
    }

    private function getAttachmentURL($id) {
        $id = array_shift($id);
        var_dump(get_post($id));

    }

    private function setFormsToSaveImages() {
        return fileAttachmentsUpload::getUploadImageGalleryOption();
    }

    public function saveAttachmentCF7() {
        if(isset($_POST['saveAttachmentCF7'])) {
           update_post_meta($_POST['post_ID'], 'single_attachment_cf7'. rand(), $_POST['saveAttachmentCF7']);
        }
    }
}