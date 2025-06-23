<header class="header shop">
    <!-- Topbar -->
    <div class="topbar mobile-large-font">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Left -->
                    <div class="top-left">
                        <ul class="list-main">
                            @php
                                $settings=DB::table('settings')->get();
                            @endphp
                            <li class="mobile-large-font"><i class="ti-headphone-alt mobile-large-icon"></i>@foreach($settings as $data) {{$data->phone}} @endforeach</li>
                            <li class="mobile-large-font"><i class="ti-email mobile-large-icon"></i> @foreach($settings as $data) {{$data->email}} @endforeach</li>
                        </ul>
                    </div>
                    <!--/ End Top Left -->
                </div>
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Right -->
                    <div class="right-content">
                        <ul class="list-main">
                            @auth
                                @if(Auth::user()->role=='admin')
                                    <li class="mobile-large-font"><i class="ti-user mobile-large-icon"></i> <a href="{{route('admin')}}" target="_blank" class="mobile-large-font">Dashboard</a></li>
                                @else
                                    <li class="mobile-large-font"><i class="ti-user mobile-large-icon"></i> <a href="{{route('user')}}" target="_blank" class="mobile-large-font">Dashboard</a></li>
                                @endif
                                <li class="mobile-large-font"><i class="ti-power-off mobile-large-icon"></i> <a href="{{route('user.logout')}}" class="mobile-large-font">Logout</a></li>

                            @else
                                <li class="mobile-large-font"><i class="ti-power-off mobile-large-icon"></i><a href="{{route('login.form')}}" class="mobile-large-font">Login /</a> <a href="{{route('register.form')}}" class="mobile-large-font">Register</a></li>
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
                        <a href="{{route('home')}}"><img src="images/image.png" alt="logo" class="mobile-large-logo"></a>
                    </div>
                    <!--/ End Logo -->
                    <!-- Search Form -->
                    <div class="search-top">
                        <div class="top-search"><a href="#0" class="mobile-touch-target"><i class="ti-search mobile-large-search-icon"></i></a></div>
                        <!-- Search Form -->
                        <div class="search-top">
                            <form class="search-form">
                                <input type="text" placeholder="Search here..." name="search" class="mobile-large-input">
                                <button value="search" type="submit" class="mobile-touch-button"><i class="ti-search mobile-large-icon"></i></button>
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
                            <select class="mobile-large-select">
                                <option>All Category</option>
                                @foreach(Helper::getAllCategory() as $cat)
                                    <option>{{$cat->title}}</option>
                                @endforeach
                            </select>
                            <form method="POST" action="{{route('product.search')}}">
                                @csrf
                                <input name="search" placeholder="Search Products Here....." type="search" class="mobile-large-input">
                                <button class="btnn mobile-touch-button" type="submit"><i class="ti-search mobile-extra-large-icon"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-12">
                    <div class="right-bar">
                        <!-- Search Form -->
                        <div class="sinlge-bar shopping">
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
                            <a href="{{route('wishlist')}}" class="single-icon mobile-touch-target">
                                <i class="fa fa-heart-o mobile-extra-large-icon"></i>
                                <span class="total-count mobile-large-badge">{{Helper::wishlistCount()}}</span>
                            </a>
                            <!-- Shopping Item -->
                            @auth
                                <div class="shopping-item">
                                    <div class="dropdown-cart-header mobile-large-font">
                                        <span>{{count(Helper::getAllProductFromWishlist())}} Items</span>
                                        <a href="{{route('wishlist')}}">View Wishlist</a>
                                    </div>
                                    <ul class="shopping-list">
                                        {{-- {{Helper::getAllProductFromCart()}} --}}
                                            @foreach(Helper::getAllProductFromWishlist() as $data)
                                                    @php
                                                        $photo=explode(',',$data->product['photo']);
                                                    @endphp
                                                    <li>
                                                        <a href="{{route('wishlist-delete',$data->id)}}" class="remove" title="Remove this item"><i class="fa fa-remove mobile-large-icon"></i></a>
                                                        <a class="cart-img" href="#"><img src="{{$photo[0]}}" alt="{{$photo[0]}}"></a>
                                                        <h4 class="mobile-large-font"><a href="{{route('product-detail',$data->product['slug'])}}" target="_blank">{{$data->product['title']}}</a></h4>
                                                        <p class="quantity mobile-medium-font">{{$data->quantity}} x - <span class="amount">${{number_format($data->price,2)}}</span></p>
                                                    </li>
                                            @endforeach
                                    </ul>
                                    <div class="bottom">
                                        <div class="total mobile-large-font">
                                            <span>Total</span>
                                            <span class="total-amount">${{number_format(Helper::totalWishlistPrice(),2)}}</span>
                                        </div>
                                        <a href="{{route('cart')}}" class="btn animate mobile-large-button">Cart</a>
                                    </div>
                                </div>
                            @endauth
                            <!--/ End Shopping Item -->
                        </div>
                        <div class="sinlge-bar shopping">
                            <a href="{{route('cart')}}" class="single-icon mobile-touch-target">
                                <i class="ti-bag mobile-extra-large-icon"></i>
                                <span class="total-count mobile-large-badge">{{Helper::cartCount()}}</span>
                            </a>
                            <!-- Shopping Item -->
                            @auth
                                <div class="shopping-item">
                                    <div class="dropdown-cart-header mobile-large-font">
                                        <span>{{count(Helper::getAllProductFromCart())}} Items</span>
                                        <a href="{{route('cart')}}">View Cart</a>
                                    </div>
                                    <ul class="shopping-list">
                                        {{-- {{Helper::getAllProductFromCart()}} --}}
                                            @foreach(Helper::getAllProductFromCart() as $data)
                                                    @php
                                                        $photo=explode(',',$data->product['photo']);
                                                    @endphp
                                                    <li>
                                                        <a href="{{route('cart-delete',$data->id)}}" class="remove" title="Remove this item"><i class="fa fa-remove mobile-large-icon"></i></a>
                                                        <a class="cart-img" href="#"><img src="{{$photo[0]}}" alt="{{$photo[0]}}"></a>
                                                        <h4 class="mobile-large-font"><a href="{{route('product-detail',$data->product['slug'])}}" target="_blank">{{$data->product['title']}}</a></h4>
                                                        <p class="quantity mobile-medium-font">{{$data->quantity}} x - <span class="amount">{{number_format($data->price,2)}} Birr</span></p>
                                                    </li>
                                            @endforeach
                                    </ul>
                                    <div class="bottom">
                                        <div class="total mobile-large-font">
                                            <span>Total</span>
                                            <span class="total-amount">{{number_format(Helper::totalCartPrice(),2)}} Birr</span>
                                        </div>
                                        <a href="{{route('checkout')}}" class="btn animate mobile-large-button">Checkout</a>
                                    </div>
                                </div>
                            @endauth
                            <!--/ End Shopping Item -->
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
                                                <a href="{{route('home')}}" class="mobile-large-nav-link">Home</a>
                                            </li>
                                            <li class="{{Request::path()=='about-us' ? 'active' : ''}}">
                                                <a href="{{route('about-us')}}" class="mobile-large-nav-link">About Us</a>
                                            </li>
                                            <li class="@if(Request::path()=='product-grids'||Request::path()=='product-lists')  active  @endif">
                                                <a href="{{route('product-grids')}}" class="mobile-large-nav-link">Products</a>
                                                <span class="new mobile-small-badge">New</span>
                                            </li>
                                            {{Helper::getHeaderCategory()}}
                                            <li class="{{Request::path()=='blog' ? 'active' : ''}}">
                                                <a href="{{route('blog')}}" class="mobile-large-nav-link">Blog</a>
                                            </li>
                                            <li class="{{Request::path()=='contact' ? 'active' : ''}}">
                                                <a href="{{route('contact')}}" class="mobile-large-nav-link">Contact Us</a>
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

<style>
/* Mobile-only font and icon size increases */
@media (max-width: 867px) {

    /* Font sizes for mobile */
    .mobile-large-font {
        font-size: 10px;
    }

    .mobile-medium-font {
        font-size: 1.4rem !important;
    }

    /* Icon sizes for mobile */
    .mobile-large-icon {
        font-size: 1.8rem !important;
        margin-right: 8px !important;
    }

    .mobile-extra-large-icon {
        font-size: 1.9rem !important;
    }

    .mobile-large-search-icon {
        font-size: 1.5rem !important;
    }

    /* Input and form elements */
    .mobile-large-input {
        font-size: 1.2rem !important;
        padding: 12px 15px !important;
    }

    .mobile-large-select {
        font-size: 1.1rem !important;
        padding: 12px 15px !important;
    }

    /* Touch targets */
    .mobile-touch-target {
        min-width: 48px !important;
        min-height: 48px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .mobile-touch-button {
        padding: 12px 15px !important;
        min-width: 48px !important;
        min-height: 48px !important;
    }

    /* Badges and counts */
    .mobile-large-badge {
        font-size: 1rem !important;
        padding: 4px 8px !important;
        min-width: 24px !important;
        height: 24px !important;
    }

    .mobile-small-badge {
        font-size: 0.9rem !important;
        padding: 4px 8px !important;
    }

    /* Navigation links */
    .mobile-large-nav-link {
        font-size: 1.3rem !important;
        padding: 15px 20px !important;
        min-height: 48px !important;
        display: flex !important;
        align-items: center !important;
    }

    /* Buttons */
    .mobile-large-button {
        font-size: 1.1rem !important;
        padding: 12px 20px !important;
        min-height: 48px !important;
    }

    /* Logo */
    .mobile-large-logo {
        max-height: 55px !important;
    }
}

/* Tablet adjustments */
@media (min-width: 768px) and (max-width: 991px) {
    .mobile-extra-large-icon {
        font-size: 1.6rem !important;
    }

    .mobile-large-nav-link {
        font-size: 1.2rem !important;
    }
}

/* Desktop - keep original sizes */
@media (min-width: 992px) {
    /* All mobile classes will have no effect on desktop */
}
</style>
