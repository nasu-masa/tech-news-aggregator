<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexArticleRequest;
use App\Http\Requests\UpdateArticleStatusRequest;
use App\Models\Article;
use App\Models\UserArticle;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(IndexArticleRequest $request)
    {
        $query = Article::query()
            ->with([
                'source' => function ($query) use ($request) {
                    $query->withExists([
                        'users as is_subscribed' => function ($query) use ($request) {
                            $query->where('users.id', $request->user()->id);
                        },
                    ]);
                },
                'userArticles' => function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                },
            ])
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

        if ($request->boolean('subscribed_only')) {
            $query->whereHas('source.users', function ($query) use ($request) {
                $query->where('users.id', $request->user()->id);
            });
        }

        return $query->paginate(20);
    }

    public function show(Request $request, Article $article)
    {
        $article->load([
            'source' => function ($query) use ($request) {
                $query->withExists([
                    'users as is_subscribed' => function ($query) use ($request) {
                        $query->where('users.id', $request->user()->id);
                    },
                ]);
            },
            'userArticles' => function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            },
        ]);

        return $article;
    }

    public function updateStatus(
        UpdateArticleStatusRequest $request,
        Article $article
    ) {
        $userArticle = UserArticle::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
            ],
            $request->validated(),
        );

        return response()->json($userArticle);
    }
}
