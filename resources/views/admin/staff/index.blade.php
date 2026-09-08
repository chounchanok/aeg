@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการพนักงานและช่างซ่อม - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายชื่อพนักงานและช่างซ่อม (Staff & Technicians)</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif

    {{-- 🌟 เตือนเมื่อสิทธิ์ในฐานข้อมูลยังไม่ตรงกับที่กำหนดในโค้ด (RolePermissionSeeder) เช่น เพิ่งอัปเดตระบบแล้วยังไม่ได้ซิงค์
         → เมนู/สิทธิ์ใหม่จะไม่ขึ้นให้บางแผนก จนกว่าจะกดซิงค์ --}}
    @if(!empty($rbacDiff))
        <div class="alert alert-warning show mt-5" role="alert">
            <div class="flex items-start">
                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2 flex-shrink-0"></i>
                <div class="flex-1">
                    <div class="font-medium">สิทธิ์ของแผนกในฐานข้อมูลยังไม่ตรงกับเวอร์ชันล่าสุดของระบบ — บางแผนกอาจไม่เห็นเมนูใหม่</div>
                    <ul class="list-disc ml-5 mt-1 text-xs">
                        @foreach($rbacDiff as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
                <form action="{{ route('admin.staff.sync-permissions') }}" method="POST" class="ml-3 flex-shrink-0" onsubmit="return confirm('ซิงค์แผนกและสิทธิ์ให้ตรงกับระบบเวอร์ชันล่าสุด? (ไม่กระทบแผนกที่กำหนดให้พนักงานแต่ละคนไว้)');">
                    @csrf
                    <button type="submit" class="btn btn-warning whitespace-nowrap"><i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i> ซิงค์สิทธิ์ตอนนี้</button>
                </form>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-staff-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มพนักงาน/ช่างใหม่
            </button>
            <form action="{{ route('admin.staff.sync-permissions') }}" method="POST" class="ml-auto" onsubmit="return confirm('ซิงค์แผนกและสิทธิ์ให้ตรงกับระบบเวอร์ชันล่าสุด? (ไม่กระทบแผนกที่กำหนดให้พนักงานแต่ละคนไว้)');">
                @csrf
                <button type="submit" class="btn btn-outline-secondary" title="เขียนแผนก/สิทธิ์ตาม RolePermissionSeeder ลงฐานข้อมูล (รันซ้ำได้)">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i> ซิงค์สิทธิ์แผนก
                </button>
            </form>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">ชื่อ-นามสกุล</th>
                        <th class="whitespace-nowrap">Username (ใช้ Login)</th>
                        <th class="text-center whitespace-nowrap">ตำแหน่ง (Role)</th>
                        <th class="whitespace-nowrap">แผนก / สิทธิ์การใช้งาน (RBAC)</th>
                        <th class="text-center whitespace-nowrap">เบอร์โทรติดต่อ</th>
                        <th class="text-center whitespace-nowrap">สถานะ</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffs as $staff)
                        <tr class="intro-x">
                            <td class="font-medium whitespace-nowrap">{{ $staff->name ?? '-' }}</td>
                            <td>{{ $staff->username }}</td>
                            <td class="text-center">
                                @if($staff->role == 'technician')
                                    <span class="px-2 py-1 rounded-full bg-warning/20 text-warning font-medium">ช่างซ่อม</span>
                                @elseif($staff->role == 'super_admin')
                                    <span class="px-2 py-1 rounded-full bg-danger/20 text-danger font-medium">Super Admin</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-primary/20 text-primary font-medium">Admin</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $staff->department_names ?? '-' }}</td>
                            <td class="text-center">{{ $staff->phone ?? '-' }}</td>
                            <td class="text-center">
                                @if($staff->is_active)
                                    <span class="text-success"><i data-lucide="check-circle" class="w-4 h-4 mr-1 inline"></i> ใช้งานปกติ</span>
                                @else
                                    <span class="text-danger"><i data-lucide="x-circle" class="w-4 h-4 mr-1 inline"></i> ปิดใช้งาน</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-edit-staff"
                                    data-id="{{ $staff->id }}"
                                    data-tw-toggle="modal" data-tw-target="#edit-staff-modal">
                                    <i data-lucide="edit" class="w-4 h-4"></i> แก้ไข
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 🌟 ตารางสรุป "แผนกไหนทำอะไรได้บ้าง" อ่านจากฐานข้อมูลจริง (ใช้ตรวจสอบว่าสิทธิ์ที่ตั้งไว้ถูกต้องไหม) --}}
        <div class="intro-y col-span-12 box p-5">
            <div class="flex items-center border-b border-slate-200/60 pb-4 mb-4">
                <i data-lucide="shield-check" class="w-5 h-5 mr-2 text-primary"></i>
                <div class="font-medium text-base mr-auto">แผนกและสิทธิ์การใช้งาน (RBAC) — ตามข้อมูลในฐานข้อมูลปัจจุบัน</div>
                <span class="text-xs text-slate-500">{{ $roles->count() }} แผนก</span>
            </div>
            <div class="grid grid-cols-12 gap-4">
                @foreach($roles as $role)
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 border border-slate-200/60 rounded-md p-4">
                        <div class="flex items-center">
                            <div class="font-medium mr-auto">{{ $role->name }}</div>
                            <code class="text-xs text-slate-400">{{ $role->key }}</code>
                        </div>
                        @if($role->description)
                            <div class="text-slate-500 text-xs mt-1">{{ $role->description }}</div>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-1">
                            @if($role->is_full_access)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-success/20 text-success font-medium">เข้าถึงได้ทุกเมนู (full access)</span>
                            @endif
                            @forelse($role->permissions as $perm)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600" title="{{ $perm->key }}">{{ $perm->name }}</span>
                            @empty
                                @if(!$role->is_full_access)
                                    <span class="text-xs text-danger">ยังไม่มีสิทธิ์ใดๆ — กด "ซิงค์สิทธิ์แผนก" ด้านบน</span>
                                @endif
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div id="add-staff-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            {{-- ใช้ <form autocomplete="off"> ครอบ modal เพื่อจำกัดขอบเขต autofill ของ Chrome ให้อยู่แค่ในฟอร์มนี้
                 (ถ้าไม่มี <form> Chrome จะมองทั้งหน้าเป็นฟอร์มเดียว แล้วเอา username ที่จำไว้ไปเติมในช่อง Search ของหน้า) --}}
            <form class="modal-content" autocomplete="off" onsubmit="return false;">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">เพิ่มบัญชีพนักงานใหม่</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input id="staff-name" name="staff_name" type="text" class="form-control" placeholder="เช่น นายสมชาย ช่างแอร์" autocomplete="off">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input id="staff-phone" name="staff_phone" type="text" class="form-control" placeholder="089xxxxxxx" autocomplete="off">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Username (ใช้เข้าสู่ระบบ)</label>
                        <input id="staff-username" name="staff_username" type="text" class="form-control" placeholder="tech_somchai" autocomplete="off">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">รหัสผ่านชั่วคราว</label>
                        {{-- autocomplete="new-password" = บอก Chrome ว่านี่คือช่องตั้งรหัสใหม่ ไม่ใช่ช่อง login → ไม่ดึงรหัส/username ที่จำไว้มาเติม --}}
                        <input id="staff-password" name="staff_password" type="password" class="form-control" placeholder="******" autocomplete="new-password">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ตำแหน่ง</label>
                        <select id="staff-role" class="form-select">
                            <option value="technician">ช่างซ่อม (Technician)</option>
                            <option value="admin">แอดมินส่วนกลาง (Admin)</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">แผนก / สิทธิ์การใช้งาน (เลือกได้มากกว่า 1 แผนก — เว้นว่างได้ถ้าเป็นช่างซ่อม)</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($roles as $role)
                                <div class="form-check">
                                    <input id="staff-role-id-{{ $role->id }}" class="form-check-input staff-role-checkbox" type="checkbox" value="{{ $role->id }}">
                                    <label class="form-check-label" for="staff-role-id-{{ $role->id }}">{{ $role->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="button" id="btn-save-staff" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <div id="edit-staff-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" autocomplete="off" onsubmit="return false;">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">แก้ไขข้อมูลพนักงาน</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <input type="hidden" id="edit-staff-id">
                    <div class="col-span-12">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input id="edit-staff-name" name="edit_staff_name" type="text" class="form-control" autocomplete="off">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input id="edit-staff-phone" name="edit_staff_phone" type="text" class="form-control" autocomplete="off">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ตำแหน่ง</label>
                        <select id="edit-staff-role" class="form-select">
                            <option value="technician">ช่างซ่อม (Technician)</option>
                            <option value="admin">แอดมินส่วนกลาง (Admin)</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)</label>
                        {{-- autocomplete="new-password" = บอก Chrome ว่านี่คือช่องตั้งรหัสใหม่ ไม่ใช่ช่อง login → ไม่ดึงรหัส/username ที่จำไว้มาเติม --}}
                        <input id="edit-staff-password" name="edit_staff_password" type="password" class="form-control" placeholder="******" autocomplete="new-password">
                    </div>
                    <div class="col-span-12">
                        <div class="form-check form-switch">
                            <input id="edit-staff-is-active" class="form-check-input" type="checkbox">
                            <label class="form-check-label" for="edit-staff-is-active">เปิดใช้งานบัญชีนี้</label>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">แผนก / สิทธิ์การใช้งาน</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($roles as $role)
                                <div class="form-check">
                                    <input id="edit-staff-role-id-{{ $role->id }}" class="form-check-input edit-staff-role-checkbox" type="checkbox" value="{{ $role->id }}">
                                    <label class="form-check-label" for="edit-staff-role-id-{{ $role->id }}">{{ $role->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="button" id="btn-update-staff" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    @endsection

@section('script')
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $(document).ready(function(){
        $('.datatable').DataTable({
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" },
            "pageLength": 10,
            // ปิด autocomplete ของช่องค้นหา DataTable กัน Chrome เอา username ที่จำไว้ (เบอร์โทรที่ใช้ login) มาเติมเอง
            "initComplete": function() {
                $('.dataTables_filter input').attr('autocomplete', 'off');
            }
        });

        $('#btn-save-staff').click(function() {
            let btn = $(this);
            btn.prop('disabled', true).html('กำลังบันทึก...');

            let roleIds = $('.staff-role-checkbox:checked').map(function() { return $(this).val(); }).get();

            axios.post(`{{ route('admin.staff.store') }}`, {
                name: $('#staff-name').val(),
                phone: $('#staff-phone').val(),
                username: $('#staff-username').val(),
                password: $('#staff-password').val(),
                role: $('#staff-role').val(),
                role_ids: roleIds,
            }).then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: res.data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }).catch(err => {
                let errorMsg = err.response.data.message || 'เกิดข้อผิดพลาดในการบันทึก';
                Swal.fire('ข้อผิดพลาด', errorMsg, 'error');
                btn.prop('disabled', false).html('บันทึก');
            });
        });

        // เปิด modal แก้ไข — ดึงข้อมูลพนักงานคนนั้นมาเติมในฟอร์ม
        $(document).on('click', '.btn-edit-staff', function() {
            let id = $(this).data('id');

            axios.get(`/admin/staff/${id}/edit`).then(res => {
                let staff = res.data.staff;
                let roleIds = res.data.role_ids || [];

                $('#edit-staff-id').val(staff.id);
                $('#edit-staff-name').val(staff.name);
                $('#edit-staff-phone').val(staff.phone);
                $('#edit-staff-role').val(staff.role);
                $('#edit-staff-password').val('');
                $('#edit-staff-is-active').prop('checked', staff.is_active == 1);

                $('.edit-staff-role-checkbox').prop('checked', false);
                roleIds.forEach(function(rid) {
                    $('#edit-staff-role-id-' + rid).prop('checked', true);
                });
                // 🌟 ไม่ต้องสั่งเปิด modal เองใน JS — ปุ่มมี data-tw-toggle/data-tw-target อยู่แล้ว
                // ทำให้ theme (Tailwick) เปิด modal ให้อัตโนมัติตอนคลิก โค้ดนี้แค่เติมข้อมูลลงฟอร์มก่อนเปิด
            }).catch(() => {
                Swal.fire('ข้อผิดพลาด', 'ไม่พบข้อมูลพนักงานคนนี้', 'error');
            });
        });

        $('#btn-update-staff').click(function() {
            let btn = $(this);
            btn.prop('disabled', true).html('กำลังบันทึก...');

            let id = $('#edit-staff-id').val();
            let roleIds = $('.edit-staff-role-checkbox:checked').map(function() { return $(this).val(); }).get();

            axios.post(`/admin/staff/${id}/update`, {
                name: $('#edit-staff-name').val(),
                phone: $('#edit-staff-phone').val(),
                role: $('#edit-staff-role').val(),
                password: $('#edit-staff-password').val(),
                is_active: $('#edit-staff-is-active').is(':checked') ? 1 : 0,
                role_ids: roleIds,
            }).then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: res.data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }).catch(err => {
                let errorMsg = (err.response && err.response.data && err.response.data.message) || 'เกิดข้อผิดพลาดในการบันทึก';
                Swal.fire('ข้อผิดพลาด', errorMsg, 'error');
                btn.prop('disabled', false).html('บันทึก');
            });
        });
    });
</script>
@endsection
