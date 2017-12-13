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
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->deny('upload');
    }
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
            if ($this->request->getParam('action') == 'upload') {
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
        $this->viewbuilder()->setLayout('blank');
        $this->render('/Pages/blank');
        $uploadDir = Configure::read('App.eventImagePath') . 'full' . DS;
        $fileTypes = ['jpg', 'jpeg', 'gif', 'png'];
        $verifyToken = md5(Configure::read('App.upload_verify_token') . $_POST['timestamp']);
        if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $imageId = $this->Images->getNextId();
            $fileParts = pathinfo($_FILES['Filedata']['name']);
            $filename = $imageId . '.' . strtolower($fileParts['extension']);
            $targetFile = $uploadDir . $filename;
            if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
                if ($this->Images->autoResize($tempFile)) {
                    if (move_uploaded_file($tempFile, $targetFile)) {
                        if ($this->Images->createTiny($targetFile) && $this->Images->createSmall($targetFile)) {
                            // Create DB entry for the image
                            $image = $this->Images->newEntity();
                            $image->filename = $filename;
                            $image->user_id = $this->request->session()->read('Auth.User.id');
                            if ($this->Images->save($image)) {
                                // If the event ID is available, create association
                                if (isset($_POST['event_id']) && is_int($_POST['event_id'])) {
                                    if (isset($_POST['event_id']) && is_int($_POST['event_id'])) {
                                        $ass = $this->EventsImages->newEntity();
                                        $ass->image_id = $imageId;
                                        $ass->event_id = $_POST['event_id'];
                                        if ($this->EventsImages->save($ass)) {
                                        }
                                    }
                                }
                                echo $imageId;
                            } else {
                                $this->response->withStatus(500);
                                echo 'Error saving image';
                            }
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
