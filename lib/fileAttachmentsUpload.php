<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 10/04/18
 * Time: 11:48
 */

namespace FileAttachments;


class fileAttachmentsUpload
{
    public static function getUploadImageGalleryOption($kind) {
        ?>
        <div class="upload">
            <div>
                <input type="hidden" name="saveAttachmentCF7type" id="saveAttachmentCF7type" value="<?= $kind ?>" />
                <input type="hidden" name="saveAttachmentCF7" id="saveAttachmentCF7" value="" />

                <button type="submit" class="upload_image_button button"><?= __( 'Upload', RSSFI_TEXT ) ?></button>
            </div>
        </div>
        <?php
        self::uploadImageGallery();
    }

    private static function uploadImageGallery() {
        ?>
        <script>
            jQuery('.upload_image_button').click(function() {
                var send_attachment_bkp = wp.media.editor.send.attachment;
                var button = jQuery(this);
                wp.media.editor.send.attachment = function(props, attachment) {
                    jQuery(button).parent().prev().attr('src', attachment.url);
                    jQuery(button).prev().val(attachment.id);
                    wp.media.editor.send.attachment = send_attachment_bkp;
                }
                wp.media.editor.open(button);
                return false;
            });
        </script>
        <?php
    }
}