<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Label;
use App\Models\NoteShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{

    // ─── TRANG CHỦ ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user   = Auth::user();
        $search  = $request->input('search', '');
        $labelId = $request->input('label_id');

        $notes = Note::where('user_id', $user->id)
            ->when($search, fn($q) => $q->where(fn($q2) =>
                $q2->where('title', 'like', "%$search%")
                   ->orWhere('content', 'like', "%$search%")
            ))
            ->when($labelId, fn($q) => $q->whereHas('labels', fn($q2) => $q2->where('labels.id', $labelId)))
            ->with(['images', 'labels', 'shares'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->orderByDesc('updated_at')
            ->get();

        $labels = Label::where('user_id', $user->id)->get();
        return view('notes.index', compact('notes', 'labels', 'user'));
    }

    // ─── API: LẤY GHI CHÚ ─────────────────────────────────────────
    public function get($id)
    {
        $note   = Note::with(['images', 'labels', 'shares.sharedWith'])->findOrFail($id);
        $access = $note->checkAccess(Auth::id());

        if (!$access) return response()->json(['error' => 'Không có quyền truy cập'], 403);

        if ($note->isLocked() && !session("unlocked_notes.$id")) {
            return response()->json(['locked' => true, 'id' => $id]);
        }

        $data          = $note->toArray();
        $data['access'] = $access;
        // Thêm lock_password indicator (không trả về hash)
        $data['has_lock'] = $note->isLocked();
        return response()->json($data);
    }

    // ─── API: TẠO GHI CHÚ ─────────────────────────────────────────
    public function create(Request $request)
    {
        $note = Note::create(['user_id' => Auth::id(), 'title' => '', 'content' => '']);
        return response()->json(['success' => true, 'id' => $note->id]);
    }

    // ─── API: AUTO-SAVE ───────────────────────────────────────────
    public function save(Request $request)
    {
        $data  = $request->json()->all();
        $note  = Note::findOrFail($data['id'] ?? 0);
        $access = $note->checkAccess(Auth::id());

        if (!in_array($access, ['owner', 'edit'])) {
            return response()->json(['error' => 'Không có quyền chỉnh sửa'], 403);
        }

        $note->update(['title' => $data['title'] ?? '', 'content' => $data['content'] ?? '']);
        return response()->json(['success' => true]);
    }

    // ─── API: XÓA ─────────────────────────────────────────────────
    public function delete($id)
    {
        $note = Note::findOrFail($id);
        if ($note->checkAccess(Auth::id()) !== 'owner') {
            return response()->json(['error' => 'Chỉ chủ sở hữu mới có thể xóa'], 403);
        }

        // Xóa file ảnh
        foreach ($note->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        $note->delete();
        return response()->json(['success' => true]);
    }

    // ─── API: GHIM ────────────────────────────────────────────────
    public function togglePin($id)
    {
        $note = Note::findOrFail($id);
        $newPinned = !$note->is_pinned;
        $note->update(['is_pinned' => $newPinned, 'pinned_at' => $newPinned ? now() : null]);
        return response()->json(['success' => true, 'is_pinned' => $newPinned]);
    }

    // ─── API: KHÓA (Better Approach) ──────────────────────────────
    public function setLock(Request $request)
    {
        $data = $request->json()->all();
        $note = Note::findOrFail($data['id'] ?? 0);
        $note->lock_password = $data['password']; // mutator tự hash
        $note->save();
        return response()->json(['success' => true]);
    }

    public function removeLock(Request $request)
    {
        $data = $request->json()->all();
        $note = Note::findOrFail($data['id'] ?? 0);

        if (!$note->verifyLock($data['current_password'] ?? '')) {
            return response()->json(['error' => 'Mật khẩu hiện tại không đúng']);
        }

        $note->update(['lock_password' => null]);
        session()->forget("unlocked_notes.{$note->id}");
        return response()->json(['success' => true]);
    }

    public function verifyLock(Request $request)
    {
        $data = $request->json()->all();
        $note = Note::findOrFail($data['id'] ?? 0);

        if ($note->verifyLock($data['password'] ?? '')) {
            session(["unlocked_notes.{$note->id}" => true]);
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Mật khẩu không đúng']);
    }

    // ─── API: UPLOAD ẢNH ──────────────────────────────────────────
    public function uploadImages(Request $request)
    {
        $note    = Note::findOrFail($request->note_id);
        $uploaded = [];

        foreach ($request->file('images', []) as $file) {
            if (!$file->isValid()) continue;
            $path = $file->store('uploads/notes', 'public');
            $note->images()->create(['image_path' => $path]);
            $uploaded[] = ['path' => $path];
        }
        return response()->json(['success' => true, 'images' => $uploaded]);
    }

    public function removeImage($imageId)
    {
        $image = \App\Models\NoteImage::findOrFail($imageId);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return response()->json(['success' => true]);
    }

    // ─── API: CHIA SẺ ─────────────────────────────────────────────
    public function share(Request $request)
    {
        $data       = $request->json()->all();
        $note       = Note::findOrFail($data['note_id'] ?? 0);
        $sharedUser = User::where('email', $data['email'] ?? '')->first();

        if (!$sharedUser)                      return response()->json(['error' => 'Email không tồn tại']);
        if ($sharedUser->id === Auth::id())    return response()->json(['error' => 'Không thể chia sẻ với chính mình']);

        NoteShare::updateOrCreate(
            ['note_id' => $note->id, 'shared_with_id' => $sharedUser->id],
            ['owner_id' => Auth::id(), 'permission' => $data['permission'] ?? 'read']
        );

        return response()->json(['success' => true, 'shared_with' => $sharedUser->name]);
    }

    public function sharedWithMe()
    {
        $shared = NoteShare::with(['note', 'owner'])
            ->where('shared_with_id', Auth::id())
            ->orderByDesc('shared_at')
            ->get();
        $labels = Label::where('user_id', Auth::id())->get();
        return view('notes.shared', compact('shared', 'labels'));
    }

    public function updatePermission(Request $request)
    {
        $data  = $request->json()->all();
        $share = NoteShare::findOrFail($data['share_id'] ?? 0);
        $share->update(['permission' => $data['permission'] ?? 'read']);
        return response()->json(['success' => true]);
    }

    public function revokeShare($shareId)
    {
        NoteShare::findOrFail($shareId)->delete();
        return response()->json(['success' => true]);
    }

    // ─── API: NHÃN ────────────────────────────────────────────────
    public function syncLabels(Request $request)
    {
        $data = $request->json()->all();
        $note = Note::findOrFail($data['note_id'] ?? 0);
        $note->labels()->sync($data['label_ids'] ?? []);
        return response()->json(['success' => true]);
    }

    // ─── API: TÌM KIẾM ────────────────────────────────────────────
    public function search(Request $request)
    {
        $q       = $request->input('q', '');
        $labelId = $request->input('label_id');
        $userId  = Auth::id();

        $notes = Note::where('user_id', $userId)
            ->when($q, fn($query) => $query->where(fn($q2) =>
                $q2->where('title', 'like', "%$q%")->orWhere('content', 'like', "%$q%")
            ))
            ->when($labelId, fn($query) => $query->whereHas('labels', fn($q2) => $q2->where('labels.id', $labelId)))
            ->with('labels')
            ->withCount('shares')
            ->orderByDesc('is_pinned')->orderByDesc('updated_at')
            ->get()
            ->map(function ($note) {
                $note->lock_password_exists = $note->isLocked();
                return $note;
            });

        return response()->json($notes);
    }
}
