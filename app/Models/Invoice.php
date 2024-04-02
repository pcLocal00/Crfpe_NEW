<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;
    protected $table = 'inv_invoices';

    protected $guarded = [];

    protected static $logName = 'invoices_log';
    
    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }
    public function entity()
    {
        return $this->belongsTo(Entitie::class, 'entitie_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function user_creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function entity_funder()
    {
        return $this->belongsTo(Entitie::class, 'entitie_funder_id');
    }
    public function contact_funder()
    {
        return $this->belongsTo(Contact::class, 'contact_funder_id');
    }
}
