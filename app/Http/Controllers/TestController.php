<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Classroom_Schedules;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use App\Models\Group;
use App\Models\RegisterCourse;
use App\Models\Schedule;
use App\Models\Serie;
use App\Models\Session;
use App\Models\Subject;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function getDates(Request $request): \Illuminate\Http\JsonResponse
    {
        $records = Session::query()->orWhereBetween('Day',[$request['Date_from'],$request['Date_until']])
            ->where('shifting','=',0)->get();
            return response()->json($records);
    }
}
