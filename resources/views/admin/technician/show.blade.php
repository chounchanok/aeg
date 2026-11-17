@extends('../layout/' . $layout)

@php
    $info = $job['job_info'];
    $cust = $job['customer_info'];
    $stepIndex = array_search($status, $flow, true);
@endphp

@section('subhead')
    <title>งาน {{ $info['ticket_number'] }} - AEG Admin</title>
    <style>

        /* CSS เฉพาะหน้าช่าง (prefix tj- — ห้ามใช้ ad- เพราะตัวบล็อกโฆษณาจะซ่อน) */
        .tj-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
        .tj-card { background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 3px 15px #0000000b; }
        .tj-card .lbl { font-size: 12px; color: #64748b; }
        .tj-card .val { font-size: 24px; font-weight: 600; color: #1e3a8a; line-height: 1.3; }
        .tj-badge { display: inline-block; font-size: 12px; padding: 3px 10px; border-radius: 999px; font-weight: 500; white-space: nowrap; }
        .tj-s-assigned { background: #fef3c7; color: #92400e; }
        .tj-s-accepted, .tj-s-traveling, .tj-s-arrived { background: #dbeafe; color: #1e40af; }
        .tj-s-in_progress { background: #e0e7ff; color: #3730a3; }
        .tj-s-completed { background: #dcfce7; color: #166534; }
        .tj-s-cancelled, .tj-s-pending { background: #f1f5f9; color: #475569; }
        .tj-alert { padding: 12px 16px; border-radius: 8px; margin-top: 20px; }
        .tj-alert.ok { background: #dcfce7; color: #166534; }
        .tj-alert.err { background: #fee2e2; color: #991b1b; }

        .tj-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }
        @media (max-width: 1024px) { .tj-grid { grid-template-columns: 1fr; } }
        .tj-box { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 3px 15px #0000000b; }
        .tj-box h3 { font-weight: 600; font-size: 15px; margin: 0 0 12px; color: #0f172a; }
        .tj-kv { display: grid; grid-template-columns: 140px 1fr; gap: 6px 12px; font-size: 14px; }
        .tj-kv .k { color: #64748b; }
        .tj-steps { display: flex; gap: 4px; overflow-x: auto; padding-bottom: 4px; }
        .tj-step { flex: 1; min-width: 92px; text-align: center; font-size: 12px; color: #94a3b8; }
        .tj-step .dot { width: 28px; height: 28px; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; background: #e2e8f0; color: #64748b; font-weight: 600; }
        .tj-step.done .dot { background: #1e3a8a; color: #fff; }
        .tj-step.done { color: #1e3a8a; }
        .tj-step.current .dot { background: #f59e0b; color: #fff; }
        .tj-step.current { color: #b45309; font-weight: 600; }
        .tj-timeline { list-style: none; margin: 0; padding: 0; }
        .tj-timeline li { position: relative; padding: 0 0 14px 22px; font-size: 13px; }
        .tj-timeline li::before { content: ''; position: absolute; left: 5px; top: 5px; width: 9px; height: 9px; border-radius: 50%; background: #1e3a8a; }
        .tj-timeline li::after { content: ''; position: absolute; left: 9px; top: 16px; bottom: 0; width: 1px; background: #cbd5e1; }
        .tj-timeline li:last-child::after { display: none; }
        .tj-timeline .t { color: #94a3b8; font-size: 12px; }
        .tj-imgs { display: flex; flex-wrap: wrap; gap: 8px; }
        .tj-imgs a img, .tj-imgs video { width: 110px; height: 110px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
        .tj-big-btn { width: 100%; padding: 14px; font-size: 16px; }
        .tj-sig-wrap { border: 2px dashed #cbd5e1; border-radius: 10px; background: #fff; touch-action: none; }
        .tj-sig-wrap canvas { width: 100%; height: 200px; display: block; cursor: crosshair; }
        .tj-stars { display: flex; gap: 6px; flex-wrap: wrap; }
        .tj-stars label { cursor: pointer; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 8px; user-select: none; }
        .tj-stars input { display: none; }
        .tj-stars input:checked + span { color: #b45309; font-weight: 600; }
        .tj-stars label:has(input:checked) { border-color: #f59e0b; background: #fffbeb; }
        .tj-field { margin-bottom: 14px; }
        .tj-req { color: #dc2626; }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8" style="gap:10px; flex-wrap:wrap;">
        <h2 class="text-lg font-medium mr-auto">งาน {{ $info['ticket_number'] ?? ('#' . $info['id']) }}</h2>
        <span class="tj-badge tj-s-{{ $status }}">{{ $labels[$status] ?? $status }}</span>
        <a href="{{ route('admin.tech.jobs') }}" class="btn btn-outline-secondary">กลับ</a>
    </div>

    @if(session('success'))<div class="tj-alert ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="tj-alert err">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="tj-alert err"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- ขั้นตอนงาน --}}
    @if($stepIndex !== false)
        <div class="tj-box mt-5">
            <div class="tj-steps">
                @foreach($flow as $i => $s)
                    <div class="tj-step {{ $i < $stepIndex || $status === 'completed' ? 'done' : ($i === $stepIndex ? 'current' : '') }}">
                        <div class="dot">{{ $i < $stepIndex || $status === 'completed' ? '✓' : $i + 1 }}</div>
                        {{ ['assigned' => 'รับมอบหมาย', 'accepted' => 'รับงาน', 'traveling' => 'เดินทาง', 'arrived' => 'ถึงหน้างาน', 'in_progress' => 'กำลังซ่อม', 'completed' => 'ปิดงาน'][$s] }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="tj-grid mt-5">
        <div>
            {{-- ปุ่มขั้นถัดไป --}}
            @if($next)
                <div class="tj-box mb-4">
                    <form action="{{ route('admin.tech.status', $info['id']) }}" method="POST"
                          onsubmit="return confirm('ยืนยัน: {{ $next['label'] }}?');">
                        @csrf
                        <input type="hidden" name="status" value="{{ $next['status'] }}">
                        <button type="submit" class="btn btn-primary tj-big-btn">{{ $next['label'] }}</button>
                    </form>
                </div>
            @endif

            {{-- ฟอร์มปิดงาน (หลังเริ่มซ่อม) --}}
            @if($status === 'in_progress')
                <div class="tj-box mb-4">
                    <h3>ปิดงาน / ส่งงาน</h3>
                    <form id="completeForm" action="{{ route('admin.tech.complete', $info['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="tj-field">
                            <label class="form-label">รูป/วิดีโอ ก่อนซ่อม</label>
                            <input type="file" name="before_media[]" multiple accept="image/jpeg,image/png,video/mp4,video/quicktime" class="form-control">
                        </div>
                        <div class="tj-field">
                            <label class="form-label">รูป/วิดีโอ หลังซ่อม</label>
                            <input type="file" name="after_media[]" multiple accept="image/jpeg,image/png,video/mp4,video/quicktime" class="form-control">
                            <div class="text-xs text-slate-500 mt-1">jpg, png, mp4, mov ไม่เกินไฟล์ละ 20MB</div>
                        </div>
                        <div class="tj-field">
                            <label class="form-label">บันทึกจากช่าง</label>
                            <textarea name="technician_note" rows="3" class="form-control" placeholder="สิ่งที่ตรวจพบ / สิ่งที่แก้ไข">{{ old('technician_note') }}</textarea>
                        </div>
                        <div class="tj-field">
                            <label class="form-label">คะแนนความพึงพอใจของลูกค้า <span class="tj-req">*</span></label>
                            <div class="tj-stars">
                                @for($i = 5; $i >= 0; $i--)
                                    <label><input type="radio" name="rating" value="{{ $i }}" {{ (string) old('rating') === (string) $i ? 'checked' : '' }}><span>{{ $i > 0 ? str_repeat('★', $i) : '0' }}</span></label>
                                @endfor
                            </div>
                        </div>
                        <div class="tj-field">
                            <label class="form-label">ลายเซ็นลูกค้า <span class="tj-req">*</span></label>
                            <div class="tj-sig-wrap"><canvas id="sigPad"></canvas></div>
                            <div style="display:flex; justify-content:space-between; margin-top:6px;">
                                <span class="text-xs text-slate-500">ให้ลูกค้าเซ็นด้วยนิ้ว/เมาส์ในกรอบ</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sigClear()">ล้างลายเซ็น</button>
                            </div>
                            <input type="hidden" name="customer_signature_data" id="sigData">
                        </div>
                        <div class="tj-field">
                            <label class="form-label">ตำแหน่งปัจจุบัน <span class="tj-req">*</span></label>
                            <div id="geoStatus" class="text-sm text-slate-500">กำลังขอตำแหน่ง…</div>
                            <input type="hidden" name="latitude" id="lat">
                            <input type="hidden" name="longitude" id="lng">
                        </div>
                        <button type="submit" class="btn btn-primary tj-big-btn">ยืนยันปิดงาน</button>
                    </form>
                </div>
            @endif

            {{-- ผลการปิดงาน --}}
            @if($completion)
                <div class="tj-box mb-4">
                    <h3>ผลการปิดงาน</h3>
                    <div class="tj-kv">
                        <div class="k">คะแนนลูกค้า</div><div>{{ str_repeat('★', (int) $completion->rating) ?: '0' }} ({{ (int) $completion->rating }}/5)</div>
                        <div class="k">บันทึกจากช่าง</div><div>{{ $completion->technician_note ?: '-' }}</div>
                        <div class="k">ตำแหน่งตอนปิดงาน</div><div>
                            @if($completion->latitude)<a href="https://www.google.com/maps?q={{ $completion->latitude }},{{ $completion->longitude }}" target="_blank" rel="noopener">{{ $completion->latitude }}, {{ $completion->longitude }}</a>@else - @endif
                        </div>
                    </div>
                    @foreach(['before_images' => 'ก่อนซ่อม', 'after_images' => 'หลังซ่อม'] as $key => $title)
                        @if(!empty($completion->$key))
                            <div class="mt-3 text-sm font-medium">{{ $title }}</div>
                            <div class="tj-imgs mt-1">
                                @foreach($completion->$key as $url)
                                    @if(preg_match('/\.(mp4|mov)$/i', $url))<video src="{{ $url }}" controls></video>
                                    @else<a href="{{ $url }}" target="_blank"><img src="{{ $url }}" alt=""></a>@endif
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                    @if($completion->customer_signature_path)
                        <div class="mt-3 text-sm font-medium">ลายเซ็นลูกค้า</div>
                        <img src="{{ $completion->customer_signature_path }}" alt="signature" style="max-width:280px; border:1px solid #e2e8f0; border-radius:8px; margin-top:4px;">
                    @endif
                </div>
            @endif

            <div class="tj-box mb-4">
                <h3>รายละเอียดงาน</h3>
                <div class="tj-kv">
                    <div class="k">วันนัดหมาย</div><div>{{ $info['preferred_date'] ? \Carbon\Carbon::parse($info['preferred_date'])->format('d/m/Y') : '-' }} {{ $info['time_slot'] ? '· ' . $info['time_slot'] : '' }}</div>
                    <div class="k">อาการ / ปัญหา</div><div style="white-space:pre-line">{{ $info['problem_description'] ?: '-' }}</div>
                    <div class="k">โควต้าบริการคงเหลือ</div><div>{{ $info['remaining_services'] }} ครั้ง</div>
                    <div class="k">แจ้งเมื่อ</div><div>{{ \Carbon\Carbon::parse($info['created_at'])->format('d/m/Y H:i') }}</div>
                </div>
                @if(count($job['customer_images']))
                    <div class="mt-3 text-sm font-medium">รูปจากลูกค้า</div>
                    <div class="tj-imgs mt-1">
                        @foreach($job['customer_images'] as $img)
                            @php $src = $img->image_url ?? $img->image_path ?? $img->url ?? null; @endphp
                            @if($src)<a href="{{ $src }}" target="_blank"><img src="{{ $src }}" alt=""></a>@endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div>
            <div class="tj-box mb-4">
                <h3>ลูกค้า</h3>
                <div class="tj-kv" style="grid-template-columns: 90px 1fr;">
                    <div class="k">ชื่อ</div><div>{{ $cust['name'] }}</div>
                    <div class="k">โทร</div><div>@if($cust['phone'])<a href="tel:{{ $cust['phone'] }}">{{ $cust['phone'] }}</a>@else - @endif</div>
                    <div class="k">อุปกรณ์</div><div>{{ $cust['product_name'] }}</div>
                    <div class="k">S/N</div><div>{{ $cust['serial_number'] }}</div>
                    <div class="k">ที่อยู่</div><div>{{ $cust['full_address'] ?: '-' }}</div>
                </div>
                @if($cust['latitude'] && $cust['longitude'])
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $cust['latitude'] }},{{ $cust['longitude'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-full mt-3">นำทางด้วย Google Maps</a>
                @endif
            </div>

            <div class="tj-box">
                <h3>ไทม์ไลน์</h3>
                @if(count($job['tracking_logs']))
                    <ul class="tj-timeline">
                        @foreach($job['tracking_logs'] as $log)
                            <li><div>{{ $log->status }}</div>
                                @if($log->description && $log->description !== $log->status)<div class="text-slate-500">{{ $log->description }}</div>@endif
                                <div class="t">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</div></li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-slate-500 text-sm">ยังไม่มีบันทึก</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if($status === 'in_progress')
    <script>
        // ===== ลายเซ็นลูกค้า (เมาส์ / นิ้ว / ปากกา) =====
        const canvas = document.getElementById('sigPad');
        const ctx = canvas.getContext('2d');
        let drawing = false, signed = false;
        function sigResize() {
            const r = canvas.getBoundingClientRect(), ratio = window.devicePixelRatio || 1;
            canvas.width = r.width * ratio; canvas.height = r.height * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, r.width, r.height);
            ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#0f172a';
            signed = false;
        }
        function pos(e) { const r = canvas.getBoundingClientRect(); return [e.clientX - r.left, e.clientY - r.top]; }
        canvas.addEventListener('pointerdown', e => { drawing = true; canvas.setPointerCapture(e.pointerId); ctx.beginPath(); ctx.moveTo(...pos(e)); });
        canvas.addEventListener('pointermove', e => { if (!drawing) return; ctx.lineTo(...pos(e)); ctx.stroke(); signed = true; });
        ['pointerup', 'pointercancel', 'pointerleave'].forEach(ev => canvas.addEventListener(ev, () => drawing = false));
        function sigClear() { sigResize(); }
        window.addEventListener('load', sigResize);
        sigResize();

        // ===== ตำแหน่งปัจจุบัน (แอปบังคับส่งพิกัดตอนปิดงาน) =====
        const geo = document.getElementById('geoStatus');
        function getGeo() {
            if (!navigator.geolocation) { geo.textContent = 'เบราว์เซอร์นี้ไม่รองรับการระบุตำแหน่ง'; return; }
            navigator.geolocation.getCurrentPosition(p => {
                document.getElementById('lat').value = p.coords.latitude.toFixed(7);
                document.getElementById('lng').value = p.coords.longitude.toFixed(7);
                geo.innerHTML = '✅ ' + p.coords.latitude.toFixed(5) + ', ' + p.coords.longitude.toFixed(5) + ' (แม่นยำ ±' + Math.round(p.coords.accuracy) + ' ม.)';
            }, () => {
                geo.innerHTML = '❌ ไม่ได้รับอนุญาตให้ใช้ตำแหน่ง — กดไอคอนแม่กุญแจที่แถบที่อยู่ → อนุญาต "ตำแหน่ง" แล้ว <a href="#" onclick="getGeo();return false;">ลองอีกครั้ง</a>';
            }, { enableHighAccuracy: true, timeout: 15000 });
        }
        getGeo();

        document.getElementById('completeForm').addEventListener('submit', function (e) {
            if (!this.querySelector('input[name="rating"]:checked')) { e.preventDefault(); alert('กรุณาเลือกคะแนนความพึงพอใจของลูกค้า'); return; }
            if (!signed) { e.preventDefault(); alert('กรุณาให้ลูกค้าเซ็นรับงาน'); return; }
            if (!document.getElementById('lat').value) { e.preventDefault(); alert('ยังไม่ได้ตำแหน่งปัจจุบัน กรุณาอนุญาตการใช้ตำแหน่ง'); getGeo(); return; }
            if (!confirm('ยืนยันปิดงานนี้? (ปิดแล้วแก้ไขไม่ได้ และจะตัดโควต้าบริการของลูกค้า 1 ครั้ง)')) { e.preventDefault(); return; }
            document.getElementById('sigData').value = canvas.toDataURL('image/png');
            const btn = this.querySelector('button[type="submit"]'); btn.disabled = true; btn.textContent = 'กำลังบันทึก…';
        });
    </script>
    @endif
@endsection
