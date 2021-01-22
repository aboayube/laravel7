<?php

namespace App\Http\Livewire\Backend;

use Livewire\Component;
use App\Models\Post;
use App\Models\Comment;

class LastPostComments extends Component
{
    public function render()
    {

    	//->take(5) يجيب اخر 5
    	$posts=Post::wherePostType('post')->withCount('comments')->orderBy('id','desc')->take(5)->get();
    	$comments=Comment::orderBy("id",'desc')->take(5)->get();
        return view('livewire.backend.last-post-comments', [
            'posts' => $posts,
            'comments'  => $comments,
        ]);
    }
}
