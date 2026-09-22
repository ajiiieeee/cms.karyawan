<?php
namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/** @extends Factory<User> */
class UserFactory extends Factory { protected $model = User::class; public function definition(): array { return ['username'=>fake()->unique()->userName(),'nama'=>fake()->name(),'email'=>fake()->unique()->safeEmail(),'password'=>'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','remember_token'=>Str::random(10),'is_active'=>true]; } }
