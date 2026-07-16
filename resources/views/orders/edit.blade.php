@extends('layout.master')
@section('title', 'Category')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>ویرایش سفارش #{{ $order->id }}</h4>

        <a href="{{ route('order_index') }}" class="btn btn-secondary">
            بازگشت
        </a>
    </div>

    <form action="{{ route('orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">شماره سفارش</label>

                        <input type="text" class="form-control" value="{{ $order->id }}" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">شهر</label>

                        <input type="text" class="form-control" value="{{ $order->address->city->name }}" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">مبلغ پرداختی</label>

                        <input type="text" class="form-control" value="{{ number_format($order->paying_amount) }} تومان"
                            disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">تاریخ سفارش</label>

                        <input type="text" class="form-control"
                            value="{{ verta($order->created_at)->format('%Y/%m/%d H:i') }}" disabled>
                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            وضعیت سفارش
                        </label>

                        <select name="status" class="form-select">

                            <option value="0" {{ $order->getRawOriginal('status') == 0 ? 'selected' : '' }}>
                                در انتظار پرداخت
                            </option>

                            <option value="1" {{ $order->getRawOriginal('status') == 1 ? 'selected' : '' }}>
                                در حال پردازش
                            </option>

                            <option value="2" {{ $order->getRawOriginal('status') == 2 ? 'selected' : '' }}>
                                ارسال شده
                            </option>

                            <option value="3" {{ $order->getRawOriginal('status') == 3 ? 'selected' : '' }}>
                                لغو شده
                            </option>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            وضعیت پرداخت
                        </label>

                        <select name="payment_status" class="form-select">

                            <option value="0" {{ $order->getRawOriginal('payment_status') == 0 ? 'selected' : '' }}>
                                ناموفق
                            </option>

                            <option value="1" {{ $order->getRawOriginal('payment_status') == 1 ? 'selected' : '' }}>
                                موفق
                            </option>

                        </select>

                    </div>

                </div>

            </div>

            <div class="card-footer text-end">

                <button class="btn btn-primary">
                    ذخیره تغییرات
                </button>

            </div>

        </div>

    </form>

    <hr class="my-5">

    <h5 class="mb-3">
        محصولات سفارش
    </h5>

    <div class="table-responsive">

        <table class="table table-bordered align-middle">

            <thead>

                <tr>
                    <th>تصویر</th>
                    <th>نام</th>
                    <th>قیمت</th>
                    <th>تعداد</th>
                    <th>جمع</th>
                </tr>

            </thead>

            <tbody>

                @foreach ($order->orderItems as $item)
                    <tr>

                        <td width="100">

                            <img src="{{ asset('images/products/' . $item->product->primary_image) }}"
                                class="img-fluid rounded">

                        </td>

                        <td>{{ $item->product->name }}</td>

                        <td>{{ number_format($item->price) }} تومان</td>

                        <td>{{ $item->quantity }}</td>

                        <td>{{ number_format($item->subtotal) }} تومان</td>

                    </tr>
                @endforeach

            </tbody>

        </table>

    </div>

@endsection
