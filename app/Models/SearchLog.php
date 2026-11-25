<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'normalized_query',
        'display_query',
        'results_count',
        'session_hash',
    ];

    protected function casts(): array
    {
        return [
            'results_count' => 'integer',
        ];
    }

    public static function record(string $rawQuery, int $resultsCount, ?string $sessionId = null): void
    {
        $clean = trim(preg_replace('/\s+/', ' ', $rawQuery));
        $length = mb_strlen($clean);

        if ($length < 3 || $length > 80 || $resultsCount <= 0) {
            return;
        }

        $normalized = Str::lower($clean);
        $sessionHash = $sessionId ? hash('sha256', $sessionId) : null;

        if ($sessionHash) {
            $recentlyLogged = static::where('normalized_query', $normalized)
                ->where('session_hash', $sessionHash)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->exists();

            if ($recentlyLogged) {
                return;
            }
        }

        static::create([
            'normalized_query' => $normalized,
            'display_query' => Str::title($clean),
            'results_count' => $resultsCount,
            'session_hash' => $sessionHash,
        ]);
    }
}