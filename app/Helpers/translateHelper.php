<?php

namespace App\Helpers;

use App\Jobs\TranslateTextJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class translateHelper
{

    /**
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
    public static function translateText(string $text, string $lang): ?string
    {
        if ($lang === 'en') {
            return $text;
        }
        $tr = new GoogleTranslate();
        $tr->setTarget($lang);

        return $tr->translate($text);
    }

    /**
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
//    public static function translateCached(string $key, string $text, string $lang)
//    {
//        if ($lang === 'en') {
//            return $text;
//        }
//
//        $cacheKey = "translation.{$key}.{$lang}";
//
//        // Return cached value if exists
//        if (Cache::has($cacheKey)) {
//            return Cache::get($cacheKey);
//        }
//
//        // Check DB
//        $row = DB::table('translations')
//            ->where('key', $key)
//            ->where('lang', $lang)
//            ->first();
//
//        if ($row) {
//            Cache::put($cacheKey, $row->value, 86400);
//            return $row->value;
//        }
//        $translated = self::translateText($text, $lang);
//        TranslateTextJob::dispatch($key, $text, $lang);  // Dispatch job ONCE
//
//        return $text;  //  Fallback response
//    }

    public static function translateCached(string $key, string $text, string $lang)
    {
        // English = master (no translation)
        if ($lang === 'en' || empty($text)) {
            return $text;
        }

        $cacheKey = "translation.{$key}.{$lang}";

        //  Cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Database second
        $row = DB::table('translations')
            ->where('key', $key)
            ->where('lang', $lang)
            ->first();

        if ($row) {
            Cache::put($cacheKey, $row->value, 86400);
            return $row->value;
        }

        // Google Translate
        try {
            $tr = new GoogleTranslate();
            $tr->setTarget($lang);

            $translated = $tr->translate($text);

            //Store in DB (avoid duplicates)
            DB::table('translations')->updateOrInsert(
                ['key' => $key, 'lang' => $lang],
                [
                    'value'      => $translated,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Store in Cache
            Cache::put($cacheKey, $translated, 86400);

            return $translated;

        } catch (\Throwable $e) {

            Log::error('Translation failed', [
                'key'  => $key,
                'lang' => $lang,
                'err'  => $e->getMessage(),
            ]);

            return $text;
        }
    }


    public static function toEnglish(string $text, string $lang): string
    {
        if ($lang === 'en' || empty($text)) {
            return $text;
        }

        try {
            $tr = new \Stichoza\GoogleTranslate\GoogleTranslate();
            $tr->setSource($lang)->setTarget('en');

            return $tr->translate($text);
        } catch (\Throwable $e) {
            return $text; // fallback
        }
    }



}
