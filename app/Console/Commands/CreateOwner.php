<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('studio:create-owner {email?} {--name=}')]
#[Description('Создать первого владельца Smartnet CRM')]
class CreateOwner extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (User::where('is_owner', true)->exists()) {
            $this->error('Владелец уже существует.');

            return self::FAILURE;
        }

        $email = $this->argument('email') ?: $this->ask('Email владельца');
        $name = $this->option('name') ?: $this->ask('Имя владельца');
        $password = $this->secret('Пароль (не менее 12 символов)');
        $validator = Validator::make(compact('email', 'name', 'password'), [
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        User::create(['email' => $email, 'name' => $name, 'password' => $password, 'is_owner' => true]);
        $this->info('Владелец создан.');

        return self::SUCCESS;
    }
}
