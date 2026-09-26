<?php

namespace App\Filament\Resources;

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Filament\Resources\ReleaseResource\Pages;
use App\Models\Release;
use App\Support\Duration;
use App\Support\Spotify\SpotifyUrlRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Release')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('artist_display')
                            ->label('Credit')
                            ->maxLength(255)
                            ->placeholder('THK & Pacha Man')
                            ->helperText('The full credit as shown on the site, guests included. Leave empty to use the artists below.'),
                        Forms\Components\Select::make('artists')
                            ->relationship('artists', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Roster artists only. Their pages will list this release.'),
                        Forms\Components\Select::make('format')
                            ->options(ReleaseFormat::class)
                            ->required()
                            ->default(ReleaseFormat::Single->value),
                        Forms\Components\Select::make('genre')
                            ->options(Genre::class),
                        Forms\Components\DatePicker::make('released_at')
                            ->label('Release date'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Listen')
                    ->description('A release can be saved with only the smartlink (pre-save) and get its Spotify link on release day.')
                    ->schema([
                        Forms\Components\TextInput::make('spotify_url')
                            ->label('Spotify URL')
                            ->placeholder('https://open.spotify.com/album/...')
                            ->maxLength(255)
                            ->requiredWithout('smartlink_url')
                            ->rule(new SpotifyUrlRule('album', 'track'))
                            ->helperText('The album or track link from Spotify ("Share" → "Copy link"). It is saved without tracking parameters.'),
                        Forms\Components\TextInput::make('smartlink_url')
                            ->label('Smartlink')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://...')
                            ->helperText('One link to every platform, from the distributor. Also works for pre-save.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Artwork')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Cover')
                            ->image()
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('1200')
                            ->directory('release-covers')
                            ->imagePreviewHeight('200')
                            ->panelAspectRatio('1:1')
                            ->helperText('Square artwork, resized to 1200×1200. Until one is uploaded, the site shows the Spotify thumbnail.'),
                    ]),

                Forms\Components\Section::make('Tracks')
                    ->schema([
                        Forms\Components\Repeater::make('tracks')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('position')
                            ->defaultItems(0)
                            ->addActionLabel('Add track')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('version')
                                    ->maxLength(255)
                                    ->placeholder('Extended Mix'),
                                Forms\Components\TextInput::make('duration_seconds')
                                    ->label('Duration')
                                    ->placeholder('3:45')
                                    ->regex(Duration::PATTERN)
                                    ->validationMessages(['regex' => 'Use minutes:seconds, e.g. 3:45.'])
                                    ->formatStateUsing(fn (mixed $state): mixed => is_numeric($state) ? Duration::format((int) $state) : $state)
                                    ->dehydrateStateUsing(fn (?string $state): ?int => filled($state) ? Duration::toSeconds($state) : null),
                                Forms\Components\TextInput::make('isrc')
                                    ->label('ISRC')
                                    ->maxLength(15),
                            ])
                            ->columns(5)
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                    ]),

                Forms\Components\Section::make('Promotion')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->helperText('Shown in the homepage hero. If several releases are featured, the newest one wins.')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('support')
                            ->label('DJ support')
                            ->placeholder('Add a DJ')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('chart_position')
                            ->maxLength(255)
                            ->placeholder('#59'),
                        Forms\Components\TextInput::make('chart_name')
                            ->maxLength(255)
                            ->placeholder('Beatport Hype'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->hiddenLabel()
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
                            ->helperText('Leave empty to generate it from the title. Changing it changes the public URL.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('artists'))
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('')
                    ->square()
                    ->width(60)
                    ->height(60),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit')
                    ->state(fn (Release $record): string => $record->credit)
                    ->searchable(['artist_display']),
                Tables\Columns\TextColumn::make('format')
                    ->badge(),
                Tables\Columns\TextColumn::make('released_at')
                    ->label('Released')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('format')
                    ->options(ReleaseFormat::class),
                Tables\Filters\SelectFilter::make('genre')
                    ->options(Genre::class),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\SelectFilter::make('artists')
                    ->label('Artist')
                    ->relationship('artists', 'name')
                    ->preload(),
            ])
            ->defaultSort('released_at', 'desc')
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
            'index' => Pages\ListReleases::route('/'),
            'create' => Pages\CreateRelease::route('/create'),
            'edit' => Pages\EditRelease::route('/{record}/edit'),
        ];
    }
}
