<?php

namespace Warehouse\Controller;

use Warehouse\Entity\Attachment;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Class AttachmentController
 * @package Warehouse\Controller
 */
class AttachmentController extends AbstractActionController
{
    const RECIPE_ATTACHMENT = 'recipe';
    const STOCK_ATTACHMENT  = 'stock';

    /**
     * @var array
     */
    protected $config;
    /**
     * @var
     */
    protected $doctrine;

    /**
     * @param array $config
     * @param $doctrine
     */
    public function __construct(
        array $config,
        $doctrine
    ) {
        $this->config   = $config;
        $this->doctrine = $doctrine;
    }

    /**
     * @param array $file
     * @param string $documentType
     * @param string $parentId
     * @param string $fileLabel
     * @return array
     */
    public function addAttachment(array $file, string $documentType, string $parentId, string $fileLabel = ''): array
    {
        if (null !== $file and '' !== $file['name']) {
            $uploaded = is_uploaded_file($file['tmp_name']);
            if (!$uploaded) {
                return [
                    'result'  => 'error',
                    'message' => 'The image has been uploaded. Maximum size ' . $this->config['upload_max_size'] . '.',
                ];
            }
            if (self::RECIPE_ATTACHMENT === $documentType) {
                $path = $this->config['path']['recipe_image_upload'];
            } else {
                $path = $this->config['path']['stock_image_upload'];
            }
            $image_saved = move_uploaded_file($file['tmp_name'], $path . '/' . $file['name']);
            if (!$image_saved) {
                return [
                    'result'  => 'error',
                    'message' => 'The image has not been uploaded. Contact the administrator.',
                ];
            }
            rename($path . '/' . $file['name'],  $path . '/' . $parentId . '_' . $file['name']);

            $attachment = new Attachment();
            $attachment->setFileName($parentId . '_' . $file['name']);
            $attachment->setPath($path);
            $attachment->setMime($file['type']);
            $attachment->setSize($file['size']);

            if ($file['type'] == 'image/jpeg' or $file['type'] == 'image/jpg' or $file['type'] == 'image/png' or $file['type'] == 'image/bmp' or $file['type'] == 'image/gif') {
                //if another default photo already exists, modify defaultphoto field with 0 for the old one
                if (self::RECIPE_ATTACHMENT === $documentType) {
                    $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeIdDefaultPhoto($parentId);
                } else {
                    $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\StockAttachment')->findByStockIdDefaultPhoto($parentId);
                }
                if ($attachmentEntity !== null and count($attachmentEntity) != 0) {
                    $attachmentPhoto = $attachmentEntity[0];
                    $date = new \DateTime('now');
                    if (self::RECIPE_ATTACHMENT === $documentType) {
                        $attachmentPhoto->setDescription("Recipe photo - " . $date->format('Y-m-d H:i:s'));
                    } else {
                        $attachmentPhoto->setDescription("Article photo - " . $date->format('Y-m-d H:i:s'));
                    }
                    $attachmentPhoto->setDefaultPhoto(0);
                    $this->doctrine->persist($attachmentPhoto);
                }
                $attachment->setDefaultPhoto("1");
                if (self::RECIPE_ATTACHMENT === $documentType) {
                    $attachment->setDescription("Recipe photo");
                } else {
                    $attachment->setDescription("Article photo");
                }
            } else {
                $attachment->setDefaultPhoto("0");
                if ($fileLabel != '') {
                    $attachment->setDescription($fileLabel);
                } else {
                    $attachment->setDescription($file['name']);
                }
            }
            $attachment->setCreationDate(new \DateTime('now'));
            $this->doctrine->persist($attachment);
            $this->doctrine->flush();

            return [
                'result' => 'success',
                'id'     => $attachment->getId(),
            ];
        }
        return [
            'result' => 'error',
            'id'     => '',
        ];
    }

    public function openAction(): void
    {
        $id = $this->params()->fromRoute('id', 0);

        $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\Attachment')->findByAttachmentId($id);
        $attachment = $attachmentEntity[0];

        $filename = $attachment->getPath() . '/' . $attachment->getFileName();

        header('Content-type: '.$attachment->getMime());
        header('Content-Disposition: inline; filename="'.$attachment->getFileName().'"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filename));
        header('Accept-Ranges: bytes');
    }

    public function changedescriptionAction()
    {
        $id          = $this->params()->fromRoute('id', 0);
        $description = $_POST['file-label'];

        $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\Attachment')->findByAttachmentId($id);
        $attachment = $attachmentEntity[0];

        $attachment->setDescription($description);
        $this->doctrine->persist($attachment);
        $this->doctrine->flush();

        return new JsonModel([
            'status'  => 'success',
            'message' => 'Description changed successfully',
        ]);
    }
}
