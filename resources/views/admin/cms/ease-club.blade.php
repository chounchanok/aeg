@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการของรางวัล EASE CLUB - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ระบบสิทธิประโยชน์ EASE CLUB (Rewards)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-reward-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มของรางวัลใหม่
            </button>
        </div>
        
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="w-16">รูปภาพ</th>
                        <th>ชื่อของรางวัล</th>
                        <th>หมวดหมู่</th>
                        <th class="text-center">แต้มที่ใช้</th>
                        <th class="text-center">สต๊อก</th>
                        <th class="text-center">สิทธิ์การแลก (Tier)</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rewards as $rw)
                        <tr class="intro-x">
                            <td>
                                <div class="w-10 h-10 image-fit zoom-in">
                                    <img alt="Reward" class="rounded-md border" src="{{ $rw->image_url ?? asset('dist/images/preview-1.jpg') }}">
                                </div>
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $rw->title_th }}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $rw->title_en ?? '-' }}</div>
                            </td>
                            <td><span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded">{{ $rw->category_name }}</span></td>
                            <td class="text-center text-primary font-medium">{{ number_format($rw->points_required) }} Pt</td>
                            <td class="text-center">
                                <span class="{{ $rw->stock_quantity <= 5 ? 'text-danger font-medium' : '' }}">{{ $rw->stock_quantity }}</span>
                            </td>
                            <td class="text-center">
                                @if($rw->minimum_tier_required)
                                    <span class="text-warning font-medium">{{ $rw->minimum_tier_required }} ขึ้นไป</span>
                                @else
                                    <span class="text-success">แลกได้ทุกคน</span>
                                @endif
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <button type="button" class="btn-edit-reward flex items-center mr-3 text-primary" data-tw-toggle="modal" data-tw-target="#edit-reward-modal" data-reward="{{ json_encode($rw) }}">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </button>
                                    <form action="{{ route('admin.cms.ease-club.rewards.delete', $rw->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบของรางวัลนี้?');">
                                        @csrf
                                        <button type="submit" class="flex items-center text-danger">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="add-reward-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.cms.ease-club.rewards.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่มของรางวัลใหม่</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อของรางวัล (TH) <span class="text-danger">*</span></label>
                        <input name="title_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อของรางวัล (EN)</label>
                        <input name="title_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ไฟล์รูปภาพ (ถ้ามี)</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">แต้มที่ใช้แลก (Points) <span class="text-danger">*</span></label>
                        <input name="points_required" type="number" class="form-control" required min="0">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">จำนวนสต๊อก (ชิ้น)</label>
                        <input name="stock_quantity" type="number" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ระดับขั้นต่ำ (Tier)</label>
                        <select name="minimum_tier_required" class="form-select">
                            <option value="">แลกได้ทุกคน</option>
                            <option value="Advance">Advance ขึ้นไป</option>
                            <option value="Platinum">Platinum ขึ้นไป</option>
                            <option value="Beyond">Beyond เท่านั้น</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">รายละเอียด (TH)</label>
                        <textarea name="description_th" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">รายละเอียด (EN)</label>
                        <textarea name="description_en" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-reward-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="edit-reward-form" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไขของรางวัล</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อของรางวัล (TH) <span class="text-danger">*</span></label>
                        <input name="title_th" id="edit_title_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อของรางวัล (EN)</label>
                        <input name="title_en" id="edit_title_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                        <select name="category_id" id="edit_category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">เปลี่ยนไฟล์รูปภาพ (เว้นว่างได้)</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">แต้มที่ใช้แลก (Points) <span class="text-danger">*</span></label>
                        <input name="points_required" id="edit_points_required" type="number" class="form-control" required min="0">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">จำนวนสต๊อก (ชิ้น)</label>
                        <input name="stock_quantity" id="edit_stock_quantity" type="number" class="form-control" min="0">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ระดับขั้นต่ำ (Tier)</label>
                        <select name="minimum_tier_required" id="edit_minimum_tier_required" class="form-select">
                            <option value="">แลกได้ทุกคน</option>
                            <option value="Advance">Advance ขึ้นไป</option>
                            <option value="Platinum">Platinum ขึ้นไป</option>
                            <option value="Beyond">Beyond เท่านั้น</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">รายละเอียด (TH)</label>
                        <textarea name="description_th" id="edit_description_th" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">รายละเอียด (EN)</label>
                        <textarea name="description_en" id="edit_description_en" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    @endsection

@section('script')
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" }
        });

        // ดึงข้อมูลเข้าฟอร์มตอนกดแก้ไข
        $('.btn-edit-reward').on('click', function() {
            let reward = $(this).data('reward');
            
            $('#edit-reward-form').attr('action', `{{ url('/admin/cms/ease-club/rewards') }}/${reward.id}/update`);
            
            $('#edit_title_th').val(reward.title_th);
            $('#edit_title_en').val(reward.title_en);
            $('#edit_category_id').val(reward.category_id);
            $('#edit_points_required').val(reward.points_required);
            $('#edit_stock_quantity').val(reward.stock_quantity);
            $('#edit_minimum_tier_required').val(reward.minimum_tier_required || '');
            $('#edit_description_th').val(reward.description_th);
            $('#edit_description_en').val(reward.description_en);
        });
    });
</script>
@endsection