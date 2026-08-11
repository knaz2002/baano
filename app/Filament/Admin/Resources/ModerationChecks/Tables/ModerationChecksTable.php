<?php

namespace App\Filament\Admin\Resources\ModerationChecks\Tables;

use App\Models\ModerationCheck;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ModerationChecksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('moderatable_type')
                    ->label('Тип материала')
                    ->formatStateUsing(
                        fn (string $state): string => class_basename($state)
                    )
                    ->badge(),

                TextColumn::make('moderatable_id')
                    ->label('ID материала')
                    ->sortable(),

                TextColumn::make('content_type')
                    ->label('Элемент')
                    ->badge(),

                TextColumn::make('content_reference')
                    ->label('Источник')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('provider')
                    ->label('Проверяющий')
                    ->badge()
                    ->searchable(),

                TextColumn::make('model')
                    ->label('Модель')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Результат')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'pending' => 'Ожидает проверки',
                            'draft' => 'Черновик',
                            'pending_moderation' => 'На модерации',
                            'approved' => 'Одобрено',
                            'manual_review' => 'Ручная проверка',
                            'rejected' => 'Отклонено',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'approved' => 'success',
                            'manual_review' => 'info',
                            'rejected' => 'danger',
                            'pending',
                            'pending_moderation' => 'warning',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('categories')
                    ->label('Категории')
                    ->formatStateUsing(
                        fn (mixed $state): string =>
                            self::formatArray($state)
                    )
                    ->limit(60)
                    ->wrap()
                    ->placeholder('—')
                    ->tooltip(
                        fn (ModerationCheck $record): string =>
                            self::formatArray($record->categories)
                    ),

                TextColumn::make('scores')
                    ->label('Оценки риска')
                    ->formatStateUsing(
                        fn (mixed $state): string =>
                            self::formatJson($state)
                    )
                    ->limit(60)
                    ->wrap()
                    ->placeholder('—')
                    ->tooltip(
                        fn (ModerationCheck $record): string =>
                            self::formatJson($record->scores)
                    )
                    ->toggleable(),

                TextColumn::make('reason')
                    ->label('Причина')
                    ->limit(60)
                    ->wrap()
                    ->placeholder('—')
                    ->tooltip(
                        fn (ModerationCheck $record): ?string =>
                            $record->reason
                    ),

                TextColumn::make('content_snapshot')
                    ->label('Версия содержимого')
                    ->formatStateUsing(
                        fn (mixed $state): string =>
                            self::formatJson($state)
                    )
                    ->limit(80)
                    ->wrap()
                    ->placeholder('—')
                    ->tooltip(
                        fn (ModerationCheck $record): string =>
                            self::formatJson($record->content_snapshot)
                    )
                    ->toggleable(),

                TextColumn::make('reviewer.name')
                    ->label('Модератор')
                    ->placeholder('—'),

                TextColumn::make('checked_at')
                    ->label('Проверено')
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Решение человека')
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Результат')
                    ->options([
                        'pending' => 'Ожидает проверки',
                        'approved' => 'Одобрено',
                        'manual_review' => 'Ручная проверка',
                        'rejected' => 'Отклонено',
                    ]),

                SelectFilter::make('provider')
                    ->label('Проверяющий')
                    ->options([
                        'local_rules' => 'Локальные правила',
                        'openai' => 'OpenAI',
                        'tesseract' => 'Tesseract OCR',
                        'human' => 'Модератор',
                    ]),

                SelectFilter::make('content_type')
                    ->label('Тип содержимого')
                    ->options([
                        'text' => 'Текст',
                        'image' => 'Изображение',
                        'ocr' => 'OCR-текст',
                        'filename' => 'Название файла',
                        'profile' => 'Профиль',
                        'human_review' => 'Ручное решение',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('id', 'desc');
    }

    private static function formatArray(mixed $value): string
    {
        if (!is_array($value) || $value === []) {
            return '';
        }

        return implode(', ', array_map(
            static fn (mixed $item): string => (string) $item,
            $value
        ));
    }

    private static function formatJson(mixed $value): string
    {
        if ($value === null || $value === [] || $value === '') {
            return '';
        }

        if (!is_array($value)) {
            return (string) $value;
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_PRETTY_PRINT
        ) ?: '';
    }
}
