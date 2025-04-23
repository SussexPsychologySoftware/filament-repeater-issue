<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('simple_repeaters')
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
                                    $set('named_repeater', $array);
                                    $set('unnamed_repeater', $array);
                                    $set('input_data', json_encode($array));
                                } else if ($state === 'named')  {
                                    $set('named_repeater', [['input'=>'abc'], ['input'=>'def'], ['input'=>'ghi']]);
                                    $set('unnamed_repeater', [[''=>'abc'], [''=>'def'], [''=>'ghi']]);
                                    $set('input_data', json_encode([[''=>'abc'], [''=>'def'], [''=>'ghi']]).' AND '.json_encode([['input'=>'abc'], ['input'=>'def'], ['input'=>'ghi']]));
                                }

                                $set('named_data', json_encode($get('named_repeater')));
                                $set('unnamed_data', json_encode($get('unnamed_repeater')));

                            }),

                        Repeater::make('named_repeater')
                            ->label('Named Repeater')
                            ->simple(
                                TextInput::make('input')
                                    ->live()
                                    //->disabled()
                                    ->afterStateHydrated(function (Component $component, ?string $state) {
                                        // Logs only on 'add new' as null
                                        Log::info('Named input hydrated, state:', [$state]);
                                    })
                                    ->afterStateUpdated(function (Component $component, ?string $state) {
                                        // Logs only on 'add new', but with data of previously updated component?
                                        Log::info('Named input updated, state:', [$state]);
                                    })
                            )
                            ->afterStateUpdated(function (Component $component, ?array $state, Get $get, Set $set) {
                                $set('named_data', json_encode($state));
                                Log::info('Named repeater updated, state:', [$state]);
                            }),
//                            ->afterStateHydrated(function (Component $component, array|string|null $state) {
//                                // Never fires - attaching this stops data saving?
//                                Log::info('Named repeater hydrated, state:', [$state]);
//                            }),

                        Repeater::make('unnamed_repeater')
                            ->label('Unnamed Repeater')
                            ->simple(
                                TextInput::make('')
                                    ->live()
                                    //->disabled()
                                    ->afterStateHydrated(function (Component $component, array|string|null $state) { //NOTE array|string $state...
                                        // Logs only on 'add new' as [[]]
                                        Log::info('Unnamed input hydrated, state:', [$state]);
                                    })
                                    ->afterStateUpdated(function (Component $component, ?string $state) {
                                        // Logs only on 'add new', but with data of previously updated component?
                                        Log::info('Unnamed input updated, state:', [$state]);
                                    })
                            )
                            ->afterStateUpdated(function (Component $component, ?array $state, Get $get, Set $set) {
                                $set('unnamed_data', json_encode($state));
                                Log::info('Unnamed repeater updated, state:', [$state]);
                            })
//                            ->afterStateHydrated(function (Component $component, array|string $state) {})
                            ->afterStateHydrated(function (Component $component, array|string $state) {
                                // Never fires
                                Log::info('Unnamed repeater hydrated, state:', [$state]);
                            })

                        ]),

                Grid::make('display')
                    ->columns(3)
                    ->schema([
                        Textarea::make('input_data')
                            ->label('Input data')
                            ->disabled(),

                        Textarea::make('named_data')
                            ->label('Named Data state')
                            ->disabled(),

                        Textarea::make('unnamed_data')
                            ->label('Unnamed Data state')
                            ->disabled(),

                    ]),

                Actions::make([
                    Action::make('exportJson')
                        ->label('Export')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (?Product $record) {
                            //https://v2.filamentphp.com/tricks/add-action-to-table-to-export-record-as-json-file
                            $filename = date('Y-m-d').'.json';
                            // Return a download response
                            return response()->streamDownload(function () use ($record) {
                                echo json_encode($record, JSON_PRETTY_PRINT);
                            }, $filename, [
                                'Content-Type' => 'application/json',
                            ]);
                        }),

                    Action::make('importJson')
                        ->label('Import')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->form([
                            FileUpload::make('file')
                                ->required()
                                ->storeFiles(false)
                                ->acceptedFileTypes(['application/json'])
                                ->helperText('If importing a flow from another user, make sure ')
                        ])
                        ->action(function (array $data, ?Product $record, Set $set, Get $get) {
                            //https://filamentphp.com/docs/3.x/forms/fields/file-upload
                            // For debugging
                            $tempFile = $data['file'];
                            // Get file contents - this works with TemporaryUploadedFile
                            $fileContent = $tempFile->get();
                            $ProductData = json_decode($fileContent, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                Notification::make()
                                    ->title('Invalid JSON file')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            // Set the flow data to the form
                            $set('', $ProductData);
                        })
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
