<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaylistResource\Pages;
use App\Models\Playlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaylistResource extends Resource
{
    protected static ?string $model = Playlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Spotify embed')
                    ->schema([
                        Forms\Components\Textarea::make('spotify_embed_url')
                            ->label('Spotify embed code')
                            ->required()
                            ->autosize()
                            ->rows(4)
                            ->helperText('Paste the full Spotify embed iframe code here.'),
                    ]),

                Forms\Components\Section::make('Display')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->integer()
                            ->default(0)
                            ->helperText('Lower numbers appear first in the slider.'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Inactive playlists are hidden from the homepage slider.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('spotify_embed_url')
                    ->label('Embed')
                    ->limit(40)
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
            'index' => Pages\ListPlaylists::route('/'),
            'create' => Pages\CreatePlaylist::route('/create'),
            'edit' => Pages\EditPlaylist::route('/{record}/edit'),
        ];
    }
}
