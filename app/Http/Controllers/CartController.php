<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Cart;
use Illuminate\Support\Str;
use Helper;
class CartController extends Controller
{
    protected $product=null;
    public function __construct(Product $product){
        $this->product=$product;
    }
public function addToCart(Request $request)
{
    // Debug: Log all incoming request data


    \Log::info('=== ADD TO CART DEBUG ===');
    \Log::info('All Request Data:', $request->all());

    // 1) VALIDATE INPUT
    $data = $request->validate([
        'slug'                => 'required|string|exists:products,slug',
        'quant'               => 'required|array',
        'quant.*'             => 'integer|min:1',
        'attributes'          => 'nullable|array',
        'attributes.*'        => 'nullable|integer', // Changed to nullable
        'selected_price'      => 'required|numeric|min:0',
    ]);

    \Log::info('Validated Data:', $data);

    $product = Product::where('slug', $data['slug'])->firstOrFail();
    $quantity = array_values($data['quant'])[0];

    // 2) Process attributes - filter out empty values
    $attributeOptions = [];
    if (!empty($data['attributes'])) {
        \Log::info('Processing attributes:', $data['attributes']);

        foreach ($data['attributes'] as $attrId => $valueId) {
            \Log::info("Attribute ID: {$attrId}, Value ID: {$valueId}");

            if (!empty($valueId) && $valueId !== '') {
                $attributeOptions[$attrId] = (int)$valueId;
                \Log::info("Added to attributeOptions: {$attrId} => {$valueId}");
            }
        }
    }

    \Log::info('Final attributeOptions:', $attributeOptions);

    // 3) Calculate price
    $finalPrice = $this->calculateAttributePrice($product, $attributeOptions);
    if ($finalPrice === null) {
        $finalPrice = $data['selected_price'];
    }

    \Log::info('Final Price:', $finalPrice);

    // 4) Prepare attribute options for storage
    $attributeOptionsJson = !empty($attributeOptions) ? json_encode($attributeOptions) : null;
    \Log::info('Attribute Options JSON:', $attributeOptionsJson);

    // 5) Check for existing cart item
    $query = Cart::where('user_id', auth()->id())
                 ->whereNull('order_id')
                 ->where('product_id', $product->id);

    if ($attributeOptionsJson) {
        $query->where('attribute_options', $attributeOptionsJson);
    } else {
        $query->whereNull('attribute_options');
    }

    $existing = $query->first();

    if ($existing) {
        \Log::info('Updating existing cart item:', $existing->id);
        $existing->quantity += $quantity;
        $existing->amount = $existing->price * $existing->quantity;

        if ($existing->product->stock < $existing->quantity) {
            return back()->with('error','Stock not sufficient!');
        }

        $existing->save();
    } else {
        \Log::info('Creating new cart item');

        $cartData = [
            'user_id'           => auth()->id(),
            'product_id'        => $product->id,
            'price'             => $finalPrice,
            'quantity'          => $quantity,
            'amount'            => $finalPrice * $quantity,
            'attribute_options' => $attributeOptionsJson,
        ];

        \Log::info('Cart data to be saved:', $cartData);

        $cart = Cart::create($cartData);

        \Log::info('Cart created with ID:', $cart->id);
        \Log::info('Cart attribute_options after save:', $cart->attribute_options);

        // Double check what was actually saved
        $savedCart = Cart::find($cart->id);
        \Log::info('Verification - Cart from DB:', [
            'id' => $savedCart->id,
            'attribute_options' => $savedCart->attribute_options,
            'attribute_options_type' => gettype($savedCart->attribute_options)
        ]);

        if ($cart->product->stock < $quantity) {
            return back()->with('error','Stock not sufficient!');
        }
    }

    session()->flash('success','Product successfully added to cart');
    return back();
}

private function calculateAttributePrice($product, $selectedAttributes)
{
    \Log::info('Calculating price for attributes:', $selectedAttributes);

    if (empty($selectedAttributes)) {
        $price = ($product->price * (100 - ($product->discount ?? 0))) / 100;
        \Log::info('No attributes, using base price:', $price);
        return $price;
    }

    // Get the last selected attribute value
    $lastAttributeValueId = end($selectedAttributes);
    \Log::info('Using last attribute value ID:', $lastAttributeValueId);

    $attributeValue = AttributeValue::find($lastAttributeValueId);
    \Log::info('Found attribute value:', $attributeValue ? $attributeValue->toArray() : 'null');

    if ($attributeValue && $attributeValue->price > 0) {
        $price = ($attributeValue->price * (100 - ($product->discount ?? 0))) / 100;
        \Log::info('Using attribute price:', $price);
        return $price;
    }

    $price = ($product->price * (100 - ($product->discount ?? 0))) / 100;
    \Log::info('Fallback to base price:', $price);
    return $price;
}


public function singleAddToCart(Request $request){
    $request->validate([
        'slug'           => 'required',
        'quant'          => 'required',
        'selected_price' => 'required|numeric',
        'attributes'     => 'nullable|array',
        'attributes.*'   => 'nullable|string',
    ]);

    $product = Product::where('slug', $request->slug)->first();

    if($product->stock < $request->quant[1]){
        return back()->with('error','Out of stock, You can add other products.');
    }

    if (($request->quant[1] < 1) || empty($product)) {
        request()->session()->flash('error','Invalid Products');
        return back();
    }

    // Get the selected price from the form
    $selectedPrice = floatval($request->selected_price);

    // Safely get attributes and filter out empty values
    $selectedAttributes = [];
    if ($request->has('attributes') && is_array($request->attributes)) {
        $selectedAttributes = array_filter($request->attributes, function($value) {
            return !empty($value);
        });
    }

    // Debug - you can remove this later
    \Log::info('Cart Debug', [
        'selected_price' => $selectedPrice,
        'selected_attributes' => $selectedAttributes,
        'product_default_price' => $product->price
    ]);

    // Build query to check for existing cart item with same attributes
    $query = Cart::where('user_id', auth()->user()->id)
                 ->where('order_id', null)
                 ->where('product_id', $product->id);

    if (!empty($selectedAttributes)) {
        $query->where('attribute_options', json_encode($selectedAttributes));
    } else {
        $query->whereNull('attribute_options');
    }

    $already_cart = $query->first();

    if($already_cart) {
        $already_cart->quantity = $already_cart->quantity + $request->quant[1];
        $already_cart->amount = ($selectedPrice * $request->quant[1]) + $already_cart->amount;

        if ($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) {
            return back()->with('error','Stock not sufficient!');
        }

        $already_cart->save();
    } else {
        $cart = new Cart;
        $cart->user_id = auth()->user()->id;
        $cart->product_id = $product->id;
        $cart->price = $selectedPrice; // This should now be the correct selected price
        $cart->quantity = $request->quant[1];
        $cart->amount = ($selectedPrice * $request->quant[1]);
        $cart->attribute_options = !empty($selectedAttributes) ? json_encode($selectedAttributes) : null;

        if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) {
            return back()->with('error','Stock not sufficient!');
        }

        $cart->save();
    }

    request()->session()->flash('success','Product successfully added to cart.');
    return back();
}
    public function cartDelete(Request $request){
        $cart = Cart::find($request->id);
        if ($cart) {
            $cart->delete();
            request()->session()->flash('success','Cart successfully removed');
            return back();
        }
        request()->session()->flash('error','Error please try again');
        return back();
    }

    public function cartUpdate(Request $request){
        // dd($request->all());
        if($request->quant){
            $error = array();
            $success = '';
            // return $request->quant;
            foreach ($request->quant as $k=>$quant) {
                // return $k;
                $id = $request->qty_id[$k];
                // return $id;
                $cart = Cart::find($id);
                // return $cart;
                if($quant > 0 && $cart) {
                    // return $quant;

                    if($cart->product->stock < $quant){
                        request()->session()->flash('error','Out of stock');
                        return back();
                    }
                    $cart->quantity = ($cart->product->stock > $quant) ? $quant  : $cart->product->stock;
                    // return $cart;

                    if ($cart->product->stock <=0) continue;
                    $after_price=($cart->product->price-($cart->product->price*$cart->product->discount)/100);
                    $cart->amount = $after_price * $quant;
                    // return $cart->price;
                    $cart->save();
                    $success = 'Cart successfully updated!';
                }else{
                    $error[] = 'Cart Invalid!';
                }
            }
            return back()->with($error)->with('success', $success);
        }else{
            return back()->with('Cart Invalid!');
        }
    }

    // public function addToCart(Request $request){
    //     // return $request->all();
    //     if(Auth::check()){
    //         $qty=$request->quantity;
    //         $this->product=$this->product->find($request->pro_id);
    //         if($this->product->stock < $qty){
    //             return response(['status'=>false,'msg'=>'Out of stock','data'=>null]);
    //         }
    //         if(!$this->product){
    //             return response(['status'=>false,'msg'=>'Product not found','data'=>null]);
    //         }
    //         // $session_id=session('cart')['session_id'];
    //         // if(empty($session_id)){
    //         //     $session_id=Str::random(30);
    //         //     // dd($session_id);
    //         //     session()->put('session_id',$session_id);
    //         // }
    //         $current_item=array(
    //             'user_id'=>auth()->user()->id,
    //             'id'=>$this->product->id,
    //             // 'session_id'=>$session_id,
    //             'title'=>$this->product->title,
    //             'summary'=>$this->product->summary,
    //             'link'=>route('product-detail',$this->product->slug),
    //             'price'=>$this->product->price,
    //             'photo'=>$this->product->photo,
    //         );

    //         $price=$this->product->price;
    //         if($this->product->discount){
    //             $price=($price-($price*$this->product->discount)/100);
    //         }
    //         $current_item['price']=$price;

    //         $cart=session('cart') ? session('cart') : null;

    //         if($cart){
    //             // if anyone alreay order products
    //             $index=null;
    //             foreach($cart as $key=>$value){
    //                 if($value['id']==$this->product->id){
    //                     $index=$key;
    //                 break;
    //                 }
    //             }
    //             if($index!==null){
    //                 $cart[$index]['quantity']=$qty;
    //                 $cart[$index]['amount']=ceil($qty*$price);
    //                 if($cart[$index]['quantity']<=0){
    //                     unset($cart[$index]);
    //                 }
    //             }
    //             else{
    //                 $current_item['quantity']=$qty;
    //                 $current_item['amount']=ceil($qty*$price);
    //                 $cart[]=$current_item;
    //             }
    //         }
    //         else{
    //             $current_item['quantity']=$qty;
    //             $current_item['amount']=ceil($qty*$price);
    //             $cart[]=$current_item;
    //         }

    //         session()->put('cart',$cart);
    //         return response(['status'=>true,'msg'=>'Cart successfully updated','data'=>$cart]);
    //     }
    //     else{
    //         return response(['status'=>false,'msg'=>'You need to login first','data'=>null]);
    //     }
    // }

    // public function removeCart(Request $request){
    //     $index=$request->index;
    //     // return $index;
    //     $cart=session('cart');
    //     unset($cart[$index]);
    //     session()->put('cart',$cart);
    //     return redirect()->back()->with('success','Successfully remove item');
    // }

    public function checkout(Request $request){
        // $cart=session('cart');
        // $cart_index=\Str::random(10);
        // $sub_total=0;
        // foreach($cart as $cart_item){
        //     $sub_total+=$cart_item['amount'];
        //     $data=array(
        //         'cart_id'=>$cart_index,
        //         'user_id'=>$request->user()->id,
        //         'product_id'=>$cart_item['id'],
        //         'quantity'=>$cart_item['quantity'],
        //         'amount'=>$cart_item['amount'],
        //         'status'=>'new',
        //         'price'=>$cart_item['price'],
        //     );

        //     $cart=new Cart();
        //     $cart->fill($data);
        //     $cart->save();
        // }
        return view('frontend.pages.checkout');
    }
}
