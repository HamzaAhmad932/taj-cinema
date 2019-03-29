@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->display_name_plural)

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        </h1>
        @can('add', app($dataType->model_name))
            <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>{{ __('voyager::generic.add_new') }}</span>
            </a>
        @endcan
        @can('delete', app($dataType->model_name))
            @include('voyager::partials.bulk-delete')
        @endcan
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
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    <!-- Start Panel body -->
                    <div class="panel-body">
                        <!-- <form action="" method="get" class="form-search">
                            <div id="search-input">
                                <select id="search_key" name="key">
                                    <option value="id">Id</option>
                                    <option value="screen_id">Screen Id</option>
                                    <option value="movie_id">Movie Id</option>
                                    <option value="show_start_date">Show Start Date</option>
                                    <option value="show_time_start">Show Time Start</option>
                                    <option value="hall">Hall</option>
                                    <option value="gallery">Gallery</option>
                                    <option value="box">Box</option>
                                    <option value="created_at">Created At</option>
                                    <option value="updated_at">Updated At</option>
                                    <option value="formate">Formate</option>
                                    <option value="show_end_time">Show End Time</option>
                                </select>
                                <select id="filter" name="filter" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
                                    <option value="contains">contains</option>
                                    <option value="equals">=</option>
                                </select>
                                <div class="input-group col-md-12">
                                    <input type="text" class="form-control" placeholder="Search" name="s" value="">
                                    <span class="input-group-btn">
                                        <button class="btn btn-info btn-lg" type="submit">
                                            <i class="voyager-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </form> -->
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        {{--@can('delete', app('App\ShowClosed'))--}}
                                            <th>
                                                <input type="checkbox" class="select_all">
                                            </th>
                                        {{--@endcan--}}
                                        <th>Movie Name</th>
                                        <th>Distributor</th>
                                        <th>Screen</th>
                                        <th>Show Time</th>
                                        <th>Format</th>
                                        <th>Total Tickets</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shows as $show)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="row_id" id="checkbox_1" value="1">
                                        </td>
                                        <td><p>{{$show->show->movie->movie_name}}</p></td>
                                        <td><p>{{$show->distributor->distributor_name}}</p></td>
                                        <td><p>{{$show->show->screen->screen_name}}</p></td>
                                        <td><p>{{$show->show->show_time_start}}</p></td>
                                        <td><p>{{$show->show->formate}}</p></td>
                                        <td><p>{{$show->total_tickets}}</p></td>
                                        <td><p>{{$show->total_amount}}</p></td>
                                        <td>
                                            <button title="Shares" class="btn btn-sm btn-danger delete" data-id="{{$show->id}}" data-show-id="{{$show->show_id}}" data-amount="{{$show->total_amount}}" id="share-{{$show->id}}" data-distributor-name="{{$show->distributor->distributor_name}}" data-per-ticket-amount="{{$show->per_ticket_amount}}" data-total-tickets="{{$show->total_tickets}}" data-distributor-id="{{$show->distributor->id}}"
                                                data-hall-ticket="{{$show->show->hall}}" data-gallary-ticket="{{$show->show->gallery}}" data-box-ticket="{{$show->show->box}}" data-screen-id="{{$show->show->screen->id}}">
                                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Shares</span>
                                            </button>
                                        </td>
                                        <!-- <td class="no-sort no-click" id="bread-actions">
                                            <a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete" data-id="1" id="delete-1">
                                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Delete</span>
                                            </a>
                                            <a href="http://localhost:8000/admin/shows/1/edit" title="Edit" class="btn btn-sm btn-primary pull-right edit">
                                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Edit</span>
                                            </a>
                                            <a href="http://localhost:8000/admin/shows/1" title="View" class="btn btn-sm btn-warning pull-right view">
                                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">View</span>
                                            </a>
                                        </td> -->
                                    </tr>
                                    @endforeach()
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- Single delete modal --}}
    <div class="modal modal-info fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row">
                        <div class="col-sm-3 text-left">
                            <h4 class="modal-title"><i class="voyager-edit"></i> Shares</h4>
                        </div>
                        <div class="col-sm-4 text-right">
                            Remaining Tickets: <span id="remaining_ticket" style="font-weight: bold;">0</span>
                        </div>
                        <div class="col-sm-4 text-right">
                            Remaining amount: <span id="remaining_amount" style="font-weight: bold;">0</span>
                        </div>

                        <div class="col-sm-1">
                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Stepper form start -->
                    <div class="container">
                        <div class="stepwizard">
                            <div class="stepwizard-row setup-panel row">
                                <div class="stepwizard-step col-md-4">
                                    <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
                                    <p>Staff & Rep.</p>
                                </div>
                                <div class="stepwizard-step col-md-4">
                                    <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                                    <p>Distributor</p>
                                </div>
                                <div class="stepwizard-step col-md-4">
                                    <a href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
                                    <p>Other</p>
                                </div>
                            </div>
                        </div>
                        <form role="form">
                            <input type="hidden" id="show_id" value="">
                            <input type="hidden" id="show_closed_id" value="">
                            <input type="hidden" id="total_amount" value="">
                            <input type="hidden" id="total_tickets" value="">
                            <input type="hidden" id="per_ticket_amount" value="">
                            <input type="hidden" id="distributor_id" value="">
                            <input type="hidden" id="ticket_hall" value="">
                            <input type="hidden" id="ticket_gallary" value="">
                            <input type="hidden" id="ticket_box" value="">
                            <input type="hidden" id="screen_id" value="">

                            <div class="row setup-content" id="step-1">
                                <div class="col-xs-12">
                                    <div class="col-md-6">
                                        <h3> Staff</h3>
                                        <div class="form-group">
                                            <label class="control-label">Hall</label>
                                            <input id="staff_hall"  maxlength="10" type="number" required="required" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Gallary</label>
                                            <input id="staff_gallary" maxlength="10" type="number" required="required" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Box</label>
                                            <input id="staff_box" maxlength="10" type="number" required="required" class="form-control"  />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h3> Representative</h3>
                                        <div class="form-group">
                                            <label class="control-label">Hall</label>
                                            <input id="representative_hall"  maxlength="10" type="number" required="required" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Gallary</label>
                                            <input id="representative_gallary" maxlength="10" type="number" required="required" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Box</label>
                                            <input id="representative_box" maxlength="10" type="number" required="required" class="form-control"  />
                                        </div>
                                    </div>
                                    <button class="btn btn-primary nextBtn btn-lg pull-right" type="button" >Next</button>
                                </div>
                            </div>
                            <div class="row setup-content" id="step-2">
                                <div class="col-xs-12">
                                    <div class="col-md-12">
                                        <h3> Distributor</h3>
                                        <div class="form-group">
                                            <label class="control-label">Distributor Name</label>
                                            <input id="distributor_name" type="text" required="required" class="form-control" disabled />
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Percentage %</label>
                                            <input id="distributor_percentage" type="number" required="required" class="form-control" />
                                        </div>
                                        <button class="btn btn-primary nextBtn btn-lg pull-right" type="button" > Next</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row setup-content" id="step-3">
                                <div class="col-xs-12">
                                    <div class="col-md-12">
                                        <h3> Others</h3>
                                        <div class="form-group">
                                            <label class="control-label">Salary %</label>
                                            <input id="salary_percentage" type="text" required="required" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">AC %</label>
                                            <input id="ac_percentage" type="number" required="required" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">1 Rupee</label>
                                            <input id="1rupee_percentage" type="number" required="required" class="form-control" />
                                        </div>
                                        <button id="save_share_button" class="btn btn-primary nextBtn btn-lg pull-right" type="button" >Save</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        </div>
                    <!-- stepper form end -->
                </div>
                <!-- <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('voyager::generic.delete_confirm') }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                </div> -->
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('javascript')
    <script src="{{asset('js/wizard.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/blockui.js')}}"></script>
    <script>
        $(document).ready(function () {

        /********************************************************************
        * Core Functions definitions
        * *******************************************************************
        */
        var calculateStaffTickets = function(field) {
            let staff_hall_per = ($('#staff_hall').val()) == "" ? 0 : $('#staff_hall').val();
            let staff_box_per = ($('#staff_box').val()) == "" ? 0 : $('#staff_box').val();
            let staff_gallary_per = ($('#staff_gallary').val()) == "" ? 0 : $('#staff_gallary').val();
            let rep_hall_per = ($('#representative_hall').val()) == "" ? 0 : $('#representative_hall').val();
            let rep_gallary_per = ($('#representative_gallary').val()) == "" ? 0 : $('#representative_gallary').val();
            let rep_box_per = ($('#representative_box').val()) == "" ? 0 : $('#representative_box').val();

            let tickets = parseFloat(staff_hall_per) + parseFloat(staff_box_per) + parseFloat(staff_gallary_per) + parseFloat(rep_hall_per) + parseFloat(rep_gallary_per) + parseFloat(rep_box_per);
            let total_tickets = $('#total_tickets').val();
            let remaining_ticket = total_tickets - tickets;
            console.log('tickets : ' + tickets);

            // let tickts_amount = 0;

            let staff_hall_price = parseFloat(staff_hall_per) * $('#ticket_hall').val();
            let staff_gallary_price = parseFloat(staff_gallary_per) * $('#ticket_gallary').val();
            let staff_box_price = parseFloat(staff_box_per) * $('#ticket_box').val();
            let rep_hall_price = parseFloat(rep_hall_per) * $('#ticket_hall').val();
            let rep_gallary_price = parseFloat(rep_gallary_per) * $('#ticket_gallary').val();
            let rep_box_price = parseFloat(rep_box_per) * $('#ticket_box').val();

            let total_price = staff_hall_price + staff_gallary_price + staff_box_price + rep_hall_price + rep_gallary_price + rep_box_price;
            console.log('Total price : '+ total_price);

            // if(field == 'hall'){
            //     tickts_amount = $('#ticket_hall').val() * parseInt(staff_hall_per);
            // }
            
            // if(field == 'gallary'){
            //     tickts_amount = $('#ticket_gallary').val() * tickets;
            //     console.log('Gallary Price : '+$('#ticket_gallary').val());
            // }

            // if(field == 'box'){
            //     tickts_amount = $('#ticket_box').val() * tickets;
            // }
            // console.log("Total ticket amount : "+tickts_amount);

            
            let l_collection = $('#total_amount').val() - total_price;
            $('#remaining_ticket').text(remaining_ticket);
            $('#remaining_amount').text(l_collection);
            localStorage.setItem('remaining_amount', l_collection);

        };
        var calculateStaffPercentages = function() {

            // let staff_hall_per = ($('#staff_hall').val()) == "" ? 0 : $('#staff_hall').val();
            // let staff_box_per = ($('#staff_box').val()) == "" ? 0 : $('#staff_box').val();
            // let staff_gallary_per = ($('#staff_gallary').val()) == "" ? 0 : $('#staff_gallary').val();
            // let rep_hall_per = ($('#representative_hall').val()) == "" ? 0 : $('#representative_hall').val();
            let ac_percentage = ($('#ac_percentage').val()) == "" ? 0 : $('#ac_percentage').val();
            let salary_percentage = ($('#salary_percentage').val()) == "" ? 0 : $('#salary_percentage').val();
            // let dist_per = ($('#distributor_percentage').val()) == "" ? 0 : $('#distributor_percentage').val();


            let total_per = parseFloat(ac_percentage) + parseFloat(salary_percentage); //+ parseFloat(dist_per);
            // let total_per = parseFloat(staff_hall_per) + parseFloat(staff_box_per) + parseFloat(staff_gallary_per) + parseFloat(rep_hall_per) + parseFloat(rep_gallary_per) + parseFloat(rep_box_per) + parseFloat(dist_per);

            let t_collection = localStorage.getItem('remaining_amount_distributor');
            let per_amount = (total_per/100)*t_collection;
            let remaining_amount = t_collection - per_amount;
            $('#remaining_amount').text(remaining_amount);
            localStorage.setItem('remaining_amount_percentages', remaining_amount);
        };
        var calculateDistributerPercentage = function(){
            let dist_per = ($('#distributor_percentage').val()) == "" ? 0 : $('#distributor_percentage').val();


            let total_per = parseFloat(dist_per);//parseFloat(ac_percentage) + parseFloat(salary_percentage); //+ parseFloat(dist_per);
            // let total_per = parseFloat(staff_hall_per) + parseFloat(staff_box_per) + parseFloat(staff_gallary_per) + parseFloat(rep_hall_per) + parseFloat(rep_gallary_per) + parseFloat(rep_box_per) + parseFloat(dist_per);

            let t_collection = localStorage.getItem('remaining_amount');
            let per_amount = (total_per/100)*t_collection;
            let remaining_amount = t_collection - per_amount;
            $('#remaining_amount').text(remaining_amount);
            localStorage.setItem('remaining_amount_distributor', remaining_amount);
        };

        var calculateRupeeAmount = function() {
            
            let rupee_amount = ($('#1rupee_percentage').val()) == "" ? 0 : $('#1rupee_percentage').val();
            let remaining_amount = localStorage.getItem('remaining_amount_percentages');
            let pay = remaining_amount - rupee_amount;
            $('#remaining_amount').text(pay);

        };

        var fetchClosedShows = function(id){

            $('div.app-container').block({ css: { 
                border: 'none', 
                padding: '15px', 
                backgroundColor: '#000', 
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                opacity: .5, 
                color: '#fff' 
            } });
            
            $.ajax({
                url : "/fetch-closed-show/"+id,
                method : 'GET',
                success : function(resp) {
                    if(resp.status_code == 200){
                        populateClosedShow(resp.data);
                        $('div.app-container').unblock();
                        $('#delete_modal').modal('show');
                    }else{
                        emptyFields();
                        $('div.app-container').unblock();
                        $('#delete_modal').modal('show');
                    }
                    
                },
                error : function(err) {
                    console.log(err);
                }
            });
        };

        var populateClosedShow = function(data) {
                
            $('#staff_hall').val(data.staff.hall);
            $('#staff_box').val(data.staff.box);
            $('#staff_gallary').val(data.staff.gallary);
            $('#representative_hall').val(data.representative.hall);
            $('#representative_gallary').val(data.representative.gallary);
            $('#representative_box').val(data.representative.box);
            $('#distributor_id').val(data.distributor.distributor_id);
            $('#distributor_percentage').val(data.distributor.distributor_percentage);
            $('#salary_percentage').val(data.others.salary_percentage);
            $('#ac_percentage').val(data.others.ac_percentage);
            $('#1rupee_percentage').val(data.others.rupee_percentage);
            $('#total_tickets').text(data.collection.tickets);
            $('#remaining_amount').text(data.collection.amount);
        };

        var emptyFields = function() {
            $('#staff_hall').val("");
            $('#staff_box').val("");
            $('#staff_gallary').val("");
            $('#representative_hall').val("");
            $('#representative_gallary').val("");
            $('#representative_box').val("");
            $('#distributor_id').val("");
            $('#distributor_percentage').val("");
            $('#salary_percentage').val("");
            $('#ac_percentage').val("");
            $('#1rupee_percentage').val("");
            $('#total_tickets').text("");
            $('#remaining_amount').text("");
        };

        var saveAllShares = function(){
            var data = {
                show_id : '',
                show_closed_id : '',
                staff : {
                    hall : '',
                    gallary : '',
                    box : ''
                },
                representative : {
                    hall : '',
                    gallary : '',
                    box : ''
                },
                distributor : {
                    distributor_id : '',
                    distributor_percentage : ''
                },
                others : {
                    salary_percentage : '',
                    ac_percentage : '',
                    rupee_percentage : ''
                },
                collection : {
                    tickets : '',
                    amount : ''
                }
            };
            data.show_id = $('#show_id').val();
            data.show_closed_id = $('#show_closed_id').val();
            data.staff.hall = ($('#staff_hall').val()) == "" ? 0 : $('#staff_hall').val();
            data.staff.box = ($('#staff_box').val()) == "" ? 0 : $('#staff_box').val();
            data.staff.gallary = ($('#staff_gallary').val()) == "" ? 0 : $('#staff_gallary').val();
            data.representative.hall = ($('#representative_hall').val()) == "" ? 0 : $('#representative_hall').val();
            data.representative.gallary = ($('#representative_gallary').val()) == "" ? 0 : $('#representative_gallary').val();
            data.representative.box = ($('#representative_box').val()) == "" ? 0 : $('#representative_box').val();
            data.distributor.distributor_id = $('#distributor_id').val();
            data.distributor.distributor_percentage = ($('#distributor_percentage').val()) == "" ? 0 : $('#distributor_percentage').val();
            data.others.salary_percentage = ($('#salary_percentage').val()) == "" ? 0 : $('#salary_percentage').val();
            data.others.ac_percentage = ($('#ac_percentage').val()) == "" ? 0 : $('#ac_percentage').val();
            data.others.rupee_percentage = ($('#1rupee_percentage').val()) == "" ? 0 : $('#1rupee_percentage').val();
            data.collection.tickets = ($('#total_tickets').text()) == "" ? 0 : $('#total_tickets').text();
            data.collection.amount = ($('#remaining_amount').text()) == "" ? 0 : $('#remaining_amount').text();

            $('#delete_modal .modal-content').block({ css: { 
                border: 'none', 
                padding: '15px', 
                backgroundColor: '#000', 
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                opacity: .5, 
                color: '#fff' 
            } });
            
            $.ajax({
                url : "{{route('save-shares')}}",
                method : 'POST',
                data : data,
                success : function(resp) {
                    if(resp.status_code == 200){
                        $('#delete_modal .modal-content').unblock();
                        toastr.success(resp.message);
                        $('#delete_modal').modal('hide');
                    }else{
                        $('#delete_modal .modal-content').unblock();
                        toastr.error(resp.message);
                    }
                    
                },
                error : function(err) {
                    console.log(err);
                }
            });
        };

            $('#search-input select').select2({
                minimumResultsForSearch: Infinity
            });
            
            $('.select_all').on('click', function(e) {
                $('input[name="row_id"]').prop('checked', $(this).prop('checked'));
            });
            
            var deleteFormAction;

        /********************************************************************
        * Event Call Functionality
        * *******************************************************************
        */

            $('td').on('click', '.delete', function (e) {
                // $('#delete_form')[0].action = 'http://localhost:8000/admin/shows/__id'.replace('__id', $(this).data('id'));
                $('#show_id').val($(this).data('show-id'));
                $('#show_closed_id').val($(this).data('id'));
                $('#total_amount').val($(this).data('amount'));
                $('#total_tickets').val($(this).data('total-tickets'));
                $('#distributor_name').val($(this).data('distributor-name'));
                $('#per_ticket_amount').val($(this).data('per-ticket-amount'));
                $('#distributor_id').val($(this).data('distributor-id'));
                $('#ticket_hall').val($(this).data('hall-ticket'));
                $('#ticket_gallary').val($(this).data('gallary-ticket'));
                $('#ticket_box').val($(this).data('box-ticket'));
                $('#screen_id').val($(this).data('screen-id'));

                if($(this).data('screen-id') == 1) {
                    $('#staff_gallary').parent().show();
                    $('#staff_box').parent().show();
                    $('#representative_gallary').parent().show();
                    $('#representative_box').parent().show();                    
                }else{
                    $('#staff_gallary').parent().hide();
                    $('#staff_box').parent().hide();
                    $('#representative_gallary').parent().hide();
                    $('#representative_box').parent().hide(); 
                }
                fetchClosedShows($(this).data('id'));
                
            });

            $('#staff_hall').on('change, mouseup, keyup', function(e){
                calculateStaffTickets('hall');
            });

            $('#staff_gallary').on('change, mouseup, keyup', function(e){
                calculateStaffTickets('gallary');
            });

            $('#staff_box').on('change, mouseup, keyup', function(e){
                calculateStaffTickets('box');
            });
            $('#representative_hall').on('change, mouseup, keyup', function(e){
                calculateStaffTickets('hall');
            });

            $('#representative_gallary').on('change, mouseup, keyup', function(e){
                calculateStaffTickets('gallary');
            });

            $('#representative_box').on('change, mouseup, keyup', function(e){
                calculateStaffTickets('box');
            });

            $('#distributor_percentage').on('change, mouseup, keyup', function(e){
                calculateDistributerPercentage();
            });
            
            $('#ac_percentage').on('change, mouseup, keyup', function(e){
                calculateStaffPercentages();
            });

            $('#salary_percentage').on('change, mouseup, keyup', function(e){
                calculateStaffPercentages();
            });

            $('#1rupee_percentage').on('change, mouseup, keyup', function(e){
                calculateRupeeAmount();
            });

            $('#save_share_button').on('click', function(e){
                saveAllShares();
            });
        });        
    
    </script>
@stop