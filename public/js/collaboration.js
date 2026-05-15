/**
 * collaboration.js - Real-time Collaboration qua WebSocket
 * Cho phép nhiều người cùng chỉnh sửa ghi chú đồng thời
 */

let wsConnection = null;
let currentCollabNoteId = null;

/**
 * Khởi tạo kết nối WebSocket cho 1 ghi chú
 */
function initCollaboration(noteId) {
    currentCollabNoteId = noteId;

    // Kết nối WebSocket server
    const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const wsUrl = wsProtocol + '//' + window.location.hostname + ':8080';

    try {
        wsConnection = new WebSocket(wsUrl);

        wsConnection.onopen = function() {
            console.log('WebSocket connected');
            // Tham gia room theo note_id
            wsConnection.send(JSON.stringify({
                type: 'join',
                note_id: noteId,
                user_id: USER_ID
            }));
        };

        wsConnection.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                handleCollabMessage(data);
            } catch (e) {
                console.error('WebSocket message parse error:', e);
            }
        };

        wsConnection.onclose = function() {
            console.log('WebSocket disconnected');
            const indicator = document.getElementById('collabIndicator');
            if (indicator) indicator.style.display = 'none';
        };

        wsConnection.onerror = function(error) {
            console.error('WebSocket error:', error);
        };

    } catch (e) {
        console.error('WebSocket connection failed:', e);
    }
}

/**
 * Xử lý message nhận được từ WebSocket
 */
function handleCollabMessage(data) {
    switch (data.type) {
        case 'update':
            // Cập nhật nội dung từ người khác (nếu khác user hiện tại)
            if (data.user_id !== USER_ID) {
                const titleInput = document.getElementById('sharedNoteTitle');
                const contentInput = document.getElementById('sharedNoteContent');

                // Lưu vị trí cursor
                const titlePos = titleInput.selectionStart;
                const contentPos = contentInput.selectionStart;

                if (data.title !== undefined) titleInput.value = data.title;
                if (data.content !== undefined) contentInput.value = data.content;

                // Khôi phục vị trí cursor
                titleInput.setSelectionRange(titlePos, titlePos);
                contentInput.setSelectionRange(contentPos, contentPos);
            }
            break;

        case 'user_joined':
            console.log('User joined:', data.user_id);
            break;

        case 'user_left':
            console.log('User left:', data.user_id);
            break;
    }
}

/**
 * Gửi cập nhật nội dung qua WebSocket
 */
function sendCollabUpdate(data) {
    if (wsConnection && wsConnection.readyState === WebSocket.OPEN) {
        wsConnection.send(JSON.stringify({
            type: 'update',
            note_id: currentCollabNoteId,
            user_id: USER_ID,
            title: data.title,
            content: data.content
        }));
    }
}

/**
 * Ngắt kết nối WebSocket
 */
function disconnectCollaboration() {
    if (wsConnection) {
        wsConnection.close();
        wsConnection = null;
    }
    currentCollabNoteId = null;
}
