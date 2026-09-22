<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable { use HasApiTokens, HasFactory, Notifiable; protected $fillable=['username','password','email','nama','foto','is_active']; protected $hidden=['password','remember_token']; protected function casts(): array { return ['password'=>'hashed','is_active'=>'boolean','last_login_at'=>'datetime']; } }
