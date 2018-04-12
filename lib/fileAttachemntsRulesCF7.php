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
        add_filter('wpcf7_editor_panels', [$this, 'setNewSectionCF7']);
        add_action('admin_footer', [$this, 'testUpload']);
        add_action("wpcf7_before_send_mail", [$this, 'initCF7beforeSend']);
        add_action('save_post', [$this, 'saveAttachmentCF7']);
        add_action('save_post', [$this, 'deleteAttachmentCF7']);
        add_action('admin_init', [$this, 'enqueueStylesAndScripts']);
        add_filter('wpcf7_mail_components', [$this, 'filterComponentsToSendMail']);
        add_action('save_post', [$this, 'saveConditionAttachmentCF7']);
    }

    private $attachmentsCF7;
    private $kinds = ['multiple', 'single'];

    public function filterComponentsToSendMail($components)
    {
        foreach ($this->attachmentsCF7 as $attachment) {
            $attachment != null ? $components['attachments'][] = $this->createTmpFileToSendInMail($attachment) : null;
        }

        return $components;
    }

    public function enqueueStylesAndScripts()
    {
        wp_enqueue_style('styles', plugin_dir_url(__FILE__) . 'assets/styles.css', false, '1.1', null);
    }

    private function getMetaValueDecoded($id, $name)
    {
        $serialize = array_shift($id);

        return json_decode($serialize)->{$name};
    }

    private function getAttachmentURL($id)
    {
        $item = $this->getMetaValueDecoded($id, 'item');

        return get_post($item)->guid;
    }

    private function getAttachmentCondition($id)
    {
        return $this->getMetaValueDecoded($id, 'condition');
    }

    private function setFormsToSaveImages($kind)
    {
        fileAttachmentsUpload::getUploadImageGalleryOption($kind);
    }

    public function initCF7beforeSend($wcf7)
    {
        $this->attachmentsCF7 = fileAttachmentsCF7beforeSend::getItemAfterCheckIfConditionsAreValid($wcf7);
    }

    private function filterByPrefix($keys, $prefix)
    {
        $filtered = array_filter(array_keys($keys), function ($key) use ($prefix) {
            return strpos($key, $prefix) === 0;
        });

        return $filtered;
    }

    public function setNewSectionCF7($panels)
    {
        $panels['file-attachments'] = array(
            'title'    => __('File Attachments', 'wpcf7cf'),
            'callback' => [$this, 'fileAttachmentsRulesPage'],
        );

        return $panels;
    }

    private function getAttachments($prefix)
    {
        $keys  = get_post_meta($_GET['post'], '', false);
        $names = $this->filterByPrefix($keys, $prefix);

        foreach ($names as $name):
            $condition = $this->getAttachmentCondition($keys[$name])
            ?>
            <tr>
                <th>[<?= $prefix === 'multiple' ? 'multiple_attachment' : $name ?>]</th>
                <th><?= $this->getAttachmentURL($keys[$name]) ?></th>
                <th><?= $prefix === 'multiple' ? "<input type='text' class='input_box' name='condition_$name' value='$condition'>"
                        : 'Not allowed' ?></th>
                <th><input name="delete_<?= $name ?>" type="checkbox"></th>
            </tr>
        <?php
        endforeach;
    }


    private function createTmpFileToSendInMail($attachment)
    {
        $temporalName = sys_get_temp_dir() . '/' . pathinfo($attachment)['basename'];

        $temporalFile = tempnam(sys_get_temp_dir(), "TMP_FILE");
        rename($temporalFile, $temporalName);

        $handle = fopen($temporalName, "w");
        fwrite($handle, file_get_contents($attachment));

        return $temporalName;
    }

    public function saveConditionAttachmentCF7()
    {
        if (isset($_POST)) {
            $filtered = array_filter(array_keys($_POST), function ($key) {
                return strpos($key, 'condition_') === 0;
            });

        }

        isset($filtered) ? $this->updateConditionsByPostID($filtered, $_POST['post_ID']) : [];
    }

    public function deleteAttachmentCF7()
    {
        $toDelete = $this->filterByPrefix($_POST, 'delete_');

        if (is_array($toDelete)) {
            foreach ($toDelete as $item) {
                $metaKey = str_replace('delete_', '', $item);

                delete_post_meta($_POST['post_ID'], $metaKey);
            }
        }
    }

    public function saveAttachmentCF7()
    {
        foreach ($this->kinds as $kind) {
            if (isset($_POST["saveAttachmentCF7_$kind"]) && ! empty($_POST["saveAttachmentCF7_$kind"])) {
                $arguments = [
                    'item'      => $_POST["saveAttachmentCF7_$kind"],
                    'condition' => '',
                ];

                update_post_meta($_POST['post_ID'], $_POST["saveAttachmentCF7type_$kind"] . '_attachment_cf7' . rand(), json_encode($arguments));
            }
        }
    }

    private function updateConditionsByPostID($filtered, $id)
    {
        foreach ($filtered as $key) {
            $metaKey = str_replace('condition_', '', $key);
            $meta    = json_decode(get_post_meta($id, $metaKey)[0]);

            $meta->condition = $_POST[$key];

            if ( ! empty($_POST[$key])) {
                update_post_meta($id, $metaKey, json_encode($meta));
            }
        }
    }

    public function fileAttachmentsRulesPage()
    {
        foreach ($this->kinds as $kind):
            ?>
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                <tr>
                    <th>Shortcode</th>
                    <th>Url</th>
                    <th>Condition</th>
                    <th>Delete?</th>
                </tr>
                </thead>
                <?php $this->getAttachments($kind); ?>
            </table>
            <?php
            $this->setFormsToSaveImages($kind);
        endforeach;
    }
}