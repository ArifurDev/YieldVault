<div class="container-xxl">
    <div class="card">

        <div class="card-body py-4">

            <form id="update-task-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="d-flex flex-column scroll-y px-5 px-lg-10">

                    <div class="fv-row mb-6">
                        <label class="required form-label">Title</label>
                        <input type="text" name="title" value="{{ $task->title }}" class="form-control form-control-solid">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="required form-label">Task Type</label>
                        <select name="type" id="task_type" class="form-control form-control-solid">
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->value }}" @selected($task->type->value === $type->value)>
                                    {{ ucfirst(str_replace('_',' ', $type->value)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fv-row mb-6" id="duration_wrapper" style="{{ $task->type->value === 'watch_ad' ? '' : 'display:none;' }}">
                        <label class="required form-label">Duration (Seconds)</label>
                        <input type="number" name="duration" value="{{ $task->duration }}" class="form-control form-control-solid">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="required form-label">XP Reward</label>
                        <input type="number" name="xp" value="{{ $task->xp }}" class="form-control form-control-solid">
                    </div>

                    <div class="fv-row mb-6">
                        <label class="required form-label">Media Type</label>
                        <select name="media_type" id="media_type" class="form-control form-control-solid">
                            @foreach($mediaTypes as $media)
                                <option value="{{ $media->value }}" @selected($task->media_type->value === $media->value)>
                                    {{ ucfirst($media->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>



                    <div class="mb-5" id="media_file_wrapper"  style="{{ in_array($task->media_type->value, ['video','image']) ? '' : 'display:none;' }}">
                        <label class="form-label required">Media</label>

                        <div class="fv-row mb-2">
                            <!-- keep same id so existing CSS/JS works -->
                            <div class="dropzone dz-clickable craft-dz" id="kt_edit_media_dropzone">
                                <div class="dz-message needsclick d-flex align-items-center gap-3">
                                    <!-- Icon (Keenthemes icon ok) -->
                                    <i class="ki-duotone ki-file-up text-primary fs-3x">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>

                                    <div class="ms-2">
                                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">Drop files here or click to upload.</h3>
                                        <span class="fs-7 fw-semibold text-gray-500">Upload only 1 image or video</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-muted fs-7">Set the product media.</div>
{{--                        @if($task->media_path)--}}
{{--                            <div class="mt-3">--}}
{{--                                <strong>Existing Media:</strong><br>--}}
{{--                                @if($task->media_type->value === 'image')--}}
{{--                                    <img src="{{ $task->media_path }}" width="120">--}}
{{--                                @elseif($task->media_type->value === 'video')--}}
{{--                                    <video src="{{ $task->media_path }}" width="200" controls></video>--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        @endif--}}
                        @if($task->media_path)
                            <div class="mt-3">
                                <strong>Existing Media:</strong><br>

                                <div class="existing-media-preview" style="cursor:pointer; display:inline-block;"
                                     data-type="{{ $task->media_type->value }}"
                                     data-src="{{ $task->media_path }}">

                                    @if($task->media_type->value === 'image')
                                        <img src="{{ $task->media_path }}" width="120">
                                    @elseif($task->media_type->value === 'video')
                                        <video src="{{ $task->media_path }}" width="200"></video>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>




                    <div class="fv-row mb-6" id="media_url_wrapper" style="{{ $task->media_type->value === 'url' ? '' : 'display:none;' }}">
                        <label class="form-label">Media URL</label>
                        <input type="text" name="media_url" value="{{ $task->media_path }}" class="form-control form-control-solid">
                    </div>

                </div>

                <div class="text-center pt-10">
                    <button type="submit" class="btn btn-primary">
                        Update Task
                    </button>
                </div>

            </form>
        </div>
    </div>


    <!-- Fullscreen Preview Modal -->
    <div id="mediaPreviewModal"
         style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.8); z-index:99999; align-items:center; justify-content:center;">

    <span id="closePreview"
          style="position:absolute; top:20px; right:30px; font-size:40px; color:white; cursor:pointer;">
        &times;
    </span>

        <div id="previewContent"></div>
    </div>

</div>



