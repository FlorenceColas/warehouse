<?php
/**
 * User: FlorenceColas
 * Date: 13/11/16
 * Version: 1.00
 * UploadFileForm: Upload file Form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Form;

use Zend\Form\Element;
use Zend\Form\Element\Checkbox;
use Zend\Form\Form;
use Zend\InputFilter;

class UploadFileForm extends Form
{
    public function __construct()
    {
        parent::__construct('upload-file-form');

        $this->setAttribute('method', 'post');

        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File('uploaded-file');
        $file->setLabel('New attachment')
            ->setAttribute('id', 'uploaded-file');
    //        ->setAttribute('multiple', true); //to autorize multiple files to upload at the same time
        $this->add($file);

        $textField = new Element\Text('attachment-description');
        $textField->setLabel('Attachment description: ')
            ->setAttribute('id', 'attachment-description');
        $this->add($textField);

        $chk = new Checkbox('chk-defaultphoto');
        $chk->setLabel('')
            ->setAttribute('id', 'chk-defaultphoto');
        $this->add($chk);
    }

    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();

        //description
        $descriptionInput = new InputFilter\Input('description-attachment');
        $descriptionInput->setRequired(false);
        $inputFilter->add($descriptionInput);

        // chk-defaultphoto
        $chk = new InputFilter\Input('chk-defaultphoto');
        $chk->setRequired(false);
        $inputFilter->add($chk);

        // File Input
        $fileInput = new InputFilter\FileInput('uploaded-file');
        $fileInput->setRequired(true);

        // You only need to define validators and filters
        // as if only one file was being uploaded. All files
        // will be run through the same validators and filters
        // automatically.
        $fileInput->getValidatorChain()
          //  ->attachByName('filesize',      array('max' => 204800))
            ->attachByName('filemimetype',  array('mimeType' => 'image/png,image/x-ms-bmp,image/jpeg,image/gif,image/tiff,video/mpeg,video/mp4,application/pdf,application/zip'));
        //    ->attachByName('fileimagesize', array('maxWidth' => 100, 'maxHeight' => 100));

        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);
    }

}