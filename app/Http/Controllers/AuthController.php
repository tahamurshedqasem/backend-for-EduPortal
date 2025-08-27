<?php


namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 🟦 تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string|min:6|confirmed',
            'age'       => 'nullable|integer',
            'gender'    => 'nullable|string',
            'country'   => 'nullable|string',
            'school'    => 'nullable|string',
            'grade'     => 'nullable|string',
            'preferred_exams' => 'nullable|array',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // ⬅️ إنشاء توكن
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    // 🟦 تسجيل الدخول
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // ⬅️ إنشاء توكن جديد
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    // 🟦 تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
    public function profile(Request $request)
    {
        $user = $request->user();

        // جميع الامتحانات لهذا المستخدم
        $exams = Exam::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['exam_type', 'subject', 'grade', 'score', 'created_at']);

        $totalExams = $exams->count();
        $passed = $exams->where('score', '>=', 50)->count();
        $averageScore = $totalExams > 0 ? round($exams->avg('score')) : 0;

        return response()->json([
            'user' => $user,
            'stats' => [
                'total_exams'   => $totalExams,
                'passed'        => $passed,
                'average_score' => $averageScore,
            ],
            'recent_exams' => $exams->take(10), // آخر 10 امتحانات
        ]);
    }

}

