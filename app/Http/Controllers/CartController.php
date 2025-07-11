<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Add to cart from home page (simple GET request)
     */
    public function addToCart($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('error', 'Please login first');
        }

        // Check stock
        if ($product->stock < 1) {
            return back()->with('error', 'Out of stock, You can add other products.');
        }

        // Calculate price (with discount if any)
        $price = $product->price;
        if ($product->discount > 0) {
            $price = $product->price - ($product->price * $product->discount / 100);
        }

        // Check for existing cart item (without attributes)
        $existingCart = Cart::where('user_id', Auth::id())
                           ->where('order_id', null)
                           ->where('product_id', $product->id)
                           ->where(function($q) {
                               $q->whereNull('attribute_options')
                                 ->orWhere('attribute_options', '')
                                 ->orWhere('attribute_options', '{}')
                                 ->orWhere('attribute_options', '[]');
                           })
                           ->first();

        if ($existingCart) {
            // Update existing cart item
            $existingCart->quantity += 1;
            $existingCart->amount = $existingCart->price * $existingCart->quantity;

            // Check stock again
            if ($existingCart->product->stock < $existingCart->quantity) {
                return back()->with('error', 'Stock not sufficient!');
            }

            $existingCart->save();

            return back()->with('success', 'Product quantity updated in cart.');
        } else {
            // Create new cart item
            $cart = new Cart();
            $cart->user_id = Auth::id();
            $cart->product_id = $product->id;
            $cart->price = $price;
            $cart->quantity = 1;
            $cart->amount = $price * 1;
            $cart->attribute_options = null; // No attributes for simple add to cart

            $cart->save();

            return back()->with('success', 'Product successfully added to cart.');
        }
    }

    public function singleAddToCart(Request $request)
    {
        $request->validate([
            'slug'           => 'required|exists:products,slug',
            'quant'          => 'required|array',
            'quant.*'        => 'integer|min:1',
            'selected_price' => 'nullable|numeric|min:0',
            'attributes'     => 'nullable|array',
            'attributes.*'   => 'nullable|integer',
        ]);

        $product = Product::where('slug', $request->slug)->firstOrFail();
        $quantity = $request->quant[1];

        // Check stock
        if ($product->stock < $quantity) {
            return back()->with('error', 'Out of stock, You can add other products.');
        }

        // Process attributes
        $selectedAttributes = [];
        $requestAttributes = $request->get('attributes', []);

        if (!empty($requestAttributes) && is_array($requestAttributes)) {
            foreach ($requestAttributes as $attrId => $valueId) {
                if (!empty($valueId) && $valueId !== '' && $valueId !== '0' && $valueId !== 0) {
                    $selectedAttributes[(string)$attrId] = (int)$valueId;
                }
            }
        }

        // Calculate price
        $selectedPrice = $request->selected_price;
        if (!$selectedPrice) {
            // Calculate default price with discount
            $selectedPrice = $product->price;
            if ($product->discount > 0) {
                $selectedPrice = $product->price - ($product->price * $product->discount / 100);
            }
        }

        // Build query to check for existing cart item with same attributes
        $query = Cart::where('user_id', Auth::id())
                     ->where('order_id', null)
                     ->where('product_id', $product->id);

        if (!empty($selectedAttributes)) {
            // For items with attributes, we need to match the exact attribute combination
            $attributesJson = json_encode($selectedAttributes);
            $query->whereRaw('attribute_options = ?', [$attributesJson]);
        } else {
            // For items without attributes
            $query->where(function($q) {
                $q->whereNull('attribute_options')
                  ->orWhere('attribute_options', '')
                  ->orWhere('attribute_options', '{}')
                  ->orWhere('attribute_options', '[]');
            });
        }

        $existingCart = $query->first();

        if ($existingCart) {
            // Update existing cart item
            $existingCart->quantity += $quantity;
            $existingCart->amount = $existingCart->price * $existingCart->quantity;

            // Check stock again
            if ($existingCart->product->stock < $existingCart->quantity) {
                return back()->with('error', 'Stock not sufficient!');
            }

            $existingCart->save();

            return back()->with('success', 'Product quantity updated in cart.');
        } else {
            // Create new cart item
            $cart = new Cart();
            $cart->user_id = Auth::id();
            $cart->product_id = $product->id;
            $cart->price = $selectedPrice;
            $cart->quantity = $quantity;
            $cart->amount = $selectedPrice * $quantity;

            // Set attribute_options
            if (!empty($selectedAttributes)) {
                $cart->attribute_options = $selectedAttributes;
            } else {
                $cart->attribute_options = null;
            }

            // Check stock
            if ($cart->product->stock < $quantity) {
                return back()->with('error', 'Stock not sufficient!');
            }

            $cart->save();

            return back()->with('success', 'Product successfully added to cart.');
        }
    }

    public function cartList()
    {
        $carts = Cart::with(['product', 'product.attributes.values'])
                     ->where('user_id', Auth::id())
                     ->whereNull('order_id')
                     ->get();

        return view('frontend.pages.cart', compact('carts'));
    }

    public function cartUpdate(Request $request)
    {
        if ($request->quant) {
            $error = [];
            $success = '';

            foreach ($request->quant as $k => $quant) {
                $id = $request->qty_id[$k];
                $cart = Cart::find($id);

                if ($quant > 0 && $cart) {
                    if ($cart->product->stock < $quant) {
                        $error[] = $cart->product->title;
                    } else {
                        $cart->quantity = $quant;
                        $cart->amount = $cart->price * $quant;
                        $cart->save();
                        $success = 'Cart successfully updated!';
                    }
                }
            }

            if (count($error) > 0) {
                $message = 'Out of stock: ' . implode(',', $error);
                return back()->with('error', $message);
            } else {
                return back()->with('success', $success);
            }
        } else {
            return back()->with('error', 'Cart invalid!');
        }
    }

    public function cartDelete($id)
    {
        $cart = Cart::find($id);
        if ($cart) {
            $cart->delete();
            return back()->with('success', 'Cart successfully removed');
        }
        return back()->with('error', 'Cart not found');
    }


    public function checkout(Request $request){
           return view('frontend.pages.checkout');
    }
}
