<?php
    use Cake\Core\Configure;

$this->Form->setTemplates([
        'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}" id="{{name}}"{{attrs}}>'
    ]);

$helpers = ['Html'];
    $upload_max = ini_get('upload_max_filesize');
    $post_max = ini_get('post_max_size');
    $server_filesize_limit = min($upload_max, $post_max);
    $manual_filesize_limit = min('10M', $server_filesize_limit);
    echo $this->Html->script('/js/image_manager.js', ['inline' => false]);
?>

<div id="image_form">
    <div id="accordion" role="tablist" aria-multiselectable="true">
      <div class="card">
        <div class="card-header" role="tab" id="image_upload_heading">
          <h5 class="mb-0">
            <a id="image_upload_toggler" data-toggle="collapse" data-parent="#accordion" href="#image_upload_container" aria-expanded="false" aria-controls="image_upload_container">
              Upload new image
            </a>
          </h5>
        </div>

        <div id="image_upload_container" class="collapse" role="tabpanel" aria-labelledby="image_upload_heading">
          <div class="card-block">
	        <a href="#" id="image_upload_button">Select image</a>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header" role="tab" id="image_select_heading">
          <h5 class="mb-0">
            <a id="image_select_toggler" class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#image_select_container" aria-expanded="false" aria-controls="image_select_container">
              Select a previously uploaded image
            </a>
          </h5>
        </div>
        <div id="image_select_container" class="collapse" role="tabpanel" aria-labelledby="image_select_heading">
        </div>
      </div>
      <ul id="selected_images">
          <?php if (!empty($event->images)): ?>
              <?php foreach ($event->images as $eventImage): ?>
                  <?php
                      $id = $eventImage['id'];
                      $filename = $eventImage['filename'];
                  ?>
                  <li id="selectedimage_<?= $id; ?>" data-image-id="<?= $id; ?>">
                      <img src="/img/icons/arrow-move.png" class="handle" alt="Move" title="Move" />
                      <label class="remove" for="delete[<?= $id ?>]">
                          Delete?
                      </label>
                      <?= $this->Form->checkbox("delete[$id]"); ?>
                      <?= $this->Calendar->thumbnail('tiny', [
                          'filename' => $filename,
                          'class' => 'selected_image'
                      ]); ?>
                      <?= $this->Form->input("newImages[$id]", [
                          'label' => 'Caption:',
                          'div' => false,
                          'type' => 'text',
                          'value' => $eventImage['_joinData']['caption'],
                          'placeholder' => "Enter a caption for this image",
                          'class' => 'caption'
                      ]); ?>
                  </li>
              <?php endforeach; ?>
          <?php endif; ?>
      </ul>
      <div class="card">
        <div class="card-header" role="tab" id="image_help_heading">
          <h5 class="mb-0">
            <a id="image_help_toggler" class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#image_help_container" aria-expanded="false" aria-controls="image_help_container">
              Help & rules
            </a>
          </h5>
        </div>
        <div id="image_help_container" class="collapse" role="tabpanel" aria-labelledby="image_help_heading">
          <div class="card-block">
            <h3>Uploading</h3>
                  <ul class="footnote">
                      <li>Images must be .jpg, .jpeg, .gif, or .png.</li>
                      <li>Each file cannot exceed <?= $manual_filesize_limit; ?>B</li>
                      <li>You can upload an image once and re-use it in multiple events.</li>
                      <li>By uploading an image, you affirm that you are not violating any copyrights.</li>
                      <li>Images must not include offensive language, nudity, or graphic violence</li>
                  </ul>

                  <h3>After selecting images</h3>
                  <ul class="footnote">
                      <li>
                          The first image will be displayed as the event's main image.
                      </li>
                      <li>
                          Click on the <img src="/img/icons/arrow-move.png" alt="Move" title="Move" /> icon to drag images up or down and resort them.
                      </li>
                      <li>
                          Click on the <img src="/img/icons/cross.png" class="remove" alt="Remove" title="Remove" /> icon to unselect an image.
                      </li>
                  </ul>
          </div>
        </div>
      </div>
    </div>
</div>

</div>

<?php
    echo $this->Html->script('/uploadifive/jquery.uploadifive.min.js', ['inline' => false]);
    echo $this->Html->css('/uploadifive/uploadifive.css', ['inline' => false]);
?>
<script>
    ImageManager.setupUpload({
        token: '<?= md5(Configure::read('App.upload_verify_token').time()) ?>',
        user_id: '<?= $userId ?>',
        event_id: "<?= (isset($event->id) ? $event->id : 'null') ?>",
        filesize_limit: '<?= $manual_filesize_limit ?>B',
        timestamp: "<?= time() ?>",
        event_img_base_url: '<?= Configure::read('App.eventImageBaseUrl') ?>'
    });
    ImageManager.user_id = <?= $userId ?>;
    ImageManager.setupManager();
</script>
