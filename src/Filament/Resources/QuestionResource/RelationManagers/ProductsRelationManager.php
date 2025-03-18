<?php

namespace Gongarce\ProductFaq\Filament\Resources\QuestionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel.product-faq::question.relations.products.title_plural');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->collection(config('lunar.media.collection'))
                    ->conversion('small')
                    ->limit(1)
                    ->square()
                    ->label(''),
                ProductResource::getNameTableColumn(),
                ProductResource::getSkuTableColumn(),
            ])
            ->recordTitleAttribute('attribute_data')
            ->recordTitle(fn(Product $record) => $record->translateAttribute('name'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(
                        __('lunarpanel.product-faq::question.relations.products.actions.attach.label')
                    )
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label(
                                __('lunarpanel.product-faq::question.relations.products.actions.attach.field')
                            )
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                return Product::search($search)
                                    ->get()
                                    ->mapWithKeys(fn (Product $record): array => [$record->getKey() => $record->translateAttribute('name')])
                                    ->all();
                            }),
                    ])
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}