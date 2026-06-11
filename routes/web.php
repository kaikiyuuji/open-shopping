<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\EstabelecimentoController;
use App\Http\Controllers\ItemCompraController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('estabelecimentos/{estabelecimento}/compras', [EstabelecimentoController::class, 'compras'])
    ->name('estabelecimentos.compras');

Route::resource('estabelecimentos', EstabelecimentoController::class)
    ->parameters(['estabelecimentos' => 'estabelecimento']);

Route::resource('produtos', ProdutoController::class)
    ->parameters(['produtos' => 'produto']);

Route::resource('compras', CompraController::class)
    ->parameters(['compras' => 'compra']);

Route::resource('compras.itens', ItemCompraController::class)
    ->parameters(['compras' => 'compra', 'itens' => 'item'])
    ->except(['index', 'show']);

Route::post('compras/{compra}/ocr', [App\Http\Controllers\ExtracaoOcrController::class, 'store'])
    ->name('compras.ocr.store');
Route::get('compras/{compra}/ocr/{extracao}/revisar', [App\Http\Controllers\ExtracaoOcrController::class, 'revisar'])
    ->name('compras.ocr.revisar');
Route::post('compras/{compra}/ocr/{extracao}/confirmar', [App\Http\Controllers\ExtracaoOcrController::class, 'confirmar'])
    ->name('compras.ocr.confirmar');
Route::delete('compras/{compra}/ocr/{extracao}', [App\Http\Controllers\ExtracaoOcrController::class, 'destroy'])
    ->name('compras.ocr.destroy');
