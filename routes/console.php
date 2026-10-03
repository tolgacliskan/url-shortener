<?php

declare(strict_types=1);

use App\Actions\User\CreateUser;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Plan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

Artisan::command('create-user {email} {--name=} {--plan=}', function (string $email): int {
    $plan = null;

    if ($this->option('plan')) {
        $plan = Plan::where('internal_id', $this->option('plan'))->first();

        if (! $plan) {
            $this->error("There is no plan with the internal id {$this->option('plan')}.");

            return Command::FAILURE;
        }
    }

    $data = [
        'name' => $this->option('name') ?: $this->ask('Name'),
        'email' => $email,
        'password' => $this->secret('Password'),
    ];

    $validator = Validator::make($data, (new RegisterRequest)->rules());

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $message) {
            $this->error($message);
        }

        return Command::FAILURE;
    }

    $user = CreateUser::execute([
        'name' => data_get($data, 'name'),
        'email' => $email,
        'password' => Hash::make(data_get($data, 'password')),
        'email_verified_at' => now(),
    ]);

    if ($plan) {
        $user->refresh()->currentWorkspace->update(['plan_id' => $plan->id]);
    }

    $this->info("Created {$user->email}.");

    return Command::SUCCESS;
})->purpose('Create a user with a personal workspace, for installs with registration closed');
