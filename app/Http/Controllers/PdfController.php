<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseHasUser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function userCourses($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        $regularCourses = CourseHasUser::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->with('course')
            ->get()
            ->map(function ($courseUser) {
                return [
                    'name' => $courseUser->course->name,
                    'duration' => $courseUser->course->duration ?? 0,
                ];
            })
            ->values();

        $additionalCourses = \App\Models\AdditionalCourse::where('user_id', $userId)
            ->where('status', 'approved')
            ->get()
            ->map(function ($addCourse) {
                return [
                    'name' => $addCourse->name,
                    'duration' => $addCourse->duration ?? 0,
                ];
            })
            ->values();
        $courses = $regularCourses->concat($additionalCourses);

        $totalDuration = $courses->sum('duration');

        $pdf = Pdf::loadView('pdf.user-courses', [
            'user' => $user,
            'courses' => $courses,
            'totalDuration' => $totalDuration,
        ])->setPaper('a4', 'portrait');

        $fileName = 'cursos-' . $user->name . '-' . ($user->surname ?? '') . '.pdf';

        return $pdf->download($fileName);
    }
}
