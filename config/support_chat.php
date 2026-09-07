<?php

/**
 * หัวข้อแชทติดต่อสอบถาม (support_chats.topic) และแผนกที่รับผิดชอบแต่ละหัวข้อ
 *
 * - key ของหัวข้อใช้ชุดเดียวกับ chatbot_topics.key (เมนูแชทบอท) เพื่อให้ตอนลูกค้ากด "คุยกับเจ้าหน้าที่"
 *   จากในหมวดของบอท ระบบเลือกหัวข้อให้อัตโนมัติได้เลย
 * - 'roles' = key ของ RBAC role (ตาราง roles) ที่เห็น/ตอบแชทหัวข้อนี้ได้ในหลังบ้าน
 *   (role แบบ full access เช่น IT และ users.role = super_admin เห็นทุกหัวข้อเสมอ)
 * - ถ้าจะเพิ่มหัวข้อหรือย้ายแผนก แก้ที่ไฟล์นี้ที่เดียว (ทั้งเว็บ แอป และหลังบ้านอ่านจากตรงนี้)
 */
return [

    'default_topic' => 'general',

    'topics' => [
        'general' => [
            'label' => 'สอบถามทั่วไป',
            'label_en' => 'General inquiry',
            'icon' => 'fa-comments',
            'roles' => ['sales_admin'],
        ],
        'security-system' => [
            'label' => 'ระบบรักษาความปลอดภัย',
            'label_en' => 'Security system',
            'icon' => 'fa-shield-halved',
            'roles' => ['security_admin'],
        ],
        'technician-service' => [
            'label' => 'บริการช่าง / แจ้งซ่อม',
            'label_en' => 'Technician service / Repair',
            'icon' => 'fa-screwdriver-wrench',
            'roles' => ['security_admin'],
        ],
        'insurance' => [
            'label' => 'ประกันภัย',
            'label_en' => 'Insurance',
            'icon' => 'fa-file-shield',
            'roles' => ['insurance_admin'],
        ],
        'locker' => [
            'label' => 'ตู้เซฟนิรภัย',
            'label_en' => 'Smart locker',
            'icon' => 'fa-vault',
            'roles' => ['smart_locker'],
        ],
        'ease-club' => [
            'label' => 'สิทธิพิเศษ EASE CLUB',
            'label_en' => 'EASE CLUB rewards',
            'icon' => 'fa-gift',
            'roles' => ['marketing'],
        ],
        'application' => [
            'label' => 'การใช้งานแอปพลิเคชัน',
            'label_en' => 'Using the application',
            'icon' => 'fa-mobile-screen',
            'roles' => ['marketing'],
        ],
    ],
];
