<?php
/**
 * User: FlorenceColas
 * Date: 10/12/16
 * Version: 1.00
 * RecipeAttachmentController: manage Recipe attachments. It contains the following actions:
 *      - open: open the attachment
 *      - delete: delete the attachment
 *      - renamefile: return a form to rename the attachment description (json)
 *      - validrename: persist the new description
 *      - changedefaultphoto: persist the defaultphoto field value
 *      - openupload: return a form to upload an attachment (json)
 *      - uploadfile: upload the new attachment
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */

namespace Warehouse\Controller;

use Warehouse\Entity\RecipeAttachment;
use Warehouse\Form\UploadFileForm;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;

class RecipeattachmentController extends AbstractActionController
{
    protected $entityManager;
    protected $authservice;
    protected $audittrailservice;

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

    private function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authservice;
    }

    private function getAuditTrailService()
    {
        if (! $this->audittrailservice) {
            $this->audittrailservice = $this->getServiceLocator()->get('AuditTrailService');
        }
        return $this->audittrailservice;
    }

    /*
     * Open the attachment in parameter in the web browser
     */

    public function openAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $em = $this->getEntityManager();
                $id = $this->params()->fromRoute('id', 0);

                $attachmentEntity = $em->getRepository('Warehouse\Entity\RecipeAttachment')->findByAttachmentId($id);
                $attachment = $attachmentEntity[0];

                $filename = $_SERVER['DOCUMENT_ROOT'].'/uploads/'.$attachment->getFileName();

                header('Content-type: '.$attachment->getMime());
                header('Content-Disposition: inline; filename="'.$attachment->getFileName().'"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($filename));
                header('Accept-Ranges: bytes');

                echo file_get_contents($filename);
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * Delete the attachment in parameter
     */
    public function deleteAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);

                //read the record in DB
                $attachmentEntity = $this->getEntityManager()->getRepository('Warehouse\Entity\RecipeAttachment')->findByAttachmentId($id);
                $attachment = $attachmentEntity[0];
                //remove file in uploads folder
                $file = $_SERVER["DOCUMENT_ROOT"] . '/'.$attachment->getPath().$attachment->getFileName();
                try{
                    $result = unlink($file);
                } catch (Exception $e) {
                    $result = false;
                    $msg = 'Failed to remove the file (' . $attachment->getPath() . $attachment->getFileName() . ')';
                }
                if (!$result) {
                    $msg = 'Failed to remove the file (' . $attachment->getPath() . $attachment->getFileName() . ')';
                }
                $file = $_SERVER["DOCUMENT_ROOT"] . '/'.$attachment->getPath().'thumb/'.$attachment->getFileName();
                try{
                    unlink($file);
                } catch (Exception $e) {
                }
                if (!$result) {
                    $msg = 'Failed to remove the thumbnail file (' . $attachment->getPath() .'thumb/'. $attachment->getFileName() . ')';
                } else {
                    //remove record in DB
                    $this->getEntityManager()->remove($attachment);
                    $this->getEntityManager()->flush();
                    $msg = 'The file has been successfully removed';
                }

                $response = $this->getResponse();
                $response->getHeaders()->addHeaders(array('Content-Type'=>'application/json;charset=UTF-8'));
                $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => $msg)));
                return $response;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * Return json code which contains a form to rename the attachment description
     */
    public function renamefileAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $layout = $this->layout();
                $layout->setTemplate('warehouse/recipeattachment/upload');
                $viewmodel = new ViewModel();

                $request = $this->getRequest();
                $id = $this->params()->fromRoute('id', 0);

                $attachmentEntity = $this->getEntityManager()->getRepository('Warehouse\Entity\RecipeAttachment')->findByAttachmentId($id);
                $attachment = $attachmentEntity[0];

                //disable layout if request by Ajax
                $viewmodel->setTerminal($request->isXmlHttpRequest());

                $html = '<form method="POST" name="rename-file-form" enctype="multipart/form-data" action="/warehouse/recipeattachment/validrename/id/'.$id.'" id="rename-file-form">';
                $html = $html . '<div class="form-element">';
                $html = $html . '<p>Old recipe attachment description: '.$attachment->getDescription().'</p>';
                $html = $html . '<p>New recipe attachment description: <input type="input" name="description" id="description"></p>';
                $html = $html . '</div>';
                $html = $html . '<button name="update">Update</button> <button name="cancel">Cancel</button>';
                $html = $html . '</form>';

                $response = $this->getResponse();
                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'contentpage' => $html)));
                return $response;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * Update the attachment in parameter with the new description
     */
    public function validrenameAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);
                $data = $this->params()->fromPost();
                $attachmentEntity = $this->getEntityManager()->getRepository('Warehouse\Entity\RecipeAttachment')->findByAttachmentId($id);
                $attachment = $attachmentEntity[0];
                if (isset($data['update']) == 1) {
                    $description = $this->getRequest()->getPost()->get("description");
                    $attachment->setDescription($description);
                    $this->getEntityManager()->persist($attachment);
                    $this->getEntityManager()->flush();
                }
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $attachment->getRecipe()->getId()]);
                return $this->getResponse();
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * Change the value of the defaultphoto field of the attachment in parameter
     */
    public function changedefaultphotoAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $layout = $this->layout();
                $layout->setTemplate('warehouse/recipeattachment/upload');
                $viewModel = new ViewModel();
                $id = $this->params()->fromRoute('id', 0);
                $attachmentEntity = $this->getEntityManager()->getRepository('Warehouse\Entity\RecipeAttachment')->findByAttachmentId($id);
                $attachment = $attachmentEntity[0];
                $recipeid = $attachment->getRecipe()->getId();
                $attachmentRecipeEntity = $this->getEntityManager()->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeId($recipeid);
                foreach($attachmentRecipeEntity as $att) {
                    if ($att->getId() == $id){
                        $att->setDefaultPhoto(1);
                        $att->setDescription("Recipe photo");
                    }
                    elseif ($att->getDefaultPhoto() == 1) {
                        $att->setDefaultPhoto(0);
                        $date = new \DateTime('now');
                        $att->setDescription("Recipe photo - ".$date->format('Y-m-d H:i:s'));
                    }
                    $this->getEntityManager()->persist($att);
                    $this->getEntityManager()->flush();
                }
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $recipeid]);
                return $viewModel;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * Return json code with contains a form to upload a new attachment
     */
    public function openuploadAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                //use a specific layout for this action
                $layout = $this->layout();
                $layout->setTemplate('warehouse/recipeattachment/upload');
                $viewmodel = new ViewModel();

                $request = $this->getRequest();
                $defaultphoto = $this->params()->fromPost('defaultphoto',null);
                $id = $this->params()->fromRoute('id', 0);

                //disable layout if request by Ajax
                $viewmodel->setTerminal($request->isXmlHttpRequest());

                $html = '<h4>Attachments</h4>';
                $html = $html . '<form method="POST" name="upload-file-form" enctype="multipart/form-data" action="/warehouse/recipeattachment/uploadfile/id/'.$id.'?defaultphoto='.$defaultphoto.'" id="upload-file-form">';
                $html = $html . '<div class="form-element">';
                $html = $html . '<input type="file" name="uploaded-file[]" id="uploaded-file">';
                $html = $html . '</div>';
                $html = $html . '<button name="add">Add new attachment</button> <button name="cancel">Cancel</button>';
                $html = $html . '</form>';

                $response = $this->getResponse();
                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'contentpage' => $html)));
                return $response;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * upload the new attachment action
     */
    public function uploadfileAction() {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $form = new UploadFileForm('upload-file-form');
                $tempFile = null;

                //file to be uploaded
                $request = $this->getRequest();
                $defaultphoto = $request->getQuery('defaultphoto');
                //current recipe id
                $id = $this->params()->fromRoute('id', 0);

                $data = $this->params()->fromPost();
                // Make certain to merge the files info!
                $post = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                $form->setData($post);
                if ($form->isValid()) {
                    if ($request->isPost()) {
                        if (isset($data['add']) == 1) {
                            $file = $form->get('uploaded-file')->getValue();
                            if ($file[0] != null) {
                                if ($file[0]['name'] != '') {
                                    $uploaded = is_uploaded_file($file[0]['tmp_name']);
                                    if (!$uploaded) {
                                        die("Image has not been uploaded");
                                    }
                                    $image_saved = move_uploaded_file($file[0]['tmp_name'], $_SERVER["DOCUMENT_ROOT"] . '/uploads/' . $file[0]['name']);
                                    if (!$image_saved) {
                                        die("File could not be moved");
                                    }
                                    rename($_SERVER["DOCUMENT_ROOT"] . '/uploads/' . $file[0]['name'], $_SERVER["DOCUMENT_ROOT"] . '/uploads/'.$id.'_' . $file[0]['name']);

                                    //create the thumbnail
                                    $imageExtensionArray = explode(".", $file[0]['name']);
                                    $extension = $imageExtensionArray[1];
                                    $fileTypes = array('jpg', 'jpeg', 'JPG', 'bmp', 'gif');
                                    if (in_array($extension,$fileTypes)) {
                                        $w = '200';
                                        $h = '200';
                                        $imageName = $id . '_' . $file[0]['name'];
                                        $thumbnail = $this->resize($w, $h, "{$imageName}", $extension, $id . '_' . $file[0]['name']);
                                        $uploaded = is_uploaded_file($thumbnail);
                                    }

                                    //add the file to the Attachment table
                                    $em = $this->getEntityManager();
                                    $recipeEntity = $em->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($id);
                                    $recipe = $recipeEntity[0];
                                    $attachment = new RecipeAttachment();
                                    $attachment->setFileName($id.'_' .$file[0]['name']);
                                    $attachment->setPath('uploads/');
                                    $attachment->setMime($file[0]['type']);
                                    $attachment->setSize($file[0]['size']);

                                    if ((null !== $this->params()->fromPost('chk-defaultphoto') or $defaultphoto == 1) and ($file[0]['type'] == 'image/jpeg' or $file[0]['type'] == 'image/jpg' or $file[0]['type'] == 'image/png' or $file[0]['type'] == 'image/bmp' or $file[0]['type'] == 'image/gif')) {
                                        //if another default photo already exists, modify defaultphoto field with 0 for the old one
                                        $attachmentEntity = $em->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeIdDefaultPhoto($id);
                                        //  var_dump(count($attachmentEntity));
                                        if ($attachmentEntity !== null and count($attachmentEntity)!=0){
                                            $attachmentPhoto = $attachmentEntity[0];
                                            $date = new \DateTime('now');
                                            $attachmentPhoto->setDescription("Recipe photo - ".$date->format('Y-m-d H:i:s'));
                                            $attachmentPhoto->setDefaultPhoto(0);
                                            $em->persist($attachmentPhoto);
                                        }
                                        $attachment->setDefaultPhoto("1");
                                        $attachment->setDescription("Recipe photo");
                                    } else {
                                        $attachment->setDefaultPhoto("0");
                                        if ($this->params()->fromPost('attachment-description') != '') {
                                            $attachment->setDescription($this->params()->fromPost('attachment-description'));
                                        } else {
                                            $attachment->setDescription($file[0]['name']);
                                        }
                                    }
                                    $attachment->setCreationDate(new \DateTime('now'));
                                    $attachment->setRecipe($recipe);
                                    $em->persist($attachment);
                                    $em->flush();
                                } else {
                                    $viewModel->setVariables(array(
                                        'textreturn' => 'no file',
                                        'success' => true,
                                    ));
                                }
                            } else {
                                $viewModel->setVariables(array(
                                    'textreturn' => 'upload ok',
                                    'success' => true,
                                ));
                            }
                        }
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $id]);
                        return $this->getResponse();
                    }
                }
                else {
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $id]);
                    return $this->getResponse();
                }
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    private function resize($width, $height, $imageName, $extension, $originalimage) {
        $docRoot = $_SERVER["DOCUMENT_ROOT"];

        /* Get original image x y */
        $tmpNM = $originalimage;

        //$rotate = imagerotate(imagecreatefromjpeg($docRoot . '/uploads/' .$originalimage), 90, 0);
        //$source = $rotate;

        list($w, $h) = getimagesize($docRoot . '/uploads/' .$originalimage);
        /* calculate new image size with ratio */
        if ($h > $w) {
            $ratio = $h / $height;
            $width = ceil($w / $ratio);
        } else {
            $ratio = $w / $width;
            $height = ceil($h / $ratio);
        }
        /* new file name */
        $path = $docRoot . '/uploads/thumb/' . $imageName;
        /* read binary data from image file */
        $imgString = file_get_contents($docRoot . '/uploads/'.$originalimage);
        /* create image from string */
        $image = imagecreatefromstring($imgString);
        $tmp = imagecreatetruecolor($width, $height);

        imagecopyresampled($tmp, $image, 0, 0, 0, 0, $width, $height, $w, $h);
        /* Save image */
        switch (strtolower($extension)) {
            case 'jpg':
                imagejpeg($tmp, $path, 100);
                break;
            case 'jpeg':
                imagejpeg($tmp, $path, 100);
                break;
            case 'JPG':
                imagejpeg($tmp, $path, 100);
            case 'png':
                imagepng($tmp, $path, 100);
                break;
            case 'gif':
                imagegif($tmp, $path, 100);
                break;
            case 'bmp':
                imagewbmp($tmp, $path);
                break;
            default:
                exit;
                break;
        }
        return $path;
        /* cleanup memory */
        imagedestroy($image);
        imagedestroy($tmp);
    }

}