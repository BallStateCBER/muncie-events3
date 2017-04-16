<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Images Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany $Events
 *
 * @method \App\Model\Entity\Image get($primaryKey, $options = [])
 * @method \App\Model\Entity\Image newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Image[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Image|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Image patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Image[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Image findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImagesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('images');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsToMany('Events', [
            'foreignKey' => 'image_id',
            'targetForeignKey' => 'event_id',
            'joinTable' => 'events_images'
        ]);
    }

    private $__errors = [];
    private $__fileToDelete = null;
    public $errors = [];
    public $maxHeight = 2000;
    public $maxWidth = 2000;
    public $tinyHeight = 50;
    public $tinyWidth = 50;
    public $tinyQuality = 90;
    public $smallWidth = 200;
    public $smallQuality = 90;
    public $fullQuality = 90;

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('filename', 'create')
            ->notEmpty('filename');

        $validator
            ->boolean('is_flyer')
            ->requirePresence('is_flyer', 'create')
            ->notEmpty('is_flyer');
            
        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    public function getNextId()
    {
        $result = $this->find('list', [
            'keyField' => 'id'
        ]);
        $result
            ->select('id')
            ->order(['id' => 'DESC'])
            ->first();
        foreach ($result as $val) {
            $result = intval($val);
            $result = $result + 1;
        }
        return $result;
    }

    public function autoResize($filepath)
    {
        list($width, $height, $type, $attr) = getimagesize($filepath);
        if ($width < $this->maxWidth && $height < $this->maxHeight) {
            // No resize necessary
            return true;
        }

        // Make longest side fit inside the maximum dimensions
        if ($width >= $height) {
            $new_width = $this->maxWidth;
            $new_height = 0;
        } else {
            $new_width = 0;
            $new_height = $this->maxHeight;
        }
        if ($this->resize($filepath, $filepath, $new_width, $new_height, $this->fullQuality)) {
            return true;
        }
        return false;
    }

    /**
     * Accepts the filename of an uploaded image and creates a tiny thumbnail
     * @param string $filename
     */
    public function createTiny($source_file)
    {
        $path = WWW_ROOT.'img'.DS.'events'.DS.'tiny'.DS;
        $filename = substr($source_file, strrpos($source_file, DS) + 1);
        $destination_file = $path.$filename;
        list($width, $height, $type, $attr) = getimagesize($source_file);

        // Make the shortest side fit inside the maximum dimensions
        if ($width >= $height) {
            $new_width = 0; // Automatically determined in ResizeBehavior::resize()
            $new_height = $this->tinyHeight;
        } else {
            $new_width = $this->tinyWidth;
            $new_height = 0;
        }
        if (! $this->resize($source_file, $destination_file, $new_width, $new_height, $this->tinyQuality)) {
            return false;
        }

        // Crop down the remaining longer size
        if (! $this->cropCenter($destination_file, $destination_file, $this->tinyWidth, $this->tinyHeight, $this->tinyQuality)) {
            return false;
        }

        return true;
    }

    /**
     * Accepts the filename of an uploaded image and creates a smaller (limited width) version
     * @param string $filename
     */
    public function createSmall($source_file)
    {
        $path = WWW_ROOT.'img'.DS.'events'.DS.'small'.DS;
        $filename = substr($source_file, strrpos($source_file, DS) + 1);
        $destination_file = $path.$filename;

        $new_width = $this->smallWidth;
        $new_height = 0; // Automatically determined in ResizeBehavior::resize()

        if (! $this->resize($source_file, $destination_file, $new_width, $new_height, $this->smallQuality)) {
            return false;
        }

        return true;
    }

    public function resize($source_file, $new_filename, $new_width = 0, $new_height = 0, $quality = 100)
    {
        if (! ($image_params = getimagesize($source_file))) {
            $this->errors[] = 'Original file is not a valid image: ' . $source_file;
            return false;
        }

        $width = $image_params[0];
        $height = $image_params[1];

        if (0 != $new_width && 0 == $new_height) {
            $scaled_width = $new_width;
            $scaled_height = floor($new_width * $height / $width);
        } elseif (0 != $new_height && 0 == $new_width) {
            $scaled_height = $new_height;
            $scaled_width = floor($new_height * $width / $height);
        } elseif (0 == $new_width && 0 == $new_height) { //assume we want to create a new image the same exact size
            $scaled_width = $width;
            $scaled_height = $height;
        } else { //assume we want to create an image with these exact dimensions, most likely resulting in distortion
            $scaled_width = $new_width;
            $scaled_height = $new_height;
        }

        //create image
        $ext = $image_params[2];
        switch ($ext) {
            case IMAGETYPE_GIF:
                $return = $this->__resizeGif($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            case IMAGETYPE_JPEG:
                $return = $this->__resizeJpeg($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            case IMAGETYPE_PNG:
                $return = $this->__resizePng($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            default:
                $return = $this->__resizeJpeg($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
        }

        return $return;
    }

    private function __resizeGif($original, $new_filename, $scaled_width, $scaled_height, $width, $height)
    {
        $error = false;

        if (!($src = imagecreatefromgif($original))) {
            $this->errors[] = 'There was an error creating your resized image (gif).';
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $this->errors[] = 'There was an error creating your true color image (gif).';
            $error = true;
        }

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (gif).';
            $error = true;
        }

        if (!($new_image = imagegif($tmp, $new_filename))) {
            $this->errors[] = 'There was an error writing your image to file (gif).';
            $error = true;
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality)
    {
        $error = false;

        if (!($src = imagecreatefromjpeg($original))) {
            $this->errors[] = 'There was an error creating your resized image (jpg).';
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $this->errors[] = 'There was an error creating your true color image (jpg).';
            $error = true;
        }

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (jpg).';
            $error = true;
        }

        if (!($new_image = imagejpeg($tmp, $new_filename, $quality))) {
            $this->errors[] = 'There was an error writing your image to file (jpg).';
            $error = true;
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizePng($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality)
    {
        $error = false;
        /**
         * we need to recalculate the quality for imagepng()
         * the quality parameter in imagepng() is actually the compression level,
         * so the higher the value (0-9), the lower the quality. this is pretty much
         * the opposite of how imagejpeg() works.
         */
        $quality = ceil($quality / 10); // 0 - 100 value
        if (0 == $quality) {
            $quality = 9;
        } else {
            $quality = ($quality - 1) % 9;
        }

        if (!($src = imagecreatefrompng($original))) {
            $this->errors[] = 'There was an error creating your resized image (png).';
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $this->errors[] = 'There was an error creating your true color image (png).';
            $error = true;
        }

        imagealphablending($tmp, false);

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (png).';
            $error = true;
        }

        imagesavealpha($tmp, true);

        if (!($new_image = imagepng($tmp, $new_filename, $quality))) {
            $this->errors[] = 'There was an error writing your image to file (png).';
            $error = true;
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $new_image;
        }

        return false;
    }

    /**
     * Saves to $destination_file the cropped center of $source_file.
     * @param string $source_file
     * @param string $destination_file
     * @param int $w
     * @param int $h
     * @param int $quality
     * @return boolean
     */
    public function cropCenter($source_file, $destination_file, $w, $h, $quality)
    {
        list($original_width, $original_height, $type, $attr) = getimagesize($source_file);
        $center_x = round($original_width / 2);
        $center_y = round($original_height / 2);
        $half_new_width = round($w / 2);
        $half_new_height = round($h / 2);
        $x = max(0, ($center_x - $half_new_width));
        $y = max(0, ($center_y - $half_new_height));
        return $this->crop($source_file, $destination_file, $w, $h, $x, $y, $quality);
    }

    /**
     * Crops $source_file and saves the result to $destination_file.
     * @param string $source_file
     * @param string $destination_file
     * @param int $w
     * @param int $h
     * @param int $x
     * @param int $y
     * @param int $quality
     * @return boolean
     */
    public function crop($source_file, $destination_file, $w, $h, $x, $y, $quality)
    {
        if (! $source_file || ! file_exists($source_file)) {
            $this->errors[] = 'No image found to crop';
            return false;
        }

        // Use for overriding destination image type
        $destination_img_type = false;

        // get image details
        $image_info = getimagesize($source_file);

        // set source as resource
        switch ($image_info['mime']) {
            case 'image/gif':
                $src = imagecreatefromgif($source_file);
                $img_type = ($destination_img_type) ? $destination_img_type : 'gif';
                break;
            case 'image/jpeg':
                $src = imagecreatefromjpeg($source_file) ;
                $img_type = ($destination_img_type) ? $destination_img_type : 'jpg';
                break;
            case 'image/png':
                $src = imagecreatefrompng($source_file);
                imagealphablending($src, true); // setting alpha blending on (we want to blend this image with the canvas)
                imagesavealpha($src, true); // save alphablending setting
                $img_type = ($destination_img_type) ? $destination_img_type : 'png';
                break;
            default:
                $this->errors[] = 'Cannot crop an image of type '.$image_info['mime'];
                return false;
        }

        // Source dimensions
        $s_width = imagesx($src);
        $s_height = imagesy($src);

        // Create target image
        $canvas = imagecreatetruecolor($w, $h);

        // Copy image
        imagecopyresampled($canvas, $src, 0, 0, $x, $y, $s_width, $s_height, $s_width, $s_height);

        // output image
        switch ($img_type) {
            case 'gif':
                $newImg = imagejpeg($canvas, $destination_file, $quality);
                break;
            case 'png':
                $quality = (intval($quality) > 90) ? 9 : round(intval($quality)/10);
                $newImg = imagepng($canvas, $destination_file, $quality);
                break;
            default:
                $newImg = imagejpeg($canvas, $destination_file, $quality);
        }

        // clean up
        imagedestroy($src);
        imagedestroy($canvas);

        return file_exists($destination_file);
    }
}
