<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateTextJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $key,
        public string $text,
        public string $lang
    ) {}

    public function handle()
    {
        if (DB::table('translations')
            ->where('key', $this->key)
            ->where('lang', $this->lang)
            ->exists()) {
            return;
        }

        try {
            $tr = new GoogleTranslate();
            $tr->setTarget($this->lang);

            $translated = $tr->translate($this->text);

            DB::table('translations')->insert([
                'key'   => $this->key,
                'lang'  => $this->lang,
                'value' => $translated,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Translation failed', [
                'key' => $this->key,
                'lang' => $this->lang,
            ]);
        }
    }
}
