@extends('layouts.app')


@section('content')
    <section id="expense_show">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-1">
                        <h4 class="card-title">Expense Details</h4>
                        <div class="d-flex gap-75">
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-icon btn-sm"
                                data-bs-toggle='tooltip' data-bs-placement="bottom" title="Home">
                                <i data-feather="home"></i>
                            </a>
                            <a href="{{ route('expenses.edit', $expense->encrypted_id) }}"
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
                                    <th>Client</th>
                                    <td>{{ $expense->client->name }}</td>
                                    <th>Project</th>
                                    <td>{{ $expense->project_name }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td>{{ $expense->currency->symbol ?? '' }}{{ $expense->amount }}</td>
                                    <th>Date</th>
                                    <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Expense Type</th>
                                    <td>{{ $expense->expense_sub_type->expense_type->title ?? '' }}</td>
                                    <th>Expense Sub Type</th>
                                    <td>{{ $expense->expense_sub_type->title ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $expense->type == '1' ? 'Recurring' : 'One-off' }}</td>
                                    <th>{{ $expense->type == '1' ? 'Frequency' : '' }}</th>
                                    <td>{{ $expense->type == '1' ? ($expense->frequency == '1' ? 'Yearly' : 'Monthly') : '' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
