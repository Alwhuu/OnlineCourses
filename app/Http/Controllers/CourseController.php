<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Foundation\Exceptions\Renderer\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Course::with(['category', 'teacher','students'])->orderByDesc('id');
        // mendapatkan seluruh data kelas dan menampilkannya

        if ($user->hasRole('teacher')) {
                $query->whereHas('teacher', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
            });
        }
        $courses = $query->paginate(5);
        // dd($course);
        return view('admin.courses.index',compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.courses.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request)
    {
        // dd($request->all());
        try {
            $teacher =Teacher::where('user_id',Auth::user()->id)->first();
            // Log::info('User ID yang login: ', ['user_id' => Auth::user()->id]);
            // Log::info('mencoba membuat kursus',$request->validated());
            if (!$teacher) {
                // Log::warning('Anda bukan seorang guru yang berwenang', ['user_id' => Auth::user()->id]);
                return redirect()->route('admin.courses.index')->withErrors('Anda Bukan Seorang Guru Yang Berwenang disini');
            }

            DB::transaction(function ()use ($request, $teacher) {

                $validated = $request->validated();
    // dd($validated);
    // untuk menyimpan thumbnail
                if ($request->hasFile('thumbnail')) {
                    $thumbnailPath = $request->file('thumbnail')->store('thumbnails','public');
                    $validated['thumbnail'] = $thumbnailPath;
                }
                // untuk mengubah slug sesuai dengan name
                $validated['slug'] = Str::slug($validated['name']);
                $validated['teacher_id'] = $teacher->id;
    // untuk fungsi create
                $course = Course::create($validated);
                // untuk menyimpan data course_keypooints yang berupa array
                Log::info("berhasil membuat kursus", ['course_id' => $course->id]);
                    if (!empty($validated['course_keypoints'])) {
                        foreach ($validated['course_keypoints'] as $keypointText) {
                            if (!empty($keypointText)) {
                                $course->course_keypoints()->create(['name' => $keypointText]);
                            }
                        }
                    }
            });

            return redirect()->route('admin.courses.index')->with('success', 'Course Berhasil Ditambahkan');

        }catch(\Exception $e){

            Log::error('Error saat membuat kursus: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()->withInput()->withErrors($e->getMessage());

        }
    }

    /**
     * Display the specified resource.
*/
    public function show(Course $course)
    {
        return view('admin.courses.show',compact('course'));;
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $categories = Category::all();
        return view('admin.courses.edit',compact('course','categories'));;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        DB::transaction(function() use($request, $course) {

            $validated = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('thumbnails','public');
                $validated['thumbnail'] = $thumbnailPath;
            }

            $validated['slug'] = Str::slug($validated['name']);

            $course->update($validated);

            if (!empty($validated['course_keypoints'])) {
                $course->course_keypoints()->delete();
                foreach ($validated['course_keypoints'] as $keypointText ) {
                    $course->course_keypoints()->create([
                        'name' => $keypointText,
                    ]);
                    // dd($course);
                }
            }
        });

        return redirect()->route('admin.courses.show', $course);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        DB::beginTransaction();
        try {
            $course->delete();
            DB::commit();
            return redirect()->route('admin.courses.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('admin.courses.index')->with('error', 'Terjadinya Sebuah Error');
            //throw $
        }
    }
}