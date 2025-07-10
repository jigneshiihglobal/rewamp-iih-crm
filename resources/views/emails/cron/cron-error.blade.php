@extends('emails.main-layout')

@section('content')
    <table>
        <tr>
            <td>
                <p>{{$title ?? ''}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>Error: </p>
            </td>
        </tr>
        <tr>
            <td>
                {{$error ?? ''}}
            </td>
        </tr>
    </table>
@endsection
