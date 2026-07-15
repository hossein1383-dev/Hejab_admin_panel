@extends('layout.master')
@section('title', 'Category')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h4 class="fw-bold">کاربران</h4>
        <a href="{{ route('user.create') }}" class="btn btn-sm btn-outline-primary">ایجاد کاربر</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>نام</th>
                    <th>شماره تلفن</th>
                    <th>ایمیل</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->cellphone }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div class="d-flex">
                                <a href="{{ route('user.edit', $user) }}" class="btn btn-sm btn-outline-info me-2">ویرایش</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
