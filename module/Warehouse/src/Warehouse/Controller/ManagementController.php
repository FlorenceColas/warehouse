<?php
/**
 * User: FlorenceColas
 * Date: 18/02/16
 * Version: 1.00
 * ManagementController: NOT USED - TEST FOR SERIAL
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 */

namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;

class ManagementController extends AbstractActionController
{
    protected $entityManager;

    public function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    public function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }


//DIO extension needed but not available/compatible with php7
//        $fd = dio_open('/dev/tty.usbserial-FTGPEHRX', O_RDONLY | O_NOCTTY);
//        $barcode = dio_read($fd, 10);
//        dio_close($fd);

/*
// Let's start the class
        $serial = new \PhpSerial();

// First we must specify the device. This works on both linux and windows (if
// your linux serial device is /dev/ttyS0 for COM1, etc)
        $serial->deviceSet("COM1");

// We can change the baud rate, parity, length, stop bits, flow control
        $serial->confBaudRate(9600);
        $serial->confParity("none");
        $serial->confCharacterLength(8);
        $serial->confStopBits(1);
        $serial->confFlowControl("none");

// Then we need to open it
        $serial->deviceOpen();

// To write into
//        $serial->re("Hello !");

// Or to read from
        $read = $serial->readPort();

// If you want to change the configuration, the device must be closed
        $serial->deviceClose();

// We can change the baud rate
        $serial->confBaudRate(9600);
*/



}