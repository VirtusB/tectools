<?php

class TecTools {
    /**
     * @var $RCMS RCMS
     */
    var $RCMS;

    private $statusList = [
      'STATUS_SOLD_OUT' => ['id' => 0, 'name' => 'Ikke på lager'],
      'STATUS_AVAILABLE' => ['id' => 1, 'name' => 'På lager'],
      'STATUS_RESERVED' => ['id' => 2, 'name' => 'Reserveret'],
      'STATUS_LOANED_OUT' => ['id' => 3, 'name' => 'Udlånt'],
    ];

    public $TOOL_IMAGE_FOLDER;
    public $RELATIVE_TOOL_IMAGE_FOLDER;

    public function __construct($RCMS) {
        $this->RCMS = $RCMS;
        $this->TOOL_IMAGE_FOLDER = $this->RCMS->uploadsfolder . '/tools/images';
        $this->RELATIVE_TOOL_IMAGE_FOLDER = $this->RCMS->relativeUploadsFolder . '/tools/images';

        if (isset($_POST['add_tool'])) {
            $this->addTool();
        }
    }

    public function addTool() {
        $toolName = $_POST['tool_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $manufacturerID = $_POST['manufacturer_id'];
        $barcode = $_POST['barcode'];

        $imageName = $_FILES['image']['name'] ?? false;
        if (!$imageName) {
            $_SESSION['create_tool_image_error'] = 'Billedet kunne ikke uploades';
            return;
        }
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = date('dmYHis') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;

        $finalImagePath = $this->TOOL_IMAGE_FOLDER . '/' . $newImageName;

        $uploadResult = move_uploaded_file($_FILES['image']['tmp_name'], $finalImagePath);
        if (!$uploadResult) {
            $_SESSION['create_tool_image_error'] = 'Billedet kunne ikke uploades';
            return;
        }

        $this->RCMS->execute('CALL addTool(?, ?, ?, ?, ?, ?)', array('isssis', &$manufacturerID, &$barcode, &$toolName, &$description, &$status, &$newImageName));
    }

    public function getStatusList() {
        return $this->statusList;
    }

    public function getAllTools() {

    }

    public function getAllManufacturers() {
        $res = $this->RCMS->execute('CALL getAllManufacturers()');
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}