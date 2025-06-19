<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::getAllProduct();
        return view('backend.product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $brands = Brand::get();
        $categories = Category::where('is_parent', 1)->get();
        return view('backend.product.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
{
    // 1) VALIDATE
    $validated = $request->validate([
        'title'                  => 'required|string',
        'summary'                => 'required|string',
        'description'            => 'nullable|string',
        'photo'                  => 'required|string',
        'size'                   => 'nullable|array',
        'stock'                  => 'required|numeric',
        'cat_id'                 => 'required|exists:categories,id',
        'brand_id'               => 'nullable|exists:brands,id',
        'child_cat_id'           => 'nullable|exists:categories,id',
        'is_featured'            => 'sometimes|in:1',
        'status'                 => 'required|in:active,inactive',
        'condition'              => 'required|in:default,new,hot',
        'price'                  => 'required|numeric',
        'discount'               => 'nullable|numeric',

        // relational attributes input:
        'attributes'             => 'nullable|array',
        'attributes.*.name'      => 'required_with:attributes|string',
        'attributes.*.values'    => 'required_with:attributes|array|min:1',
        'attributes.*.values.*'  => 'required|string',
    ]);

    // 2) SLUG & FEATURED FLAG
    $validated['slug']         = generateUniqueSlug($validated['title'], Product::class);
    $validated['is_featured']  = $request->has('is_featured') ? 1 : 0;

    // 3) SIZE ARRAY → STRING
    if (! empty($validated['size'])) {
        $validated['size'] = implode(',', $validated['size']);
    } else {
        $validated['size'] = '';
    }

    // 4) CREATE THE PRODUCT
    $product = Product::create(Arr::only($validated, [
        'title','slug','summary','description','photo',
        'size','stock','cat_id','child_cat_id',
        'brand_id','status','condition','price','discount',
        'is_featured'
    ]));

    // 5) SAVE EACH ATTRIBUTE + ITS VALUES
    if ($product && ! empty($validated['attributes'])) {
        foreach ($validated['attributes'] as $attr) {
            // create the attribute row
            $pa = $product->attributes()->create([
                'name' => $attr['name']
            ]);

            // then create each value
            foreach ($attr['values'] as $value) {
                $pa->values()->create(['value' => $value]);
            }
        }
    }

    // 6) REDIRECT WITH FEEDBACK
    $message = $product
        ? 'Product successfully added.'
        : 'There was an error; please try again.';

    return redirect()
        ->route('product.index')
        ->with($product ? 'success' : 'error', $message);
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Implement if needed
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $brands = Brand::get();
        $product = Product::findOrFail($id);
        $categories = Category::where('is_parent', 1)->get();
        $items = Product::where('id', $id)->get();

        return view('backend.product.edit', compact('product', 'brands', 'categories', 'items'));
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
        $product = Product::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|string',
            'summary' => 'required|string',
            'description' => 'nullable|string',
            'photo' => 'required|string',
            'size' => 'nullable',
            'stock' => 'required|numeric',
            'cat_id' => 'required|exists:categories,id',
            'child_cat_id' => 'nullable|exists:categories,id',
            'is_featured' => 'sometimes|in:1',
            'brand_id' => 'nullable|exists:brands,id',
            'status' => 'required|in:active,inactive',
            'condition' => 'required|in:default,new,hot',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
        ]);

        $validatedData['is_featured'] = $request->input('is_featured', 0);

        if ($request->has('size')) {
            $validatedData['size'] = implode(',', $request->input('size'));
        } else {
            $validatedData['size'] = '';
        }

        $status = $product->update($validatedData);

        $message = $status
            ? 'Product Successfully updated'
            : 'Please try again!!';

        return redirect()->route('product.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $status = $product->delete();

        $message = $status
            ? 'Product successfully deleted'
            : 'Error while deleting product';

        return redirect()->route('product.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }
}
