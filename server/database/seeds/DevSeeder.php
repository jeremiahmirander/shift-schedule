<?php

use App\User;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $test_account = 'test@example.com';
        if (!User::where('email', $test_account)->first()) {
            factory(User::class)->create([
                'email' => $test_account,
            ]);
        }
    }
}
