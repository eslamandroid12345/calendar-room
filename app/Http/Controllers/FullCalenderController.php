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
                ->get(['id', 'title', 'start', 'end']);

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
                    'title' => 'required|numeric',
                    'start' => 'required|date|date_format:Y-m-d',
                    'end' => 'required|date|date_format:Y-m-d',
                ]);
                return $this->addEvent($request);
            case 'update':
                return $this->updateEvent($request);
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
                        'title' => $request->title,
                        'start' => $begin->format("Y-m-d"),
                        'end'   => $begin->modify('+1 day')->format("Y-m-d"),
                    ]);
                }
            }else{
                $event = Event::create([
                    'title' => $request->title,
                    'start' => $begin->format("Y-m-d"),
                    'end'   => $begin->modify('+1 day')->format("Y-m-d"),
                ]);

                $events[] = $event;
            }
        }

        $allEvents = Event::all();

        return response()->json(['newEvents' => $events, 'allEvents' => $allEvents]);
    }

    private function updateEvent(Request $request): JsonResponse
    {
        $event = Event::find($request->id);
        if ($event) {
            $event->update([
                'title' => $request->title,
                'start' => $request->start,
                'end'   => $request->end,
            ]);
            return response()->json($event);
        } else {
            return response()->json(['message' => 'Event not found'], 404);
        }
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
