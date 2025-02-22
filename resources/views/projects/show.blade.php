@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $project->name }}</strong> rev. {{ $project->revision }} (by {{ $project->user->name }})
                    @can('update', $project)
                    <div class="pull-right">
                        <a class="btn btn-primary btn-xs" href="{{ route('projects.edit', ['project' => $project->slug])  }}">edit</a>
                    </div>
                    @endcan
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-8 clearfix">
                            {!! $project->descriptionHtml !!}
                        </div>
                        <div class="col-md-4 clearfix">
                            Category: {{ $project->category }}
                            <hr>
                            Status: {{ $project->status }}
                            @include('projects.partials.vote-and-notify')
                            @include('projects.partials.show-compatibility')
                            @include('projects.partials.show-dependencies')
                        </div>
                        @if($project->versions()->published()->count() > 0)
                        <div class="col-md-12 clearfix">
                            <a href="{{ url($project->versions()->published()->get()->last()->zip) }}" class="btn btn-default">Download latest egg (tar.gz)</a>

                        </div>
                        @endif
                        <div class="col-md-12 clearfix">
                            @include('projects.partials.show-files')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
