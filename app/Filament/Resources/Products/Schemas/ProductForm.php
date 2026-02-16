<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
        ->components([
            Section::make('Product Images')
                ->columnSpanFull()
                ->schema([
                    FileUpload::make('images')
                        ->image()
                        ->multiple()
                        ->directory('products')
                        ->maxFiles(5)
                        ->required(),
                ])
                ->columns(1),
    
            Section::make('Product Information')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->required(),
    
                    Hidden::make('slug'),

                    TextInput::make('price')
                        ->numeric()
                        ->prefix('IDR')
                        ->required(),
    
                    TextInput::make('stock')
                        ->numeric()
                        ->required(),
    
                    ToggleButtons::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->inline()
                    ->live()
                    ->default(true) // 🔥 INI KUNCI UTAMA
                    ->disabled(function ($get) {
                
                        $categoryId = $get('category_id');
                
                        // ✅ CREATE & belum pilih category → BOLEH aktif
                        if (! $categoryId) {
                            return false;
                        }
                
                        // ❌ category dipilih tapi nonaktif
                        return ! Category::where('id', $categoryId)
                            ->where('is_active', true)
                            ->exists();
                    })
                    ->helperText(
                        'Product hanya bisa aktif jika category aktif.'
                    ),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship(
                            name: 'category',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) =>
                            $query->where('is_active', true)
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->columns(2),
    
            Section::make('Description Product')
                ->columnSpanFull()
                ->schema([
                    Textarea::make('description')
                        ->rows(5),
                ]),
        ]);
    }
}
