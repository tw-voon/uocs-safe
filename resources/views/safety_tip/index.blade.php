@extends('master')
  @section('content')
  <div class="row">
    <div class="col-md-12">
      <h1>Safety Tips Category Management</h1>
    </div>
  </div>
	<br>
  <div class="row table-responsive">
        <table class="table table-striped table-hover">
          <tr>
            <th>No.</th>
            <th>Category Name</th>
            <th>Actions</th>
          </tr>          
          <a href="{{route('safety_tip.create')}}" class="btn btn-info pull-right">Create New Category</a><br><br>
          <?php $no=1; ?>
          @if(count($tips) != 0)
          @foreach($tips as $tip)
            <tr>
              <td>{{$no++}}</td>
              <td>{{$tip->category_name}}</td>
              <td class="col-md-2">
                <form class="" action="{{route('safety_tip.destroy',$tip->id)}}" method="post">
                  <input type="hidden" name="_method" value="delete">
                  <input type="hidden" name="_token" value="{{ csrf_token() }}">
                  <a href="{{route('tip_category.index',$tip->id)}}" class="btn btn-primary btn-block">View Detail</a>
                  <a href="{{route('safety_tip.edit',$tip->id)}}" class="btn btn-primary btn-block">Edit</a>
                  <input type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure to delete this data');" name="name" value="delete">
                </form>
              </td>
            </tr>
          @endforeach
          @endif
        </table>
  @stop