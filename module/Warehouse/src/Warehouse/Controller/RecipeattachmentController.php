<?php

namespace Warehouse\Controller;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Zend\File\Exception\BadMethodCallException;
use Zend\Mvc\Controller\AbstractActionController;

class RecipeattachmentController extends AbstractActionController
{
    protected $adapter;
    protected $config;
    protected $doctrine;

    public function __construct(
        array $config,
        $doctrine,
        DbAdapter $adapter
    ) {
        $this->config   = $config;
        $this->doctrine = $doctrine;
        $this->adapter  = $adapter;
    }

    public function addAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $file = $this->getRequest()->getFiles()->toArray();
        $fileLabel = $_POST['file-label'];

        if (null !== $file['uploaded-file'][0] and '' !== $file['uploaded-file'][0]['name']) {
            $imageExtensionArray = explode(".", $file['uploaded-file'][0]['name']);
            $extension = $imageExtensionArray[1];
            if (in_array($extension,$this->config['authorized_file_extension'])) {
                $attachment = new AttachmentController($this->config, $this->doctrine);
                $result = $attachment->addAttachment($file['uploaded-file'][0], $attachment::RECIPE_ATTACHMENT, $id, $fileLabel);
                if ('error' === $result['result']) {
                    $this->flashmessenger()->addMessage($result);
                } else {
                    //create the thumbnail
                    $w = '200';
                    $h = '200';
                    $imageName = $id . '_' . $file['uploaded-file'][0]['name'];
                    $this->resize($w, $h, "{$imageName}", $extension, $id . '_' . $file['uploaded-file'][0]['name']);

                    $sql = new Sql($this->adapter);
                    $insert = $sql->insert('recipes_attachment');
                    $insert->values([
                        'attachment_id' => $result['id'],
                        'recipes_id' => intval($id),
                    ]);
                    $selectString = $sql->buildSqlString($insert);
                    $this->adapter->query($selectString, DbAdapter::QUERY_MODE_EXECUTE);

                    $this->flashmessenger()->addMessage([
                        'result' => 'success',
                        'message' => 'The file has been uploaded successfully',
                    ]);
                }
            } else {
                $this->flashmessenger()->addMessage([
                    'result' => 'error',
                    'message' => "The extension $extension is not authorized. Please choose between jpg/jpeg/JPG/gif/GIF/png/PNG",
                ]);
            }
        }

        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $id]);
        return $this->getResponse();
    }

    public function deleteAction(){
        $id       = $this->params()->fromRoute('id', 0);
        $recipeId = $_GET['recipe_id'];

        $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\Attachment')->findByAttachmentId($id);
        if (0 == count($attachmentEntity)) {
            exit;
        }
        $attachment = $attachmentEntity[0];
        $file = $attachment->getPath() . '/' . $attachment->getFileName();
        $msg = '';
        try{
            $result = unlink($file);
        } catch (BadMethodCallException $e) {
            $result = false;
            $msg = 'Failed to remove the file (' . $attachment->getPath() . '/' . $attachment->getFileName() . ')';
        }
        if (!$result) {
            $msg = 'Failed to remove the file (' . $attachment->getPath() . '/' . $attachment->getFileName() . ')';
        }
        $file = $attachment->getPath().'/thumb/'.$attachment->getFileName();
        try{
            unlink($file);
        } catch (BadMethodCallException $e) {
            $result = false;
            $msg = 'Failed to remove the thumbnail file (' . $attachment->getPath() .'/thumb/'. $attachment->getFileName() . ')';
        }
        if (!$result) {
            $msg = 'Failed to remove the thumbnail file (' . $attachment->getPath() .'/thumb/'. $attachment->getFileName() . ')';
        } else {
            $this->doctrine->remove($attachment);
            $this->doctrine->flush();
            $msg = 'The file has been successfully removed';
        }
        $result = true;

        $this->flashmessenger()->addMessage([
            'result' => $result,
            'message' => $msg,
        ]);

        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $recipeId]);
        return $this->getResponse();
    }

    public function changedefaultphotoAction(){
        $id = $this->params()->fromRoute('id', 0);
        $recipeId = $_GET['recipe_id'];

        $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeId($recipeId);

        foreach($attachmentEntity as $att) {
            if ($att->getId() == $id){
                $att->setDefaultPhoto(1);
                $att->setDescription("Recipe photo");
            } elseif (1 == $att->getDefaultPhoto()) {
                $att->setDefaultPhoto(0);
                $date = new \DateTime('now');
                $att->setDescription("Recipe photo - ".$date->format('Y-m-d H:i:s'));
            }
            $this->doctrine->persist($att);
            $this->doctrine->flush();
        }
        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $recipeId]);
        return $this->getResponse();
    }

    private function resize($width, $height, $imageName, $extension, $originalimage) {
        list($w, $h) = getimagesize($this->config['path']['recipe_image_upload'] . '/' .$originalimage);
        /* calculate new image size with ratio */
        if ($h > $w) {
            $ratio = $h / $height;
            $width = ceil($w / $ratio);
        } else {
            $ratio = $w / $width;
            $height = ceil($h / $ratio);
        }

        $path      = $this->config['path']['recipe_public_thumb_upload'] . '/' . $imageName;
        $fileName  = $this->config['path']['recipe_image_upload'] . '/'.$originalimage;
        $tmp       = imagecreatetruecolor($width, $height);

        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
            case 'JPG':
                $image = imagecreatefromjpeg($fileName);
                imagecopyresampled($tmp, $image, 0, 0, 0, 0, $width, $height, $w, $h);
                imagejpeg($tmp, $path, 100);
                break;
            case 'PNG':
            case 'png':
                $image = imagecreatefrompng($fileName);
                imagecopyresampled($tmp, $image, 0, 0, 0, 0, $width, $height, $w, $h);
                imagepng($tmp, $path, 100);
                break;
            case 'GIF':
            case 'gif':
                $image = imagecreatefromgif($fileName);
                imagecopyresampled($tmp, $image, 0, 0, 0, 0, $width, $height, $w, $h);
                imagegif($tmp, $path);
                break;
            case 'BMP':
            case 'bmp':
                $image = imagecreatefromstring($fileName);
                //$image = imagecreatefromwbmp($fileName);
                imagecopyresampled($tmp, $image, 0, 0, 0, 0, $width, $height, $w, $h);
                imagewbmp($tmp, $path);
                break;
            default:
                exit;
                break;
        }
        imagedestroy($image);
        imagedestroy($tmp);

        return $path;
    }
}
