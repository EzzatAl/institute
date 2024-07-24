<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupStudent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Exam::class;
    protected static ?string $label = "Adult's Exam";
    protected static ?string $navigationGroup = "Exam Management";
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    public static function form(Form $form): Form
    {
        
        return $form
            ->schema([
                Forms\Components\Select::make('group_student_id')
                ->relationship('student','Berlitz_NAME')
                ->required(),
                Forms\Components\TextInput::make('exam_type')
                    ->required(),
                Forms\Components\TextInput::make('Written_Test')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Oral_Test')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Attendance')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Participation')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Home_Work')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Communication')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Vocabulary')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Structure')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Mark')
                    ->required()
                    ->numeric(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.Berlitz_NAME')
                    ->numeric(),
                Tables\Columns\TextColumn::make('exam_type'),
                Tables\Columns\TextColumn::make('Written_Test')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Oral_Test')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Attendance')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Participation')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Home_Work')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Communication')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Vocabulary')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Structure')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Mark')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
