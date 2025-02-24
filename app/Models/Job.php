<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{

    use HasFactory;

    protected $table = 'job';

    protected $fillable = [
        'title', 'category_id', 'job_type_id', 'vacancy', 'salary', 'location',
        'description', 'benefits', 'responsibility', 'qualifications', 'keywords',
        'experience', 'company_name', 'company_location', 'company_website', 'user_id'
    ];


    public function jobType(){
        return $this->belongsTo(JobType::class);
    }
    
    public function category(){
        return $this->belongsTo(Category::class);
    }

}
