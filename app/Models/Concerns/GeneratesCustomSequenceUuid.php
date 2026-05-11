<?php

namespace App\Models\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

trait GeneratesCustomSequenceUuid
{
    protected static function generateCustomSequenceUuid(string $columnName = 'uuid'): string
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $datePrefix = now($timezone)->format('dmY');
        $sequencePartLength = 4;
        $maxSequence = (10 ** $sequencePartLength) - 1;

        return Cache::lock(static::class.':'.$datePrefix.':'.$columnName, 5)->block(5, function () use (
            $columnName,
            $datePrefix,
            $sequencePartLength,
            $maxSequence,
            $timezone
        ): string {
            $sequenceCacheKey = static::class.':sequence:'.$columnName.':'.$datePrefix;
            $nextSequence = (int) Cache::get($sequenceCacheKey, 0) + 1;
            $uuidIdentifier = '';

            while ($nextSequence <= $maxSequence) {
                $rawSequenceIdentifier = $datePrefix.str_pad((string) $nextSequence, $sequencePartLength, '0', STR_PAD_LEFT);
                $uuidIdentifier = static::obfuscateSequenceToUuid($rawSequenceIdentifier, $columnName);

                $isUsed = static::query()
                    ->where($columnName, $uuidIdentifier)
                    ->exists();

                if (! $isUsed) {
                    break;
                }

                $nextSequence++;
            }

            if ($nextSequence > $maxSequence || $uuidIdentifier === '') {
                $modelClass = static::class;
                throw new \RuntimeException("Sequence limit reached for {$modelClass} on {$datePrefix}.");
            }

            $cacheExpireAt = Carbon::now($timezone)->endOfDay();
            Cache::put($sequenceCacheKey, $nextSequence, $cacheExpireAt);

            return $uuidIdentifier;
        });
    }

    protected static function obfuscateSequenceToUuid(string $rawSequenceIdentifier, string $columnName): string
    {
        $secret = (string) config('app.key', 'andalan-default-key');
        $entropySource = static::class.'|'.$columnName.'|'.$rawSequenceIdentifier;
        $hex = strtoupper(substr(hash_hmac('sha256', $entropySource, $secret), 0, 32));

        $hex[12] = '8';
        $variantNibbleOptions = ['8', '9', 'A', 'B'];
        $variantNibbleIndex = hexdec($hex[31]) % count($variantNibbleOptions);
        $hex[16] = $variantNibbleOptions[$variantNibbleIndex];

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
