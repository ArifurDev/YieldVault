<?php
namespace App\Helpers;

use App\Models\DailyTaskLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class helpers
{
    /**
     * File upload
     */
    public static function uploadFile($folderName, $file, $fileName = null, $disk = 'public'): string
    {
        $filePath = 'uploads/'.$folderName.'/'.$fileName;

        if ($disk === 's3') {
            Storage::disk($disk)->put(
                $filePath,
                file_get_contents($file),
                [
                    'visibility' => 'public',
                    'ContentType' => $file->getMimeType(),
                    'ContentDisposition' => 'inline',
                ]
            );
        } else {
            Storage::disk($disk)->putFileAs(
                'uploads/'.$folderName,
                $file,
                $fileName
            );
        }

        return $filePath;
    }

    /**
     *  remove file
     */
    public static function deleteFile($filePath, $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
            return true;
        }
        return false;
    }

    /**
     * generate temp or direct url
     */
    public static function generateTempURL($path, $disk = 'public'): ?string
    {
        if (!$path || !Storage::disk($disk)->exists($path)) {
            return null;
        }

        if (method_exists(Storage::disk($disk), 'temporaryUrl')) {
            return Storage::disk($disk)->temporaryUrl(
                $path,
                now()->addMinutes(30),
            );
        }

        return Storage::disk($disk)->url($path);
    }


    //! Send firebase notification
    public static function sendNotifyMobile($token, $data): void
    {
        try {
            // Debugging autoloader
            if (!class_exists(Factory::class)) {
                Log::error('Class "Kreait\\Firebase\\Factory" not found. Check if the Firebase SDK is installed.');
            }
            $factory = (new Factory)->withServiceAccount(storage_path('app/private/google-services.json'));

            $messaging    = $factory->createMessaging();
            $notification = FirebaseNotification::create($data['title'], Str::limit($data['message'], 100));

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);
            $messaging->send($message);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }


    /**
     * Streak calculate helper (reuse history logic)
     */
    public static function calculateStreak(int $userId): int
    {
        $completedDays = DailyTaskLog::with('dailyTask')
            ->where('user_id', $userId)
            ->get()
            ->filter(fn($log) => $log->dailyTask && $log->dailyTask->date)
            ->map(fn($log) => $log->dailyTask->date->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        $streak = 0;
        $current = now()->format('Y-m-d');

        while (in_array($current, $completedDays)) {
            $streak++;
            $current = now()->subDays($streak)->format('Y-m-d');
        }

        return $streak;
    }


}
