@extends('front::layouts.master', ['title' => 'کاتالوگ محصولات آسا'])

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('themes/defaultTheme/css/catalog/catalog.css') }}">
@endpush
@section('content')
    <section class="articles">
        @foreach($catalogs as $catalog)
            <article>
                <div class="article-wrapper">
                    <figure>
                        <img src="{{$catalog['image']}}" alt="{{$catalog['meta_description']}}" />
                    </figure>
                    <div class="article-body">
                        <h2>{{$catalog['title']}}</h2>
                        <p class='text-catalog'>{{ $catalog['description'] }}</p>
                        <div class='btn-custom'>
                            <a href="{{ $catalog['link'] }}" class="btn btn-primary read-more">دانلود
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20"
                                     fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                          clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>

                    </div>
                </div>
            </article>
        @endforeach
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('themes/defaultTheme/js/catalog/catalog.js') }}"></script>
@endpush
