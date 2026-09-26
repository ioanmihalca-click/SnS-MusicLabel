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
                        Forms\Components\TextInput::make('role')
                            ->maxLength(255)
                            ->placeholder('DJ / producer duo'),
                        Forms\Components\TextInput::make('origin')
                            ->maxLength(255)
                            ->placeholder('Stockholm, Sweden'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Photo')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->hiddenLabel()
                            ->image()
                            ->imageCropAspectRatio('4:5')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('1500')
                            ->directory('artist-photos')
                            ->imagePreviewHeight('250')
                            ->helperText('Portrait (4:5), resized to 1200×1500.'),
                    ]),

                Forms\Components\Section::make('Links')
                    ->schema([
                        Forms\Components\TextInput::make('spotify_url')
                            ->label('Spotify')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://open.spotify.com/artist/...'),
                        Forms\Components\TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.instagram.com/...'),
                        Forms\Components\TextInput::make('soundcloud_url')
                            ->label('SoundCloud')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://soundcloud.com/...'),
                        Forms\Components\TextInput::make('beatport_url')
                            ->label('Beatport')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.beatport.com/artist/...'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Highlights')
                    ->description('Short facts for the artist page, e.g. charts, key DJ support or airplay.')
                    ->schema([
                        Forms\Components\Repeater::make('highlights')
                            ->hiddenLabel()
                            ->simple(
                                Forms\Components\TextInput::make('highlight')
                                    ->required()
                                    ->maxLength(255),
                            )
                            ->defaultItems(0)
                            ->addActionLabel('Add highlight'),
                    ]),

                Forms\Components\Section::make('Press kit')
                    ->schema([
                        Forms\Components\FileUpload::make('press_kit')
                            ->hiddenLabel()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->directory('press-kits')
                            ->downloadable()
                            ->helperText('PDF, up to 10 MB.'),
                    ]),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ]),

                Forms\Components\Section::make('Advanced')
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->helperText('Leave empty to generate it from the name. Changing it changes the public URL.'),
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
                Tables\Columns\ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->width(48)
                    ->height(48),
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
