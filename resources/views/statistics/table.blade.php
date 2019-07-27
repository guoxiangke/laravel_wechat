@extends('layout')
@section('title', '统计')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 col-sm-12"> 
            <div class="table-responsive">
              <table class="table">
                  <thead>
                    <tr>
                        <th scope="col">counts</th>
                        <th scope="col">#</th>
                    </tr>
                  </thead>
                  <tbody>
                        @foreach($statistics as $statistic)
                        <tr>
                            <td scope="row">{{$statistic->total}}</td>
                            <td scope="row">{{$statistic->type}}</td>
                        </tr>
                        @endforeach
                  </tbody>
              </table>
            </div>
        </div>
    </div>
</div>
@endsection
