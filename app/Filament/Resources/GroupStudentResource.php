<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\Repeater;
use App\Enums\Evaluationn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Forms\Components;
use Filament\Forms\Constraints;
use App\Filament\Resources\GroupStudentResource\Pages;
use App\Models\Evaluation;
use App\Models\Group;
use App\Models\GroupStudent;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class GroupStudentResource extends Resource
{
    protected static ?string $model = GroupStudent::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function sidebar(Model $record): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            ->setTitle($record->group['Course'])
            ->setDescription($record->group['Group_number'])
            ->sidebarNavigation()
            ->setNavigationItems([
                PageNavigationItem::make('View Student')
                    ->url(function () use ($record) {
                        return static::getUrl('view', ['record' => $record->id]);
                    }),
                PageNavigationItem::make('Edit Student')
                    ->url(function () use ($record) {
                        return static::getUrl('edit', ['record' => $record->id]);
                    }),
            ]);
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make('Information')
                ->schema([
                    Select::make('group_id')
                        ->relationship('group', 'Group_number')
                        ->required()
                        ->native(false)
                        ->default(function ($livewire) {
                            if ($livewire->record) {
                                return $livewire->record->group_id;
                            } else {
                                $groupId = session('group_id');
                                if ($groupId) {
                                    $group = Group::query()->find($groupId);
                                    if ($group) {
                                        Log::info("Default Group Number: {$group->Group_number}");
                                        return $group->id;
                                    } else {
                                        Log::warning("Group with ID $groupId not found.");
                                    }
                                }
                                return null;
                            }
                        })
                        ->disabled(),
                    Select::make('student_id')
                        ->relationship('student', 'Berlitz_NAME')
                        ->required()
                        ->preload()
                        ->searchable(),
                    TextInput::make('Mark')
                        ->required()
                        ->default(0)
                        ->disabled(),
                ]),
            Section::make('Student Evaluation')
                ->schema([
                    Repeater::make('evaluations')
                        ->relationship('evaluations')
                        ->schema([
                            Select::make('student_id')
                                ->relationship('student', 'Berlitz_NAME')
                                ->default(function ($livewire) {
                                    return $livewire->record ? $livewire->record->student_id : null;
                                })
                                ->required()
                                ->preload()
                                ->searchable(),
                            ToggleButtons::make('Participation')
                                ->options(Evaluationn::class)
                                ->required()
                                ->inline()
                                ->columnSpan([
                                    'md' => 5,
                                ]),
                            ToggleButtons::make('Vocabulary')
                                ->options(Evaluationn::class)
                                ->required()
                                ->inline()
                                ->columnSpan([
                                    'md' => 5,
                                ]),
                            ToggleButtons::make('Behaviour')
                                ->options(Evaluationn::class)
                                ->required()
                                ->inline()
                                ->columnSpan([
                                    'md' => 5,
                                ]),
                            ToggleButtons::make('Forming_Q_S')
                                ->options(Evaluationn::class)
                                ->required()
                                ->inline()
                                ->columnSpan([
                                    'md' => 5,
                                ]),
                            MarkdownEditor::make('Notes')
                                ->required()
                                ->columnSpan([
                                    'md' => 5,
                                ]),
                        ]),
                ]),
        ]);
}


    public static function getEloquentQuery(): Builder
    {
        $groupId = session('group_id'); // Retrieve group ID from session
        return parent::getEloquentQuery()->where('group_id', $groupId);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.Course')
                    ->label('Course'),
                Tables\Columns\TextColumn::make('group.Group_number')
                    ->label('Group Number')
                    ->badge(),
                Tables\Columns\TextColumn::make('student.Berlitz_NAME'),
                Tables\Columns\TextColumn::make('Mark')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                ActionsActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Action::make('Move To')
                        ->icon('heroicon-o-arrow-uturn-right')
                        ->form(function (Form $form) {
                            return $form->schema([
                                Select::make('Group')
                                    ->native(false)
                                    ->searchable()
                                    ->options(function () {
                                        return Group::pluck('Group_number', 'id')->toArray();
                                    })
                                    ->required(),
                            ]);
                        })
                        ->action(function (GroupStudent $record, array $data) {
                            // Update the group_id of the existing record to the new group
                            $record->group_id = $data['Group'];
                            $record->save();
                        }),
                    Action::make('Evaluation')
                        ->icon('heroicon-o-plus')
                        ->form(function (Form $form) {
                            // Convert enum cases to an associative array for options
                            $evaluationOptions = collect(Evaluationn::cases())
                                ->mapWithKeys(fn(Evaluationn $evaluation) => [$evaluation->value => $evaluation->name])
                                ->toArray();

                            return $form->schema([
                                ToggleButtons::make('Participation')
                                    ->inline()
                                    ->options(Evaluationn::class)
                                    ->required(),
                                ToggleButtons::make('Vocabulary')
                                    ->inline()
                                    ->options(Evaluationn::class)
                                    ->required(),
                                ToggleButtons::make('Behaviour')
                                    ->inline()
                                    ->options(Evaluationn::class)
                                    ->required(),
                                ToggleButtons::make('Forming_Q_S')
                                    ->inline()
                                    ->options(Evaluationn::class)
                                    ->required(),
                                MarkdownEditor::make('Notes')
                                    ->maxLength(5000)
                                    ->required()
                                    ->columnSpanFull(),
                            ]);
                        })
                        ->action(function (GroupStudent $record, array $data) {
                            // Retrieve the Berlitz_NAME from the associated student
                            $instituteName = $record->student->Berlitz_NAME;

                            // Create a new evaluation record with the submitted data
                            Evaluation::create([
                                'group_student_id' => $record->id,
                                'Participation' => $data['Participation'],
                                'Vocabulary' => $data['Vocabulary'],
                                'Behaviour' => $data['Behaviour'],
                                'Forming_Q_S' => $data['Forming_Q_S'],
                                'Notes' => $data['Notes'],
                                //'institute_name' => $instituteName, // Set institute_name to Berlitz_NAME of the associated student
                            ]);
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroupStudents::route('/'),
            'create' => Pages\CreateGroupStudent::route('/create'),
            'edit' => Pages\EditGroupStudent::route('/{record}/edit'),
            'view' => Pages\ViewGroupStudent::route('/{record}/view'),
        ];
    }
}