<?php

namespace App\Filament\Resources\Competitions\Pages;

use App\Exceptions\CompetitionImportException;
use App\Filament\Resources\Competitions\CompetitionResource;
use App\Imports\CompetitionImport;
use App\Models\Competition;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CreateCompetition extends CreateRecord
{
    protected static string $resource = CompetitionResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['cleanup_at'] = Carbon::parse($data['end_at'])->addDays(30);

        return $data;
    }

    public function getTitle(): string
    {
        return 'Создать соревнования';
    }

   protected function handleRecordCreation(array $data): Model
   {
       try{
           return DB::transaction(function () use ($data){
               $competition = Competition::create($data);

               $filePath = Storage::disk('public')
                   ->path($competition->file_path);

               $import = app(CompetitionImport::class, ['competition_id' => $competition->id]);

               Excel::import($import, $filePath);

               return $competition;
           });
       }catch (CompetitionImportException $exception){

           if (isset($data['file_path'])){
               Storage::disk('public')->delete($data['file_path']);
           }

           if (isset($data['image_path'])){
               Storage::disk('public')->delete($data['image_path']);
           }

           Notification::make()
               ->title('Эксель файл содержит ошибки')
               ->body(implode('<br>', $exception->errors()))
               ->danger()
               ->persistent()
               ->send();

           $this->halt();
       }catch (\Throwable $exception){
           if (isset($data['file_path'])){
               Storage::disk('public')->delete($data['file_path']);
           }

           if (isset($data['image_path'])){
               Storage::disk('public')->delete($data['image_path']);
           }

           Log::critical('Ошибка при создании соревнований: ' . $exception->getMessage(), ['exception' => $exception]);

           Notification::make()
               ->title('Критическая ошибка системы')
               ->body('Произошла ошибка системы, попробуйте позже')
               ->danger()
               ->persistent()
               ->send();

           $this->halt();
       }
   }
}
