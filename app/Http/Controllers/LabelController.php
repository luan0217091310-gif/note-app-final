<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabelController extends Controller
{
    public function index()
    {
        $labels = Label::where('user_id', Auth::id())->withCount('notes')->get();
        return view('labels.index', compact('labels'));
    }

    public function create(Request $request)
    {
        $data  = $request->json()->all();
        $label = Label::create(['user_id' => Auth::id(), 'name' => trim($data['name'] ?? '')]);
        return response()->json(['success' => true, 'id' => $label->id, 'name' => $label->name]);
    }

    public function update(Request $request, $id)
    {
        $data  = $request->json()->all();
        $label = Label::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $label->update(['name' => trim($data['name'] ?? '')]);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Label::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        return response()->json(['success' => true]);
    }
}
