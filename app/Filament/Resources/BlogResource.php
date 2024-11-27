<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Blog;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BlogResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BlogResource\RelationManagers;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('meta_title')
                    ->maxLength(60)
                    ->helperText('Optimal length is 50-60 characters'),
                TinyEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                    Forms\Components\FileUpload::make('cover_image')
                    ->image()
                    ->imageCropAspectRatio('1200:630')
                    ->imageResizeTargetWidth('1200')
                    ->imageResizeTargetHeight('630')
                    ->directory('blog-covers')
                    ->helperText('Recommended dimensions: 1200x630 pixels'),
                    Forms\Components\DateTimePicker::make('published_at')
                    ->label('Publish Date'),
            
                Forms\Components\Textarea::make('meta_description')
                    ->maxLength(160)
                    ->helperText('Optimal length is 150-160 characters'),
                Forms\Components\TextInput::make('meta_keywords')
                    ->helperText('Comma-separated keywords'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\ImageColumn::make('cover_image')
                ->width(50)
                ->height(50),
            Tables\Columns\TextColumn::make('title'),
            Tables\Columns\TextColumn::make('published_at')
                ->dateTime(),
        ])
        ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->defaultSort('published_at', 'desc');
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
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
