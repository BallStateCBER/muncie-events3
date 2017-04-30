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
            $newWidth = $this->maxWidth;
            $newHeight = 0;
        }
        if ($width < $height) {
            $newWidth = 0;
            $newHeight = $this->maxHeight;
        }
        if ($this->resize($filepath, $filepath, $newWidth, $newHeight, $this->fullQuality)) {
            return true;
        }
        return false;
    }

    /**
     * Accepts the filename of an uploaded image and creates a tiny thumbnail
     * @param string $filename
     */
    public function createTiny($sourceFile)
    {
        $path = WWW_ROOT.'img'.DS.'events'.DS.'tiny'.DS;
        $filename = substr($sourceFile, strrpos($sourceFile, DS) + 1);
        $destinationFile = $path.$filename;
        list($width, $height, $type, $attr) = getimagesize($sourceFile);

        // Make the shortest side fit inside the maximum dimensions
        if ($width >= $height) {
            $newWidth = 0; // Automatically determined in ResizeBehavior::resize()
            $newHeight = $this->tinyHeight;
        }
        if ($width < $height) {
            $newWidth = $this->tinyWidth;
            $newHeight = 0;
        }
        if (!$this->resize($sourceFile, $destinationFile, $newWidth, $newHeight, $this->tinyQuality)) {
            return false;
        }

        // Crop down the remaining longer size
        if (!$this->cropCenter($destinationFile, $destinationFile, $this->tinyWidth, $this->tinyHeight, $this->tinyQuality)) {
            return false;
        }

        return true;
    }

    /**
     * Accepts the filename of an uploaded image and creates a smaller (limited width) version
     * @param string $filename
     */
    public function createSmall($sourceFile)
    {
        $path = WWW_ROOT.'img'.DS.'events'.DS.'small'.DS;
        $filename = substr($sourceFile, strrpos($sourceFile, DS) + 1);
        $destinationFile = $path.$filename;

        $newWidth = $this->smallWidth;
        $newHeight = 0; // Automatically determined in ResizeBehavior::resize()

        if (!$this->resize($sourceFile, $destinationFile, $newWidth, $newHeight, $this->smallQuality)) {
            return false;
        }

        return true;
    }

    public function resize($sourceFile, $newFilename, $newWidth = 0, $newHeight = 0, $quality = 100)
    {
        if (!($imageParams = getimagesize($sourceFile))) {
            $this->errors[] = 'Original file is not a valid image: ' . $sourceFile;
            return false;
        }

        $width = $imageParams[0];
        $height = $imageParams[1];

        if (0 != $newWidth && 0 == $newHeight) {
            $scaledWidth = $newWidth;
            $scaledHeight = floor($newWidth * $height / $width);
        } elseif (0 != $newHeight && 0 == $newWidth) {
            $scaledHeight = $newHeight;
            $scaledWidth = floor($newHeight * $width / $height);
        } elseif (0 == $newWidth && 0 == $newHeight) { //assume we want to create a new image the same exact size
            $scaledWidth = $width;
            $scaledHeight = $height;
        } else { //assume we want to create an image with these exact dimensions, most likely resulting in distortion
            $scaledWidth = $newWidth;
            $scaledHeight = $newHeight;
        }

        //create image
        $ext = $imageParams[2];
        switch ($ext) {
            case IMAGETYPE_GIF:
                $return = $this->resizeGifPr($sourceFile, $newFilename, $scaledWidth, $scaledHeight, $width, $height, $quality);
                break;
            case IMAGETYPE_JPEG:
                $return = $this->resizeJpegPr($sourceFile, $newFilename, $scaledWidth, $scaledHeight, $width, $height, $quality);
                break;
            case IMAGETYPE_PNG:
                $return = $this->resizePngPr($sourceFile, $newFilename, $scaledWidth, $scaledHeight, $width, $height, $quality);
                break;
            default:
                $return = $this->resizeJpegPr($sourceFile, $newFilename, $scaledWidth, $scaledHeight, $width, $height, $quality);
                break;
        }

        return $return;
    }

    private function resizeGifPr($original, $newFilename, $scaledWidth, $scaledHeight, $width, $height)
    {
        $error = false;

        if (!($src = imagecreatefromgif($original))) {
            $this->errors[] = 'There was an error creating your resized image (gif).';
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaledWidth, $scaledHeight))) {
            $this->errors[] = 'There was an error creating your true color image (gif).';
            $error = true;
        }

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (gif).';
            $error = true;
        }

        if (!($newImage = imagegif($tmp, $newFilename))) {
            $this->errors[] = 'There was an error writing your image to file (gif).';
            $error = true;
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $newImage;
        }

        return false;
    }

    private function resizeJpegPr($original, $newFilename, $scaledWidth, $scaledHeight, $width, $height, $quality)
    {
        $error = false;

        if (!($src = imagecreatefromjpeg($original))) {
            $this->errors[] = 'There was an error creating your resized image (jpg).';
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaledWidth, $scaledHeight))) {
            $this->errors[] = 'There was an error creating your true color image (jpg).';
            $error = true;
        }

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (jpg).';
            $error = true;
        }

        if (!($newImage = imagejpeg($tmp, $newFilename, $quality))) {
            $this->errors[] = 'There was an error writing your image to file (jpg).';
            $error = true;
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $newImage;
        }

        return false;
    }

    private function resizePngPr($original, $newFilename, $scaledWidth, $scaledHeight, $width, $height, $quality)
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
        }
        if (0 != $quality) {
            $quality = ($quality - 1) % 9;
        }

        if (!($src = imagecreatefrompng($original))) {
            $this->errors[] = 'There was an error creating your resized image (png).';
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaledWidth, $scaledHeight))) {
            $this->errors[] = 'There was an error creating your true color image (png).';
            $error = true;
        }

        imagealphablending($tmp, false);

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (png).';
            $error = true;
        }

        imagesavealpha($tmp, true);

        if (!($newImage = imagepng($tmp, $newFilename, $quality))) {
            $this->errors[] = 'There was an error writing your image to file (png).';
            $error = true;
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $newImage;
        }

        return false;
    }

    /**
     * Saves to $destinationFile the cropped center of $sourceFile.
     * @param string $sourceFile
     * @param string $destinationFile
     * @param int $w
     * @param int $h
     * @param int $quality
     * @return boolean
     */
    public function cropCenter($sourceFile, $destinationFile, $wVar, $hVar, $quality)
    {
        list($originalWidth, $originalHeight, $type, $attr) = getimagesize($sourceFile);
        $centerX = round($originalWidth / 2);
        $centerY = round($originalHeight / 2);
        $halfNewWidth = round($wVar / 2);
        $halfNewHeight = round($hVar / 2);
        $xVar = max(0, ($centerX - $halfNewWidth));
        $yVar = max(0, ($centerY - $halfNewHeight));
        return $this->crop($sourceFile, $destinationFile, $wVar, $hVar, $xVar, $yVar, $quality);
    }

    /**
     * Crops $sourceFile and saves the result to $destinationFile.
     * @param string $sourceFile
     * @param string $destinationFile
     * @param int $w
     * @param int $h
     * @param int $x
     * @param int $y
     * @param int $quality
     * @return boolean
     */
    public function crop($sourceFile, $destinationFile, $wVar, $hVar, $xVar, $yVar, $quality)
    {
        if (!$sourceFile || !file_exists($sourceFile)) {
            $this->errors[] = 'No image found to crop';
            return false;
        }

        // Use for overriding destination image type
        $destinationImgType = false;

        // get image details
        $imageInfo = getimagesize($sourceFile);

        // set source as resource
        switch ($imageInfo['mime']) {
            case 'image/gif':
                $src = imagecreatefromgif($sourceFile);
                $imgType = ($destinationImgType) ? $destinationImgType : 'gif';
                break;
            case 'image/jpeg':
                $src = imagecreatefromjpeg($sourceFile) ;
                $imgType = ($destinationImgType) ? $destinationImgType : 'jpg';
                break;
            case 'image/png':
                $src = imagecreatefrompng($sourceFile);
                imagealphablending($src, true); // setting alpha blending on (we want to blend this image with the canvas)
                imagesavealpha($src, true); // save alphablending setting
                $imgType = ($destinationImgType) ? $destinationImgType : 'png';
                break;
            default:
                $this->errors[] = 'Cannot crop an image of type '.$imageInfo['mime'];
                return false;
        }

        // Source dimensions
        $sWidth = imagesx($src);
        $sHeight = imagesy($src);

        // Create target image
        $canvas = imagecreatetruecolor($wVar, $hVar);

        // Copy image
        imagecopyresampled($canvas, $src, 0, 0, $xVar, $yVar, $sWidth, $sHeight, $sWidth, $sHeight);

        // output image
        switch ($imgType) {
            case 'gif':
                $newImg = imagejpeg($canvas, $destinationFile, $quality);
                break;
            case 'png':
                $quality = (intval($quality) > 90) ? 9 : round(intval($quality)/10);
                $newImg = imagepng($canvas, $destinationFile, $quality);
                break;
            default:
                $newImg = imagejpeg($canvas, $destinationFile, $quality);
        }

        // clean up
        imagedestroy($src);
        imagedestroy($canvas);

        return file_exists($destinationFile);
    }
}
