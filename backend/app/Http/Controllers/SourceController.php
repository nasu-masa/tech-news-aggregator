<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        return Source::query()
            ->where('is_active', true)
            ->withExists([
                'users as is_subscribed' => function ($query) use ($request) {
                    $query->where('users.id', $request->user()->id);
                },
            ])
            ->orderBy('name')
            ->get();
    }

    public function subscribe(Request $request, Source $source)
    {
        if (! $source->is_active) {
            abort(404);
        }

        $request->user()
            ->sources()
            ->syncWithoutDetaching([$source->id]);

        return response()->json([
            'message' => 'ニュースソースを追加しました。',
        ]);
    }

    public function unsubscribe(Request $request, Source $source)
    {
        $request->user()
            ->sources()
            ->detach($source->id);

        return response()->json([
            'message' => 'ニュースソースの購読を解除しました。',
        ]);
    }
}
