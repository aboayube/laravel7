<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;

class PostCategoriesController extends Controller
{  

    public function __construct()
    {
        if (\auth()->check()){
            $this->middleware('auth');
        } else {
            return view('backend.auth.login');
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    
        if (!\auth()->user()->ability('admin', 'manage_post_categories,show_post_categories')) {
            return redirect('admin/index');
        }
         $keyword = (isset(\request()->keyword) && \request()->keyword != '') ? \request()->keyword : null;
        $status = (isset(\request()->status) && \request()->status != '') ? \request()->status : null;
        $sort_by = (isset(\request()->sort_by) && \request()->sort_by != '') ? \request()->sort_by : 'id';
        $order_by = (isset(\request()->order_by) && \request()->order_by != '') ? \request()->order_by : 'desc';
        $limit_by = (isset(\request()->limit_by) && \request()->limit_by != '') ? \request()->limit_by : '10';

        $categories = Category::withCount('posts');
        if ($keyword != null) {
            $categories = $categories->search($keyword);
        }
        if ($status != null) {
            $categories = $categories->whereStatus($status);
        }

        $categories = $categories->orderBy($sort_by, $order_by);
        $categories = $categories->paginate($limit_by);

        return view('backend.post_categories.index', compact('categories'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
          if (!\auth()->user()->ability('admin', 'create_post_categories')) {
            return redirect('admin/index');
        }

        return view('backend.post_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       if (!\auth()->user()->ability('admin', 'create_post_categories')) {
            return redirect('admin/index');
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'status'        => 'required',
        ]);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['name']              = $request->name;
        $data['status']             = $request->status;

        Category::create($data);

        if ($request->status == 1) {
            Cache::forget('global_categories');
        }

        return redirect()->route('admin.post_categories.index')->with([
            'message' => 'Category created successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {    if (!\auth()->user()->ability('admin', 'update_post_categories')) {
            return redirect('admin/index');
        }

        $category = Category::whereId($id)->first();
        return view('backend.post_categories.edit', compact('category'));
   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!\auth()->user()->ability('admin', 'update_post_categories')) {
            return redirect('admin/index');
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'status'        => 'required',
        ]);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = Category::whereId($id)->first();

        if ($category) {
            $data['name']               = $request->name;
            $data['slug']               = null;
            $data['status']             = $request->status;


            $category->update($data);

            Cache::forget('global_categories');

            return redirect()->route('admin.post_categories.index')->with([
                'message' => 'Category updated successfully',
                'alert-type' => 'success',
            ]);

        }
        return redirect()->route('admin.post_categories.index')->with([
            'message' => 'Something was wrong',
            'alert-type' => 'danger',
        ]);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    { if (!\auth()->user()->ability('admin', 'delete_post_categories')) {
            return redirect('admin/index');
        }

        $category = Category::whereId($id)->first();

        foreach ($category->posts as $post) {
            if ($post->media->count() > 0) {
                foreach ($post->media as $media) {
                    if (File::exists('assets/posts/' . $media->file_name)) {
                        unlink('assets/posts/' . $media->file_name);
                    }
                }
            }
        }

        $category->delete();

        return redirect()->route('admin.post_categories.index')->with([
            'message' => 'Category deleted successfully',
            'alert-type' => 'success',
        ]);
    }
   
}
