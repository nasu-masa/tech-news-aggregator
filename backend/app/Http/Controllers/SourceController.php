<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSourceRequest;
use App\Models\Source;
use App\Services\FeedFetcher;
use App\Services\FeedParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SourceController extends Controller
{
    public function __construct(
        private readonly FeedFetcher $feedFetcher,
        private readonly FeedParser $feedParser,
    ) {}

    public function index(Request $request)
    {
        return Source::query()
            ->where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->whereNull('created_by_user_id')
                    ->orWhereHas('users', function ($q) use ($request) {
                        $q->where('users.id', $request->user()->id);
                    });
            })
            ->withExists([
                'users as is_subscribed' => function ($query) use ($request) {
                    $query->where('users.id', $request->user()->id);
                },
            ])
            ->orderBy('name')
            ->get();
    }

    public function store(StoreSourceRequest $request)
    {
        $feedUrl = $request->validated()['feed_url'];

        $existing = Source::where('feed_url', $feedUrl)->first();

        if ($existing) {
            $request->user()->sources()->syncWithoutDetaching([$existing->id]);
            $existing->is_subscribed = true;

            return response()->json($existing);
        }

        try {
            $xml = $this->feedFetcher->fetchXml($feedUrl);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages([
                'feed_url' => ['フィードの取得に失敗しました。'],
            ]);
        } catch (RequestException) {
            throw ValidationException::withMessages([
                'feed_url' => ['フィードの取得に失敗しました。'],
            ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'feed_url' => ['フィードの取得に失敗しました。'],
            ]);
        }

        try {
            $title = $this->feedParser->parseFeedTitle($xml);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'feed_url' => ['有効なRSS/AtomフィードのURLを指定してください。'],
            ]);
        }

        $name = $title ?: parse_url($feedUrl, PHP_URL_HOST);
        $siteUrl = parse_url($feedUrl, PHP_URL_SCHEME).'://'.parse_url($feedUrl, PHP_URL_HOST);

        $source = DB::transaction(function () use ($feedUrl, $name, $siteUrl, $request) {
            $source = Source::create([
                'name' => $name,
                'feed_url' => $feedUrl,
                'site_url' => $siteUrl,
                'created_by_user_id' => $request->user()->id,
                'is_active' => true,
            ]);

            $request->user()->sources()->syncWithoutDetaching([$source->id]);

            return $source;
        });

        $source->is_subscribed = true;

        return response()->json($source, 201);
    }

    public function subscribe(Request $request, Source $source)
    {
        if (! $source->is_active) {
            abort(404);
        }

        if (
            $source->created_by_user_id !== null
            && $source->created_by_user_id !== $request->user()->id
        ) {
            abort(404);
        }

        $request->user()->sources()->syncWithoutDetaching([$source->id]);

        return response()->json([
            'message' => 'ニュースソースを追加しました。',
        ]);
    }

    public function unsubscribe(Request $request, Source $source)
    {
        $isSubscribed = $request->user()->sources()->whereKey($source->id)->exists();

        if (! $isSubscribed) {
            abort(404);
        }

        $request->user()->sources()->detach($source->id);

        return response()->json([
            'message' => 'ニュースソースの購読を解除しました。',
        ]);
    }
}
