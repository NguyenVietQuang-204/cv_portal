@extends('front.layouts.app')

@section('main')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Cài đặt tài khoản</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('front.account.sidebar')
            </div>
            <div class="col-lg-9">
                @include('front.message')
                <div class="card border-0 shadow mb-4 p-3">
                    <div class="card-body card-form">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="fs-4 mb-1">Công việc của tôi</h3>
                            </div>
                            <div style="margin-top: -10px;">
                                <a href="{{ route('account.createJob') }}" class="btn btn-primary">Đăng việc làm</a>
                            </div>
                            
                        </div>
                        <div class="table-responsive">
                            <table class="table ">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">Tiêu đề</th>
                                        <th scope="col">Đã tạo công việc</th>
                                        <th scope="col">Ứng viên</th>
                                        <th scope="col">Trạng thái</th>
                                        <th scope="col">Hoạt động</th>
                                    </tr>
                                </thead>
                                <tbody class="border-0">
                                    @if ($job->isNotEmpty())
                                        @foreach ($job as $jobs)
                                        <tr class="active">
                                            <td>
                                                <div class="job-name fw-500">{{ $jobs->title }}</div>
                                                <div class="info1">{{ $jobs->jobType->name }} . {{ $jobs->location }}</div>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($jobs->created_at)->format('d M, Y') }}</td>
                                            <td>0 Ứng tuyển</td>
                                            <td>
                                                @if ($jobs->status == 1)
                                                    <div class="job-status text-capitalize">Active</div>
                                                @else
                                                    <div class="job-status text-capitalize">Block</div>
                                                @endif
                                                

                                            </td>
                                            <td>
                                                <div class="action-dots float-end">
                                                    <button href="#" class="btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="job-detail.html"> <i class="fa fa-eye" aria-hidden="true"></i> View</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('account.editJob', $jobs->id)}}"><i class="fa fa-edit" aria-hidden="true"></i> Edit</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="deleteJob({{ $jobs->id }})"><i class="fa fa-trash" aria-hidden="true"></i>Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif                                              
                                </tbody>
                            </table>
                        </div> 
                        <div>
                            {{ $job->links() }}
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</section>
@endsection
@section('customJs')
<script type="text/javascript">
    function deleteJob(jobId) {
        if(confirm("Bạn có chắc chắn muốn xóa?")){
            $.ajax({
                url: '{{ route('account.deleteJob') }}',
                type: 'post',
                data: {jobId: jobId},
                dataType: 'json',
                success: function(response) {
                    window.location.href='{{ route('account.myJobs') }}'
                }
            });
        } else{

        }   
    }
</script>
@endsection