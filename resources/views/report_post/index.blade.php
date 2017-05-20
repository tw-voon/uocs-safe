@extends('master')
  @section('content')
  <div class="row">
    <div class="col-md-12">
      <h1>Report</h1>
    </div>
  </div>
    <div class="row table-responsive">
        <table class="table table-striped table-hover">
          <tr>
            <th>No.</th>
            <th>Title</th>
            <th>Description</th>
            <th>Location</th>
            <th>Status</th>
            <th>Image</th>
            <th>Actions</th>
          </tr>
          <br>
          <?php $no=1; ?>
          @if(count($blogs) != 0)
          @foreach($blogs as $blog)
            <tr>
              <td>{{$no++}}</td>
              <td>{{$blog->report->report_Title}}</td>
              <td>{{$blog->report->report_Description}}</td>
              <td>{{$blog->report->location->location_name}}</td>
              <td>{{$blog->status->name}}</td>
              <td><img class="img-responsive" style=" max-width: 200px; margin: auto;" src="{{$blog->report->image}}"/></td>
              <td>
                <form class="" action="{{route('report.destroy',$blog->id)}}" method="post">
                  <input type="hidden" name="_method" value="delete">
                  <input type="hidden" name="_token" value="{{ csrf_token() }}">
                  <a href="{{route('report.edit',$blog->id)}}" class="btn btn-primary btn-block">Show</a>
                  <input type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure to delete this data');" name="name" value="delete">
                </form>
              </td>
            </tr>
          @endforeach
          @endif
        </table>
      </div>
    @if (session('status'))
        <div id="success-alert" class="alert alert-success fade in col-sm-4" style="text-align: center; position: absolute;">
            {{ session('status') }}
        </div>
    @endif
      <script type="text/javascript">
       $(document).ready (function(){ 
        $("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
        $("#success-alert").slideUp(500);
      });
       });
        
      </script>

      {!! $blogs->links('pagination') !!}
  @stop