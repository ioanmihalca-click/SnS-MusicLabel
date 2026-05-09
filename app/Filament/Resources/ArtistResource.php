<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Models\Artist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Artist')
                    ->description('Public artist profile shown on the homepage roster.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('order')
                            ->required()
                            ->integer()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),
                        Forms\Components\TextInput::make('spotify_url')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://open.spotify.com/artist/...'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('spotify_url')
                    ->label('Spotify')
                    ->url(fn (Artist $record) => $record->spotify_url, shouldOpenInNewTab: true)
                    ->limit(40)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtists::route('/'),
            'create' => Pages\CreateArtist::route('/create'),
            'edit' => Pages\EditArtist::route('/{record}/edit'),
        ];
    }
}
