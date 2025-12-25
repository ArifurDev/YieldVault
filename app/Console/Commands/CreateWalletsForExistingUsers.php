<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateWalletsForExistingUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:create-missing';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create wallet for users who do not have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::transaction(function () {

            $users = User::doesntHave('wallet')->get();

            foreach ($users as $user) {
                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0
                ]);
            }

            $this->info('Wallets created for '.$users->count().' users.');
        });

        return Command::SUCCESS;
    }
}
