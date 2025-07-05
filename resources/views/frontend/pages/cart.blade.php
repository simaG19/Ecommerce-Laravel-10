@extends('frontend.layouts.master')
@section('title','Cart Page')
@section('main-content')
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="">Cart</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Shopping Cart -->
    <div class="shopping-cart section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Shopping Summery -->
                    <div class="table-responsive">
                        <table class="table shopping-summery">
                            <thead>
                                <tr class="main-hading">
                                    <th>PRODUCT</th>
                                    <th>DETAILS</th>
                                    <th class="text-center">UNIT PRICE</th>
                                    <th class="text-center">QUANTITY</th>
                                    <th class="text-center">TOTAL</th>
                                    <th class="text-center"><i class="ti-trash remove-icon"></i></th>
                                </tr>
                            </thead>
                            <tbody id="cart_item_list">
                                <form action="{{route('cart.update')}}" method="POST">
                                    @csrf
                                    @if(Helper::getAllProductFromCart())
                                        @foreach(Helper::getAllProductFromCart() as $key=>$cart)
                                            <tr class="cart-item">
                                                @php
                                                $photo = explode(',', $cart->product['photo']);
                                                @endphp
                                                <td class="image" data-title="Product">
                                                    <div class="product-image">
                                                        <img src="{{$photo[0]}}" alt="{{$cart->product['title']}}" class="img-fluid">
                                                    </div>
                                                </td>
                                                <td class="product-details" data-title="Details">
                                                    <div class="product-info">
                                                        <h5 class="product-name">
                                                            <a href="{{route('product-detail',$cart->product['slug'])}}" target="_blank">
                                                                {{$cart->product['title']}}
                                                            </a>
                                                        </h5>

                                                        @if($cart['summary'])
                                                            <p class="product-description">{!! Str::limit($cart['summary'], 100) !!}</p>
                                                        @endif

                                                        {{-- Display selected attributes with improved styling --}}
                                                        @if($cart->hasSelectedAttributes())
                                                            @php
                                                                $selectedAttributes = $cart->selectedAttributes;
                                                            @endphp
                                                            @if($selectedAttributes && $selectedAttributes->count() > 0)
                                                                <div class="product-attributes">
                                                                    <div class="attributes-label">
                                                                        <i class="ti-settings"></i>
                                                                        <strong>Selected Options:</strong>
                                                                    </div>
                                                                    <div class="attributes-list">
                                                                        @foreach($selectedAttributes as $attr)
                                                                            <div class="attribute-item">
                                                                                <span class="attribute-badge">
                                                                                    <span class="attr-name">{{$attr['attribute_name']}}:</span>
                                                                                    <span class="attr-value">{{$attr['value']}}</span>
                                                                                    @if($attr['price'] > 0)
                                                                                        <span class="attr-price">(+{{number_format($attr['price'], 2)}} ETB)</span>
                                                                                    @endif
                                                                                </span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="price" data-title="Price">
                                                    <span class="amount">{{number_format($cart['price'], 2)}} ETB</span>
                                                </td>
                                                <td class="qty" data-title="Qty">
                                                    <div class="input-group quantity-controls">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="btn btn-outline-secondary btn-number" data-type="minus" data-field="quant[{{$key}}]">
                                                                <i class="ti-minus"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" name="quant[{{$key}}]" class="form-control input-number text-center" data-min="1" data-max="100" value="{{$cart->quantity}}" readonly>
                                                        <input type="hidden" name="qty_id[]" value="{{$cart->id}}">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary btn-number" data-type="plus" data-field="quant[{{$key}}]">
                                                                <i class="ti-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="total-amount cart_single_price" data-title="Total">
                                                    <span class="money">{{number_format($cart['amount'], 2)}} ETB</span>
                                                </td>
                                                <td class="action" data-title="Remove">
                                                    <a href="{{route('cart-delete',$cart->id)}}" class="remove-item" onclick="return confirm('Are you sure you want to remove this item?')">
                                                        <i class="ti-trash remove-icon"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="cart-actions">
                                            <td colspan="4"></td>
                                            <td colspan="2" class="text-right">
                                                <button class="btn btn-primary update-cart-btn" type="submit">
                                                    <i class="ti-reload"></i> Update Cart
                                                </button>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="text-center empty-cart" colspan="6">
                                                <div class="empty-cart-content">
                                                    <i class="ti-shopping-cart-full empty-cart-icon"></i>
                                                    <h4>Your cart is empty</h4>
                                                    <p>There are no items in your cart.</p>
                                                    <a href="{{route('product-grids')}}" class="btn btn-primary">Continue Shopping</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </form>
                            </tbody>
                        </table>
                    </div>
                    <!--/ End Shopping Summery -->
                </div>
            </div>

            @if(Helper::getAllProductFromCart())
            <div class="row">
                <div class="col-12">
                    <!-- Total Amount -->
                    <div class="total-amount">
                        <div class="row">
                            <div class="col-lg-8 col-md-5 col-12">
                                <div class="left">
                                    <div class="coupon">
                                        <form action="{{route('coupon-store')}}" method="POST">
                                            @csrf
                                            <input name="code" placeholder="Enter Your Coupon">
                                            <button class="btn">Apply</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-7 col-12">
                                <div class="right">
                                    <ul>
                                        <li class="order_subtotal" data-price="{{Helper::totalCartPrice()}}">Cart Subtotal<span>{{number_format(Helper::totalCartPrice(),2)}} ETB</span></li>
                                        @if(session()->has('coupon'))
                                        <li class="coupon_price" data-price="{{Session::get('coupon')['value']}}">You Save<span>{{number_format(Session::get('coupon')['value'],2)}} ETB</span></li>
                                        @endif
                                        @php
                                            $total_amount=Helper::totalCartPrice();
                                            if(session()->has('coupon')){
                                                $total_amount=$total_amount-Session::get('coupon')['value'];
                                            }
                                        @endphp
                                        @if(session()->has('coupon'))
                                            <li class="last" id="order_total_price">You Pay<span>{{number_format($total_amount,2)}} ETB</span></li>
                                        @else
                                            <li class="last" id="order_total_price">You Pay<span>{{number_format($total_amount,2)}} ETB</span></li>
                                        @endif
                                    </ul>
                                    <div class="button5">
                                        <a href="{{route('checkout')}}" class="btn btn-success btn-lg">
                                            <i class="ti-credit-card"></i> Checkout
                                        </a>
                                        <a href="{{route('product-grids')}}" class="btn btn-outline-primary">
                                            <i class="ti-arrow-left"></i> Continue Shopping
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ End Total Amount -->
                </div>
            </div>
            @endif
        </div>
    </div>
    <!--/ End Shopping Cart -->

    <!-- Start Shop Services Area  -->
    <section class="shop-services section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-rocket"></i>
                        <h4>Free Shipping</h4>
                        <p>Orders over 10,000 Birr</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-reload"></i>
                        <h4>Free Return</h4>
                        <p>Within 30 days returns</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-lock"></i>
                        <h4>Secure Payment</h4>
                        <p>100% secure payment</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-tag"></i>
                        <h4>Best Price</h4>
                        <p>Guaranteed price</p>
                    </div>
                    <!-- End Single Service -->
                </div>
            </div>
        </div>
    </section>
    <!-- End Shop Services -->

    <!-- Start Shop Newsletter  -->
    @include('frontend.layouts.newsletter')
    <!-- End Shop Newsletter -->
@endsection

@push('styles')
<style>
/* Cart Table Improvements */
.shopping-summery {
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 30px;
}

.shopping-summery thead tr {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.shopping-summery thead th {
    border: none;
    padding: 20px 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cart-item {
    border-bottom: 1px solid #eee;
    transition: all 0.3s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

/* Product Image */
.product-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #eee;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Product Details */
.product-details {
    padding: 20px 15px;
}

.product-name {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
}

.product-name a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.product-name a:hover {
    color: #667eea;
}

.product-description {
    color: #666;
    font-size: 13px;
    margin: 5px 0 10px 0;
    line-height: 1.4;
}

/* Product Attributes Styling */
.product-attributes {
    margin-top: 12px;
    padding: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 6px;
    border-left: 4px solid #667eea;
}

.attributes-label {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    color: #495057;
    font-size: 13px;
}

.attributes-label i {
    margin-right: 6px;
    color: #667eea;
}

.attributes-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.attribute-item {
    display: inline-block;
}

.attribute-badge {
    display: inline-flex;
    align-items: center;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.attribute-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.attr-name {
    font-weight: 600;
    color: #495057;
    margin-right: 4px;
}

.attr-value {
    color: #667eea;
    font-weight: 500;
}

.attr-price {
    color: #28a745;
    font-weight: 500;
    margin-left: 4px;
    font-size: 11px;
}

/* Price and Amount */
.price .amount,
.total-amount .money {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

/* Quantity Controls */
.quantity-controls {
    max-width: 120px;
    margin: 0 auto;
}

.quantity-controls .btn {
    border: 1px solid #dee2e6;
    background: white;
    color: #495057;
    padding: 8px 12px;
    transition: all 0.3s ease;
}

.quantity-controls .btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.quantity-controls .form-control {
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    border-left: none;
    border-right: none;
    font-weight: 600;
    background: #f8f9fa;
}

/* Remove Item */
.remove-item {
    color: #dc3545;
    font-size: 18px;
    transition: all 0.3s ease;
    padding: 8px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
}

.remove-item:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
}

/* Cart Actions */
.cart-actions td {
    padding: 20px 15px;
    background: #f8f9fa;
}

.update-cart-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.update-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Empty Cart */
.empty-cart {
    padding: 60px 20px;
}

.empty-cart-content {
    text-align: center;
}

.empty-cart-icon {
    font-size: 64px;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-cart-content h4 {
    color: #495057;
    margin-bottom: 10px;
}

.empty-cart-content p {
    color: #6c757d;
    margin-bottom: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .shopping-summery {
        font-size: 14px;
    }

    .product-image {
        width: 60px;
        height: 60px;
    }

    .attributes-list {
        flex-direction: column;
    }

    .attribute-badge {
        justify-content: center;
        margin-bottom: 4px;
    }

    .quantity-controls {
        max-width: 100px;
    }

    .shopping-summery thead {
        display: none;
    }

    .shopping-summery tbody tr {
        display: block;
        border: 1px solid #ddd;
        margin-bottom: 15px;
        border-radius: 8px;
        overflow: hidden;
    }

    .shopping-summery tbody td {
        display: block;
        text-align: right;
        border: none;
        padding: 10px 15px;
        position: relative;
    }

    .shopping-summery tbody td:before {
        content: attr(data-title) ": ";
        position: absolute;
        left: 15px;
        font-weight: 600;
        color: #495057;
    }

    .product-details {
        text-align: left !important;
    }

    .product-details:before {
        display: none;
    }
}

/* Additional styling for shipping section */
li.shipping {
    display: inline-flex;
    width: 100%;
    font-size: 14px;
}

li.shipping .input-group-icon {
    width: 100%;
    margin-left: 10px;
}

.input-group-icon .icon {
    position: absolute;
    left: 20px;
    top: 0;
    line-height: 34px;
    z-index: 3;
}

.form-select {
    height: 30px;
    width: 100%;
}

.form-select .nice-select {
    border: none;
    border-radius: 0px;
    height: 34px;
    background: #f6f6f6 !important;
    padding-left: 45px;
    padding-right: 34px;
    width: 100%;
}

.list li {
    margin-bottom: 0 !important;
}

.list li:hover {
    background: #F7941D !important;
    color: white !important;
}

.form-select .nice-select::after {
    top: 14px;
}
</style>
@endpush

@push('scripts')
<script src="{{asset('frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
<script src="{{ asset('frontend/js/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("select.select2").select2();
        $('select.nice-select').niceSelect();

        // Quantity control buttons
        $('.btn-number').click(function(e){
            e.preventDefault();

            var fieldName = $(this).attr('data-field');
            var type = $(this).attr('data-type');
            var input = $("input[name='"+fieldName+"']");
            var currentVal = parseInt(input.val());

            if (!isNaN(currentVal)) {
                if(type == 'minus') {
                    if(currentVal > input.attr('data-min')) {
                        input.val(currentVal - 1).change();
                    }
                    if(parseInt(input.val()) == input.attr('data-min')) {
                        $(this).attr('disabled', true);
                    }
                } else if(type == 'plus') {
                    if(currentVal < input.attr('data-max')) {
                        input.val(currentVal + 1).change();
                    }
                    if(parseInt(input.val()) == input.attr('data-max')) {
                        $(this).attr('disabled', true);
                    }
                }
            } else {
                input.val(0);
            }
        });

        $('.input-number').focusin(function(){
           $(this).data('oldValue', $(this).val());
        });

        $('.input-number').change(function() {
            var minValue =  parseInt($(this).attr('data-min'));
            var maxValue =  parseInt($(this).attr('data-max'));
            var valueCurrent = parseInt($(this).val());

            var name = $(this).attr('name');
            if(valueCurrent >= minValue) {
                $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
            } else {
                alert('Sorry, the minimum value was reached');
                $(this).val($(this).data('oldValue'));
            }
            if(valueCurrent <= maxValue) {
                $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
            } else {
                alert('Sorry, the maximum value was reached');
                $(this).val($(this).data('oldValue'));
            }
        });

        $(".input-number").keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                     // let it happen, don't do anything
                     return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    });

    // Shipping cost calculation
    $(document).ready(function(){
        $('.shipping select[name=shipping]').change(function(){
            let cost = parseFloat( $(this).find('option:selected').data('price') ) || 0;
            let subtotal = parseFloat( $('.order_subtotal').data('price') );
            let coupon = parseFloat( $('.coupon_price').data('price') ) || 0;
            $('#order_total_price span').text((subtotal + cost - coupon).toFixed(2) + ' ETB');
        });
    });
</script>
@endpush
