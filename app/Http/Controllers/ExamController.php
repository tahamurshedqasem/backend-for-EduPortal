<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Exam;

class ExamController extends Controller
{
    private $flaskUrl = "http://127.0.0.1:5000"; // 🔗 Flask API endpoint

    /**
     * 🟢 1. Get questions from Flask
     */
    public function generateQuestions(Request $request)
    {
        $request->validate([
            'examType' => 'required|string',
            'grade'    => 'required|string',
            'subject'  => 'required|string',
            'count'    => 'required|integer',
            'lang'     => 'nullable|string|in:en,ar', // ✅ support language
        ]);

        try {
            $response = Http::post("{$this->flaskUrl}/generate-questions", [
                "examType" => $request->examType,
                "grade"    => $request->grade,
                "subject"  => $request->subject,
                "count"    => $request->count,
                "lang"     => $request->lang ?? "en", // ✅ forward to Flask
            ]);

            if ($response->failed()) {
                return response()->json([
                    "error"   => "Flask service failed",
                    "details" => $response->body()
                ], $response->status());
            }

            $json = $response->json();

            if (isset($json['questions']) && is_string($json['questions'])) {
                $decoded = json_decode($json['questions'], true);
                if ($decoded !== null) {
                    $json['questions'] = $decoded;
                }
            }

            return response()->json($json, 200);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }


    /**
     * 🟢 2. Send answers to Flask & save score in DB
     */
    public function evaluate(Request $request)
    {
        $request->validate([
            'examType' => 'required|string',
            'grade'    => 'required|string',
            'subject'  => 'required|string',
            'answers'  => 'required|array',
            'lang'     => 'nullable|string|in:en,ar', // ✅ support language
        ]);

        $user = auth()->user();

        $response = Http::post("{$this->flaskUrl}/evaluate", [
            'examType'  => $request->examType,
            'grade'     => $request->grade,
            'subject'   => $request->subject,
            'student_id'=> $user->id,
            'answers'   => $request->answers,
            'lang'      => $request->lang ?? "en", // ✅ forward to Flask
        ]);

        if (!$response->ok()) {
            return response()->json([
                'message' => 'Flask service error',
                'details' => $response->body()
            ], 500);
        }

        $data = $response->json();

        Exam::create([
            'user_id'   => $user->id,
            'exam_type' => $request->examType,
            'grade'     => $request->grade,
            'subject'   => $request->subject,
            'score'     => $data['score'] ?? null,
        ]);

        return response()->json([
            'message'  => 'Exam evaluated successfully',
            'score'    => $data['score'] ?? null,
            'feedback' => $data['feedback'] ?? null
        ]);
    }
}
