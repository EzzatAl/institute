<?php
namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\PlacementTest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PlacementTests extends BaseWidget
{
    protected static ?string $label = "Placement Test";
    // protected static ?string $title = 'Placement Test';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(PlacementTest::query()->where('status', '=', OrderStatus::Not_yet))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('level.Number_latter'),
                Tables\Columns\TextColumn::make('employee.Full_name')
                ->label('Teacher'),
                Tables\Columns\TextColumn::make('subject.serie'),
                Tables\Columns\TextColumn::make('first_last_name')
                ->label('Student Name'),
                Tables\Columns\TextColumn::make('Email'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('Phone_number'),
                Tables\Columns\TextColumn::make('Home_number'),
                Tables\Columns\TextColumn::make('Notes'),
                Tables\Columns\TextColumn::make('Date_times'),
                Tables\Columns\TextColumn::make('created_at'),
            ])->actions([
                Tables\Actions\ViewAction::make()
                ->form([
                Select::make('level.Number_latter')
                    ->relationship('level', 'Number_latter')
                    ->label('Level')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('employee.Full_name')
                    ->relationship('employee', 'Full_name')
                    ->label('Teacher')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('subject.serie')
                    ->relationship('subject', 'serie')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('first_last_name')
                    ->label('Student Name')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('Email')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('status')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('Phone_number')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('Home_number')
                    ->preload()
                    ->native(false)
                    ->required(),
                TextInput::make('Notes')
                    ->required(),
                DateTimePicker::make('Date_times')
                    ->native(false)
                    ->required(),
                DateTimePicker::make('created_at')
                    ->native(false)
                    ->required(),
            ]),
                Tables\Actions\EditAction::make()
                ->form([
                    Select::make('level.Number_latter')
                        ->relationship('level', 'Number_latter')
                        ->label('Level')
                        ->preload()
                        ->native(false)
                        ->required(),
                    Select::make('employee.Full_name')
                        ->relationship('employee', 'Full_name')
                        ->label('Teacher')
                        ->preload()
                        ->native(false)
                        ->required(),
                    Select::make('subject.serie')
                        ->relationship('subject', 'serie')
                        ->preload()
                        ->native(false)
                        ->required(),
                    Select::make('first_last_name')
                        ->label('Student Name')
                        ->preload()
                        ->native(false)
                        ->required(),
                    Select::make('Email')
                        ->preload()
                        ->native(false)
                        ->required(),
                    Select::make('status')
                        ->preload()
                        ->native(false)
                        ->required(),
                    Select::make('Phone_number')
                        ->preload()
                        ->native(false)
                        ->required(),
                    Select::make('Home_number')
                        ->preload()
                        ->native(false)
                        ->required(),
                    TextInput::make('Notes')
                        ->required(),
                    DateTimePicker::make('Date_times')
                        ->native(false)
                        ->required(),
                    DateTimePicker::make('created_at')
                        ->native(false)
                        ->required(),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->record(fn (PlacementTest $record) => $record),
            ]);
    }
}
