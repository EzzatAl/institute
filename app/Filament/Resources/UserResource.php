<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\RelationManagers\PermissionRelationManager;
use App\Models\User;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page as PagesPage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;


    protected static ?string $navigationIcon = 'heroicon-o-users';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Access Management';
    public static function sidebar(Model $record): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            ->sidebarNavigation()
            ->setNavigationItems([
                PageNavigationItem::make('View User')
                    ->url(function () use ($record) {
                        return static::getUrl('view', ['record' => $record->id]);
                    }),
                PageNavigationItem::make('Edit User')
                    ->url(function () use ($record) {
                        return static::getUrl('edit', ['record' => $record->id]);
                    }),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->rules(['regex:/^[A-Za-z0-9\s]+$/']),
//                Forms\Components\Toggle::make('is_admin'),
                Forms\Components\TextInput::make('email')
                ->email()
                ->placeholder('Example@gmail.com')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->dehydrateStateUsing(static fn (null|string $state):
                    null|string =>
                    filled($state) ? Hash::make($state) : null
                    )
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (null|string $state): bool =>
                    filled($state),
                    )
                    ->label(static fn (PagesPage $livewire): string =>
                    ($livewire instanceof Pages\EditUser) ? "New Password" : "Password"
                    ),
                Forms\Components\Select::make('role_id')
                ->relationship('roles','name')
                ->required()
                ->preload()
                ->placeholder("User's Role")
                ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('roles.name')
                ->searchable(),
//                Tables\Columns\IconColumn::make('is_admin')
//                    ->toggleable(isToggledHiddenByDefault: true)
//                    ->boolean(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('theme'),
                Tables\Columns\TextColumn::make('theme_color'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                ->relationship('roles','name'),
                Tables\Filters\Filter::make('created_at'),
                Tables\Filters\Filter::make('updated_at'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
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
                    PermissionRelationManager::class
            ];
    }

    public static function getPages(): array
    {
        return [
            'index' => UserResource\Pages\ListUsers::route('/'),
            'edit' => UserResource\Pages\EditUser::route('/{record}/edit'),
            'view' => UserResource\Pages\ViewUsers::route('/{record}/view'),
        ];
    }
}

