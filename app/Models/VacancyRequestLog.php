<?php

namespace App\Models;

use App\Enums\VacancyRequestStatus;
use Illuminate\Database\Eloquent\Model;

class VacancyRequestLog extends Model
{
    protected $fillable = ['vacancy_request_id', 'user_id', 'status', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vacancyRequest()
    {
        return $this->belongsTo(VacancyRequest::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return VacancyRequestStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return VacancyRequestStatus::tryFrom($this->status)?->color() ?? 'secondary';
    }
}
