@extends('frontend.layouts.master')

@section('meta')
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name='copyright' content=''>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="keywords" content="online shop, purchase, cart, ecommerce site, best online shopping">
	<meta name="description" content="{{$product_detail->summary}}">
	<meta property="og:url" content="{{route('product-detail',$product_detail->slug)}}">
	<meta property="og:type" content="article">
	<meta property="og:title" content="{{$product_detail->title}}">
	<meta property="og:image" content="{{$product_detail->photo}}">
	<meta property="og:description" content="{{$product_detail->description}}">
@endsection
@section('title','E-SHOP || PRODUCT DETAIL')
@section('main-content')

		<!-- Breadcrumbs -->
		<div class="breadcrumbs">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="bread-inner">
							<ul class="bread-list">
								<li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
								<li class="active"><a href="">Shop Details</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Breadcrumbs -->

		<!-- Shop Single -->
		<section class="shop single section">
					<div class="container">
						<div class="row">
							<div class="col-12">
								<div class="row">
									<div class="col-lg-6 col-12">
										<!-- Product Slider -->
										<div class="product-gallery">
											<!-- Images slider -->
											<div class="flexslider-thumbnails">
												<ul class="slides">
													@php
														$photo=explode(',',$product_detail->photo);
													// dd($photo);
													@endphp
													@foreach($photo as $data)
														<li data-thumb="{{$data}}" rel="adjustX:10, adjustY:">
															<img src="{{$data}}" alt="{{$data}}">
														</li>
													@endforeach
												</ul>
											</div>
											<!-- End Images slider -->
										</div>
										<!-- End Product slider -->
									</div>
									<div class="col-lg-6 col-12">
										<div class="product-des">
											<!-- Description -->
											<div class="short">
												<h4>{{ $product_detail->title }}</h4>
												<div class="rating-main">
												  <ul class="rating">
													@php $rate = ceil($product_detail->getReview->avg('rate')) @endphp
													@for($i = 1; $i <= 5; $i++)
													  @if($rate >= $i)
														<li><i class="fa fa-star"></i></li>
													  @else
														<li><i class="fa fa-star-o"></i></li>
													  @endif
													@endfor
												  </ul>
												  <a href="#" class="total-review">({{ $product_detail->getReview->count() }}) Review</a>
												</div>

											@php
  $base = $product_detail->price;
  $discount = $product_detail->discount ?? 0;
  $after_discount = $base - ($base * $discount / 100);
@endphp

												{{-- <p class="price">
												  Base: <span id="base-price">{{ number_format($base,2) }}</span> Birr<br>
												  Discounted: <span id="discounted-price">{{ number_format($after_discount,2) }}</span> Birr<br>
												  <strong>Total: <span id="total-price">{{ number_format($after_discount,2) }}</span> Birr</strong>
												</p> --}}


                                               <p class="price">
  <span id="discounted-price" class="discount">{{number_format($after_discount,2)}}</span>
  <s id="original-price">{{number_format($product_detail->price,2)}} Birr</s>
</p>
												{{-- Attribute dropdowns --}}
						{{-- Attribute dropdowns - CORRECTED --}}
{{-- @foreach($product_detail->attributes as $attr)
  <div class="form-group" style="margin-top:1rem;">
    <label for="attr-{{ $attr->id }}" style="font-weight:600;">
      {{ $attr->name }}
    </label>
    <select
      id="attr-{{ $attr->id }}"
      name="attributes[{{ $attr->id }}]"
      class="form-control attribute-select"
      style="max-width:300px;"
      data-attr-id="{{ $attr->id }}"
    >
      <option value="" data-price="0">
        -- Select {{ $attr->name }} --
      </option>
      @foreach($attr->values as $val)
        <option
          value="{{ $val->id }}"
          data-price="{{ number_format($val->price, 2, '.', '') }}"
        >
          {{ $val->value }} @if($val->price > 0)(+{{ number_format($val->price,2) }} Birr)@endif
        </option>
      @endforeach
    </select>
  </div>
@endforeach
												<div class="product-buy"> --}}
    <form action="{{route('single-add-to-cart')}}" method="POST" id="add-to-cart-form">
        @csrf

        {{-- Move attribute dropdowns INSIDE the form --}}
        @foreach($product_detail->attributes as $attr)
          <div class="form-group" style="margin-top:1rem;">
            <label for="attr-{{ $attr->id }}" style="font-weight:600;">
              {{ $attr->name }}
            </label>
            <select
              id="attr-{{ $attr->id }}"
              name="attributes[{{ $attr->id }}]"
              class="form-control attribute-select"
              style="max-width:300px;"
              data-attr-id="{{ $attr->id }}"
            >
              <option value="">-- Select {{ $attr->name }} --</option>
              @foreach($attr->values as $val)
                <option
                  value="{{ $val->id }}"
                  data-price="{{ number_format($val->price, 2, '.', '') }}"
                >
                  {{ $val->value }} @if($val->price > 0)({{ number_format($val->price,2) }} Birr)@endif
                </option>
              @endforeach
            </select>
          </div>
        @endforeach

        {{-- Product info --}}
        <input type="hidden" name="slug" value="{{$product_detail->slug}}">
        <input type="hidden" name="selected_price" id="selected-price-input" value="{{ $after_discount }}">

        <div class="quantity">
            <h6>Quantity :</h6>
            <div class="input-group">
                <div class="button minus">
                    <button type="button" class="btn btn-primary btn-number" disabled="disabled" data-type="minus" data-field="quant[1]">
                        <i class="ti-minus"></i>
                    </button>
                </div>

                <input type="text" name="quant[1]" class="input-number" data-min="1" data-max="1000" value="1" id="quantity">

                <div class="button plus">
                    <button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[1]">
                        <i class="ti-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="add-to-cart mt-4">
            <button type="submit" class="btn">Add to cart</button>
            <a href="{{route('add-to-wishlist',$product_detail->slug)}}" class="btn min"><i class="ti-heart"></i></a>
        </div>
    </form>
</div>
												<!--/ End Product Buy -->
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-12">
										<div class="product-info">
											<div class="nav-main">
												<!-- Tab Nav -->
												<ul class="nav nav-tabs" id="myTab" role="tablist">
													<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#description" role="tab">Description</a></li>
													<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#reviews" role="tab">Reviews</a></li>
												</ul>
												<!--/ End Tab Nav -->
											</div>
											<div class="tab-content" id="myTabContent">
												<!-- Description Tab -->
												<div class="tab-pane fade show active" id="description" role="tabpanel">
													<div class="tab-single">
														<div class="row">
															<div class="col-12">
																<div class="single-des">
																	<p>{!! ($product_detail->description) !!}</p>
																</div>
															</div>
														</div>
													</div>
												</div>
												<!--/ End Description Tab -->
												<!-- Reviews Tab -->
												<div class="tab-pane fade" id="reviews" role="tabpanel">
													<div class="tab-single review-panel">
														<div class="row">
															<div class="col-12">

																<!-- Review -->
																<div class="comment-review">
																	<div class="add-review">
																		<h5>Add A Review</h5>
																		<p>Your email address will not be published. Required fields are marked</p>
																	</div>
																	<h4>Your Rating <span class="text-danger">*</span></h4>
																	<div class="review-inner">
																			<!-- Form -->
																@auth
																<form class="form" method="post" action="{{route('review.store',$product_detail->slug)}}">
                                                                    @csrf
                                                                    <div class="row">
                                                                        <div class="col-lg-12 col-12">
                                                                            <div class="rating_box">
                                                                                  <div class="star-rating">
                                                                                    <div class="star-rating__wrap">
                                                                                      <input class="star-rating__input" id="star-rating-5" type="radio" name="rate" value="5">
                                                                                      <label class="star-rating__ico fa fa-star-o" for="star-rating-5" title="5 out of 5 stars"></label>
                                                                                      <input class="star-rating__input" id="star-rating-4" type="radio" name="rate" value="4">
                                                                                      <label class="star-rating__ico fa fa-star-o" for="star-rating-4" title="4 out of 5 stars"></label>
                                                                                      <input class="star-rating__input" id="star-rating-3" type="radio" name="rate" value="3">
                                                                                      <label class="star-rating__ico fa fa-star-o" for="star-rating-3" title="3 out of 5 stars"></label>
                                                                                      <input class="star-rating__input" id="star-rating-2" type="radio" name="rate" value="2">
                                                                                      <label class="star-rating__ico fa fa-star-o" for="star-rating-2" title="2 out of 5 stars"></label>
                                                                                      <input class="star-rating__input" id="star-rating-1" type="radio" name="rate" value="1">
																					  <label class="star-rating__ico fa fa-star-o" for="star-rating-1" title="1 out of 5 stars"></label>
																					  @error('rate')
																						<span class="text-danger">{{$message}}</span>
																					  @enderror
                                                                                    </div>
                                                                                  </div>
                                                                            </div>
                                                                        </div>
																		<div class="col-lg-12 col-12">
																			<div class="form-group">
																				<label>Write a review</label>
																				<textarea name="review" rows="6" placeholder="" ></textarea>
																			</div>
																		</div>
																		<div class="col-lg-12 col-12">
																			<div class="form-group button5">
																				<button type="submit" class="btn">Submit</button>
																			</div>
																		</div>
																	</div>
																</form>
																@else
																<p class="text-center p-5">
																	You need to <a href="{{route('login.form')}}" style="color:rgb(54, 54, 204)">Login</a> OR <a style="color:blue" href="{{route('register.form')}}">Register</a>

																</p>
																<!--/ End Form -->
																@endauth
																	</div>
																</div>

																<div class="ratting-main">
																	<div class="avg-ratting">
																		{{-- @php
																			$rate=0;
																			foreach($product_detail->rate as $key=>$rate){
																				$rate +=$rate
																			}
																		@endphp --}}
																		<h4>{{ceil($product_detail->getReview->avg('rate'))}} <span>(Overall)</span></h4>
																		<span>Based on {{$product_detail->getReview->count()}} Comments</span>
																	</div>
																	@foreach($product_detail['getReview'] as $data)
																	<!-- Single Rating -->
																	<div class="single-rating">
																		<div class="rating-author">
																			@if($data->user_info['photo'])
																			<img src="{{$data->user_info['photo']}}" alt="{{$data->user_info['photo']}}">
																			@else
																			<img src="{{asset('backend/img/avatar.png')}}" alt="Profile.jpg">
																			@endif
																		</div>
																		<div class="rating-des">
																			<h6>{{$data->user_info['name']}}</h6>
																			<div class="ratings">

																				<ul class="rating">
																					@for($i=1; $i<=5; $i++)
																						@if($data->rate>=$i)
																							<li><i class="fa fa-star"></i></li>
																						@else
																							<li><i class="fa fa-star-o"></i></li>
																						@endif
																					@endfor
																				</ul>
																				<div class="rate-count">(<span>{{$data->rate}}</span>)</div>
																			</div>
																			<p>{{$data->review}}</p>
																		</div>
																	</div>
																	<!--/ End Single Rating -->
																	@endforeach
																</div>

																<!--/ End Review -->

															</div>
														</div>
													</div>
												</div>
												<!--/ End Reviews Tab -->
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
		</section>
		<!--/ End Shop Single -->

		<!-- Start Most Popular -->
	<div class="product-area most-popular related-product section">
        <div class="container">
            <div class="row">
				<div class="col-12">
					<div class="section-title">
						<h2>Related Products</h2>
					</div>
				</div>
            </div>
            <div class="row">
                {{-- {{$product_detail->rel_prods}} --}}
                <div class="col-12">
                    <div class="owl-carousel popular-slider">
                        @foreach($product_detail->rel_prods as $data)
                            @if($data->id !==$product_detail->id)
                                <!-- Start Single Product -->
                                <div class="single-product">
                                    <div class="product-img">
										<a href="{{route('product-detail',$data->slug)}}">
											@php
												$photo=explode(',',$data->photo);
											@endphp
                                            <img class="default-img" src="{{$photo[0]}}" alt="{{$photo[0]}}">
                                            <img class="hover-img" src="{{$photo[0]}}" alt="{{$photo[0]}}">
                                            <span class="price-dec">{{$data->discount}} % Off</span>
                                                                    {{-- <span class="out-of-stock">Hot</span> --}}
                                        </a>
                                        <div class="button-head">
                                            <div class="product-action">
                                                <a data-toggle="modal" data-target="#modelExample" title="Quick View" href="#"><i class=" ti-eye"></i><span>Quick Shop</span></a>
                                                <a title="Wishlist" href="#"><i class=" ti-heart "></i><span>Add to Wishlist</span></a>
                                                <a title="Compare" href="#"><i class="ti-bar-chart-alt"></i><span>Add to Compare</span></a>
                                            </div>
                                            <div class="product-action-2">
                                                <a title="Add to cart" href="#">Add to cart</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="product-content">
                                        <h3><a href="{{route('product-detail',$data->slug)}}">{{$data->title}}</a></h3>
                                        <div class="product-price">
                                            @php
                                                $after_discount=($data->price-(($data->discount*$data->price)/100));
                                            @endphp
                                            <span class="old">{{number_format($data->price,2)}} Birr</span>
                                            <span>{{number_format($after_discount,2)}} Birr</span>
                                        </div>

                                    </div>
                                </div>
                                <!-- End Single Product -->

                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
	<!-- End Most Popular Area -->


  <!-- Modal -->
  <div class="modal fade" id="modelExample" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span class="ti-close" aria-hidden="true"></span></button>
            </div>
            <div class="modal-body">
                <div class="row no-gutters">
                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                        <!-- Product Slider -->
                            <div class="product-gallery">
                                <div class="quickview-slider-active">
                                    <div class="single-slider">
                                        <img src="images/modal1.png" alt="#">
                                    </div>
                                    <div class="single-slider">
                                        <img src="images/modal2.png" alt="#">
                                    </div>
                                    <div class="single-slider">
                                        <img src="images/modal3.png" alt="#">
                                    </div>
                                    <div class="single-slider">
                                        <img src="images/modal4.png" alt="#">
                                    </div>
                                </div>
                            </div>
                        <!-- End Product slider -->
                    </div>
                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                        <div class="quickview-content">
                            <h2>Flared Shift Dress</h2>
                            <div class="quickview-ratting-review">
                                <div class="quickview-ratting-wrap">
                                    <div class="quickview-ratting">
                                        <i class="yellow fa fa-star"></i>
                                        <i class="yellow fa fa-star"></i>
                                        <i class="yellow fa fa-star"></i>
                                        <i class="yellow fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                    </div>
                                    <a href="#"> (1 customer review)</a>
                                </div>
                                <div class="quickview-stock">
                                    <span><i class="fa fa-check-circle-o"></i> in stock</span>
                                </div>
                            </div>
                            <h3>$29.00</h3>
                            <div class="quickview-peragraph">
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Mollitia iste laborum ad impedit pariatur esse optio tempora sint ullam autem deleniti nam in quos qui nemo ipsum numquam.</p>
                            </div>
                            <div class="size">
                                <div class="row">
                                    <div class="col-lg-6 col-12">
                                        <h5 class="title">Size</h5>
                                        <select>
                                            <option selected="selected">s</option>
                                            <option>m</option>
                                            <option>l</option>
                                            <option>xl</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <h5 class="title">Color</h5>
                                        <select>
                                            <option selected="selected">orange</option>
                                            <option>purple</option>
                                            <option>black</option>
                                            <option>pink</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="quantity">
                                <!-- Input Order -->
                                <div class="input-group">
                                    <div class="button minus">
                                        <button type="button" class="btn btn-primary btn-number" disabled="disabled" data-type="minus" data-field="quant[1]">
                                            <i class="ti-minus"></i>
                                        </button>
									</div>
                                    <input type="text" name="qty" class="input-number"  data-min="1" data-max="1000" value="1">
                                    <div class="button plus">
                                        <button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[1]">
                                            <i class="ti-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <!--/ End Input Order -->
                            </div>
                            <div class="add-to-cart">
                                <a href="#" class="btn">Add to cart</a>
                                <a href="#" class="btn min"><i class="ti-heart"></i></a>
                                <a href="#" class="btn min"><i class="fa fa-compress"></i></a>
                            </div>
                            <div class="default-social">
                                <h4 class="share-now">Share:</h4>
                                <ul>
                                    <li><a class="facebook" href="#"><i class="fa fa-facebook"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fa fa-twitter"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fa fa-pinterest-p"></i></a></li>
                                    <li><a class="dribbble" href="#"><i class="fa fa-google-plus"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal end -->

@endsection
@push('styles')
	<style>
		/* Rating */
		.rating_box {
		display: inline-flex;
		}

		.star-rating {
		font-size: 0;
		padding-left: 10px;
		padding-right: 10px;
		}

		.star-rating__wrap {
		display: inline-block;
		font-size: 1rem;
		}

		.star-rating__wrap:after {
		content: "";
		display: table;
		clear: both;
		}

		.star-rating__ico {
		float: right;
		padding-left: 2px;
		cursor: pointer;
		color: #F7941D;
		font-size: 16px;
		margin-top: 5px;
		}

		.star-rating__ico:last-child {
		padding-left: 0;
		}

		.star-rating__input {
		display: none;
		}

		.star-rating__ico:hover:before,
		.star-rating__ico:hover ~ .star-rating__ico:before,
		.star-rating__input:checked ~ .star-rating__ico:before {
		content: "\F005";
		}

	</style>
@endpush
@push('scripts')


<script>
document.addEventListener('DOMContentLoaded', () => {
  const priceInput = document.getElementById('selected-price-input');
  const selects    = document.querySelectorAll('.attribute-select');

  // Whenever ANY select changes, update the price field:
  selects.forEach(sel => {
    sel.addEventListener('change', updatePrice);
  });

  function updatePrice() {
    // If you have multiple attributes, pick logic: here we take the
    // last-changed select's price, or you could sum/add etc.
    const sel = this;
    const opt = sel.selectedOptions[0];
    const price = opt && opt.dataset.price
      ? parseFloat(opt.dataset.price)
      : 0;

    priceInput.value = price.toFixed(2);
  }

  // If you want to ensure on form submit we have a price even if no change:
  document.getElementById('add-to-cart-form').addEventListener('submit', () => {
    // if priceInput empty, default to discounted:
    if (!priceInput.value) {
      priceInput.value = "{{ number_format($after_discount,2,'.','') }}";
    }
  });
});
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
// jQuery version - Use last selected attribute price
$(document).ready(function() {
    var originalBase = {{ $base }};
    var discountPercentage = {{ $discount }};

    function updatePrices() {
        var newBasePrice = originalBase;
        var lastAttributePrice = 0;
        var hasSelectedAttribute = false;

        $('.attribute-select').each(function() {
            var selectedValue = $(this).val();
            var attrId = $(this).data('attr-id');

            console.log('Processing attribute:', attrId, 'with value:', selectedValue); // Debug log

            if (selectedValue !== '') {
                // Update hidden input
                $('#attr-input-' + attrId).val(selectedValue);

                var selectedOption = $(this).find('option:selected');
                var attributePrice = parseFloat(selectedOption.data('price')) || 0;

                if (attributePrice > 0) {
                    lastAttributePrice = attributePrice;
                    hasSelectedAttribute = true;
                }
            } else {
                // Clear hidden input
                $('#attr-input-' + attrId).val('');
            }
        });

        // Use the last found attribute price, or original if none
        if (hasSelectedAttribute) {
            newBasePrice = lastAttributePrice;
        } else {
            newBasePrice = originalBase;
        }

        // Calculate discounted price
        var newDiscounted = newBasePrice - (newBasePrice * discountPercentage / 100);

        // Update the price display
        $('#discounted-price').text(newDiscounted.toFixed(2));
        $('#original-price').text(newBasePrice.toFixed(2) + ' Birr');

        // Update the hidden price input
        $('#selected-price-input').val(newDiscounted.toFixed(2));
    }

    // Attach change event
    $(document).on('change', '.attribute-select', function() {
        updatePrices();
    });

    // Debug form submission
    $('#add-to-cart-form').on('submit', function(e) {
        console.log('Form being submitted...');

        // Log all form data
        var formData = new FormData(this);
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        // Check if any attributes are selected
        var hasAttributes = false;
        $('.attribute-select').each(function() {
            if ($(this).val() !== '') {
                hasAttributes = true;
                console.log('Attribute selected:', $(this).data('attr-id'), '=', $(this).val());
            }
        });

        if (!hasAttributes) {
            console.log('No attributes selected');
        }
    });

    // Initial update
    updatePrices();
});
</script>
@endpush
