<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pdf-editor');
});

Route::get('/pdf-editor', 'PdfEditorController@index')->name('pdf.editor');
Route::post('/pdf-editor/upload', 'PdfEditorController@uploadPdf')->name('pdf.upload');
Route::post('/pdf-editor/upload-image', 'PdfEditorController@uploadImage')->name('image.upload');
Route::post('/pdf-editor/generate-qr', 'PdfEditorController@generateQr')->name('qr.generate');
Route::post('/pdf-editor/save', 'PdfEditorController@save')->name('pdf.save');
Route::get('/pdf-editor/download/{file}', 'PdfEditorController@download')->name('pdf.download');
