<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchQueryExpander
{
    private const ALIASES = [
        'bbw'   => ['bath & body works', 'bath & body', 'bath and body'],
        'nb'    => ['new balance'],
        'vs'    => ["victoria's secret", 'victorias secret', 'victoria secret'],
        'ck'    => ['calvin klein'],
        'mk'    => ['michael kors'],
        'tb'    => ['tory burch'],
        'rl'    => ['ralph lauren', 'polo ralph lauren'],
        'th'    => ['tommy hilfiger'],
        'ysl'   => ['yves saint laurent', 'saint laurent'],
        'lv'    => ['louis vuitton'],
        'edp'   => ['eau de parfum', 'perfume', 'fragrance'],
        'edt'   => ['eau de toilette', 'perfume', 'fragrance'],
        'ffm'   => ['fine fragrance mist', 'fragrance mist', 'body mist'],
        'og'    => ['originals'],
    ];

    /**
     * @return array<int, string>
     */
    public static function expandPhrases(string $raw): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', Str::lower($raw)));
        if ($clean === '') {
            return [];
        }

        $variants = [$clean];

        if (isset(self::ALIASES[$clean])) {
            array_push($variants, ...self::ALIASES[$clean]);
        }

        $tokens = preg_split('/\s+/', $clean) ?: [];
        if (count($tokens) > 1) {
            foreach ($tokens as $idx => $token) {
                if (isset(self::ALIASES[$token])) {
                    foreach (self::ALIASES[$token] as $expansion) {
                        $replaced = $tokens;
                        $replaced[$idx] = $expansion;
                        $variants[] = implode(' ', $replaced);
                    }
                }
            }
        }

        $all = [];
        foreach ($variants as $phrase) {
            $all[] = $phrase;

            if (preg_match('/\band\b/', $phrase)) {
                $all[] = preg_replace('/\s*\band\b\s*/', ' & ', $phrase);
                $all[] = preg_replace('/\s*\band\b\s*/', '&', $phrase);
            }

            if (str_contains($phrase, '&')) {
                $all[] = trim(preg_replace('/\s*&\s*/', ' and ', $phrase));
                $all[] = trim(preg_replace('/\s*&\s*/', ' ', $phrase));
            }

            $stripped = trim(preg_replace('/[\'\-\.]+/', '', $phrase));
            if ($stripped !== '' && $stripped !== $phrase) {
                $all[] = $stripped;
            }
        }

        return array_values(array_unique(array_filter(
            array_map(fn($v) => trim(preg_replace('/\s+/', ' ', $v)), $all)
        )));
    }

    public static function applyToProductQuery(Builder $query, string $rawSearch): Builder
    {
        $phrases = self::expandPhrases($rawSearch);
        if (empty($phrases)) {
            return $query;
        }

        return $query->where(function (Builder $outer) use ($phrases, $rawSearch) {
            foreach ($phrases as $phrase) {
                $outer->orWhere(function (Builder $phraseGroup) use ($phrase) {
                    self::applyPhraseOrTokens($phraseGroup, $phrase);
                });
            }

            // Dynamic initialism match (e.g., "bbw" -> "B... & B... W...", "nb" -> "N... B...")
            if ($acronymPattern = self::buildAcronymLikePattern($rawSearch)) {
                $outer->orWhere('name', 'like', $acronymPattern)
                    ->orWhereHas('brand', fn(Builder $bq) => $bq->where('name', 'like', $acronymPattern))
                    ->orWhereHas('category', fn(Builder $cq) => $cq->where('name', 'like', $acronymPattern));
            }
        });
    }

    public static function applyToTaxonomyQuery(Builder $query, string $rawSearch, string $column = 'name'): Builder
    {
        $phrases = self::expandPhrases($rawSearch);
        if (empty($phrases)) {
            return $query;
        }

        return $query->where(function (Builder $outer) use ($phrases, $column, $rawSearch) {
            foreach ($phrases as $phrase) {
                $outer->orWhere($column, 'like', "%{$phrase}%");

                $tokens = self::meaningfulTokens($phrase);
                if (count($tokens) > 1) {
                    $outer->orWhere(function (Builder $tokenGroup) use ($tokens, $column) {
                        foreach ($tokens as $token) {
                            $tokenGroup->where($column, 'like', "%{$token}%");
                        }
                    });
                }
            }

            if ($acronymPattern = self::buildAcronymLikePattern($rawSearch)) {
                $outer->orWhere($column, 'like', $acronymPattern);
            }
        });
    }

    private static function buildAcronymLikePattern(string $raw): ?string
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', trim($raw));
        $len = strlen($clean);

        if ($len < 2 || $len > 6 || str_contains(trim($raw), ' ')) {
            return null;
        }

        $letters = str_split(Str::lower($clean));

        return implode('% ', $letters) . '%';
    }

    private static function applyPhraseOrTokens(Builder $group, string $phrase): void
    {
        self::whereAnyFieldMatches($group, $phrase);

        $tokens = self::meaningfulTokens($phrase);
        if (count($tokens) > 1) {
            $group->orWhere(function (Builder $allTokensGroup) use ($tokens) {
                foreach ($tokens as $token) {
                    $allTokensGroup->where(function (Builder $singleTokenGroup) use ($token) {
                        self::whereAnyFieldMatches($singleTokenGroup, $token);
                    });
                }
            });
        }
    }

    private static function whereAnyFieldMatches(Builder $q, string $term): void
    {
        $like = "%{$term}%";

        $q->where('name', 'like', $like)
            ->orWhere('description', 'like', $like)
            ->orWhereHas('brand', fn(Builder $bq) => $bq->where('name', 'like', $like))
            ->orWhereHas('category', fn(Builder $cq) => $cq->where('name', 'like', $like))
            ->orWhereHas('tags', fn(Builder $tq) => $tq->where('name', 'like', $like))
            ->orWhereHas('variants', function (Builder $vq) use ($like) {
                $vq->where('sku', 'like', $like)
                    ->orWhere('variant_label', 'like', $like);
            });
    }

    /**
     * @return array<int, string>
     */
    private static function meaningfulTokens(string $phrase): array
    {
        $stopWords = ['and', '&', 'of', 'the', 'for', 'in', 'a', 'an'];
        $parts = preg_split('/\s+/', $phrase) ?: [];

        return array_values(array_filter($parts, function (string $t) use ($stopWords) {
            return mb_strlen($t) >= 2 && !in_array($t, $stopWords, true);
        }));
    }
}
