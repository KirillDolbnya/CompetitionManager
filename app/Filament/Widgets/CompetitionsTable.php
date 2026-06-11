<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Competitions\CompetitionResource;
use App\Models\Competition;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CompetitionsTable extends TableWidget
{

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {

        return $table
            ->query(fn (): Builder => Competition::query())
            ->paginated(false)
            ->heading('Соревнования')
            ->columns([
                TextColumn::make('name')
                    ->label('Название'),
                TextColumn::make('start_at')
                    ->label('Дата начала'),
                TextColumn::make('end_at')
                    ->label('Дата окончания'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->state(function ($record){
                       if (now()->lt($record->start_at)) {
                           return 'Ожидается';
                       }

                       if (now()->gt($record->end_at)) {
                           return 'Завершено';
                       }

                       return 'Активно';
                    })
                    ->badge()
                    ->icon(fn ($state) => match ($state) {
                        'Активно' => 'heroicon-o-play',
                        'Ожидается' => 'heroicon-o-clock',
                        'Завершено' => 'heroicon-o-check-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Активно' => 'success',
                        'Ожидается' => 'warning',
                        'Завершено' => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => CompetitionResource::getUrl('view', [
                        'record' => $record,
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
