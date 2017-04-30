<?php
/**
 * User: FlorenceColas
 * Date: 19/11/16
 * Version: 1.00
 * TestController: NOT USED - To make tests
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;
use Warehouse\Form\UploadForm;

class TestController extends AbstractActionController
{
    protected $entityManager;

    /*
     * @param EntityManager $em
     */
    private function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /*
     * @return EntityManager
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }

    public function uploadfileAction(){

        $form = new UploadForm('upload-file-form');

        $tempFile = null;

        $request = $this->getRequest();
            if ($request->isPost()) {
            //    $files = $this->params()->fromFiles('uploaded-file');
            //    var_dump($files);
                $id = 33;//$this->params()->fromRoute('id', 0);

                // Make certain to merge the files info!
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );

                $form->setData($post);
                if ($form->isValid()) {
             //       var_dump('form is valid');

                    $data = $form->getData();
                    $files = $data['attachment-file'];
           //         var_dump($data);
           //         var_dump($files);
            //        var_dump($_FILES);
                    $i = 0;
                    if ($files != null) {
                        foreach ($files as $file) {
                            //  var_dump($file);
                   //         var_dump('name=' . $file['name']);
                    //        var_dump('tmp_name=' . $file['tmp_name']);
                      //      var_dump('error=' . $file['error']);
                        //    var_dump('size=' . $file['size']);
                          //  var_dump('type=' . $file['type']);
                            $uploaded = is_uploaded_file($file['tmp_name']);
                            if (!$uploaded) {
                                die("Image has not been uploaded");
                            }
                            $image_saved = move_uploaded_file($file['tmp_name'], $_SERVER["DOCUMENT_ROOT"].'/uploads/' . $file['name']);
                            if (!$image_saved) {
                                die("File could not be moved");
                            }
                            $i = $i + 1;
                        }
                    }
                   // die ("stop");
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'test', 'action' => 'uploadfile']);
                    return $this->getResponse();
                } else {
                    var_dump('form not valid');
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'test', 'action' => 'uploadfile']);
                    return $this->getResponse();
                    // Form not valid, but file uploads might be valid...
                    // Get the temporary file information to show the user in the view
/*                    $fileErrors = $form->get('uploaded-file')->getMessages();
                    if (empty($fileErrors)) {
                        $tempFile = $form->get('uploaded-file')->getValue();
                    }
*/
                }
            } else {
                return array(
                    'formuploadedfile' => $form,
                    'tempFile' => $tempFile,
                );
            }
        /*
        $form = new UploadForm('upload-form');
        $tempFile = null;

        $prg = $this->fileprg($form);
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response) {
            var_dump($prg->getStatusCode());
            return $prg; // Return PRG redirect response
        } elseif (is_array($prg)) {
           // var_dump($form);
            if ($form->isValid()) {
                $data = $form->getData();
                $request = $this->getRequest();
                $files = $data['attachment-file'];
                var_dump($files);
                //$files = $this->params()->fromFiles('attachment-file');



                if ($files != null) {
                    foreach ($files as $file) {
                        //  var_dump($file);
                        var_dump('name=' . $file['name']);
                        var_dump('tmp_name=' . $file['tmp_name']);
                        var_dump('error=' . $file['error']);
                        var_dump('size=' . $file['size']);
                        var_dump('type=' . $file['type']);

                        $uploaded = is_uploaded_file($file['tmp_name']);
                        if (!$uploaded) {
                            var_dump('error=' . $file['error']);
                            var_dump('tmp_name=' . $file['tmp_name']);
                            die("Image has not been uploaded");
                        }

                        $image_saved = move_uploaded_file($file['tmp_name'], $_SERVER["DOCUMENT_ROOT"].'/uploads/' . $file['name']);
                        //$image_saved = move_uploaded_file($file['tmp_name'], '/private/var/temp/' . $file['name']);
                        if (!$image_saved) {
                            switch ($file['error']) {
                                case 1:
                                    var_dump('The file is bigger than this PHP installation allows');
                                    break;
                                case 2:
                                    var_dump('The file is bigger than this form allows');
                                    break;
                                case 3:
                                    var_dump('Only part of the file was uploaded');
                                    break;
                                case 4:
                                    var_dump('No file was uploaded');
                                    break;
                                default:
                                    var_dump('unknown errror');
                            }

                            die("File could not be moved");
                        }

              /*          $destination=$_SERVER[DOCUMENT_ROOT]."/Uploader/UploadedFiles/" . $_FILES["file"]["name"];

                        if(move_uploaded_file($_FILES["file"]["tmp_name"],  $destination)){
                            echo ("Stored in".$_SERVER[DOCUMENT_ROOT]."/Uploader/UploadedFiles/".$_FILES["file"]["name"]);
                        }else{
                            $html_body = '<h1>File upload error!</h1>';
                            switch ($_FILES[0]['error']) {
                                case 1:
                                    $html_body .= 'The file is bigger than this PHP installation allows';
                                    break;
                                case 2:
                                    $html_body .= 'The file is bigger than this form allows';
                                    break;
                                case 3:
                                    $html_body .= 'Only part of the file was uploaded';
                                    break;
                                case 4:
                                    $html_body .= 'No file was uploaded';
                                    break;
                                default:
                                    $html_body .= 'unknown errror';
                            }
                            echo ($html_body);
                        }
*/
        /*

                    }
                } else {
                    die("Upload failed");
                }
                // die("stop");
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'test', 'action' => 'uploadfile']);
                return $this->getResponse();

                // Form is valid, save the form!
                //return $this->redirect()->toRoute('upload-form/success');
            } else {
                // Form not valid, but file uploads might be valid...
                // Get the temporary file information to show the user in the view
                $fileErrors = $form->get('attachment-file')->getMessages();
                if (empty($fileErrors)) {
                    $tempFile = $form->get('attachment-file')->getValue();
                }
            }
        }

        return array(
            'form'     => $form,
            'tempFile' => $tempFile,
        );
        */

/*

     $form = new UploadForm('upload-form');
        $tempFile = null;

        $id = 33;//$this->params()->fromRoute('id', 0);

        $request = $this->getRequest();
        // Make certain to merge the files info!

        if ($request->isPost()) {
            $files = $this->params()->fromFiles('attachment-file');
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();

                var_dump($request->getFiles()->toArray());
                var_dump($files);
                if ($files != null) {
                    foreach ($files as $file) {
                        //  var_dump($file);
                        var_dump('name=' . $file['name']);
                        var_dump('tmp_name=' . $file['tmp_name']);
                        var_dump('error=' . $file['error']);
                        var_dump('size=' . $file['size']);
                        var_dump('type=' . $file['type']);
                        $uploaded = is_uploaded_file($file['tmp_name']);
                        if (!$uploaded) {
                            //      die("Image has not been uploaded");
                        }
                        $image_saved = move_uploaded_file($file['tmp_name'], '/Users/FlorenceColas/php/warehouse/public/uploads/' . $file['name']);
                        if (!$image_saved) {
                            die("File could not be moved");
                        }
                    }
                } else {
                    die("Upload failed");
                }
               // die("stop");
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'test', 'action' => 'uploadfile']);
                return $this->getResponse();

            } else {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'test', 'action' => 'uploadfile']);
                return $this->getResponse();
            }
        }

        return $form;
*/


        /*

        $form = new UploadForm('upload-form');

        $request = $this->getRequest();
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                // Form is valid, save the form!
                if (!empty($post['isAjax'])) {
                    return new JsonModel(array(
                        'status'   => true,
                        'redirect' => $this->url()->fromRoute('upload-form/success'),
                        'formData' => $data,
                    ));
                } else {
                    // Fallback for non-JS clients
                    return $this->redirect()->toRoute('upload-form/success');
                }
            } else {
                if (!empty($post['isAjax'])) {
                    // Send back failure information via JSON
                    return new JsonModel(array(
                        'status'     => false,
                        'formErrors' => $form->getMessages(),
                        'formData'   => $form->getData(),
                    ));
                }
            }
        }

        return array('form' => $form);
*/
    }

    /*
    public function uploadProgressAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $progress = new \Zend\ProgressBar\Upload\SessionProgress();
        return new \Zend\View\Model\JsonModel($progress->getProgress($id));
    }
    */

    public function successAction(){

    }
}