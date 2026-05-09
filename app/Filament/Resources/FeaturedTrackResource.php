<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedTrackResource\Pages;
use App\Models\FeaturedTrack;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeaturedTrackResource extends Resource
{
    protected static ?string $model = FeaturedTrack::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 25;

    protected static ?string $navigationLabel = 'Featured Track';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Track')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('artist_name')
                            ->label('Artist')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Spotify')
                    ->schema([
                        Forms\Components\TextInput::make('spotify_track_url')
                            ->label('Spotify track URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->helperText('Open Spotify, copy the track URL — e.g. https://open.spotify.com/track/XXXX. The embed src is derived automatically.'),
                    ]),

                Forms\Components\Section::make('Display')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->directory('featured-tracks')
                            ->imagePreviewHeight('200')
                            ->panelAspectRatio('1:1')
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('released_at')
                            ->label('Released'),
                        Forms\Components\TextInput::make('order')
                            ->integer()
                            ->default(0)
                            ->helperText('Lower numbers surface first.'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Inactive tracks are hidden from the hero pill.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->square()
                    ->width(60),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->width(60),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('released_at')
                    ->date()
                    ->sortable()
                    ->toggleable(),
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
            'index' => Pages\ListFeaturedTracks::route('/'),
            'create' => Pages\CreateFeaturedTrack::route('/create'),
            'edit' => Pages\EditFeaturedTrack::route('/{record}/edit'),
        ];
    }
}
