<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomResource\Pages;
use App\Filament\Resources\ClassroomResource\RelationManagers;
use App\Models\Classroom;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = "Administration";
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function sidebar(Model $record): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            ->sidebarNavigation()
            ->setNavigationItems([
                PageNavigationItem::make('View class room')
                    ->url(function () use ($record) {
                        return static::getUrl('view', ['record' => $record->id]);
                    }),
                PageNavigationItem::make('Edit class room')
                    ->url(function () use ($record) {
                        return static::getUrl('edit', ['record' => $record->id]);
                    }),
                PageNavigationItem::make('Calendar class room')
                    ->url(function () use ($record) {
                        return static::getUrl('calendar', ['record' => $record->id]);
                    }),
            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Class_Number')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord:true)
                    ->minValue(0),
                Forms\Components\TextInput::make('Capacity')
                    ->required()
                    ->numeric()
                    ->minValue(3),
                Forms\Components\TextInput::make('Notes')
                    ->rules(['regex:/^[a-zA-Z0-9]+$/'])
                    ->maxLength(255),
                Forms\Components\Toggle::make('State')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Class_Number')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('Capacity')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Notes')
                    ->searchable(),
                Tables\Columns\IconColumn::make('State')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListClassrooms::route('/'),
            'create' => Pages\CreateClassroom::route('/create'),
            'edit' => Pages\EditClassroom::route('/{record}/edit'),
            'view' => Pages\ViewClassroom::route('/{record}/view'),
            'calendar' => Pages\Calendar::route('{record}/calendar'),
        ];
    }
}
