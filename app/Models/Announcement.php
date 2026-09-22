<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class Announcement extends Model { protected $fillable=['title','content','published_at','expires_at','is_published']; protected function casts(): array { return ['published_at'=>'datetime','expires_at'=>'datetime','is_published'=>'boolean']; } public function scopeActive(Builder $query): Builder { return $query->where('is_published',true)->where('published_at','<=',now())->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>=',now())); } }
