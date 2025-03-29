<?php

namespace App\Console\Commands;

use App\Enums\BookingAbilityEnum;
use App\Enums\ResourceAbilityEnum;
use App\Models\User;
use Illuminate\Console\Command;

class MakeUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::query()->create([
            'name' => 'admin',
            'email' => 'admin@example.ru',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'),
        ]);

        echo $user->createToken('test', [
            ResourceAbilityEnum::RESOURCE_GET,
            ResourceAbilityEnum::RESOURCE_CREATE,
            BookingAbilityEnum::BOOKING_CREATE,
            BookingAbilityEnum::BOOKING_SHOW,
            BookingAbilityEnum::BOOKING_DESTROY,
        ])->plainTextToken;
    }
}
