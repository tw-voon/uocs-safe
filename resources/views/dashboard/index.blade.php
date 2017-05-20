@extends('master')
  @section('content')

<div class="row">

	<div class="col-sm-4">
		<div class="panel panel-default">
			<div class="panel-heading">Total Apps User</div>
  			<div class="panel-body">
  				<div class="row">
  					<div class="col-md-4">
  						<span class="glyphicon glyphicon-user"></span>
  					</div>
  					<div class="col-md-8">
						<h1>{{$users}} User</h1>
					</div>
  				</div>
  			</div>
		</div>
	</div>

	<div class="col-sm-4">
		<div class="panel panel-default">
			<div class="panel-heading">Total Post Pending</div>
  			<div class="panel-body">
  					<div class="row">
  						<div class="col-md-4">
  							<span class="glyphicon glyphicon-list-alt"></span>
  						</div>
  						<div class="col-md-8">
  							<h1>{{$unapprove}} Post</h1>
  						</div>
  					</div>
  			</div>
		</div>
	</div>

	<div class="col-sm-4">
		<div class="panel panel-default">
			<div class="panel-heading">Frequent Report Type</div>
  			<div class="panel-body">
  					<div class="row">
  						<div class="col-md-4">
  							<span class="glyphicon glyphicon-fire"></span>
  						</div>
  						<div class="col-md-8">
  							<h1>{{$trend->typeName}}</h1>
  						</div>
  					</div>
  			</div>
		</div>
	</div>
	
</div>

<div class="row">
<div class="col-md-8">
	<div class="panel panel-default">
		<div class="panel-heading">
      <div class="row">
        <div class="col-md-1"> <h5>Map</h5> </div> 
        <div class="col-md-9"> 
        <form action="{{route('dashboard.filter')}}" method="GET">
          <select id="filters" name="filter" class="form-control"> 
            <option value="0">All</option>
            @foreach($types as $type)
              <option value="{{$type->id}}">{{$type->typeName}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary">Search</button>
        </div> 
        </form>
      </div>
      </div>
  		<div class="panel-body">
			<div style="width: 100%; height: 500px;">
					{!! Mapper::render() !!}
			</div>
		</div>
	</div>
</div>
<div class="col-sm-4">
  <div class="panel panel-default">
  <form action="{{route('email.select')}}" method="get">
      <div class="panel-heading"> 
        <div class="row">
        <div class="col-md-9"> 
          <h5>Auto Report Section </h5> 
          </div> 
        <div class="col-md-2"> 
            <button type="submit" class="btn btn-primary">Report</button>
        </div> 
      </div>
      </div>
        <div class="panel-body">

        @foreach($isAutoReport as $report)

            @if($report->isAutoReport == 1)

            <div class="row">
              <div class="col-md-3">
                <span class="glyphicon glyphicon-envelope"></span>
              </div>
              <div class="col-md-8">
                <h3>{{$report->typeName}} <span style="font-size: 10pt"> ({{$report->count}} report) </span></h3>
              </div>
            </div> 

            @endif
          @endforeach
        @if(count($isAutoReport) == 0)
          <div class="row">
              <div class="col-md-4">
                <span class="glyphicon glyphicon-ok"></span>
              </div>
              <div class="col-md-8"> No post to report </div>
        @endif
        </div>
    </div>
    </form>
</div>
</div>

<div class="row">
	<a href="dashboard/automail">Send email</a>
</div>

  @stop