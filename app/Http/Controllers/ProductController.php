<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

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
    $validatedData = $request->validate([
        'title'                 => 'required|string',
        'summary'               => 'required|string',
        'description'           => 'nullable|string',
        'photo'                 => 'required|string',
        'size'                  => 'nullable|array',
        'stock'                 => 'required|numeric',
        'cat_id'                => 'required|exists:categories,id',
        'brand_id'              => 'nullable|exists:brands,id',
        'child_cat_id'          => 'nullable|exists:categories,id',
        'is_featured'           => 'sometimes|in:1',
        'status'                => 'required|in:active,inactive',
        'condition'             => 'required|in:default,new,hot',
        'price'                 => 'required|numeric',
        'discount'              => 'nullable|numeric',

        // JSON properties
        'properties'            => 'nullable|array',
        'properties.key.*'      => 'required_with:properties|string',
        'properties.value.*'    => 'required_with:properties|string',
    ]);

    // 2) TRANSFORM properties → single associative array
    if (! empty($validatedData['properties'])) {
        $props = [];
        foreach ($validatedData['properties']['key'] as $i => $name) {
            $props[$name] = $validatedData['properties']['value'][$i] ?? null;
        }
        $validatedData['properties'] = $props;
    } else {
        // ensure it's null if nothing provided
        $validatedData['properties'] = null;
    }

    // 3) GENERATE SLUG & HANDLE is_featured
    $validatedData['slug']        = generateUniqueSlug($validatedData['title'], Product::class);
    $validatedData['is_featured'] = $request->has('is_featured') ? 1 : 0;

    // 4) HANDLE size array → comma string (if you still need it)
    if (! empty($validatedData['size'])) {
        $validatedData['size'] = implode(',', $validatedData['size']);
    } else {
        $validatedData['size'] = '';
    }

    // 5) CREATE
    $product = Product::create($validatedData);

    // 6) REDIRECT
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
