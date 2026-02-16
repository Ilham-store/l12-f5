<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;


class CategoryResource extends Resource
{
    protected static ?int $navigationSort = 2;
   
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::InboxArrowDown;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) =>
                    $set('slug', Str::slug($state))
                ),

                Hidden::make('slug'),

                ToggleButtons::make('is_active')
                ->boolean()
                ->inline()
                ->required()
                ->disabled(fn ($record) =>
                $record?->products()->exists())
                ->helperText(
                    'Category tidak bisa dinonaktifkan jika masih memiliki product.'
                ),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                ->searchable()
                ->sortable(),

                TextColumn::make('active_products_count')
                ->label('Active Products')
                ->counts([
                    'products as active_products_count' => fn ($query) =>
                        $query->where('is_active', true),
                ])
                ->sortable(),

                IconColumn::make('is_active')
                ->boolean()
                ->label('Active')])
                ->recordActions([Action::make('toggleActive')
                ->label('Toggle')
                ->icon('heroicon-o-power')
                ->action(function ($record) {

                    if (
                        $record->is_active &&
                        $record->products()
                            ->where('is_active', true)
                            ->exists()
                    ) {
                        Notification::make()
                            ->title('Tidak dapat dinonaktifkan')
                            ->body('Category masih memiliki product aktif.')
                            ->status('danger')
                            ->send();

                        return;
                    }

                    $record->update([
                        'is_active' => ! $record->is_active,
                    ]);
                }),
                ])
                
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
