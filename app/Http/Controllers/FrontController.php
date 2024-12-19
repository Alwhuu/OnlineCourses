<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubcribeTransactionRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\SubscribeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{
    //
    public function index(){

        $courses = Course::with(['category', 'teacher', 'students'])->orderByDesc('id')->get();

        return view('front.index', compact('courses'));
    }

    // public function category(Category $category )  {
                
    //     // return dd($category);
    // }

    public function details(Course $course){
        return view('front.details',compact('course'));
        // return dd("jai");
    }

    public function learning(Course $course, $courseVideoId)  {

        $user = Auth::user();

        if (!$user->hasActiveSubcription()) {
            return  redirect()->route('front.pricing');
        }

        $video = $course->course_videos()->firstWhere('id', $courseVideoId);

        $user->courses()->syncWithoutDetaching($course->id,['is_active' => true]);

        return view('front.learning', compact('course','video'));

    }
    public function pricing() {
        // return dd("halaman pricing");
        if (Auth::user()->hasActiveSubcription()) {
        return redirect()->route('front.pricing');
        }
    }
    public function checkout(){
        if (Auth::user()->hasActiveSubcription()) {
            return redirect()->route('front.index');
        }

        return view('front.checkout');
    }

    public function checkout_store(StoreSubcribeTransactionRequest $request){

        $user = Auth::user();

        if (Auth::user()->hasActiveSubcription()) {
            return redirect()->route('front.index');
        }

        DB::transaction(function () use ($request , $user) {
            $validated = $request->validated();

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs','public');
                $validated['proof'] = $proofPath;
            }

            $validated['user_id'] = $user->id;
            $validated['total_amount'] = 50000;
            $validated['is_paid'] = false;

            $transaction = SubscribeTransaction::create($validated);
        });

        return redirect()->route('dashboard');
    }

    public function category(Category $category) {
        $courses = $category->courses()->get();
        return view("front.category",compact("courses"));
    }

}
