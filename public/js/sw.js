/**
 * Service Worker - Khả năng Offline (PWA)
 * Cache tài nguyên tĩnh, API responses
 * Đồng bộ dữ liệu khi có mạng trở lại
 */

const CACHE_NAME = 'note-app-v1';
const OFFLINE_URL = '/offline.html';

// Danh sách tài nguyên cần cache
const STATIC_ASSETS = [
    '/',
    '/css/style.css',
    '/js/app.js',
    '/js/collaboration.js'
];

// =============================================
// INSTALL - Cache tài nguyên tĩnh
// =============================================
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Service Worker: Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .catch(function(err) {
                console.log('SW Cache error:', err);
            })
    );
    self.skipWaiting();
});

// =============================================
// ACTIVATE - Xóa cache cũ
// =============================================
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames
                    .filter(function(name) { return name !== CACHE_NAME; })
                    .map(function(name) { return caches.delete(name); })
            );
        })
    );
    self.clients.claim();
});

// =============================================
// FETCH - Network first, fallback to cache
// =============================================
self.addEventListener('fetch', function(event) {
    // Bỏ qua POST requests
    if (event.request.method !== 'GET') return;

    // Bỏ qua WebSocket
    if (event.request.url.includes('ws://') || event.request.url.includes('wss://')) return;

    event.respondWith(
        fetch(event.request)
            .then(function(response) {
                // Nếu có mạng, lưu vào cache và trả về
                const responseClone = response.clone();
                caches.open(CACHE_NAME).then(function(cache) {
                    cache.put(event.request, responseClone);
                });
                return response;
            })
            .catch(function() {
                // Nếu offline, lấy từ cache
                return caches.match(event.request)
                    .then(function(response) {
                        return response || new Response('Offline - Không có kết nối mạng', {
                            status: 503,
                            statusText: 'Service Unavailable',
                            headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                        });
                    });
            })
    );
});

// =============================================
// SYNC - Đồng bộ dữ liệu offline khi có mạng
// =============================================
self.addEventListener('sync', function(event) {
    if (event.tag === 'sync-notes') {
        event.waitUntil(syncOfflineNotes());
    }
});

/**
 * Đồng bộ các ghi chú đã chỉnh sửa offline lên server
 */
async function syncOfflineNotes() {
    try {
        // Mở IndexedDB lấy pending changes
        const db = await openDB();
        const tx = db.transaction('pending_changes', 'readonly');
        const store = tx.objectStore('pending_changes');
        const changes = await getAllFromStore(store);

        for (const change of changes) {
            try {
                await fetch(change.url, {
                    method: change.method,
                    headers: change.headers,
                    body: change.body
                });

                // Xóa change đã sync thành công
                const deleteTx = db.transaction('pending_changes', 'readwrite');
                deleteTx.objectStore('pending_changes').delete(change.id);
            } catch (err) {
                console.log('Sync failed for:', change.id);
            }
        }
    } catch (err) {
        console.log('Sync error:', err);
    }
}

// =============================================
// IndexedDB Helpers cho offline storage
// =============================================
function openDB() {
    return new Promise(function(resolve, reject) {
        const request = indexedDB.open('NoteAppOffline', 1);

        request.onupgradeneeded = function(event) {
            const db = event.target.result;
            // Store cho cached notes
            if (!db.objectStoreNames.contains('notes')) {
                db.createObjectStore('notes', { keyPath: 'id' });
            }
            // Store cho pending changes (offline edits)
            if (!db.objectStoreNames.contains('pending_changes')) {
                db.createObjectStore('pending_changes', { keyPath: 'id', autoIncrement: true });
            }
        };

        request.onsuccess = function(event) {
            resolve(event.target.result);
        };

        request.onerror = function(event) {
            reject(event.target.error);
        };
    });
}

function getAllFromStore(store) {
    return new Promise(function(resolve, reject) {
        const request = store.getAll();
        request.onsuccess = function() { resolve(request.result); };
        request.onerror = function() { reject(request.error); };
    });
}

// =============================================
// Message handler - Nhận lệnh từ main thread
// =============================================
self.addEventListener('message', function(event) {
    if (event.data.type === 'CACHE_NOTES') {
        // Cache notes data vào IndexedDB
        cacheNotesToDB(event.data.notes);
    }
});

async function cacheNotesToDB(notes) {
    try {
        const db = await openDB();
        const tx = db.transaction('notes', 'readwrite');
        const store = tx.objectStore('notes');

        for (const note of notes) {
            store.put(note);
        }
    } catch (err) {
        console.log('Cache notes to DB error:', err);
    }
}
