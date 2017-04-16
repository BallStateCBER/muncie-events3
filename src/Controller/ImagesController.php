<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Images Controller
 *
 * @property \App\Model\Table\ImagesTable $Images
 */
class ImagesController extends AppController
{
    public function upload()
    {
        $uploadDir = WWW_ROOT.'img'.DS.'events'.DS.'full'.DS;
        $fileTypes = ['jpg', 'jpeg', 'gif', 'png'];
        $verifyToken = md5(Configure::read('upload_verify_token') . $_POST['timestamp']);
        if (! empty($_FILES) && $_POST['token'] == $verifyToken) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $image_id = $this->Images->getNextId();
            $userId = $this->request->session()->read('Auth.User.id');
            $fileParts = pathinfo($_FILES['Filedata']['name']);
            $filename = $image_id.'.'.strtolower($fileParts['extension']);
            $targetFile = $uploadDir.$filename;
            if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
                if ($this->Images->autoResize($tempFile)) {
                    if (move_uploaded_file($tempFile, $targetFile)) {
                        if ($this->Images->createTiny($targetFile) && $this->Images->createSmall($targetFile)) {
                            // Create DB entry for the image
                            $newImage = TableRegistry::get('Images');
                            $newImage = $newImage->newEntity();
                            $newImage->user_id = $userId;
                            $newImage->filename = $filename;
                            if ($this->Images->save($newImage)) {
                                // If the event ID is available, create association
                            } else {
                                $this->response->statusCode(500);
                                echo 'Error saving image';
                                debug($newImage);
                            }
                            echo $newImage->id;
                        } else {
                            $this->response->statusCode(500);
                            echo 'Error creating thumbnail';
                            if (! empty($this->Images->errors)) {
                                echo ': '.implode('; ', $this->Images->errors);
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
                        echo ': '.implode('; ', $this->Images->errors);
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
     */
    public function fileExists()
    {
        exit(0);
    }

    public function newest($user_id)
    {
        $result = $this->Images->find('first', [
            'conditions' => ['user_id' => $user_id],
            'order' => 'created DESC',
            'contain' => false,
            'fields' => ['id', 'filename']
        ]);
        if ($result) {
        } else {
            echo 0;
        }
        $this->viewbuilder()->setLayout('blank');
        $this->render('/Pages/blank');
    }

    public function filename($image_id)
    {
        $image = $this->Images->get($image_id);
        $image_id = $image->id;
        $filename = $image->filename;
        echo $filename ? $filename : 0;

        $this->viewbuilder()->setLayout('blank');
        $this->render('/Pages/blank');
    }

    public function user_images($user_id)
    {
        $this->viewbuilder()->setLayout('ajax');

        $this->set([
            'images' => $this->Images->Users->getImagesList($user_id)
        ]);
    }

    public function index()
    {
        $this->paginate = [
            'contain' => ['Users']
        ];
        $images = $this->paginate($this->Images);

        $this->set(compact('images'));
        $this->set('_serialize', ['images']);
    }

    /**
     * View method
     *
     * @param string|null $id Image id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $image = $this->Images->get($id, [
            'contain' => ['Users', 'Events']
        ]);

        $this->set('image', $image);
        $this->set('_serialize', ['image']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $image = $this->Images->newEntity();
        if ($this->request->is('post')) {
            $image = $this->Images->patchEntity($image, $this->request->getData());
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $users = $this->Images->Users->find('list', ['limit' => 200]);
        $events = $this->Images->Events->find('list', ['limit' => 200]);
        $this->set(compact('image', 'users', 'events'));
        $this->set('_serialize', ['image']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Image id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $image = $this->Images->get($id, [
            'contain' => ['Events']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $image = $this->Images->patchEntity($image, $this->request->getData());
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $users = $this->Images->Users->find('list', ['limit' => 200]);
        $events = $this->Images->Events->find('list', ['limit' => 200]);
        $this->set(compact('image', 'users', 'events'));
        $this->set('_serialize', ['image']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Image id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $image = $this->Images->get($id);
        if ($this->Images->delete($image)) {
            $this->Flash->success(__('The image has been deleted.'));
        } else {
            $this->Flash->error(__('The image could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
