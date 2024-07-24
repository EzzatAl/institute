<?php

namespace App\Filament\Resources\SessionResource\RelationManagers;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Session;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'attendance';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group_student_id')
                    ->relationship('student','Berlitz_NAME')
                    ->required(),
                Forms\Components\ToggleButtons::make('status')
                    ->required()
                    ->inline()
                    ->options(AttendanceStatus::class)
                    ->default('Attendance'),
                Forms\Components\TextInput::make('homework_mark')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Notes')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        $status = Attendance::pluck('status')->unique()->toArray();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.Berlitz_NAME')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('homework_mark')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Notes')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine($status, $status))
                    ->label('Status')
                    ->placeholder('All Status'),
            ])
            ->headerActions([
            ])
            ->actions([

            ])
            ->bulkActions([

            ]);
    }
}
