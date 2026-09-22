<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:create-admin {name} {email}')]
#[Description('Create or promote an administrator account')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::query()->firstOrNew(['email' => $this->argument('email')]);
        $user->name = $this->argument('name');
        $user->password = $this->secret('Password');
        $user->is_admin = true;
        $user->save();

        $this->components->info("Administrator [{$user->email}] is ready.");

        return self::SUCCESS;
    }
}
