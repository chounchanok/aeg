<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ChatbotService
 *
 * ตัวจัดการเนื้อหาและตรรกะของแชทบอทเมนูปุ่มกด (Chat Bot) หน้า support-chat:
 * - ดึงเมนูหมวดหลัก / บริการย่อย / รายละเอียดบริการ
 * - ค้นหาคำตอบจากข้อความอิสระที่ลูกค้าพิมพ์เข้ามา (จับคู่กับธนาคาร keyword และ FAQ ของบริการ)
 * - ตรวจสอบเวลาทำการ เพื่อเลือกข้อความส่งต่อเจ้าหน้าที่ (escalation) ให้ตรงกับ spec
 *
 * หมายเหตุเรื่องความแม่นยำ: การจับคู่คำถามใช้ similar_text() + คำที่ overlap กัน (เหมือน FaqBotService)
 * ไม่ใช่ AI/LLM จริง จึงตอบได้แม่นเฉพาะคำถามที่ใกล้เคียงกับข้อมูลที่มีอยู่เท่านั้น
 */
class ChatbotService
{
    protected int $matchThreshold = 45;

    // เวลาทำการอ้างอิงจากบริการช่าง AEG: จันทร์-เสาร์ 09:00-18:00 (เวลาไทย)
    protected string $timezone = 'Asia/Bangkok';
    protected int $businessStartHour = 9;
    protected int $businessEndHour = 18;

    public function getTopics(): array
    {
        return DB::table('chatbot_topics')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function getTopic(int $topicId)
    {
        return DB::table('chatbot_topics')->where('id', $topicId)->where('is_active', true)->first();
    }

    public function getServices(int $topicId): array
    {
        return DB::table('chatbot_services')
            ->where('topic_id', $topicId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function getServiceDetail(int $serviceId): ?array
    {
        $service = DB::table('chatbot_services')->where('id', $serviceId)->where('is_active', true)->first();
        if (!$service) {
            return null;
        }

        $faqs = DB::table('chatbot_service_faqs')
            ->where('service_id', $serviceId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'question_th', 'answer_th']);

        return [
            'id' => $service->id,
            'topic_id' => $service->topic_id,
            'name_th' => $service->name_th,
            'info_th' => $service->info_th,
            'info_en' => $service->info_en,
            'extra_info' => $service->extra_info ? json_decode($service->extra_info, true) : [],
            'has_technician_contact' => (bool) $service->has_technician_contact,
            'has_purchase_interest' => (bool) $service->has_purchase_interest,
            'has_claim' => (bool) $service->has_claim,
            'purchase_link_route' => $service->purchase_link_route,
            'faqs' => $faqs,
        ];
    }

    /**
     * ค้นหาคำตอบจากข้อความอิสระ โดยจับคู่กับทั้งธนาคาร keyword (chatbot_keyword_faqs)
     * และ FAQ ของบริการย่อย (chatbot_service_faqs) ภายในขอบเขตที่กำหนด
     *
     * @param  int|null  $topicId  จำกัดขอบเขตค้นหาเฉพาะหมวดนี้ (null = ค้นหาทุกหมวด)
     */
    public function keywordSearch(string $message, ?int $topicId = null): array
    {
        $userNorm = $this->normalize($message);
        $best = null;
        $bestScore = 0.0;

        // 1) ค้นหาในธนาคาร keyword
        $keywordQuery = DB::table('chatbot_keyword_faqs')->where('is_active', true);
        if ($topicId) {
            $keywordQuery->where('topic_id', $topicId);
        }
        foreach ($keywordQuery->get() as $row) {
            $keywords = json_decode($row->keywords, true) ?: [];
            $candidates = array_merge($keywords, [$row->question_label]);
            $score = $this->scoreAgainstCandidates($userNorm, $candidates);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = [
                    'source' => 'keyword',
                    'question' => $row->question_label,
                    'answer' => $row->answer_th,
                ];
            }
        }

        // 2) ค้นหาใน FAQ ของบริการย่อย (ครอบคลุมหมวดที่ไม่มีธนาคาร keyword เช่น EASE CLUB)
        $faqQuery = DB::table('chatbot_service_faqs as f')
            ->join('chatbot_services as s', 's.id', '=', 'f.service_id')
            ->where('f.is_active', true)
            ->where('s.is_active', true);
        if ($topicId) {
            $faqQuery->where('s.topic_id', $topicId);
        }
        foreach ($faqQuery->get(['f.question_th', 'f.answer_th']) as $row) {
            $score = $this->scoreAgainstCandidates($userNorm, [$row->question_th]);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = [
                    'source' => 'service_faq',
                    'question' => $row->question_th,
                    'answer' => $row->answer_th,
                ];
            }
        }

        if ($best && $bestScore >= $this->matchThreshold) {
            return [
                'matched' => true,
                'question' => $best['question'],
                'answer' => $best['answer'],
                'score' => round($bestScore, 1),
            ];
        }

        return [
            'matched' => false,
            'question' => null,
            'answer' => null,
            'score' => round($bestScore, 1),
        ];
    }

    /**
     * true ถ้าตอนนี้อยู่ในเวลาทำการ (จันทร์-เสาร์ 09:00-18:00 เวลาไทย)
     */
    public function isBusinessHours(): bool
    {
        $now = Carbon::now($this->timezone);
        // Carbon: 0=Sunday ... 6=Saturday
        if ($now->dayOfWeek === Carbon::SUNDAY) {
            return false;
        }
        return $now->hour >= $this->businessStartHour && $now->hour < $this->businessEndHour;
    }

    /**
     * ข้อความส่งต่อเจ้าหน้าที่ (escalation) ให้ตรงกับเวลาทำการปัจจุบัน
     */
    public function getEscalationMessage(): string
    {
        if ($this->isBusinessHours()) {
            return 'ทางเราได้รับเรื่องแล้ว ขออนุญาติส่งต่อเรื่องให้เจ้าหน้าที่ และเนื่องจากมีการสอบถามเข้ามามาก กรุณารอสักครู่นะคะ';
        }

        return 'ทางเราได้รับเรื่องแล้ว ขออนุญาติส่งต่อเรื่องให้เจ้าหน้าที่ ขณะนี้อยู่ในนอกเวลาทำการ เจ้าหน้าที่จะรีบมาตอบให้ไวที่สุดค่ะ หากท่านมีเรื่องติดต่อเร่งด่วนสามารถติดต่อ (+66)2-238-4561 ตลอด 24 ชั่วโมง';
    }

    protected function scoreAgainstCandidates(string $userNorm, array $candidates): float
    {
        $best = 0.0;
        foreach ($candidates as $text) {
            $norm = $this->normalize((string) $text);
            if ($norm === '') {
                continue;
            }

            $percent = 0.0;
            similar_text($userNorm, $norm, $percent);

            $bonus = 0;
            if (mb_strlen($norm) >= 4 && (str_contains($userNorm, $norm) || str_contains($norm, $userNorm))) {
                $bonus = 25;
            }

            $wordOverlap = $this->wordOverlapRatio($userNorm, $norm);
            $total = $percent + $bonus + ($wordOverlap * 20);

            if ($total > $best) {
                $best = $total;
            }
        }

        return min($best, 100.0);
    }

    protected function wordOverlapRatio(string $a, string $b): float
    {
        $wordsA = array_values(array_filter(explode(' ', $a), fn ($w) => mb_strlen($w) >= 2));
        $wordsB = array_values(array_filter(explode(' ', $b), fn ($w) => mb_strlen($w) >= 2));

        if (empty($wordsA) || empty($wordsB)) {
            return 0.0;
        }

        $matches = 0;
        foreach ($wordsA as $w) {
            foreach ($wordsB as $w2) {
                if ($w === $w2 || str_contains($w2, $w) || str_contains($w, $w2)) {
                    $matches++;
                    break;
                }
            }
        }

        return $matches / max(count($wordsA), 1);
    }

    protected function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
