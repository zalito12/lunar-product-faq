<?php

namespace Gongarce\ProductFaq\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Gongarce\ProductFaq\Filament\Resources\QuestionResource;
use Gongarce\ProductFaq\Models\Question;
use Lunar\Admin\Events\ProductCollectionsUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Collection;

class ManageProductQuestionsPage extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'questions';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-faq');
    }

    public function getTitle(): string
    {
        return __('lunarpanel.product-faq::question.label_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel.product-faq::question.label_plural');
    }

    public function form(Form $form): Form
    {
        return QuestionResource::getDefaultForm($form);
    }

    //protected static ?string $recordTitleAttribute = 'text';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->recordTitle(fn (Question $record) => $record->translate('text'))
            ->columns(QuestionResource::getTableColumns())
            ->reorderable('position')
            ->defaultSort('position')
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}