<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header class="header shop">
    <!-- Topbar -->
    <div class="topbar">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Left -->
                    <div class="top-left">
                        {{-- <ul class="list-main">
                            @php
                                $settings=DB::table('settings')->get();
                            @endphp
                            <li><i class="ti-headphone-alt" style="font-size: 1.3rem; margin-right: 8px;"></i><span style="font-size: 1.1rem;">@foreach($settings as $data) {{$data->phone}} @endforeach</span></li>
                            <li><i class="ti-email" style="font-size: 1.3rem; margin-right: 8px;"></i><span style="font-size: 1.1rem;"> @foreach($settings as $data) {{$data->email}} @endforeach</span></li>
                        </ul> --}}
                    </div>
                    <!--/ End Top Left -->
                </div>
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Right -->
                    <div class="right-content">
                        <ul class="list-main">
                            @auth
                                @if(Auth::user()->role=='admin')
                                    <li><i class="ti-user" style="font-size: 1.3rem; margin-right: 8px;"></i> <a href="{{route('admin')}}" target="_blank" style="font-size: 1.1rem;">Dashboard</a></li>
                                @else
                                    <li><i class="ti-user" style="font-size: 1.3rem; margin-right: 8px;"></i> <a href="{{route('user')}}" target="_blank" style="font-size: 1.1rem;">Dashboard</a></li>
                                @endif
                                <li><i class="ti-power-off" style="font-size: 1.3rem; margin-right: 8px;"></i> <a href="{{route('user.logout')}}" style="font-size: 1.1rem;">Logout</a></li>

                            @else
                                <li><i class="ti-power-off" style="font-size: 1.3rem; margin-right: 8px;"></i><a href="{{route('login.form')}}" style="font-size: 1.1rem;">Login /</a> <a href="{{route('register.form')}}" style="font-size: 1.1rem;">Register</a></li>
                            @endauth
                        </ul>
                    </div>
                    <!-- End Top Right -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Topbar -->
    <div class="middle-inner">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-2 col-12">
                    <!-- Logo -->
                    <div class="logo">
                        @php
                            $settings=DB::table('settings')->get();
                        @endphp
                        <a href="{{route('home')}}"><img src="images/image.png" alt="logo" style="max-height: 55px; width: auto;"></a>
                    </div>
                    <!--/ End Logo -->
                    <!-- Search Form -->
                    <div class="search-top">
                        <div class="top-search">
                            <a href="#0" style="min-width: 50px; min-height: 50px; display: inline-flex; align-items: center; justify-content: center; padding: 10px;">
                                <i class="ti-search" style="font-size: 1.6rem;"></i>
                            </a>
                        </div>
                        <!-- Search Form -->
                        <div class="search-top">
                            <form class="search-form">
                                <input type="text" placeholder="Search here..." name="search" style="font-size: 1.3rem; padding: 15px; min-height: 50px;">
                                <button value="search" type="submit" style="padding: 15px; min-width: 50px; min-height: 50px;">
                                    <i class="ti-search" style="font-size: 1.4rem;"></i>
                                </button>
                            </form>
                        </div>
                        <!--/ End Search Form -->
                    </div>
                    <!--/ End Search Form -->
                    <div class="mobile-nav"></div>
                </div>
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="search-bar-top">
                        <div class="search-bar">
                            <select style="font-size: 1.2rem; padding: 15px; min-height: 50px;">
                                <option>All Category</option>
                                @foreach(Helper::getAllCategory() as $cat)
                                    <option>{{$cat->title}}</option>
                                @endforeach
                            </select>
                            <form method="POST" action="{{route('product.search')}}">
                                @csrf
                                <input name="search" placeholder="Search Products Here....." type="search" style="font-size: 1.3rem; padding: 15px; min-height: 50px; flex: 1;">
                                <button class="btnn" type="submit" style="padding: 15px 20px; min-width: 50px; min-height: 50px;">
                                    <i class="ti-search" style="font-size: 1.5rem;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Desktop Cart/Wishlist - Hidden on Mobile -->
                <div class="col-lg-2 col-md-3 col-12 d-none d-md-block">
                    <div class="right-bar">
                        <!-- Wishlist -->
                        <div class="sinlge-bar shopping" style="position: relative;">
                            @php
                                $total_prod=0;
                                $total_amount=0;
                            @endphp
                           @if(session('wishlist'))
                                @foreach(session('wishlist') as $wishlist_items)
                                    @php
                                        $total_prod+=$wishlist_items['quantity'];
                                        $total_amount+=$wishlist_items['amount'];
                                    @endphp
                                @endforeach
                           @endif

                            <a href="{{route('wishlist')}}" class="single-icon" style="min-width: 30px; min-height: 30px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; padding: 10px; margin: 5px;">
                                <i class="fa fa-heart-o" style="font-size: 22px; margin-right: 4px;"></i>
                                <span class="total-count" style="font-size: 1.1rem; padding: 6px 5px; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; background: #dc3545; color: white; border-radius: 50%; font-weight: bold;">{{Helper::wishlistCount()}}</span>
                            </a>

                            <!-- Desktop Shopping Item -->
                            @auth
                                <div class="shopping-item" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); width: 320px; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s ease;">
                                    <div class="dropdown-cart-header" style="font-size: 1.2rem; padding: 15px; border-bottom: 1px solid #eee;">
                                        <span>{{count(Helper::getAllProductFromWishlist())}} Items</span>
                                        <a href="{{route('wishlist')}}" style="color: #007bff;">View All</a>
                                    </div>
                                    <ul class="shopping-list" style="max-height: 300px; overflow-y: auto; padding: 0; margin: 0; list-style: none;">
                                        @foreach(Helper::getAllProductFromWishlist() as $data)
                                                @php
                                                    $photo=explode(',',$data->product['photo']);
                                                @endphp
                                                <li style="padding: 15px; border-bottom: 1px solid #f5f5f5; display: flex; align-items: center;">
                                                    <a href="{{route('wishlist-delete',$data->id)}}" class="remove" title="Remove this item" style="margin-right: 10px;">
                                                        <i class="fa fa-remove" style="font-size: 1.3rem; color: #dc3545;"></i>
                                                    </a>
                                                    <a class="cart-img" href="#" style="margin-right: 15px;"><img src="{{$photo[0]}}" alt="{{$photo[0]}}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"></a>
                                                    <div style="flex: 1;">
                                                        <h4 style="font-size: 1.1rem; margin: 0 0 5px 0; line-height: 1.3;">
                                                            <a href="{{route('product-detail',$data->product['slug'])}}" target="_blank" style="color: #333; text-decoration: none;">{{$data->product['title']}}</a>
                                                        </h4>
                                                        <p class="quantity" style="font-size: 1rem; margin: 0; color: #666;">{{$data->quantity}} x - <span class="amount">${{number_format($data->price,2)}}</span></p>
                                                    </div>
                                                </li>
                                        @endforeach
                                    </ul>
                                    <div class="bottom" style="padding: 15px; border-top: 1px solid #eee;">
                                        <div class="total" style="font-size: 1.2rem; font-weight: bold; margin-bottom: 15px; display: flex; justify-content: space-between;">
                                            <span>Total</span>
                                            <span class="total-amount">${{number_format(Helper::totalWishlistPrice(),2)}}</span>
                                        </div>
                                        <a href="{{route('cart')}}" class="btn animate" style="font-size: 1.1rem; padding: 12px 20px; width: 100%; text-align: center; display: block; background: #007bff; color: white; text-decoration: none; border-radius: 6px;">Move to Cart</a>
                                    </div>
                                </div>
                            @endauth
                        </div>

                        <!-- Shopping Cart -->
                        <div class="sinlge-bar shopping" style="position: relative;">
                            <a href="{{route('cart')}}" class="single-icon" style="min-width: 40px; min-height: 40px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; padding: 10px; margin: 5px;">
                                <i class="ti-bag" style="font-size: 22px; margin-right: 4px;"></i>
                                <span class="total-count" style="font-size: 1.1rem; padding: 6px 10px; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; background: #007bff; color: white; border-radius: 50%; font-weight: bold;">{{Helper::cartCount()}}</span>
                            </a>

                            <!-- Desktop Shopping Item -->
                            @auth
                                <div class="shopping-item" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); width: 320px; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.3s ease;">
                                    <div class="dropdown-cart-header" style="font-size: 1.2rem; padding: 15px; border-bottom: 1px solid #eee;">
                                        <span>{{count(Helper::getAllProductFromCart())}} Items</span>
                                        <a href="{{route('cart')}}" style="color: #007bff;">View All</a>
                                    </div>
                                    <ul class="shopping-list" style="max-height: 300px; overflow-y: auto; padding: 0; margin: 0; list-style: none;">
                                        @foreach(Helper::getAllProductFromCart() as $data)
                                                @php
                                                    $photo=explode(',',$data->product['photo']);
                                                @endphp
                                                <li style="padding: 15px; border-bottom: 1px solid #f5f5f5; display: flex; align-items: center;">
                                                    <a href="{{route('cart-delete',$data->id)}}" class="remove" title="Remove this item" style="margin-right: 10px;">
                                                        <i class="fa fa-remove" style="font-size: 1.3rem; color: #dc3545;"></i>
                                                    </a>
                                                    <a class="cart-img" href="#" style="margin-right: 15px;"><img src="{{$photo[0]}}" alt="{{$photo[0]}}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"></a>
                                                    <div style="flex: 1;">
                                                        <h4 style="font-size: 1.1rem; margin: 0 0 5px 0; line-height: 1.3;">
                                                            <a href="{{route('product-detail',$data->product['slug'])}}" target="_blank" style="color: #333; text-decoration: none;">{{$data->product['title']}}</a>
                                                        </h4>
                                                        <p class="quantity" style="font-size: 1rem; margin: 0; color: #666;">{{$data->quantity}} x - <span class="amount">{{number_format($data->price,2)}} Birr</span></p>
                                                    </div>
                                                </li>
                                        @endforeach
                                    </ul>
                                    <div class="bottom" style="padding: 15px; border-top: 1px solid #eee;">
                                        <div class="total" style="font-size: 1.2rem; font-weight: bold; margin-bottom: 15px; display: flex; justify-content: space-between;">
                                            <span>Total</span>
                                            <span class="total-amount">{{number_format(Helper::totalCartPrice(),2)}} Birr</span>
                                        </div>
                                        <a href="{{route('checkout')}}" class="btn animate" style="font-size: 1.1rem; padding: 12px 20px; width: 100%; text-align: center; display: block; background: #28a745; color: white; text-decoration: none; border-radius: 6px;">Checkout</a>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Header Inner -->
    <div class="header-inner">
        <div class="container">
            <div class="cat-nav-head">
                <div class="row">
                    <div class="col-lg-12 col-12">
                        <div class="menu-area">
                            <!-- Main Menu -->
                            <nav class="navbar navbar-expand-lg">
                                <div class="navbar-collapse">
                                    <div class="nav-inner">
                                        <ul class="nav main-menu menu navbar-nav">
                                            <li class="{{Request::path()=='home' ? 'active' : ''}}">
                                                <a href="{{route('home')}}" style="font-size: 15px; padding: 18px 25px; min-height: 50px; display: flex; align-items: center;">Home</a>
                                            </li>
                                            <li class="{{Request::path()=='about-us' ? 'active' : ''}}">
                                                <a href="{{route('about-us')}}" style="font-size: 15px; padding: 18px 25px; min-height: 50px; display: flex; align-items: center;">About Us</a>
                                            </li>
                                            <li class="@if(Request::path()=='product-grids'||Request::path()=='product-lists')  active  @endif">
                                                <a href="{{route('product-grids')}}" style="font-size: 15px; padding: 18px 25px; min-height: 50px; display: flex; align-items: center;">Products</a>
                                                <span class="new" style="font-size: 1rem; padding: 6px 10px; margin-left: 8px;">New</span>
                                            </li>
                                            {{Helper::getHeaderCategory()}}
                                            <li class="{{Request::path()=='blog' ? 'active' : ''}}">
                                                <a href="{{route('blog')}}" style="font-size: 15px; padding: 18px 25px; min-height: 50px; display: flex; align-items: center;">Blog</a>
                                            </li>
                                            <li class="{{Request::path()=='contact' ? 'active' : ''}}">
                                                <a href="{{route('contact')}}" style="font-size: 15px; padding: 18px 25px; min-height: 50px; display: flex; align-items: center;">Contact Us</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </nav>
                            <!--/ End Main Menu -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ End Header Inner -->
</header>

<!-- Mobile Floating Cart & Wishlist Buttons -->
<div class="mobile-floating-buttons d-md-none">
    <!-- Floating Wishlist Button -->
    <div class="floating-wishlist" style="position: fixed; bottom: 90px; right: 15px; z-index: 9999;">
        <a href="{{route('wishlist')}}" style="
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            box-shadow: 0 2px 12px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
            position: relative;
        ">
            <i class="fa fa-heart-o" style="font-size: 1.3rem;"></i>
            @if(Helper::wishlistCount() > 0)
            <span style="
                position: absolute;
                top: -6px;
                right: -6px;
                background: white;
                color: #dc3545;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                font-weight: bold;
                border: 1px solid #dc3545;
            ">{{Helper::wishlistCount()}}</span>
            @endif
        </a>
    </div>

    <!-- Floating Cart Button -->
    <div class="floating-cart" style="position: fixed; bottom: 38px; right: 15px; z-index: 9999;">
        <a href="{{route('cart')}}" style="
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            box-shadow: 0 3px 15px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
        ">
            <i class="ti-bag" style="font-size: 1.4rem;"></i>
            @if(Helper::cartCount() > 0)
            <span style="
                position: absolute;
                top: -6px;
                right: -6px;
                background: white;
                color: #007bff;
                border-radius: 50%;
                width: 22px;
                height: 22px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                font-weight: bold;
                border: 1px solid #007bff;
            ">{{Helper::cartCount()}}</span>
            @endif
        </a>
    </div>
</div>

<style>
/* Desktop hover effects */
@media (min-width: 768px) {
    .sinlge-bar:hover .shopping-item {
        opacity: 1 !important;
        visibility: visible !important;
    }
}

/* Mobile floating button animations */
@media (max-width: 767px) {
    .floating-cart a:active,
    .floating-wishlist a:active {
        transform: scale(0.95);
    }

    .floating-cart a:hover,
    .floating-wishlist a:hover {
        transform: scale(1.05);
    }

    /* Pulse animation for buttons with items */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .floating-cart a,
    .floating-wishlist a {
        animation: pulse 2s infinite;
    }
}

/* Hide floating buttons on desktop */
@media (min-width: 768px) {
    .mobile-floating-buttons {
        display: none !important;
    }
}
</style>

<script>
// Add smooth scroll behavior and better mobile interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add touch feedback for mobile buttons
    const floatingButtons = document.querySelectorAll('.floating-cart a, .floating-wishlist a');

    floatingButtons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });

        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Optional: Add vibration feedback on touch (if supported)
    floatingButtons.forEach(button => {
        button.addEventListener('click', function() {
            if ('vibrate' in navigator) {
                navigator.vibrate(50); // Short vibration
            }
        });
    });
});
</script>

</body>
</html>
