<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\PostResource;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all posts
        $posts = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }
    /**
     * store
     *
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::create([
            'image' => $image->hashName(),
            'title'=> $request->title,
            'content' => $request->content,
        ]);
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', $post);
    }

    /**
     * show
     *
     * @param mixed $post
     * @return void
     */
    public function show($id)
    {
        //find post by ID
        $post = Post::find($id);

        //return single post as a resource
        return new PostResource(true, 'Detail Data Post!', $post);
    }


    //codingan ini digunakan untuk ngupdate data yang kita sudah masukan
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'title' =>'required | min:10',
            'content' =>'required | min:10',
        ]);

        //check validator
        if($validator->fails()){
            return Response()->json($validator->errors(), 422);
        }

        //get ID
        $post = Post::find($id);

        //check image if no empty
        if($request->hasFile('image')) {

            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/' . basename($post->image));

            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }else{

            //update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        //return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    /**
     * destroy
     *
     * @param mixed $post
     * @return void
     */


     //codingan ini kegunaannnya untuk mendelete data yang kita inginkan
    public function destroy($id)
    {
        //find post by ID
        $post = Post::find($id);

        //delete image
        Storage::delete('public/posts/' . basename($post->image));

        $post->delete();
        return new PostResource(true, 'Data Berhasil Dihapus!', $post);
    }
}
