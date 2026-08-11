<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Общие настройки
    |--------------------------------------------------------------------------
    */

    'enabled' => env('MODERATION_ENABLED', true),

    'connection' => env(
        'MODERATION_QUEUE_CONNECTION',
        'database'
    ),

    'queue' => env('MODERATION_QUEUE', 'moderation'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Moderation API
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'enabled' => env('OPENAI_MODERATION_ENABLED', false),
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env(
            'OPENAI_MODERATION_MODEL',
            'omni-moderation-latest'
        ),
        'timeout' => (int) env(
            'OPENAI_MODERATION_TIMEOUT',
            30
        ),
        'connect_timeout' => (int) env(
            'OPENAI_MODERATION_CONNECT_TIMEOUT',
            10
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | OCR изображений
    |--------------------------------------------------------------------------
    */

    'ocr' => [
        'enabled' => env('OCR_ENABLED', false),
        'required' => env('OCR_REQUIRED', false),
        'binary' => env('OCR_BINARY', 'tesseract'),
        'languages' => env('OCR_LANGUAGES', 'rus+eng'),
        'page_segmentation_mode' => (int) env('OCR_PSM', 6),
        'timeout' => (int) env('OCR_TIMEOUT', 60),
        'max_text_length' => (int) env(
            'OCR_MAX_TEXT_LENGTH',
            20000
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Пороговые значения риска
    |--------------------------------------------------------------------------
    |
    | Значения предварительные. Их нужно откалибровать на русскоязычном
    | тестовом наборе Baano перед включением автоматического отклонения.
    |
    */

    'thresholds' => [
        'manual_review' => (float) env(
            'MODERATION_MANUAL_REVIEW_THRESHOLD',
            0.35
        ),

        'rejected' => (float) env(
            'MODERATION_REJECT_THRESHOLD',
            0.85
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Категории автоматического отклонения
    |--------------------------------------------------------------------------
    */

    'automatic_rejection_categories' => [
        'sexual_minors',
        'direct_threat',
        'drug_sale',
        'illegal_instructions',
        'terrorism_propaganda',
        'terrorism_call_to_action',
        'explicit_profanity_in_name',
        'explicit_profanity_in_title',
    ],

    /*
    |--------------------------------------------------------------------------
    | Категории обязательной ручной проверки
    |--------------------------------------------------------------------------
    */

    'manual_review_categories' => [
        'religious_symbols',
        'political_symbols',
        'legal_weapons',
        'medical_images',
        'quoted_profanity',
        'borderline_erotic',
        'conflicting_results',
    ],

    /*
    |--------------------------------------------------------------------------
    | Проверяемые типы содержимого
    |--------------------------------------------------------------------------
    */

    'content_types' => [
        'text',
        'image',
        'ocr',
        'filename',
        'profile',
    ],
];
