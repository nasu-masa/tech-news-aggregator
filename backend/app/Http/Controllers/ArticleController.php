<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(IndexArticleRequest $request)
    {
        $query = Article::query()
            ->with('source')
            ->orderByRaw('published_at DESC NULLS LAST')
            ->latest('id');

        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword');

            $query->where(function ($query) use ($keyword) {
                $query
                    ->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->integer('source_id'));
        }

        return $query->paginate(20);
    }

    public function show(Article $article)
    {
        return $article->load('source');
    }
}
