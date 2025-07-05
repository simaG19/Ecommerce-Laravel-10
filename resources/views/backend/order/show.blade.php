@extends('backend.layouts.master')

@section('title','Order Detail')

@section('main-content')
<div class="card">
    <h5 class="card-header">Order Details
        <a href="{{route('order.pdf',$order->id)}}" class="btn btn-sm btn-primary shadow-sm float-right">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate PDF
        </a>
    </h5>
    <div class="card-body">
        @if($order)
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Photo</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Attributes</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @if($order->items && $order->items->count() > 0)
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>
                            @if($item->product)
                                {{$item->product->title}}
                            @else
                                <span class="text-muted">Product not found</span>
                            @endif
                        </td>
                        <td>
                            @if($item->product && $item->product->photo)
                                @php
                                    $photo=explode(',',$item->product->photo);
                                @endphp
                                <img src="{{$photo[0]}}" class="img-fluid zoom" style="max-width:80px" alt="{{$item->product->title}}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>{{number_format($item->price,2)}} ETB</td>
                        <td>{{$item->quantity}}</td>
                        <td>
                            @php
                                $attributeError = false;
                                $hasAttributes = false;
                                $selectedAttributes = collect();

                                try {
                                    $hasAttributes = $item->hasSelectedAttributes();
                                    if ($hasAttributes) {
                                        $selectedAttributes = $item->selectedAttributes;
                                    }
                                } catch (Exception $e) {
                                    $attributeError = true;
                                    \Log::error('Error loading attributes for order item ' . $item->id . ': ' . $e->getMessage());
                                }
                            @endphp

                            @if($attributeError)
                                <span class="text-danger">Error loading attributes</span>
                            @elseif($hasAttributes && $selectedAttributes && $selectedAttributes->count() > 0)
                                <div class="order-attributes">
                                    @foreach($selectedAttributes as $attr)
                                        <span class="badge badge-info">
                                            <strong>{{$attr['attribute_name']}}:</strong> {{$attr['value']}}
                                            @if($attr['price'] > 0)
                                                <small>(+{{number_format($attr['price'], 2)}} ETB)</small>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">No attributes</span>
                            @endif
                        </td>
                        <td>{{number_format($item->amount,2)}} ETB</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center">No items found for this order</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <section class="confirmation_part section_padding">
            <div class="order_boxes">
                <div class="row">
                    <div class="col-lg-6 col-lx-4">
                        <div class="order-info">
                            <h4 class="text-center pb-4">ORDER INFORMATION</h4>
                            <table class="table">
                                <tr class="">
                                    <td>Order Number</td>
                                    <td> : {{$order->order_number}}</td>
                                </tr>
                                <tr>
                                    <td>Order Date</td>
                                    <td> : {{$order->created_at->format('D d M, Y')}} at {{$order->created_at->format('g:i a')}} </td>
                                </tr>
                                <tr>
                                    <td>Quantity</td>
                                    <td> : {{$order->quantity}}</td>
                                </tr>
                                <tr>
                                    <td>Order Status</td>
                                    <td> :
                                        @if($order->status=='new')
                                            <span class="badge badge-primary">{{$order->status}}</span>
                                        @elseif($order->status=='process')
                                            <span class="badge badge-warning">{{$order->status}}</span>
                                        @elseif($order->status=='delivered')
                                            <span class="badge badge-success">{{$order->status}}</span>
                                        @else
                                            <span class="badge badge-danger">{{$order->status}}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Payment Method</td>
                                    <td> :
                                        @if($order->payment_method=='cod')
                                            Cash on Delivery
                                        @else
                                            PayPal
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Payment Status</td>
                                    <td> :
                                        @if($order->payment_status=='paid')
                                            <span class="badge badge-success">{{$order->payment_status}}</span>
                                        @else
                                            <span class="badge badge-danger">{{$order->payment_status}}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sub Total</td>
                                    <td> : {{number_format($order->sub_total,2)}} ETB</td>
                                </tr>
                                <tr>
                                    <td>Shipping Charge</td>
                                    <td> : {{number_format($order->delivery_charge ?? 0,2)}} ETB</td>
                                </tr>
                                @if($order->coupon > 0)
                                <tr>
                                    <td>Coupon</td>
                                    <td> : {{number_format($order->coupon,2)}} ETB</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Total Amount</strong></td>
                                    <td> : <strong>{{number_format($order->total_amount,2)}} ETB</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-6 col-lx-4">
                        <div class="shipping-info">
                            <h4 class="text-center pb-4">SHIPPING INFORMATION</h4>
                            <table class="table">
                                <tr class="">
                                    <td>Full Name</td>
                                    <td> : {{$order->first_name}} {{$order->last_name}}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td> : {{$order->email}}</td>
                                </tr>
                                <tr>
                                    <td>Phone No.</td>
                                    <td> : {{$order->phone}}</td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td> : {{$order->address1}}, {{$order->address2}}</td>
                                </tr>
                                <tr>
                                    <td>Country</td>
                                    <td> : {{$order->country}}</td>
                                </tr>
                                <tr>
                                    <td>Post Code</td>
                                    <td> : {{$order->post_code}}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif
    </div>
</div>

<style>
.order-attributes {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.order-attributes .badge {
    font-size: 11px;
    padding: 4px 8px;
    margin: 2px 0;
}

.order-attributes .badge-info {
    background-color: #17a2b8;
    color: white;
}
</style>
@endsection

@push('styles')
<style>
    .order-info,.shipping-info{
        background:#ECECEC;
        padding:20px;
    }
    .order-info h4,.shipping-info h4{
        text-decoration: underline;
    }
</style>
@endpush
