<?php

namespace App\Http\Controllers;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\File;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AccountController extends Controller
{
    //đăng kí
    public function registration(){
        return view('front.account.registration');
    }

    public function processRegistration(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required',
        ]);

        if($validator -> passes()){
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success','Bạn đã đăng ký thành công.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' =>  $validator->errors()
            ]);
        }
    }

    //đăng nhập
    public function login(){
        return view('front.account.login');
    }

    public function authenticate(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->passes()){
            if (Auth::attempt(['email'=>$request->email, 'password'=>$request->password])) {
                return redirect()->route('account.profile');
            } else {
                return redirect()->route('account.login')->with('error', 'Email/Mật khẩu không chính xác');
            }
        }else{
            return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'));
        }
    }

    public function profile(){


        $id = Auth::user()->id;
        $user = User::where('id', $id)->first();

        return view('front.account.profile',[
            'user' => $user 
        ]); 
    }

    public function updateProfile(Request $request){
        Log::info('Dữ liệu nhận được:', $request->all()); // Ghi log dữ liệu gửi lên
        
        $id = Auth::user()->id;
        $user = User::find($id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy người dùng']);
        }

        // Kiểm tra validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email,' . $id,
            'mobile' => 'nullable|numeric|digits_between:3,15',
            'designation' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            Log::error('Validation lỗi:', $validator->errors()->toArray());
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            
            Log::info('Dữ liệu cũ:', $user->toArray());

            // Cập nhật từng giá trị
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            
            Log::info('Dữ liệu thay đổi:', $user->getDirty()); 

            // Nếu có thay đổi, lưu lại
            if ($user->isDirty()) {
                $user->save();
                User::where('id', $id)->update(['designation' => $request->designation]);
                Log::info('Dữ liệu đã lưu:', $user->toArray());
                return response()->json(['status' => true, 'message' => 'Hồ sơ được cập nhật thành công.']);
            }

            return response()->json(['status' => false, 'message' => 'Không có thay đổi nào được phát hiện.']);
        } catch (Exception $e) {
            Log::error('Lỗi khi cập nhật profile:', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Cập nhật không thành công']);
        }
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $request){
        // dd($request->all());
        $id = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'image' => 'required|image'
        ]);

        if ($validator->passes()) {

            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id.'-'.time().'.'.$ext;
            $image->move(public_path('/profile_pic/'), $imageName);

            // create
            $sourcePath = public_path('/profile_pic/'.$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath); // 800 x 600

            $image->cover(150,150); 
            $image->toPng()->save(public_path('/profile_pic/thumb/'.$imageName));

            // delete
            if (File::exists(public_path('profile_pic/thumb/' . Auth::user()->image))) {
                File::delete(public_path('profile_pic/thumb/' . Auth::user()->image));
            }
            
            if (File::exists(public_path('profile_pic/' . Auth::user()->image))) {
                File::delete(public_path('profile_pic/' . Auth::user()->image));
            }

            User::where('id',$id)->update(['image' => $imageName]);

            session()->flash('success', 'Ảnh hồ sơ được cập nhật thành công.');

            return response()->json([
                'status' => true,
                'errors' => [] 
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors() 
            ]);
        }
    }

    public function createJob() {

        $categories = Category::orderBy('name','ASC')->where('status',1)->get();

        $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get();

        return view('front.account.job.create',[
            'categories' => $categories,
            'jobTypes' => $jobTypes,
        ]);
    }

    public function saveJob(Request $request) {
        

        $rules = [
            'title' => 'required|min:3|max:200',
            'category_id' => 'required|integer',
            'job_type_id' => 'required|integer',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {

            $data = $request->only([
                'title', 'category_id', 'job_type_id', 'vacancy', 'salary',
                'location', 'description', 'benefits', 'responsibility',
                'qualifications', 'keywords', 'experience', 'company_name',
                'company_location', 'company_website'
            ]);
            $data['user_id'] = Auth::id();

            Job::create($data);


            return response()->json([
                'status' => true,
                'message' => 'Đã thêm công việc thành công.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'errors' => ['database' => $e->getMessage()]
            ]);
        }
    }

    public function myJobs(){
        
        $job = Job::where('user_id',optional(Auth::user())->id)->with('jobType')->orderBy('created_at','DESC')->paginate(5);
        
    

        return view('front.account.job.my-jobs',[
            'job' => $job
        ]);

    }


    public function editJob(Request $request, $id){
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Bạn cần phải đăng nhập trước.');
        }
        if (!is_numeric($id)) {
            abort(404);
        }
        $categories = Category::orderBy('name','ASC')->where('status',1)->get();
        $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get();

        $job = Job::where('user_id', Auth::user()->id)->findOrFail($id);


        return view('front.account.job.edit',[
            'categories' =>$categories,
            'jobTypes' =>$jobTypes,
            'job' => $job
        ]);
    }

    public function updateJob(Request $request,$id) {
        

        $rules = [
            'title' => 'required|min:3|max:200',
            'category_id' => 'required|integer',
            'job_type_id' => 'required|integer',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $job = Job::find($id);

            if (!$job) {
                return response()->json([
                    'status' => false,
                    'errors' => ['job' => 'Không tìm thấy công việc.']
                ]);
            }

            $data = $request->only([
                'title', 'category_id', 'job_type_id', 'vacancy', 'salary',
                'location', 'description', 'benefits', 'responsibility',
                'qualifications', 'keywords', 'experience', 'company_name',
                'company_location', 'company_website'
            ]);
            $data['user_id'] = Auth::id();
            
            $job->update($data);


            return response()->json([
                'status' => true,
                'message' => 'Cập nhật công việc thành công.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'errors' => ['database' => $e->getMessage()]
            ]);
        }
    }   


    public function deleteJob(Request $request){
        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $request->jobId,
        ])->first();

        if($job ==null){
            session()->flash('error','Công việc đã bị xóa hoặc không tìm thấy.');
            return response()->json([
                'status' =>true,
            ]);
        }

        Job::where('id', $request->jobId)->delete();
        session()->flash('success','Xóa công việc thành công.');
            return response()->json([
                'status' =>true,
            ]);
    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(),[
            'old_password' => 'required',
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' =>false,
                'errors' => $validator->errors(),

            ]);
        }

        if(Hash::check($request->old_password,Auth::user()->password) == false){
            session()->flash('error','Your old password is incorrect.');
            return response()->json([
                'status' =>true,
            ]);
        }

        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        session()->flash('success','Password updated successfully.');
        return response()->json([
            'status' =>true,
        ]);


    }
}
