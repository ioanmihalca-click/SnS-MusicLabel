<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReleaseResource\Pages;
use App\Models\Release;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Spotify')
                    ->schema([
                        Forms\Components\Textarea::make('spotify_embed_code')
                            ->label('Spotify embed code')
                            ->helperText('Paste the full Spotify embed iframe code here.')
                            ->autosize()
                            ->rows(4),
                    ]),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('spotify_embed_code')
                    ->label('Embed')
                    ->limit(40)
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('has_description')
                    ->label('Has description')
                    ->state(fn (Release $record) => filled(strip_tags((string) $record->description)))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_description')
                    ->label('Has description')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('description')->where('description', '!=', ''),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('description')->orWhere('description', '')),
                    ),
            ])
            ->defaultSort('created_at', 'desc')
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
