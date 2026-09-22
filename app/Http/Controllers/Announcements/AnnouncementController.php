<?php
namespace App\Http\Controllers\Announcements;
use App\Models\Announcement;
use Illuminate\View\View;
class AnnouncementController extends \App\Http\Controllers\Controller { public function index(): View { return view('announcements.index',['announcements'=>Announcement::active()->latest('published_at')->paginate(10)]); } }
