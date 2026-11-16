@extends('../layout/' . $layout)

@section('subhead')
    <title>QR ดาวน์โหลดแอป (ทีม) - AEG Admin</title>
    <style>
        /* ใช้ CSS ของหน้านี้เอง (คลาส Tailwind บางตัวไม่มีใน CSS ที่ build ไว้ของธีม)
           ⚠️ ห้ามตั้งชื่อคลาสขึ้นต้นด้วย "ad-" — ตัวบล็อกโฆษณาในเบราว์เซอร์จะซ่อนทิ้ง */
        .teamqr-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
        .teamqr-card { background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 3px 15px #0000000b; }
        .teamqr-card .lbl { font-size: 12px; color: #64748b; }
        .teamqr-card .val { font-size: 24px; font-weight: 600; color: #1e3a8a; line-height: 1.3; }
        .teamqr-card .sub { font-size: 11px; color: #94a3b8; }
        .teamqr-rank { display: inline-flex; width: 30px; height: 30px; border-radius: 50%; align-items: center; justify-content: center; font-weight: 700; background: #f1f5f9; color: #475569; }
        .teamqr-rank.r1 { background: #fde68a; color: #92400e; }
        .teamqr-rank.r2 { background: #e2e8f0; color: #334155; }
        .teamqr-rank.r3 { background: #fed7aa; color: #9a3412; }
        .teamqr-table th, .teamqr-table td { white-space: nowrap; }
        .teamqr-pill { font-size: 11px; padding: 2px 8px; border-radius: 999px; background: #f1f5f9; color: #475569; }
        .teamqr-bars { display: flex; align-items: flex-end; gap: 3px; height: 120px; padding-top: 6px; }
        .teamqr-bar { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; gap: 1px; min-width: 0; }
        .teamqr-bar .s { background: #93c5fd; border-radius: 2px 2px 0 0; }
        .teamqr-bar .i { background: #1e3a8a; border-radius: 2px 2px 0 0; }
        .teamqr-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 16px; }
        .teamqr-modal.show { display: flex; }
        .teamqr-modal-box { background: #fff; border-radius: 12px; padding: 20px; width: 100%; max-width: 420px; }
        .teamqr-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        @media (max-width: 640px) { .teamqr-form-row { grid-template-columns: 1fr; } }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8 flex-wrap gap-2">
        <h2 class="text-lg font-medium mr-auto">QR ดาวน์โหลดแอป (แยกทีม)</h2>
        <button type="button" class="btn btn-primary shadow-md" onclick="tqOpen('createModal')">
            <i data-lucide="plus" class="w-4 h-4 mr-1"></i> สร้างทีม
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success show flex items-center mb-2 mt-5" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger show mb-2 mt-5"><ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
    @endif

    {{-- ตัวกรองช่วงวันที่ / การเรียงอันดับ --}}
    <form method="GET" class="intro-y box p-5 mt-5 flex flex-wrap items-end gap-3">
        <div>
            <label class="form-label text-xs">ตั้งแต่</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div>
            <label class="form-label text-xs">ถึง</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div>
            <label class="form-label text-xs">จัดอันดับตาม</label>
            <select name="sort" class="form-select">
                @if($showInstalls)<option value="installs" {{ $sort === 'installs' ? 'selected' : '' }}>ยอดติดตั้ง</option>@endif
                <option value="scans" {{ $sort === 'scans' ? 'selected' : '' }}>ยอดสแกน QR</option>
                <option value="scans_unique" {{ $sort === 'scans_unique' ? 'selected' : '' }}>ผู้สแกน (ไม่ซ้ำ)</option>
                <option value="scans_per_member" {{ $sort === 'scans_per_member' ? 'selected' : '' }}>ยอดสแกนต่อคน</option>
                @if($showInstalls)<option value="installs_per_member" {{ $sort === 'installs_per_member' ? 'selected' : '' }}>ยอดติดตั้งต่อคน</option>@endif
            </select>
        </div>
        <button type="submit" class="btn btn-primary">แสดง</button>
        <a href="{{ route('admin.app-downloads.index', ['range' => 'month', 'sort' => $sort]) }}" class="btn btn-outline-secondary">เดือนนี้</a>
        <a href="{{ route('admin.app-downloads.index', ['range' => '30d', 'sort' => $sort]) }}" class="btn btn-outline-secondary">30 วัน</a>
        <a href="{{ route('admin.app-downloads.index', ['sort' => $sort]) }}" class="btn btn-outline-secondary">ทั้งหมด</a>
    </form>

    {{-- สรุปยอดรวม --}}
    <div class="teamqr-cards mt-5">
        <div class="teamqr-card">
            <div class="lbl">สแกน QR ทั้งหมด</div>
            <div class="val">{{ number_format($totals['scans']) }}</div>
            <div class="sub">iOS {{ number_format($totals['scans_ios']) }} · Android {{ number_format($totals['scans_android']) }} · คอม {{ number_format($totals['scans_other']) }}</div>
        </div>
        @if($showInstalls)
        <div class="teamqr-card">
            <div class="lbl">ติดตั้งผ่าน QR ทีม</div>
            <div class="val">{{ number_format($totals['installs']) }}</div>
            <div class="sub">Android {{ number_format($totals['installs_android']) }} · iOS {{ number_format($totals['installs_ios']) }} (ประมาณ)</div>
        </div>
        <div class="teamqr-card">
            <div class="lbl">ติดตั้งทั้งหมดที่แอปรายงาน</div>
            <div class="val">{{ number_format($totals['app_installs_all']) }}</div>
            <div class="sub">Android {{ number_format($totals['app_installs_android']) }} · iOS {{ number_format($totals['app_installs_ios']) }} · ไม่ผ่านทีม {{ number_format($totals['app_installs_no_team']) }}</div>
        </div>
        <div class="teamqr-card">
            <div class="lbl">อัตราสแกน → ติดตั้ง</div>
            <div class="val">{{ $totals['scans_unique'] > 0 ? number_format($totals['installs'] / $totals['scans_unique'] * 100, 1) . '%' : '-' }}</div>
            <div class="sub">เทียบกับผู้สแกนไม่ซ้ำ {{ number_format($totals['scans_unique']) }}</div>
        </div>
        @else
        <div class="teamqr-card">
            <div class="lbl">ผู้สแกน (ไม่ซ้ำ)</div>
            <div class="val">{{ number_format($totals['scans_unique']) }}</div>
            <div class="sub">นับจากเครือข่าย/อุปกรณ์ที่ต่างกัน</div>
        </div>
        <div class="teamqr-card">
            <div class="lbl">จำนวนทีม / พนักงาน</div>
            <div class="val">{{ number_format($rows->count()) }} <span style="font-size:14px;color:#64748b">ทีม</span> · {{ number_format($totals['members']) }} <span style="font-size:14px;color:#64748b">คน</span></div>
            <div class="sub">เปิดใช้งาน {{ number_format($rows->where('is_active', true)->count()) }} ทีม · เฉลี่ย {{ $totals['members'] > 0 ? number_format($totals['scans'] / $totals['members'], 2) : '0' }} สแกน/คน</div>
        </div>
        @endif
    </div>

    {{-- อันดับทีม --}}
    <div class="intro-y box p-5 mt-5">
        <div class="font-medium text-base mb-3">อันดับทีม</div>
        <div class="overflow-x-auto">
            <table class="table teamqr-table">
                <thead>
                    <tr>
                        <th>อันดับ</th>
                        <th>ทีม</th>
                        <th class="text-center">คนในทีม</th>
                        @if($showInstalls)<th class="text-center">ติดตั้ง</th>@endif
                        <th class="text-center">สแกน QR</th>
                        <th class="text-center">สแกน/คน</th>
                        <th class="text-center">iOS / Android / คอม</th>
                        <th class="text-center">ผู้สแกน (ไม่ซ้ำ)</th>
                        <th>ลิงก์ / QR</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr style="{{ $r->is_active ? '' : 'opacity:.5' }}">
                            <td><span class="teamqr-rank {{ $r->rank <= 3 ? 'r' . $r->rank : '' }}">{{ $r->rank }}</span></td>
                            <td>
                                <div class="font-medium">{{ $r->name }} @unless($r->is_active)<span class="teamqr-pill">ปิดใช้งาน</span>@endunless</div>
                                <div class="text-xs text-slate-500">{{ $r->code }}</div>
                                @if($r->description)<div class="text-xs text-slate-500">{{ $r->description }}</div>@endif
                            </td>
                            <td class="text-center">{{ number_format($r->member_count) }}</td>
                            @if($showInstalls)
                            <td class="text-center">
                                <div class="font-medium text-primary">{{ number_format($r->installs) }}</div>
                                <div class="text-xs text-slate-500">A {{ number_format($r->installs_android) }} · i {{ number_format($r->installs_ios) }}*</div>
                            </td>
                            @endif
                            <td class="text-center font-medium">{{ number_format($r->scans) }}</td>
                            <td class="text-center">{{ $r->member_count > 0 ? number_format($r->scans_per_member, 2) : '-' }}</td>
                            <td class="text-center text-xs">{{ number_format($r->scans_ios) }} / {{ number_format($r->scans_android) }} / {{ number_format($r->scans_other) }}</td>
                            <td class="text-center">{{ number_format($r->scans_unique) }}</td>
                            <td>
                                <div class="text-xs">{{ $r->link }}</div>
                                <div class="mt-1 flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="tqShowQr(@js($r->name), @js($r->link), @js($r->code))">
                                        <i data-lucide="qr-code" class="w-4 h-4 mr-1"></i> QR
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tqCopy(@js($r->link), this)">คัดลอกลิงก์</button>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tqOpen('editModal{{ $r->id }}')">แก้ไข</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $showInstalls ? 10 : 9 }}" class="text-center text-slate-500 py-6">ยังไม่มีทีม — กด "สร้างทีม" เพื่อเริ่ม</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($showInstalls)
        <div class="text-xs text-slate-500 mt-3">
            * <b>Android</b> = ยืนยันจาก Play Install Referrer (แม่นยำ) · <b>iOS</b> = จับคู่จากเครือข่ายเดียวกับที่สแกน QR ภายใน 24 ชม. (โดยประมาณ — Apple ไม่ส่งข้อมูลแหล่งที่มาให้แอป)
            ยอดติดตั้ง iOS จริงแยกทีมดูได้ที่ App Store Connect → Analytics → Campaigns (ชื่อ campaign = รหัสลิงก์ของทีม)<br>
            ยอดติดตั้งนับเมื่อแอปเวอร์ชันที่รองรับถูกเปิดครั้งแรก (POST /api/app-installs) — ยอดรวมจากสโตร์ทั้งหมดยังต้องดูใน App Store Connect / Google Play Console
        </div>
        @else
        <div class="text-xs text-slate-500 mt-3">
            ยอดสแกน = จำนวนครั้งที่มีคนสแกน QR / กดลิงก์ของทีมแล้วถูกพาไป App Store หรือ Google Play (ไม่นับบอท/ตัว preview ลิงก์ในแชท)
            — ยอดติดตั้งจริงดูได้ใน App Store Connect / Google Play Console
        </div>
        @endif
    </div>

    {{-- กราฟรายวัน 30 วัน --}}
    @php $max = max(1, collect($daily)->max(fn ($d) => $showInstalls ? max($d['scans'], $d['installs']) : $d['scans'])); @endphp
    <div class="intro-y box p-5 mt-5">
        <div class="font-medium text-base mb-1">30 วันล่าสุด</div>
        <div class="text-xs text-slate-500 mb-2">
            <span style="display:inline-block;width:10px;height:10px;background:#93c5fd;border-radius:2px"></span> สแกน QR
            @if($showInstalls)<span style="display:inline-block;width:10px;height:10px;background:#1e3a8a;border-radius:2px;margin-left:10px"></span> ติดตั้ง@endif
        </div>
        <div class="teamqr-bars">
            @foreach($daily as $d)
                <div class="teamqr-bar" title="{{ $d['date'] }}: สแกน {{ $d['scans'] }}{{ $showInstalls ? ' / ติดตั้ง ' . $d['installs'] : '' }}">
                    <div class="s" style="height: {{ round($d['scans'] / $max * 100) }}px"></div>
                    @if($showInstalls)<div class="i" style="height: {{ round($d['installs'] / $max * 100) }}px"></div>@endif
                </div>
            @endforeach
        </div>
        <div class="text-xs text-slate-400 mt-1" style="display:flex; justify-content:space-between;">
            <span>{{ $daily[0]['date'] ?? '' }}</span><span>{{ end($daily)['date'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== Modal: สร้างทีม ===== --}}
    <div id="createModal" class="teamqr-modal" onclick="if(event.target===this)tqClose(this.id)">
        <div class="teamqr-modal-box">
            <div class="font-medium text-base mb-3">สร้างทีม</div>
            <form action="{{ route('admin.app-downloads.store') }}" method="POST">
                @csrf
                <label class="form-label">ชื่อทีม <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required maxlength="100" placeholder="เช่น ทีมขาย กรุงเทพ 1">
                <label class="form-label mt-3">จำนวนคนในทีม</label>
                <input type="number" name="member_count" min="0" class="form-control" placeholder="0">
                <div class="teamqr-form-row mt-3">
                    <div>
                        <label class="form-label">รหัสลิงก์ (ไม่บังคับ)</label>
                        <input type="text" name="code" class="form-control" maxlength="40" pattern="[A-Za-z0-9\-]+" placeholder="sales-bkk-1">
                    </div>
                    <div>
                        <label class="form-label">หมายเหตุ</label>
                        <input type="text" name="description" class="form-control" maxlength="255" placeholder="หัวหน้าทีม / พื้นที่">
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-1">ลิงก์จะเป็น {{ url('/d') }}/<b>รหัสลิงก์</b> (เว้นว่าง = สร้างจากชื่อทีมให้อัตโนมัติ)</div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="tqClose('createModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">สร้างทีม</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal: แก้ไขทีม ===== --}}
    @foreach($rows as $r)
        <div id="editModal{{ $r->id }}" class="teamqr-modal" onclick="if(event.target===this)tqClose(this.id)">
            <div class="teamqr-modal-box">
                <div class="font-medium text-base mb-3">แก้ไขทีม</div>
                <form action="{{ route('admin.app-downloads.update', $r->id) }}" method="POST">
                    @csrf
                    <label class="form-label">ชื่อทีม</label>
                    <input type="text" name="name" value="{{ $r->name }}" class="form-control" required maxlength="100">
                    <label class="form-label mt-3">จำนวนคนในทีม</label>
                    <input type="number" name="member_count" value="{{ $r->member_count }}" min="0" class="form-control">
                    <label class="form-label mt-3">รหัสลิงก์</label>
                    <input type="text" name="code" value="{{ $r->code }}" class="form-control" maxlength="40" pattern="[A-Za-z0-9\-]+">
                    <div class="text-xs text-warning mt-1">⚠️ เปลี่ยนรหัสลิงก์แล้ว QR ที่พิมพ์ไปแล้วจะไม่นับยอดให้ทีมนี้อีก</div>
                    <label class="form-label mt-3">หมายเหตุ</label>
                    <input type="text" name="description" value="{{ $r->description }}" class="form-control" maxlength="255">
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="is_active" value="1" id="active{{ $r->id }}" class="form-check-input border mr-2" {{ $r->is_active ? 'checked' : '' }}>
                        <label for="active{{ $r->id }}">เปิดใช้งาน (ปิด = ลิงก์ยังพาไปสโตร์ได้ แต่ไม่นับยอด)</label>
                    </div>
                    <div class="flex justify-between gap-2 mt-4">
                        <button type="submit" form="deleteTeam{{ $r->id }}" class="btn btn-outline-danger">ลบทีม</button>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="tqClose('editModal{{ $r->id }}')">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </div>
                </form>
                <form id="deleteTeam{{ $r->id }}" action="{{ route('admin.app-downloads.destroy', $r->id) }}" method="POST"
                      onsubmit="return confirm('ลบทีมนี้และประวัติการสแกนของทีม? (ถ้าแค่หยุดใช้ แนะนำให้ปิดใช้งานแทน)');">
                    @csrf
                </form>
            </div>
        </div>
    @endforeach

    {{-- ===== Modal: QR Code ===== --}}
    <div id="qrModal" class="teamqr-modal" onclick="if(event.target===this)tqClose(this.id)">
        <div class="teamqr-modal-box text-center">
            <div class="font-medium text-base" id="qrTitle"></div>
            <div class="text-xs text-slate-500 mb-3" id="qrLink"></div>
            <div id="qrBox" style="display:flex;justify-content:center;"></div>
            <div class="text-xs text-slate-500 mt-3">สแกนด้วยกล้องมือถือ — iPhone ไป App Store, Android ไป Google Play อัตโนมัติ</div>
            <div class="flex justify-center gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary" onclick="tqClose('qrModal')">ปิด</button>
                <button type="button" class="btn btn-primary" onclick="tqDownloadQr()"><i data-lucide="download" class="w-4 h-4 mr-1"></i> ดาวน์โหลด PNG</button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function tqOpen(id) { document.getElementById(id).classList.add('show'); }
        function tqClose(id) { document.getElementById(id).classList.remove('show'); }

        let tqQr = { name: '', link: '', code: '' };
        function tqShowQr(name, link, code) {
            tqQr = { name, link, code };
            document.getElementById('qrTitle').textContent = name;
            document.getElementById('qrLink').textContent = link;
            const box = document.getElementById('qrBox');
            box.innerHTML = '';
            new QRCode(box, { text: link, width: 240, height: 240, correctLevel: QRCode.CorrectLevel.M });
            tqOpen('qrModal');
        }

        // รวม QR + ชื่อทีม + ลิงก์ เป็นรูปเดียวพร้อมพิมพ์
        function tqDownloadQr() {
            const src = document.querySelector('#qrBox canvas');
            if (!src) return;
            const pad = 40, size = 600, out = document.createElement('canvas');
            out.width = size + pad * 2; out.height = size + pad * 2 + 110;
            const ctx = out.getContext('2d');
            ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, out.width, out.height);
            ctx.imageSmoothingEnabled = false;
            ctx.drawImage(src, pad, pad, size, size);
            ctx.fillStyle = '#0f172a'; ctx.textAlign = 'center';
            ctx.font = 'bold 34px sans-serif'; ctx.fillText(tqQr.name, out.width / 2, size + pad + 55);
            ctx.fillStyle = '#64748b'; ctx.font = '22px sans-serif'; ctx.fillText(tqQr.link, out.width / 2, size + pad + 95);
            const a = document.createElement('a');
            a.download = 'qr-' + tqQr.code + '.png';
            a.href = out.toDataURL('image/png');
            a.click();
        }

        function tqCopy(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const old = btn.textContent; btn.textContent = 'คัดลอกแล้ว'; setTimeout(() => btn.textContent = old, 1500);
            });
        }

        @if($errors->any() && old('name') && !request()->routeIs('*update*'))
            tqOpen('createModal');
        @endif
    </script>
@endsection
