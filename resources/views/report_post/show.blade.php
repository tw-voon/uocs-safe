@extends('master')
  @section('content')
  <div class="row">
    <div class="col-md-12">
      <h1>Show Data</h1>
    </div>
  </div>
  <div class="container">
  <div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#demo">
    <h4> Reported by: <strong>{{$report[0]->report->mobileuser->name}}</strong>, 
    Status: <?php 
    if($report[0]->status->id == 1)
        echo "<span class='glyphicon glyphicon-ok' data-toggle='tooltip' data-placement='right' title='{$report[0]->status->name}'>";
    elseif ($report[0]->status->id == 2) 
        echo "<span class='glyphicon glyphicon-remove' data-toggle='tooltip' data-placement='right' title='{$report[0]->status->name}'>";
    else
        echo "<span class='glyphicon glyphicon-option-horizontal' data-toggle='tooltip' data-placement='right' title='{$report[0]->status->name}'>";
             ?></h4> 
    </div>
    <div  id="demo" class="panel-body collapse in" style="padding-left: 2.2vw; padding-right: 2.2vw">
    <div class="row">
        <label>Report Title: </label><br>
        <input type="textarea" class="form-control" name="report_Description" disabled="true" value="{{$report[0]->report->report_Title}}">
      </div>
      <br>
      <div class="row">
        <label>Report Description: </label><br>
        <input type="textarea" class="form-control" name="report_Description" disabled="true" value="{{$report[0]->report->report_Description}}">
      </div>
      <br>
      <div class="row">
        <label>Report Category: </label><br>
        <input type="textarea" class="form-control" name="typeName" disabled="true" value="{{$report[0]->report->category->typeName}}">
      </div>
      <br>
      <div class="row">
        <label>Report Location: </label><br>
        <input type="text" class="form-control" name="report_Description" disabled="true" value="{{$report[0]->report->location->location_name}}"> <br>
        <div id="map"></div>
      </div>
      <br>
      <div class="row">
        <label>Report Image: </label><br>
        <div style="text-align: center">
          <img src="{{$report[0]->report->image}}" alt="{{$report[0]->report->report_Title}}">
        <input type="hidden" name="image" value="$report[0]->report->image">
        </div>
      </div>
    </div>
  </div>
</div>
{{--$status_detail--}}
<div class="container">
<form action="{{route('report.update',$report[0]->id)}}" method="post">
<input name="_method" type="hidden" value="PATCH">
      {{csrf_field()}}
   <div class="panel panel-primary">
    <div class="panel-heading">
      <h4><strong>Action to be taken: </strong></h4>
    </div>
    <div class="panel-body" style="padding-left: 2.2vw; padding-right: 2.2vw">
      <div class="row">
        <label>Status: </label><br>
        <select name="approve_status" class="form-control">
        @foreach($status_detail as $status)
          @if($status->id == $report[0]->status->id)
            <option value="{{$status->id}}" selected="true">{{$status->name}}</option>
          @else 
            <option value="{{$status->id}}">{{$status->name}}</option>
          @endif
        @endforeach
        </select>
      </div>
      <br>
      <div class="row">
        <label>Reason: </label><br>
        @if(isset($report[0]->reason))
          <input type="textarea" name="reason" class="form-control" value="{{$report[0]->reason}}">
        @else
           <input type="textarea" name="reason" class="form-control">
        @endif
      </div>
      <br>
      <div class="row">
        <label>Action taken: </label><br>
        @if(isset($report[0]->reason))
        <input type="textarea" name="action_taken" class="form-control" value="{{$report[0]->action_taken}}">
        @else
           <input type="textarea" name="action_taken" class="form-control">
        @endif
      </div>
      <br>
      <div class="row">
        <div class="clearfix">
          <button type="submit" value="save" class="btn btn-primary btn-lg pull-right">Submit</button>
        </div>
      </div>
    </div>
  </div>
</form>
</div>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEw4F7C21g9g3i7JnsNIemUxLUH2NpVDk&callback=initMap">
    </script>
<script>
      function initMap() {
        var uluru = {lat: {{$report[0]->report->location->location_latitute}}, lng: {{$report[0]->report->location->location_longitute}}};
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 15,
          center: uluru,
          zoomControl: false,
        });
        var marker = new google.maps.Marker({
          position: uluru,
          map: map
        });
      }
    </script>
  @stop