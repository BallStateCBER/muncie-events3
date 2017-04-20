<?php
    use Cake\Core\Configure;

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
          <div class="card-block">
                    <ul id="selected_images">
                        <?php if (!empty($this->request->data['EventsImages'])): ?>
                            <?php foreach ($this->request->data['EventsImages'] as $selected_image): ?>
                                <?php
                                    $id = $selected_image['image_id'];
                                    $filename = $selected_image['filename'];
                                ?>
                                <li id="selectedimage_<?php echo $id; ?>" data-image-id="<?php echo $id; ?>">
                                    <img src="/img/icons/arrow-move.png" class="handle" alt="Move" title="Move" />
                                    <a href="#" class="remove"><img src="/img/icons/cross.png" class="remove" alt="Remove" title="Remove" /></a>
                                    <?php echo $this->Calendar->thumbnail('tiny', [
                                        'filename' => $filename,
                                        'class' => 'selected_image'
                                    ]); ?>
                                    <?php echo $this->Form->input("Images.$id", [
                                        'label' => 'Caption:',
                                        'div' => false,
                                        'type' => 'text',
                                        'value' => $selected_image['caption'],
                                        'placeholder' => "Enter a caption for this image",
                                        'class' => 'caption'
                                    ]); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
          </div>
        </div>
      </div>
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
                      <li>Each file cannot exceed <?php echo $manual_filesize_limit; ?>B</li>
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
    $this->Js->buffer("
        ImageManager.setupUpload({
            token: '".md5(Configure::read('upload_verify_token').time())."',
            user_id: '".$this->request->session()->read('Auth.User.id')."',
            event_id: ".(isset($event_id) ? $event_id : 'null').",
            filesize_limit: '{$manual_filesize_limit}B',
            timestamp: ".time()."
        });
        ImageManager.setupManager();
    ");
?>
