<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('')
                    ->columns(3)
                    ->schema([
                        ToggleButtons::make('Index type')
                            ->live()
                            ->options([
                                'index' => "Index array",
                                'named' => "Nested and named"
                            ])
                            ->afterStateUpdated(function(?string $state, Get $get, Set $set): void {
                                if ($state === 'index') {
                                    $array = ['abc', 'def', 'ghi'];
                                    $set('named', $array);
                                    $set('unnamed', $array);
                                    $set('input_data', json_encode($array));
                                } else if ($state === 'named')  {
                                    $set('named', [['input'=>'abc'], ['input'=>'def'], ['input'=>'ghi']]);
                                    $set('unnamed', [[''=>'abc'], [''=>'def'], [''=>'ghi']]);
                                    $set('input_data', json_encode([[''=>'abc'], [''=>'def'], [''=>'ghi']]).' AND '.json_encode([['input'=>'abc'], ['input'=>'def'], ['input'=>'ghi']]));
                                }

                                $set('named_data', json_encode($get('named')));
                                $set('unnamed_data', json_encode($get('unnamed')));

                            }),

                        Repeater::make('named')
                            ->label('Named Repeater')
                            ->simple(
                                TextInput::make('input')
                                    ->disabled()
                            ),

                        Repeater::make('unnamed')
                            ->label('Unnamed Repeater')
                            ->simple(
                                TextInput::make('')
                                    ->disabled()
                            )

                        ]),

                Grid::make('display')
                    ->columns(3)
                    ->schema([
                        TextInput::make('input_data')
                            ->label('Input data')
                            ->disabled(),

                        TextInput::make('named_data')
                            ->label('$get Named Data')
                            ->disabled(),

                        TextInput::make('unnamed_data')
                            ->label('$get Unnamed Data')
                            ->disabled(),

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
