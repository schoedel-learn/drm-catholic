@extends('velzon.layouts.auth')
@section('title')
    Register
@endsection
@section('content')
    <!-- auth-page wrapper -->
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden m-0">
                            <div class="row justify-content-center g-0">
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                        <div class="position-relative h-100 d-flex flex-column">
                                            <div class="mb-4">
                                                <a href="/" class="d-block">
                                                    <img src="{{ URL::asset('images/cw-icon-white.svg') }}"
                                                        alt="Catholic.Work" height="40">
                                                </a>
                                            </div>
                                            <div class="mt-auto">
                                                <div class="mb-3">
                                                    <i class="ri-double-quotes-l display-4" style="color: #818CF8;"></i>
                                                </div>
                                                <div id="qoutescarouselIndicators" class="carousel slide"
                                                    data-bs-ride="carousel">
                                                    <div class="carousel-indicators">
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="0" class="active" aria-current="true"
                                                            aria-label="Slide 1"></button>
                                                        <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                            data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                    </div>
                                                    <div class="carousel-inner text-center text-white-50 pb-5">
                                                        <div class="carousel-item active">
                                                            <p class="fs-15 fst-italic">" Join Catholic.Work and empower
                                                                your diocesan ministry. "</p>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <p class="fs-15 fst-italic">" Modern tools for building and
                                                                nurturing your faith community. "</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h5 class="text-primary">Create Account</h5>
                                            <p class="text-muted">Get started with Catholic.Work today.</p>
                                        </div>

                                        <div class="mt-4">
                                            <form method="POST" action="{{ route('register') }}" class="needs-validation"
                                                novalidate>
                                                @csrf

                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('name') is-invalid @enderror" id="name"
                                                        name="name" value="{{ old('name') }}" placeholder="Enter your name"
                                                        required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email"
                                                        class="form-control @error('email') is-invalid @enderror" id="email"
                                                        name="email" value="{{ old('email') }}"
                                                        placeholder="Enter email address" required>
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label" for="password">Password <span
                                                            class="text-danger">*</span></label>
                                                    <div class="position-relative auth-pass-inputgroup">
                                                        <input type="password"
                                                            class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                                                            id="password" name="password" placeholder="Enter password"
                                                            required>
                                                        <button
                                                            class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                            type="button" id="password-addon">
                                                            <i class="ri-eye-fill align-middle"></i>
                                                        </button>
                                                        @error('password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label" for="password_confirmation">Confirm Password
                                                        <span class="text-danger">*</span></label>
                                                    <input type="password"
                                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                                        id="password_confirmation" name="password_confirmation"
                                                        placeholder="Confirm password" required>
                                                    @error('password_confirmation')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                                                    <div class="mb-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input @error('terms') is-invalid @enderror"
                                                                type="checkbox" id="terms" name="terms" required>
                                                            <label class="form-check-label" for="terms">
                                                                I agree to the <a href="{{ route('terms.show') }}"
                                                                    target="_blank" class="text-primary">Terms of Service</a>
                                                                and <a href="{{ route('policy.show') }}" target="_blank"
                                                                    class="text-primary">Privacy Policy</a>
                                                            </label>
                                                            @error('terms')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="mt-4">
                                                    <button class="btn btn-primary w-100" type="submit"
                                                        style="background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); border: none; box-shadow: 0 4px 15px rgba(79,70,229,0.35);">Create
                                                        Account</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="mt-5 text-center">
                                            <p class="mb-0">Already have an account? <a href="{{ route('login') }}"
                                                    class="fw-semibold text-primary text-decoration-underline"> Sign in</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0">&copy;
                                <script>document.write(new Date().getFullYear())</script> Catholic.Work. Built with <i
                                    class="mdi mdi-heart text-danger"></i> for the Church
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
@endsection