<?php

namespace Gongarce\ProductFaq\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\RelationManagers\ProductsRelationManager;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\RelationManagers\VariantsRelationManager;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Resources\BaseResource;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource\Pages;
use Gongarce\ProductFaq\Models\Contracts\Question;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;

class QuestionResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';  // TODO: remove me in Filament 3.1

    protected static ?int $navigationSort = 3;

    public static function getLabel(): string
    {
        return __('lunarpanel.product-faq::question.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.product-faq::question.label_plural');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('lunarpanel::product.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema(
                        static::getMainFormComponents(),
                    )
            ])
            ->columns(1);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getAnswerFormComponent(),
        ];
    }

    public static function getNameFormComponent(): Component
    {
        return
            TranslatedText::make('text')
            ->label(__('lunarpanel.product-faq::question.form.text.label'))
            ->required()
            ->autofocus();
    }

    public static function getAnswerFormComponent(): Component
    {
        return TranslatedText::make('answer')
            ->label(__('lunarpanel.product-faq::question.form.answer.label'))
            ->optionRichtext(true)
            ->required();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
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

    public static function getTableColumns(): array
    {
        return [
            TranslatedTextColumn::make('text')
                ->label(
                    __('lunarpanel.product-faq::question.table.text.label')
                )
                ->searchable(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestion::route('/'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
