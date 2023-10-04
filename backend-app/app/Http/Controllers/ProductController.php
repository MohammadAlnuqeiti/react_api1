<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return Product::all() ->  لا يحبب استخدامها لانه بجيب كل المعلومات من الداتا بيس ولا ينصح به في اي بي اي

        return Product::select('id' , 'title' , 'description' , 'image')->get();
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

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image'
        ]);

        $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('product/image',$request->image,$imageName); //بحدد الصورة اللي بدي احفضها و بأي اسم اريد ان أحفظها به
        // Storage::putFileAs('product/image', $request->image, $imageName);
        Product::create($request->post() + ['image' => $imageName]);
        return response()->json([
            'message' => 'Item added successfully'
        ]);

    }


    public function show(Product $product)
    {
        return response()->json([
            'product' => $product
        ]);
    }


    public function edit(Product $product)
    {
        //
    }


    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable' // لأنه في حال ما غير الصورة يحتفظ بالصورة الاصلية
        ]);

        $product->fill($request->post())->update();

        if($request->hasFile('image')){

            if ($product->image) {

                $exist = Storage::disk('public')->exists('product/image/'.$product->image); //للبحث عن الصورة
                // or  $exist = Storage::disk('public')->exists("product/image/{$request->image}");
                if ($exist) {
                    Storage::disk('public')->delete("product/image/{$product->image}");
                }

            }
            $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('product/image',$request->image,$imageName);
            // Storage::putFileAs('product/image', $request->image, $imageName);
            $product->image = $imageName;
            $product->save();

        }
        return response()->json([
            'message' => 'Item updated successfully'
        ]);
    }

    public function destroy(Product $product)
    {
        // لحذف الصورة
        if ($product->image) {
            $exist = Storage::disk('public')->exists('product/image/'.$product->image); //للبحث عن الصورة
            // or  $exist = Storage::disk('public')->exists("product/image/{$request->image}");
            if ($exist) {
                $exist = Storage::disk('public')->delete("product/image/{$product->image}");
            }
        }

        $product->delete();

        return response()->json([
            'message' => 'Item deleted successfully'
        ]);
    }
}
