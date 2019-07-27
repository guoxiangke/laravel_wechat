@extends ('layout')

@section('title')
【600】节目简介
@endsection

@section('content')
<div class="accordion" id="accordionExample">
  @foreach($categorys as $key => $category)
    @php
      $expanded = 'false';
      $collapsed = 'collapsed';
      $show = '';
      if($key==0) {
        $expanded= 'true';
        $collapsed = '';
        $show = 'show';
      }

    @endphp
    <div class="card">
      <div class="card-header" id="heading{{$key}}">
        <h2 class="mb-0">
          <button class="btn btn-link {{$collapsed}}" type="button" data-toggle="collapse" data-target="#collapse{{$key}}" aria-expanded="{{$expanded}}" aria-controls="collapse{{$key}}">
            <i class="arrow"></i>
            {{$category}}
          </button>
        </h2>
      </div>
      <div id="collapse{{$key}}" class="collapse {{$show}}" aria-labelledby="heading{{$key}}" data-parent="#accordionExample">
        <div class="card-body">

          @foreach ($lymetas as $lymeta)
            @if($lymeta->category != $key)
              @continue
            @endif
            <a href="https://txly2.net/{{$lymeta->code}}" target="_blank">
            <div class="no-a-color shadow-lg p-3 mb-5 bg-white rounded">
              <p>编号:【{{$lymeta->index}}】{{$lymeta->name}}</p>
              <img src="https://images.weserv.nl/?w=194&url={{$lymeta->image}}">
              <p>简介: {{$lymeta->description}}<br/>主持人: {{$lymeta->author}}</p>
            </div>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  @endforeach
</div>
<div class="mt-3 mb-3 text-center">
  <img src="https://images.weserv.nl/?url=http://txly2.net/images/Liangyou.png" class="rounded">
</div>

  {{-- @php
    $tmp_category = ''
  @endphp
  @foreach($categorys as $key => $category)
  <a href="#category{{$key}}"> {{$category}}</a>
  @endforeach

  <h5 id="category0">{{ $categorys[0] }}</h5>
  @foreach ($lymetas as $key => $lymeta)
    @if($lymeta->category != $tmp_category)
      <h5 id="category{{$lymeta->category}}"> {{ $categorys[$lymeta->category] }}</h5>
      @php
        $tmp_category = $lymeta->category
      @endphp
    @endif
    <p>编号:【{{$lymeta->index}}】{{$lymeta->name}} / {{$lymeta->author}}</p>
    <img src="{{$lymeta->image}}">
    <p>{{$lymeta->description}}</p>
  @endforeach --}}
@endsection

@section('styles')
  <style type="text/css">
    a{
      color: #212529;
      text-decoration:none;
    }
    a:hover{
      text-decoration:none;
    }
    #accordionExample{
      border: 1px solid rgba(0, 0, 0, 0.125);
      border-bottom: none;
    }
    #accordionExample .card{
      border: none;
    }
    .card-header{
      padding: 0.75rem 0rem;
    }
    h2.mb-0 button{
      width: 100%;
      text-align: left;
    }
    h2.mb-0 button:hover{
      text-decoration:none;
    }
    .mb-0 button.btn-link i{
      background-image: url(/images/arrow_down.png);
      background-repeat: no-repeat;
      background-size: 12px;
      background-position-y: center;
      transition: all 0.2s ease;
      width: 12px;
      height: 12px;
      display: inline-block;
    }
    .mb-0 button.collapsed i{
      -webkit-transform: rotate(-90deg);
      -moz-transform: rotate(-90deg);
      -ms-transform: rotate(-90deg);
      -o-transform: rotate(-90deg);
      transform: rotate(-90deg);
    }
  </style>
@endsection
