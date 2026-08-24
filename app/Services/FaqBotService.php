<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * FaqBotService
 *
 * บอทตอบคำถามลูกค้าแบบง่าย ๆ โดยจับคู่คำถามที่พิมพ์เข้ามากับตาราง `faqs` ที่มีอยู่แล้ว
 * ไม่ได้ใช้ AI/LLM ภายนอก — เป็นการจับคู่ข้อความด้วย similar_text() + คำที่ overlap กัน
 *
 * ข้อจำกัดที่ควรรู้:
 * - แม่นยำเฉพาะคำถามที่คำ/ความหมายใกล้เคียงกับ FAQ ที่มีอยู่ ถ้าลูกค้าถามด้วยคำที่ต่างไปมาก
 *   (เช่น ใช้คำพ้องความหมาย) อาจจับคู่ไม่เจอ
 * - ถ้าจำนวน FAQ เยอะขึ้นมาก (หลักพันขึ้นไป) การวนลูปแบบนี้จะช้าลง ควรพิจารณาย้ายไปใช้
 *   full-text search หรือ embedding-based search แทนในอนาคต
 */
class FaqBotService
{
    /**
     * คะแนนขั้นต่ำ (0-100) ที่จะถือว่าบอท "ตอบได้" ต่ำกว่านี้ถือว่ายังไม่พบคำตอบ
     */
    protected int $matchThreshold = 45;

    /**
     * หาคำตอบที่ใกล้เคียงที่สุดจากตาราง faqs
     *
     * @return array{matched: bool, faq_id: ?int, question: ?string, answer: ?string, category: ?string, score: float}
     */
    public function findAnswer(string $question): array
    {
        $userNorm = $this->normalize($question);

        $faqs = DB::table('faqs')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $best = null;
        $bestScore = 0.0;

        foreach ($faqs as $faq) {
            $score = $this->score($userNorm, $faq);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        if ($best && $bestScore >= $this->matchThreshold) {
            return [
                'matched' => true,
                'faq_id' => $best->id,
                'question' => $best->question_th,
                'answer' => $best->answer_th,
                'category' => $best->category,
                'score' => round($bestScore, 1),
            ];
        }

        return [
            'matched' => false,
            'faq_id' => null,
            'question' => null,
            'answer' => null,
            'category' => null,
            'score' => round($bestScore, 1),
        ];
    }

    /**
     * ให้คะแนนความใกล้เคียงระหว่างคำถามลูกค้ากับ FAQ หนึ่งข้อ (0-100)
     */
    protected function score(string $userNorm, object $faq): float
    {
        $candidates = array_filter([
            $faq->question_th ?? null,
            $faq->question_en ?? null,
            $faq->answer_th ?? null,
        ]);

        $best = 0.0;

        foreach ($candidates as $text) {
            $norm = $this->normalize((string) $text);
            if ($norm === '') {
                continue;
            }

            $percent = 0.0;
            similar_text($userNorm, $norm, $percent);

            // โบนัส: ถ้าข้อความฝั่งหนึ่งเป็น substring ของอีกฝั่ง (ลูกค้าพิมพ์คำถามตรง ๆ หรือใกล้เคียงมาก)
            $bonus = 0;
            if (mb_strlen($norm) >= 4 && (str_contains($userNorm, $norm) || str_contains($norm, $userNorm))) {
                $bonus = 25;
            }

            // โบนัสจากคำ (แยกด้วยช่องว่าง) ที่ overlap กัน
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

    /**
     * ตัดเครื่องหมายวรรคตอน ทำเป็นตัวพิมพ์เล็ก และลดช่องว่างซ้ำ
     * คงตัวอักษรไทย/อังกฤษ/ตัวเลข/ช่องว่างไว้เท่านั้น
     */
    protected function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
