<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class PostController extends Controller
{
    //------------- [ Create new Post ] ----------------

    public function createPost(Request $request) {

        $img_name = "";

        // validate input
        $validator  =   Validator::make($request->all(),
            [
                'title'              =>      'required',
            ]
        );

        // if validation fails
        if($validator->fails()) {

            return response()->json(["validation errors" => $validator->errors()]);

        }

        // Retrieve User with acccess token
        $user           =       Auth::user();

        // Upload Featured Image   
        $validator      =   Validator::make($request->all(),
           ['featured_img'      =>   'required|mimes:jpeg,png,jpg,bmp|max:2048']);
 
        // if validation fails
        if($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        if($file   =   $request->file('featured_img')) {

            $img_name      =   time().time().'.'.$file->getClientOriginalExtension();

            $target_path    =   public_path('/uploads/');

                $file->move($target_path, $img_name);    
                
                // Creating slug 

                $slug           =       str_replace(" ", "-", strtolower($request->title));

                $slug           =       preg_replace('/[^A-Za-z0-9\-]/', '', $slug);

                $slug           =       preg_replace('/-+/', '-', $slug);

                // creating array of inputs
                $input              =       array(
                    'title'          =>          $request->title,
                    'slug'           =>          $slug,
                    'featured_img'   =>          $img_name,  
                    'user_id'        =>          $user->id
                );

                // save into database
                $post                   =       Post::create($input);
        }

        return response()->json(["success" => true, "data" => $post]);
    }


    // --------- [ Post Listing By User Token ] -------------
    public function postListing() {

        // Get user of access token
        $user           =       Auth::user();

        // Listing post through user id
        $posts          =       Post::where("user_id", $user->id)->get();

        return response()->json(["success" => true, "data" => $posts]);
    }
}
