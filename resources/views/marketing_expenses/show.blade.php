@extends('layouts.app')


@section('content')
    <section id="expense_show">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-1">
                        <h4 class="card-title">Expense Details</h4>
                        <div class="d-flex gap-75">
                            <a href="{{ route('marketing.expenses.index') }}" class="btn btn-outline-secondary btn-icon btn-sm"
                                data-bs-toggle='tooltip' data-bs-placement="bottom" title="Home">
                                <i data-feather="home"></i>
                            </a>
                            <a href="{{ route('marketing.expenses.edit', $expense->encrypted_id) }}"
                                class="btn btn-icon btn-outline-info btn-sm" data-bs-toggle='tooltip'
                                data-bs-placement="bottom" title="Edit">
                                <i data-feather="edit"></i>
                            </a>
                        </div>
                    </div>
                    <hr class="m-0">
                    <div class="card-body pt-1">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Amount</th>
                                    <td>{{ $expense->currency->symbol ?? '' }}{{ $expense->amount }}</td>
                                    <th>Date</th>
                                    <td>{{ $expense->marketing_expense_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Expense Type</th>
                                    <td>{{ $expense->marketing_expense_type->title ?? '' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
