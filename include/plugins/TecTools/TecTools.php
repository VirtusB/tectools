<?php

class TecTools {
    /**
     * @var RCMS $RCMS
     */
    var $RCMS;

    public $TOOL_IMAGE_FOLDER;
    public $RELATIVE_TOOL_IMAGE_FOLDER;

    public function __construct($RCMS) {
        $this->RCMS = $RCMS;
        $this->TOOL_IMAGE_FOLDER = $this->RCMS->getUploadsFolder() . '/tools/images';
        $this->RELATIVE_TOOL_IMAGE_FOLDER = $this->RCMS->getRelativeUploadsFolder() . '/tools/images';

        if (isset($_POST['add_tool'])) {
            $this->addTool();
        }

        if (isset($_POST['edit_tool'])) {
            $this->editTool();
        }

        if (isset($_POST['add_category'])) {
            $this->addCategory();
        }

        if (isset($_POST['edit_category'])) {
            $this->editCategory();
        }

        if (isset($_POST['add_manufacturer'])) {
            $this->addManufacturer();
        }

        if (isset($_POST['edit_manufacturer'])) {
            $this->editManufacturer();
        }

        if (isset($_POST['edit_user'])) {
            $this->editUser();
        }
    }

    public function array_equal($a, $b) {
        return (
            is_array($a)
            && is_array($b)
            && count($a) == count($b)
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }

    public function getUserByID($userID) {
        $res = $this->RCMS->execute('CALL getUserByID(?)', array('i', &$userID));
        return $res->fetch_assoc();
    }

    private function editUser() {
        $userID = $_POST['user_id'];

        if (!$this->authorizeUser($userID)) {
            return;
        }

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $zipcode = $_POST['zipcode'];
        $city = $_POST['city'];
        $level = $_POST['level'];

        $currentUser = $this->getUserByID($_POST['user_id']);

        if (isset($_POST['password']) && $_POST['password'] !== '') {
            $password = $this->RCMS->Login->saltPass($_POST['password']);
        } else {
            $password = $currentUser['Password'];
        }

        $this->RCMS->execute('CALL editUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array('issssssssi', &$userID, &$firstname, &$lastname, &$email, &$password, &$phone, &$address, &$zipcode, &$city, &$level));
        header('Location: /dashboard');
    }

    public function removeAllCategoriesFromTool($toolID) {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $toolID = intval($toolID);

        $this->RCMS->execute('CALL removeAllCategoriesFromTool(?)', array('i', &$toolID));
    }

    private function authorizeUser($userID) {
        $userID = intval($userID);

        if (!$this->RCMS->Login->isLoggedIn()) {
            return false;
        }

        if ($this->RCMS->Login->isAdmin() === false && intval($userID) !== $this->RCMS->Login->getUserID()) {
            return false;
        }

        return true;
    }

    private function addManufacturer() {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerName = $_POST['manufacturer_name'];

        $this->RCMS->execute('CALL addManufacturer(?)', array('s', &$manufacturerName));
        header('Location: /dashboard');
    }

    public function getManufacturer($manufacturerID) {
        $manufacturerID = intval($manufacturerID);

        $res = $this->RCMS->execute('CALL getManufacturer(?)', array('i', &$manufacturerID));
        return $res->fetch_assoc();
    }

    private function editManufacturer() {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerID = intval($_POST['manufacturer_id']);
        $manufacturerName = $_POST['manufacturer_name'];

        $this->RCMS->execute('CALL editManufacturer(?, ?)', array('is', &$manufacturerID, &$manufacturerName));
        header('Location: /dashboard');
    }

    private function addCategory() {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $categoryName = $_POST['category_name'];

        $this->RCMS->execute('CALL addCategory(?)', array('s', &$categoryName));
        header('Location: /dashboard');
    }

    public function getCategory($categoryID) {
        $categoryID = intval($categoryID);

        $res = $this->RCMS->execute('CALL getCategory(?)', array('i', &$categoryID));
        return $res->fetch_assoc();
    }

    private function editCategory() {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $categoryID = intval($_POST['category_id']);
        $categoryName = $_POST['category_name'];

        $this->RCMS->execute('CALL editCategory(?, ?)', array('is', &$categoryID, &$categoryName));
        header('Location: /dashboard');
    }

    private function editTool() {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $toolID = $_POST['tool_id'];
        $toolName = $_POST['tool_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $manufacturerID = $_POST['manufacturer_id'];
        $categories = $_POST['categories'] ?? [];

        $currentTool = $this->getToolByID($toolID);

        if (!empty($categories)) {
            $currentToolCategoryIDs = array_map(function ($category) {
                return strval($category['CategoryID']);
            }, $currentTool['Categories']); 

            if ($this->array_equal($categories, $currentToolCategoryIDs) === false) {
                // opdater kategorier
                $this->removeAllCategoriesFromTool($toolID);
                foreach ($categories as $categoryID) {
                    $this->addToolToCategory($toolID, $categoryID);
                }
            }
        }

        $imageName = $_FILES['image']['name'] ?? false;
        if ($imageName) {
            // opdater billede
            $newImageName = $this->uploadImage($imageName, $_FILES['image']['tmp_name']);
            if (!$newImageName) {
                return;
            }
        } else {
            // brug gamle billede
            $newImageName = $currentTool['Image'];
        }

        $this->RCMS->execute('CALL editTool(?, ?, ?, ?, ?, ?)', array('issisi', &$manufacturerID, &$toolName, &$description, &$status, &$newImageName, &$toolID));
        header('Location: /dashboard');
    }

    private function uploadImage($imageName, $tmpName) {
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = date('dmYHis') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $tmpName);

        if (!isset($type) || !in_array($type, array("image/png", "image/jpeg", "image/gif"))) {
            $_SESSION['tool_image_upload_error'] = 'Billedet kunne ikke uploades';
            return false;
        }

        $finalImagePath = $this->TOOL_IMAGE_FOLDER . '/' . $newImageName;

        $uploadResult = move_uploaded_file($_FILES['image']['tmp_name'], $finalImagePath);
        if (!$uploadResult) {
            $_SESSION['tool_image_upload_error'] = 'Billedet kunne ikke uploades';
            return;
        }

        return $newImageName;
    }

    private function addTool() {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $toolName = $_POST['tool_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $manufacturerID = $_POST['manufacturer_id'];
        $categories = $_POST['categories'] ?? [];

        $imageName = $_FILES['image']['name'] ?? false;
        if (!$imageName) {
            return;
        }

        $newImageName = $this->uploadImage($imageName, $_FILES['image']['tmp_name']);

        if (!$newImageName) {
            return;
        }

        $res = $this->RCMS->execute('CALL addTool(?, ?, ?, ?, ?)', array('issis', &$manufacturerID, &$toolName, &$description, &$status, &$newImageName));

        $toolID = $res->fetch_assoc()['lastInsertId'];

        foreach ($categories as $categoryID) {
            $this->addToolToCategory($toolID, $categoryID);
        }

        header('Location: /dashboard');
    }

    public function addToolToCategory($toolID, $categoryID) {
        $toolID = intval($toolID);
        $categoryID = intval($categoryID);

        $this->RCMS->execute('CALL addToolToCategory(?, ?)', array('ii', &$toolID, &$categoryID));
    }

    public function getToolByID($toolID) {
        $toolID = intval($toolID);

        $res = $this->RCMS->execute('CALL getToolByID(?)', array('i', &$toolID));
        $tool = $res->fetch_assoc();

        $tool['Categories'] = $this->getCategoriesForTool($tool['ToolID']);

        return $tool;
    }

    public function getAllTools() {
        $res = $this->RCMS->execute('CALL getAllTools();');

        $tools = $res->fetch_all(MYSQLI_ASSOC);

        foreach ($tools as $key => $tool) {
            $tools[$key]['Categories'] = $this->getCategoriesForTool($tool['ToolID']);
        }

        return $tools;
    }

    public function getAllStatuses() {
        $res = $this->RCMS->execute('CALL getAllStatuses();');

        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllCategories() {
        $res = $this->RCMS->execute('CALL getAllCategories()');

        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function getCategoriesForTool($toolID) {
        $toolID = intval($toolID);

        $res = $this->RCMS->execute('CALL getCategoriesForTool(?)', array('i', &$toolID));

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    public function getAllManufacturers() {
        $res = $this->RCMS->execute('CALL getAllManufacturers()');
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}