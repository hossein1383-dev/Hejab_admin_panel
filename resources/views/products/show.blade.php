@extends('layout.master')
@section('title', 'نمایش محصول')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h4 class="fw-bold">نمایش محصول</h4>
    </div>

    <div class="row gy-4 mb-5">

        <div class="col-md-12 mb-5">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <img src="{{ asset('images/products/' . $product->primary_image) }}" class="rounded" width=350 height=220
                        alt="primary-image">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">نام</label>
            <input disabled type="text" value="{{ $product->name }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label class="form-label">دسته بندی</label>
            <input disabled type="text" value="{{ $product->category->name }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label class="form-label">وضعیت</label>
            <input disabled type="text" value="{{ $product->status ? 'فعال' : 'غیر فعال' }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label class="form-label">قیمت</label>
            <input disabled type="text" value="{{ number_format($product->price) }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label class="form-label">تعداد</label>
            <input disabled type="text" value="{{ $product->quantity }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label class="form-label">قیمت حراجی</label>
            <input disabled type="text"
                value="{{ $product->sale_price ? number_format($product->sale_price) : 'ندارد' }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label class="form-label">تاریخ شروع حراجی</label>
            <input disabled type="text"
                value="{{ $product->date_on_sale_from != null ? getJalaliDate($product->date_on_sale_from) : '' }}"
                class="form-control" />
        </div>

        <div class="col-md-3">
            <label class="form-label">تاریخ پایان حراجی</label>
            <input disabled type="text"
                value="{{ $product->date_on_sale_to != null ? getJalaliDate($product->date_on_sale_to) : '' }}"
                class="form-control" />
        </div>

        <div class="col-md-12">
            <label class="form-label">توضیحات</label>
            <textarea disabled rows="5" class="form-control">{{ $product->description }}</textarea>
        </div>

        <!-- ===== نمایش سایزها و موجودی ===== -->
        <!-- ===== نمایش سایزها ===== -->
        <div class="product-sizes mt-4">
            <h5>سایزهای موجود:</h5>
            @if ($product->sizes->isNotEmpty())
                <div class="d-flex flex-wrap gap-3 mt-2" id="sizeCheckboxes">
                    @foreach ($product->sizes as $size)
                        <div class="form-check">
                            <input class="form-check-input size-checkbox" type="checkbox" name="size"
                                value="{{ $size->size_name }}" id="size_{{ $size->size_name }}"
                                {{ $size->stock == 0 ? 'disabled' : '' }}>
                            <label class="form-check-label" for="size_{{ $size->size_name }}">
                                {{ $size->size_name }}
                                <span class="badge {{ $size->stock ? 'bg-success' : 'bg-danger' }} ms-1">
                                    {{ $size->stock ? 'موجود' : 'ناموجود' }}
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
            @else
        
            @endif
        </div>

        <div class="col-md-12">
            <label class="form-label">تصاویر دیگر</label>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($product->images as $image)
                    <img class="rounded" width="200" src="{{ asset('images/products/' . $image->image) }}"
                        alt="images">
                @endforeach
            </div>
        </div>
    </div>
@endsection
