<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    /**
     * Get the images of the schedule.
     */
    public function images()
    {
        return $this->hasMany(ScheduleImage::class);
    }
}
