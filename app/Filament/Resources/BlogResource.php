<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogResource extends Resource
{
    public const EMBEDS_WARNING = 'The editor cannot keep embedded players: saving this post removes them. Use plain Spotify links instead; a Spotify link alone in a paragraph becomes a player on the site.';

    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Embedded players')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->compact()
                    ->visible(fn (?Blog $record): bool => $record !== null && self::hasEmbeds($record))
                    ->schema([
                        Forms\Components\Placeholder::make('embeds_warning')
                            ->hiddenLabel()
                            ->content(self::EMBEDS_WARNING),
                    ]),

                Forms\Components\Section::make('Article')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->imageCropAspectRatio('1200:630')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('630')
                            ->directory('blog-covers')
                            ->imagePreviewHeight('200')
                            ->helperText('Recommended dimensions: 1200x630 pixels'),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->seconds(false)
                            ->helperText('Posts dated in the future are hidden until that date.'),
                    ]),

                Forms\Components\Section::make('SEO')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(60)
                            ->helperText('Optimal length is 50-60 characters.'),
                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(160)
                            ->rows(2)
                            ->helperText('Optimal length is 150-160 characters.'),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->helperText('Comma-separated keywords.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('')
                    ->square()
                    ->width(60)
                    ->height(60),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Live')
                    ->state(fn (Blog $record) => $record->published_at !== null && $record->published_at->isPast())
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('published')
                    ->label('Published only')
                    ->query(fn (Builder $q) => $q->whereNotNull('published_at')->where('published_at', '<=', now())),
                Tables\Filters\Filter::make('scheduled')
                    ->label('Scheduled')
                    ->query(fn (Builder $q) => $q->whereNotNull('published_at')->where('published_at', '>', now())),
                Tables\Filters\Filter::make('draft')
                    ->label('Drafts')
                    ->query(fn (Builder $q) => $q->whereNull('published_at')),
            ])
            ->defaultSort('published_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publishNow')
                        ->label('Publish now')
                        ->icon('heroicon-o-rocket-launch')
                        ->action(fn ($records) => $records->each->update(['published_at' => now()])),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['published_at' => null])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Whether the stored post has iframes (players pasted on the old site),
     * which Filament's rich editor strips on save.
     */
    public static function hasEmbeds(Blog $blog): bool
    {
        return str_contains(strtolower((string) $blog->content), '<iframe');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
