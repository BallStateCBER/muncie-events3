<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Images Controller
 *
 * @property \App\Model\Table\ImagesTable $Images
 */
class ImagesController extends AppController
{
    /**
     * upload method.
     *
     * @return void
     */
    public function upload()
    {
        $uploadDir = WWW_ROOT . 'img' . DS . 'events' . DS . 'full' . DS;
        $fileTypes = ['jpg', 'jpeg', 'gif', 'png'];
        $verifyToken = md5(Configure::read('upload_verify_token') . $_POST['timestamp']);
        if (! empty($_FILES) && $_POST['token'] == $verifyToken) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $imageId = $this->Images->getNextId();
            $userId = $this->request->session()->read('Auth.User.id');
            $fileParts = pathinfo($_FILES['Filedata']['name']);
            $filename = $imageId . '.' . strtolower($fileParts['extension']);
            $targetFile = $uploadDir . $filename;
            if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
                if ($this->Images->autoResize($tempFile)) {
                    if (move_uploaded_file($tempFile, $targetFile)) {
                        if ($this->Images->createTiny($targetFile) && $this->Images->createSmall($targetFile)) {
                            // Create DB entry for the image
                            $newImage = TableRegistry::get('Images');
                            $newImage = $newImage->newEntity();
                            $newImage->user_id = $userId;
                            $newImage->filename = $filename;
                            $this->Images->save($newImage);
                            if (!$this->Images->save($newImage)) {
                                $this->response->statusCode(500);
                                echo 'Error saving image';
                                debug($newImage);
                            }
                            echo $newImage->id;
                        } else {
                            $this->response->statusCode(500);
                            echo 'Error creating thumbnail';
                            if (! empty($this->Images->errors)) {
                                echo ': ' . implode('; ', $this->Images->errors);
                            }
                        }
                    } else {
                        $this->response->statusCode(500);
                        echo 'Could not save file.';
                    }
                } else {
                    $this->response->statusCode(500);
                    echo 'Error resizing image';
                    if (! empty($this->Images->errors)) {
                        echo ': ' . implode('; ', $this->Images->errors);
                    }
                }
            } else {
                echo 'Invalid file type.';
            }
        } else {
            $this->response->statusCode(500);
            echo 'Security code incorrect';
        }
        $this->viewbuilder()->setLayout('blank');
        $this->render('/Pages/blank');
    }

    /**
     * Effectively bypasses Uploadify's check for an existing file
     * (because the filename is changed as it's being saved).
     *
     * @return void
     */
    public function fileExists()
    {
        exit(0);
    }

    /**
     * newest method.
     *
     * @param int $userId of the user whose images we need
     * @return void
     */
    public function newest($userId)
    {
        $result = $this->Images->find('first', [
            'conditions' => ['user_id' => $userId],
            'order' => 'created DESC',
            'contain' => false,
            'fields' => ['id', 'filename']
        ]);
        if ($result) {
        }
        if (!$result) {
            echo 0;
        }
        $this->viewbuilder()->setLayout('blank');
        $this->render('/Pages/blank');
    }

    /**
     * filename method.
     *
     * @param int $imageId of the image we're setting the filename for
     * @return void
     */
    public function filename($imageId)
    {
        $image = $this->Images->get($imageId);
        $imageId = $image->id;
        $filename = $image->filename;
        echo $filename ? $filename : 0;
        $this->viewbuilder()->setLayout('blank');
        $this->render('/Pages/blank');
    }

    /**
     * userImages method.
     *
     * @param int $userId of the user whose images we need
     * @return void
     */
    public function userImages($userId)
    {
        $this->viewbuilder()->setLayout('ajax');
        $this->set([
            'images' => $this->Images->Users->getImagesList($userId)
        ]);
    }
}
