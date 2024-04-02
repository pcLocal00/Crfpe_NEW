<?php

namespace App\Models;

use App\Models\Task;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Comment extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    protected $table = 'tasks_comment';
    protected static $logAttributes = ['*'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
}
