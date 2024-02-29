<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;

class FullCalenderController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index(Request $request)
    {

        if($request->ajax()) {

            $data = Event::whereDate('start', '>=', $request->start)
                ->whereDate('end',   '<=', $request->end)
                ->get(['id', 'room_number','room_price', 'start', 'end']);

            return response()->json($data);
        }

        return view('fullcalender');
    }

    /**
     * Write code on Method
     *
     * @return \Illuminate\Http\JsonResponse()
     */

    public function ajax(Request $request): JsonResponse
    {

        switch ($request->type) {
            case 'add':
                $request->validate([
                    'room_number' => 'required|numeric',
                    'room_price' => 'required|numeric',
                    'start' => 'required|date|date_format:Y-m-d|after_or_equal:today',
                    'end' => 'required|date|date_format:Y-m-d|after:start',
                ]);
                return $this->addEvent($request);
            case 'delete':
                return $this->deleteEvent($request);
            default:
                return response()->json(['error' => 'Invalid request type']);
        }
    }

    private function addEvent(Request $request): JsonResponse
    {
        $begin = new \DateTime($request->start);
        $end   = new \DateTime($request->end);

        $events = [];

        while ($begin < $end) {

            $updateMultipleEvents = Event::query()
                ->whereDate('start', '>=', $begin)
                ->whereDate('end',   '<=', $end)
                ->get();

            if ($updateMultipleEvents->count() > 0) {
                foreach ($updateMultipleEvents as $eventM) {
                    $eventM->update([
                        'room_number' => $request->room_number,
                        'room_price' => $request->room_price,
                        'start' => $begin->format("Y-m-d"),
                        'end'   => $begin->modify('+1 day')->format("Y-m-d"),
                    ]);
                }
            }else{
                $event = Event::create([
                    'room_number' => $request->room_number,
                    'room_price' => $request->room_price,
                    'start' => $begin->format("Y-m-d"),
                    'end'   => $begin->modify('+1 day')->format("Y-m-d"),
                ]);

                $events[] = $event;
            }
        }

        $allEvents = Event::all();

        return response()->json(['newEvents' => $events, 'allEvents' => $allEvents]);
    }


    private function deleteEvent(Request $request): JsonResponse
    {
        $event = Event::find($request->id);
        if ($event) {
            $event->delete();
            return response()->json($event);
        } else {
            return response()->json(['error' => 'Event not found'], 404);
        }
    }

}
