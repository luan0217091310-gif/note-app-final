<?php
/**
 * LabelController - Quản lý nhãn (CRUD, gắn nhãn, lọc)
 */

require_once BASE_PATH . '/models/Label.php';
require_once BASE_PATH . '/models/User.php';

class LabelController {
    private $labelModel;

    public function __construct() {
        $this->labelModel = new Label();
    }

    /**
     * Hiển thị trang quản lý nhãn
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $labels = $this->labelModel->getAllByUser($userId);

        $userModel = new User();
        $user = $userModel->findById($userId);

        require BASE_PATH . '/views/layouts/header.php';
        require BASE_PATH . '/views/layouts/sidebar.php';
        require BASE_PATH . '/views/labels/index.php';
        require BASE_PATH . '/views/layouts/footer.php';
    }

    /**
     * Thêm nhãn mới (API - JSON)
     */
    public function create() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            echo json_encode(['error' => 'Tên nhãn không được trống']);
            return;
        }

        $id = $this->labelModel->create($userId, $name);

        if ($id) {
            echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
        } else {
            echo json_encode(['error' => 'Không thể tạo nhãn']);
        }
    }

    /**
     * Đổi tên nhãn (API - JSON)
     */
    public function update($id = null) {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');

        if (!$id || empty($name)) {
            echo json_encode(['error' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->labelModel->update($id, $name);
        echo json_encode(['success' => $result]);
    }

    /**
     * Xóa nhãn (API - JSON)
     */
    public function delete($id = null) {
        header('Content-Type: application/json');

        if (!$id) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            return;
        }

        $result = $this->labelModel->delete($id);
        echo json_encode(['success' => $result]);
    }
}
