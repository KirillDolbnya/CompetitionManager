<?php

namespace App\Filament\Resources\Competitions\Schemas;

use App\Models\Competition;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CompetitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->placeholder('Краевые соревнования')
                            ->label('Название')
                            ->required()
                            ->maxLength(50),

                        Textarea::make('description')
                            ->maxLength(100)
                            ->rows(1)
                            ->cols(10)
                            ->autosize(true)
                            ->placeholder('Описание соревнований')
                            ->label('Описание'),

                        FileUpload::make('image_path')
                            ->label('Изображение')
                            ->disk('public')
                            ->image()
                            ->required()
                            ->maxSize(1024),

                        FileUpload::make('file_path')
                            ->label('Эксель')
                            ->disk('public')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->maxSize(1024)
                            ->required(),
                        DatePicker::make('start_at')
                            ->label('Начало соревнований')
                            ->required()
                            ->minDate(now()->startOfDay())
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    $hasOverlap = Competition::query()
                                        ->where('start_at', '<=', $get('end_at'))
                                        ->where('end_at', '>=', $get('start_at'))
                                        ->exists();

                                    if ($hasOverlap) {
                                        $fail('Даты проведения соревнований не могут пересекаться');
                                    }
                                },
                            ])
                            ->before('end_at'),

                        DatePicker::make('end_at')
                            ->label('Конец соревнований')
                            ->required()
                            ->after('start_at'),
                    ]),
            ]);
    }
}
