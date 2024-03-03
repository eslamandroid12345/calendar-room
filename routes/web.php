
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FullCalenderController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::controller(FullCalenderController::class)->group(function(){
    Route::get('fullcalender', 'index')->name('events.create');
    Route::post('fullcalenderAjax', 'ajax')->name('events');
});

Route::get('get-all-events', function (){

    $start = '2024-03-03';
    $end = '2024-03-07';

    $events = \App\Models\Event::query()
    ->select('id','room_number','room_price','start','end')
        ->whereDate('start', '>=', $start)
        ->whereDate('end',   '<=', $end)
        ->get();

    if ($events->contains('room_number', 0)) {
        return [];
    }

//    return $events;

    $prices = [];
    foreach ($events as $event){
        $prices[] = $event->room_price;
    }

    return $prices;

    ############## Sum Prices for days of room
//    $totalRoomPrice = $events->sum('room_price');
//    return $totalRoomPrice;



    ############# Days number #################
//    $begin = new DateTime($start);
//    $end   = new DateTime($end);
//    $different = $begin->diff($end);
//    $days = $different->format('%a');


});
