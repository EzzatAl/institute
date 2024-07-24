<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Employee;
use App\Models\Media;
use Filament\Forms; 
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Form;
use Filament\Infolists\Components\VideoEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\Toggle::make('VIN')
                    ->label('VIN')
                    ->default(false),
                Forms\Components\MarkdownEditor::make('description')
                    ->maxLength(1024)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('url')
                    ->label('URL')
                    ->placeholder('URL For Publishing')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->directory('image')
                    ->multiple()
                    ->image()
                    ->preserveFilenames()
                    ->enableDownload()
                    ->enableOpen()
                    ->placeholder('Drag & Drop your Image or Browse'),
                Forms\Components\FileUpload::make('video')
                    ->directory('video')
                    ->multiple()
                    ->acceptedFileTypes(['video/*'])
                    ->storeFileNamesIn('video_name')
                    ->preserveFilenames()
                    ->placeholder('Drag & Drop your Video or Browse')
                    ->enableDownload()
                    ->enableOpen(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('title'),
                MarkdownEditor::make('description')
                    ->columnSpanFull(),
                TextEntry::make('url')
                    ->label('URL')
                    ->columnSpanFull()
                    ->url(fn (Media $record): string => '#' . urlencode($record->url)),
                ImageEntry::make('image')
                    ->getStateUsing(fn ($record) => $record->image ? $record->image[0] : null),
                VideoEntry::make('video'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    ImageColumn::make('image')
                        ->height('100%')
                        ->width('100%')
                        ->getStateUsing(fn ($record) => $record->image ? $record->image[0] : null),
                    Stack::make([
                        TextColumn::make('title')
                            ->weight(FontWeight::Bold),
                        TextColumn::make('url')
                            ->formatStateUsing(fn (string $state): string => str($state)->after('://')->ltrim('www.')->trim('/'))
                            ->color('gray')
                            ->limit(30),
                    ]),
                ])->space(3),
                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('description')
                            ->color('gray')
                            ->size('vertical')
                            ->limit(30),
                    ]),
                ])->collapsible(),
            ])
            ->filters([
                //
            ])
            ->contentGrid([
                'md' => 100,
                'xl' => 3,
            ])
            ->paginated([
                10,
                20,
                30,
                'all',
            ])
            ->actions([
                Action::make('visit')
                    ->label('Visit link')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn(Media $record) => $record->url)
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
