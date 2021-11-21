<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Mail;

class ProductController extends Controller
{

    public function register(Request $request){
        $fields = $request->validate([
             'name' => 'required|string',
             'email' => 'required|string|unique:users,email',
             'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
             'name' => $fields['name'],
             'email' => $fields['email'],
             'password' => bcrypt($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
             'user' => $user,
             'token' => $token
        ];

        return response($response, 201);
    }

    public function login(Request $request){
        $fields = $request->validate([
             'email' => 'required|string',
             'password' => 'required|string',
        ]);
        
        // check email
        $user = User::where('email', $fields['email'])->first();

        // check password
        if(!$user || !Hash::check($fields['password'], $user->password)){
           return response([
              'message' => 'Bed creds'
           ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
             'user' => $user,
             'token' => $token
        ];

        return response($response, 201);
    }

    public function logout(Request $request){
         auth()->user()->tokens()->delete();

         return [
            'message' => 'Logged Out'
         ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productdetails = Product::all();
        if(!empty($productdetails)){
            return response()->json(['status' => 200, 'message' => 'success', 'data' =>$productdetails]);
        }else{
            return response()->json(['status' => 201, 'message' => 'some thing went wrong', 'data' => '']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
             'category_id' => 'required',
             'name' => 'required',
             'slug' => 'required',
             'price' => 'required',
             'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

//print_r($_POST);die;
// echo $request->price;
// echo $request->slug;die;


        $input = $request->all();

        if ($image = $request->file('image')) {
            $destinationPath = 'image/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $input['image'] = "$profileImage";
        }
            
           return Product::create($input);
        //return Product::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Product::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $product = Product::find($id);
        $product->update($request->all());
        return $product;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Product::destroy($id);
    }

    public function search($search)
    {
        return Product::where('name','like','%'.$search.'%')
               ->orwhere('price','like','%'.$search.'%')->get();
    }

    public function getproductbycategory(){
        //DB::enableQueryLog();

        $productlist = DB::table('categories')
        ->select('products.*','categories.name as category_name')
        ->join('products','products.category_id','=','categories.id')
        ->groupBy('products.category_id')
        ->orderBy('products.category_id','desc')
        ->get();

        //$productlist = Category::join('products','products.category_id','=','categories.id')->get(['products.*','categories.name as category_name']);
        
        //dd(DB::getQueryLog());  //this is for last query print
         return $productlist;
    }

    public function download($id)
    {
        $image = Product::find($id);
        //echo $image['image'];die();
        //$file = public_path(). "/image/$image['image']";

        $file = public_path(). "/image/".$image['image']."";
        //$file = public_path(). "/image/20211103163142.jpg";

        $headers = ['Content-Type: image/jpeg'];

        $to = 'yourmail@gmail.com';
        $name = 'test';
        $message = 'hello demo api';
        $subject = 'Testing purpose mail';

        $this->email($to,$name,$message,$subject);
        return \Response::download($file, $image['image'], $headers);
    }

    public function email($to,$name,$message,$subject){
        
        $data = array("name"=>$name, "data"=>$message );
        $user['to'] = $to;
        $user['subject'] = $subject;

        Mail::send('mail',$data, function($message) use( $user)
        {
            $message->to($user['to']);
            $message->subject($user['subject']);
        });

        return true;
    }
}