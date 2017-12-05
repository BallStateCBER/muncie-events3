<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Images Controller
 */
class ImagesController extends AppController
{
    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        if (isset($user)) {
            if ($user['role'] == 'admin') {
                return true;
            }
        }

        return false;
    }
    /**
     * upload method.
     *
     * @return void
     */
    public function upload()
    {
        $uploadDir = Configure::read('App.eventImagePath') . DS . 'full' . DS;
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
                                $this->response->withStatus(500);
                                echo 'Error saving image';
                                debug($newImage);
                            }
                            echo $newImage->id;
                        } else {
                            $this->response->withStatus(500);
                            echo 'Error creating thumbnail';
                            if (! empty($this->Images->errors)) {
                                echo ': ' . implode('; ', $this->Images->errors);
                            }
                        }
                    } else {
                        $this->response->withStatus(500);
                        echo 'Could not save file.';
                    }
                } else {
                    $this->response->withStatus(500);
                    echo 'Error resizing image';
                    if (! empty($this->Images->errors)) {
                        echo ': ' . implode('; ', $this->Images->errors);
                    }
                }
            } else {
                echo 'Invalid file type.';
            }
        } else {
            $this->response->withStatus(500);
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
     * @return null
     */
    public function newest($userId)
    {
        $result = $this->Images->find('first', [
            'conditions' => ['user_id' => $userId],
            'order' => 'created DESC',
            'contain' => false,
            'fields' => ['id', 'filename']
        ]);
        $this->viewbuilder()->setLayout('blank');
        $this->render('/Pages/blank');
        if (!$result) {
            echo 0;
        }

        return null;
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
            'images' => $this->Users->getImagesList($userId)
        ]);
    }
}
