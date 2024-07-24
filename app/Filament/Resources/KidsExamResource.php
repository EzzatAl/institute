<?php

namespace App\Filament\Resources;

use App\Enums\Evaluationn;
use App\Filament\Resources\KidsExamResource\Pages;
use App\Filament\Resources\KidsExamResource\RelationManagers;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\KidsExam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KidsExamResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = KidsExam::class;
    protected static ?string $label = "Kid's Exam";
    protected static ?string $navigationGroup = "Exam Management";
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group_student_id')
                ->relationship('student','Berlitz_NAME')
                ->required()
                ->native(false),
                Forms\Components\ToggleButtons::make('Communication')
                ->required()
                ->inline()
                ->options(Evaluationn::class),
                Forms\Components\ToggleButtons::make('Vocabulary')
                ->required()
                ->inline()
                ->options(Evaluationn::class),
                Forms\Components\ToggleButtons::make('Structure')
                ->required()
                ->inline()
                ->options(Evaluationn::class),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.Berlitz_NAME'),
                Tables\Columns\TextColumn::make('Communication')
                ->badge(),
                Tables\Columns\TextColumn::make('Vocabulary')
                ->badge(),
                Tables\Columns\TextColumn::make('Structure')
                ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKidsExams::route('/'),
            'create' => Pages\CreateKidsExam::route('/create'),
            'edit' => Pages\EditKidsExam::route('/{record}/edit'),
        ];
    }
}
