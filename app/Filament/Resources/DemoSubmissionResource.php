<?php

namespace App\Filament\Resources;

use App\Enums\DemoStatus;
use App\Filament\Resources\DemoSubmissionResource\Pages;
use App\Models\DemoSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * The demos sent through /demos. What the artist sent is read-only; the
 * label sets the status and keeps its own notes. Demos are only created by
 * the public form.
 */
class DemoSubmissionResource extends Resource
{
    protected static ?string $model = DemoSubmission::class;

    protected static ?string $modelLabel = 'demo';

    protected static ?string $navigationLabel = 'Demos';

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Label';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'artist_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Demo')
                    ->description('As sent by the artist.')
                    ->schema([
                        Forms\Components\TextInput::make('artist_name')
                            ->label('Artist or project')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->disabled(),
                        Forms\Components\TextInput::make('link')
                            ->label('Private link')
                            ->disabled()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('openLink')
                                    ->label('Open')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->url(fn (DemoSubmission $record): string => $record->link, shouldOpenInNewTab: true),
                            )
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('genre')
                            ->disabled()
                            ->formatStateUsing(fn (DemoSubmission $record): ?string => $record->genreLabel()),
                        Forms\Components\TextInput::make('country')
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('rights')
                            ->label('Rights')
                            ->content(fn (DemoSubmission $record): string => $record->rights_confirmed
                                ? 'The artist confirmed they own or control all rights and that any samples are cleared.'
                                : 'Not confirmed.'),
                        Forms\Components\Placeholder::make('received')
                            ->label('Received')
                            ->content(fn (DemoSubmission $record): string => $record->created_at?->format('d.m.Y H:i') ?? ''),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Review')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(DemoStatus::class)
                            ->required()
                            ->selectablePlaceholder(false)
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->rows(5)
                            ->maxLength(65535)
                            ->helperText('Only visible in the admin.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('genre')
                    ->formatStateUsing(fn (DemoSubmission $record): ?string => $record->genreLabel()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('link')
                    ->label('Link')
                    ->formatStateUsing(fn (string $state): string => Str::after((string) parse_url($state, PHP_URL_HOST), 'www.'))
                    ->url(fn (DemoSubmission $record): string => $record->link, shouldOpenInNewTab: true)
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->iconPosition('after'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(DemoStatus::class),
                Tables\Filters\SelectFilter::make('genre')
                    ->options(DemoSubmission::genreOptions()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Review'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * The number of demos nobody has listened to yet.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = DemoSubmission::query()->where('status', DemoStatus::New->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New demos';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDemoSubmissions::route('/'),
            'edit' => Pages\EditDemoSubmission::route('/{record}/edit'),
        ];
    }
}
