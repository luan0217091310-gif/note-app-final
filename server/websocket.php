<?php
/**
 * WebSocket Server - Real-time Collaboration
 * Cho phép nhiều người dùng chỉnh sửa ghi chú đồng thời
 * 
 * Chạy: php server/websocket.php
 * Yêu cầu: composer require cboden/ratchet
 */

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class NoteCollabServer implements MessageComponentInterface {
    protected $clients;
    protected $rooms; // note_id => [connections]

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
        echo "WebSocket Server khởi động...\n";
    }

    /**
     * Khi client kết nối
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Kết nối mới: {$conn->resourceId}\n";
    }

    /**
     * Khi nhận được message từ client
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['type'])) return;

        switch ($data['type']) {
            case 'join':
                // Tham gia room (note_id)
                $noteId = $data['note_id'];
                if (!isset($this->rooms[$noteId])) {
                    $this->rooms[$noteId] = [];
                }
                $this->rooms[$noteId][$from->resourceId] = $from;
                $from->noteId = $noteId;
                $from->userId = $data['user_id'] ?? 0;

                echo "User {$from->userId} joined room {$noteId}\n";

                // Thông báo cho các thành viên khác trong room
                $this->broadcastToRoom($noteId, [
                    'type' => 'user_joined',
                    'user_id' => $from->userId
                ], $from);
                break;

            case 'update':
                // Phát nội dung cập nhật đến tất cả thành viên khác trong room
                $noteId = $data['note_id'];
                $this->broadcastToRoom($noteId, [
                    'type' => 'update',
                    'user_id' => $data['user_id'],
                    'title' => $data['title'] ?? '',
                    'content' => $data['content'] ?? ''
                ], $from);
                break;
        }
    }

    /**
     * Khi client ngắt kết nối
     */
    public function onClose(ConnectionInterface $conn) {
        // Xóa khỏi room
        if (isset($conn->noteId) && isset($this->rooms[$conn->noteId])) {
            unset($this->rooms[$conn->noteId][$conn->resourceId]);

            // Thông báo user rời
            $this->broadcastToRoom($conn->noteId, [
                'type' => 'user_left',
                'user_id' => $conn->userId ?? 0
            ], $conn);

            // Xóa room rỗng
            if (empty($this->rooms[$conn->noteId])) {
                unset($this->rooms[$conn->noteId]);
            }
        }

        $this->clients->detach($conn);
        echo "Ngắt kết nối: {$conn->resourceId}\n";
    }

    /**
     * Khi có lỗi
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Lỗi: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Gửi message đến tất cả thành viên trong room (trừ sender)
     */
    private function broadcastToRoom($noteId, $data, ConnectionInterface $exclude = null) {
        if (!isset($this->rooms[$noteId])) return;

        $json = json_encode($data);
        foreach ($this->rooms[$noteId] as $id => $conn) {
            if ($exclude && $id === $exclude->resourceId) continue;
            $conn->send($json);
        }
    }
}

// =============================================
// KHỞI CHẠY SERVER
// =============================================
$port = 8081;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NoteCollabServer()
        )
    ),
    $port
);

echo "WebSocket Server chạy trên cổng {$port}\n";
$server->run();
