@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->display_name_plural)

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        </h1>
        @can('add', app($dataType->model_name))
            <a href="{{ route('coa-add', config('db_const.account.account_type.main_type')) }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>ADD COA Main</span>
            </a>
            <a href="{{ route('coa-add', config('db_const.account.account_type.sub_type')) }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>ADD COA Sub</span>
            </a>
            <a href="{{ route('coa-add', config('db_const.account.account_type.subsidiary_type')) }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>ADD COA Subsidiary</span>
            </a>
        @endcan
        {{-- @can('delete', app($dataType->model_name))
            @include('voyager::partials.bulk-delete')
        @endcan --}}
        @can('edit', app($dataType->model_name))
            @if(isset($dataType->order_column) && isset($dataType->order_display_column))
                <a href="{{ route('voyager.'.$dataType->slug.'.order') }}" class="btn btn-primary">
                    <i class="voyager-list"></i> <span>{{ __('voyager::bread.order') }}</span>
                </a>
            @endif
        @endcan
        @include('voyager::multilingual.language-selector')
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        @foreach($pAccounts as $key1 => $account)
                            <span>{{$account->account_name}}</span>
                            <span class="pull-right"><a class="btn btn-danger delete" data-id="{{$account->id}}" href="">Delete</a></span>
                            <span class="pull-right"><a class="btn btn-primary" href="{{ route('voyager.'.$dataType->slug.'.edit', $account->id) }}">Edit</a></span>
                            <hr>
                            @foreach($sAccounts as $key2 => $sub)
                                @if($sub->parent_id == $account->id)
                                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&nbsp;</span><span>{{$sub->account_name}}</span>
                                <span class="pull-right" style="margin-left: 8px;"><a class="btn btn-danger" @click.prevent="test()" data-id="{{$sub->id}}">Delete</a></span>
                                <span class="pull-right"><a class="btn btn-primary" href="{{ route('voyager.'.$dataType->slug.'.edit', $sub->id) }}">Edit</a></span>
                                <hr>
                                @foreach($sbAccounts as $key3 => $sb)
                                    @if($sb->parent_id == $sub->id)
                                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----&nbsp;</span><span>{{$sb->account_name}}</span>
                                    <span class="pull-right" style="margin-left: 8px;"><a class="btn btn-danger" @click.prevent="test()" data-id="{{$sb->id}}">Delete</a></span>
                                    <span class="pull-right"><a class="btn btn-primary" href="{{ route('voyager.'.$dataType->slug.'.edit', $sb->id) }}">Edit</a></span>
                                    <hr>
                                    @endif()
                                @endforeach()
                                @endif()
                            @endforeach()
                        @endforeach()
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Single delete modal --}}
    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager::generic.delete_question') }} {{ strtolower($dataType->display_name_singular) }}?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('voyager::generic.delete_confirm') }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('css')
@if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
    <link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
@endif
@stop

@section('javascript')
    <!-- DataTables -->
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    <script>
        $(document).ready(function () {
            @if (!$dataType->server_side)
                var table = $('#dataTable').DataTable({!! json_encode(
                    array_merge([
                        "order" => $orderColumn,
                        "language" => __('voyager::datatable'),
                        "columnDefs" => [['targets' => -1, 'searchable' =>  false, 'orderable' => false]],
                    ],
                    config('voyager.dashboard.data_tables', []))
                , true) !!});
            @else
                $('#search-input select').select2({
                    minimumResultsForSearch: Infinity
                });
            @endif

            @if ($isModelTranslatable)
                $('.side-body').multilingual();
                //Reinitialise the multilingual features when they change tab
                $('#dataTable').on('draw.dt', function(){
                    $('.side-body').data('multilingual').init();
                })
            @endif
            $('.select_all').on('click', function(e) {
                $('input[name="row_id"]').prop('checked', $(this).prop('checked'));
            });
        });


        var deleteFormAction;
        // $('#delete').on('click', function (e) {
        //     console.log('working');
        //     $('#delete_form')[0].action = '{{ route('voyager.'.$dataType->slug.'.destroy', ['id' => '__id']) }}'.replace('__id', $(this).data('id'));
        //     $('#delete_modal').modal('show');
        // });
        var instance = new Vue({
            elem : '.page-content',
            data : {

            },
            methods : {
                test(){
                    alert('working');
                }
            }
        });
    </script>
@stop
