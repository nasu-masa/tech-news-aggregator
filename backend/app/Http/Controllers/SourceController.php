<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSourceRequest;
use App\Jobs\ImportFeedJob;
use App\Models\Source;
use App\Models\User;
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

    private const CUSTOM_LIMIT = 3;

    private function countSubscribedCustomSources(User $user): int
    {
        return $user->sources()
            ->where('sources.is_default', false)
            ->where('sources.is_active', true)
            ->count();
    }

    private function ensureCustomLimitNotExceeded(User $user): void
    {
        if ($this->countSubscribedCustomSources($user) >= self::CUSTOM_LIMIT) {
            throw ValidationException::withMessages([
                'feed_url' => ['カスタムRSSの購読は最大3件までです。購読を解除してから追加してください。'],
            ]);
        }
    }

    // Call only inside a transaction after locking the user's row.
    private function attachWithinLimit(User $user, Source $source): void
    {
        if ($source->is_active && ! $source->is_default
            && ! $user->sources()->whereKey($source->id)->exists()) {
            $this->ensureCustomLimitNotExceeded($user);
        }

        $user->sources()->syncWithoutDetaching([$source->id]);
    }

    public function store(StoreSourceRequest $request)
    {
        $feedUrl = $request->validated()['feed_url'];

        $existing = Source::where('feed_url', $feedUrl)->first();

        if ($existing) {
            DB::transaction(function () use ($request, $existing) {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $this->attachWithinLimit($user, $existing);
            });
            $existing->is_subscribed = true;

            return response()->json($existing);
        }

        // Avoid fetching RSS when already full; recheck under lock before attaching.
        $this->ensureCustomLimitNotExceeded($request->user());

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
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            // Another request may have registered this URL while RSS was fetched.
            $source = Source::firstOrCreate(['feed_url' => $feedUrl], [
                'name' => $name,
                'site_url' => $siteUrl,
                'created_by_user_id' => $user->id,
                'is_active' => true,
            ]);

            $this->attachWithinLimit($user, $source);

            return $source;
        });

        if ($source->wasRecentlyCreated) {
            ImportFeedJob::dispatch($source);
        }

        $source->is_subscribed = true;

        return response()->json($source, $source->wasRecentlyCreated ? 201 : 200);
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

        DB::transaction(function () use ($request, $source) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $this->attachWithinLimit($user, $source);
        });

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
