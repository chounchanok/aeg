{{-- 🌟 คำแนะนำขนาดรูปใต้ input อัปโหลด (ค่ากำหนดใน config/image_sizes.php) --}}
@php $hint = config('image_sizes.' . $key); @endphp
@if($hint)
    <div class="text-xs text-slate-500 mt-1 leading-relaxed">
        <i data-lucide="image" class="w-3 h-3 inline -mt-0.5"></i>
        ขนาดแนะนำ <span class="font-medium text-slate-700">{{ $hint['size'] }}</span>
        · สัดส่วน {{ $hint['ratio'] }} · ขั้นต่ำ {{ $hint['min'] }}<br>
        แสดงที่: {{ $hint['where'] }}{{ !empty($extra) ? ' · ' . $extra : '' }}
    </div>
@endif
