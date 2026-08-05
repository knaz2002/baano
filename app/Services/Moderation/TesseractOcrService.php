<?php

namespace App\Services\Moderation;

use RuntimeException;
use Symfony\Component\Process\Process;

class TesseractOcrService
{
    public function isEnabled(): bool
    {
        return (bool) config('moderation.ocr.enabled');
    }

    public function extractText(string $imagePath): string
    {
        if (!$this->isEnabled()) {
            throw new RuntimeException(
                'Распознавание текста отключено.'
            );
        }

        if (!is_file($imagePath) || !is_readable($imagePath)) {
            throw new RuntimeException(
                'Изображение недоступно для распознавания текста.'
            );
        }

        $process = new Process([
            (string) config(
                'moderation.ocr.binary',
                'tesseract'
            ),
            $imagePath,
            'stdout',
            '-l',
            (string) config(
                'moderation.ocr.languages',
                'rus+eng'
            ),
            '--psm',
            (string) config(
                'moderation.ocr.page_segmentation_mode',
                6
            ),
        ]);

        $process->setTimeout(
            (float) config('moderation.ocr.timeout', 60)
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                'Ошибка OCR: '
                . trim($process->getErrorOutput())
            );
        }

        return $this->normalizeText(
            $process->getOutput()
        );
    }

    private function normalizeText(string $text): string
    {
        $text = trim($text);

        $text = preg_replace(
            '/[ \t]+/u',
            ' ',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\R{3,}/u',
            "\n\n",
            $text
        ) ?? $text;

        return mb_substr(
            $text,
            0,
            (int) config(
                'moderation.ocr.max_text_length',
                20000
            ),
            'UTF-8'
        );
    }
}
