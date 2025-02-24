<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobApplication;
use App\Models\JobType;
use Illuminate\Http\Request;
use Psy\CodeCleaner\FinalClassPass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class JobsController extends Controller
{
    public function index(Request $request) {

        $categories = Category::where('status',1)->get();
        $jobTypes = JobType::where('status',1)->get();

        $job = Job::where('status', 1);

        if(!empty($request->keyword)){
            $job = $job->where(function($query ) use($request) {
                $query->orWhere('title','like','%'.$request->keyword.'%');
                $query->orWhere('keywords','like','%'.$request->keyword.'%');
            });

        }

        if(!empty($request->location)){
            $job = $job->where('location',$request->location);
        }

        if(!empty($request->category)){
            $job = $job->where('category_id',$request->category);
        }

        $jobTypeArray = [];
        if(!empty($request->jobType)){
            $jobTypeArray = explode(',',$request->jobType);

            $job = $job->whereIn('job_type_id',$jobTypeArray);
        }

        if(!empty($request->experience)){
            $job = $job->where('experience',$request->experience);
        }

        $job = $job->with(['jobType','category']);

        if($request->sort == '0'){
            $job = $job->orderBy('created_at','ASC');
        } else{
            $job = $job->orderBy('created_at','DESC');
        }

        $job = $job->paginate(50);

        return view('front.jobs',[
            'categories' =>$categories,
            'jobTypes' =>$jobTypes,
            'job' => $job,
            'jobTypeArray' => $jobTypeArray,
        ]);
    }


    public function detail($id){

        $job = Job::where([
            'id' => $id,
            'status' => 1,
        ])->with(['jobType','category'])->first();
        if($job==null){
            abort(404);
        }

        return view('front.jobDetail',compact('job'));
    }


    public function applyJob(Request $request) {
       
        $id = $request->id;
       

        $job = Job::where('id',$id)->first();

        if($job==null){
            $message = 'Job does not exist.';
            session()->flash('error',$message);
            return response()->json([
                'status' => false,
                'message' =>$message,
            ]);
        }

        $employer_id = $job->user_id;

        if ($employer_id == Auth::user()->id){
            $message = 'You can not apply on your own job.';
            session()->flash('error',$message);
            return response()->json([
                'status' => false,
                'message' =>$message,
            ]);
        }
        

        $jobApplicationCount = JobApplication::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id, 
        ])->count();

        if($jobApplicationCount > 0) {
            $message = 'You already applied on this job.';
            session()->flash('error',$message);
            return response()->json([
                'status' => false,
                'message' =>$message,
            ]);
        }

        $application = new JobApplication;
        $application->job_id = $id;
        $application->user_id = Auth::user()->id;
        $application->employer_id = $employer_id;
        $application->applied_date = now();
        $application->save();

        $employer = User::where('id',$employer_id)->first();
        $mailData = [
            'employer' => $employer,
            'user' => Auth::user(),
            'job' => $job, 
        ];
        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));


        $message = 'You have successfully applied.';
        session()->flash('success',$message);
        return response()->json([
            'status' => true,
            'message' =>$message,
        ]);

    }
}
