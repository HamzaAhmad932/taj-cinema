@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="icon voyager-new"></i> ADD COA Main
    </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                       <div class="row">
                           <div class="col-md-6">
                               <div class="form-group">
                                    <label for="id">ID</label>
                                    <input type="text" class="form-control" disabled="disabled" value="{{$getMaxId+1}}">
                               </div>
                           </div>
                           <div class="col-md-6">
                               <div class="form-group">
                                    <label for="id">Account Name</label>
                                    <input v-model="account_name" type="text" class="form-control">
                                    <span v-if="hasError.account_name" class="text-danger" role="alert">
                                        <strong>@{{errorMessage.account_name}}</strong>
                                    </span>
                               </div>
                           </div>
                       </div>
                       @if($type != 'M')
                       <div class="row">
                           <div class="col-md-6">
                               <div class="form-group">
                                    <label for="parent_id">Parent Account</label>
                                   <select class="form-control select2" name="parent_id" id="parent_id" v-model="parent_id" v-select2>
                                        @foreach($ac_parent as $ac)
                                        <option :value="{{$ac->id}}">{{$ac->account_name}}</option>
                                        @endforeach()
                                   </select>
                                   <span v-if="hasError.parent_id" class="text-danger" role="alert">
                                        <strong>@{{errorMessage.parent_id}}</strong>
                                    </span>
                               </div>
                           </div>
                       </div>
                       @endif()
                       <div class="row">
                           <div class="col-md-6"></div>
                           <div class="col-md-6 text-right">
                               <button class="btn btn-primary" @click.prevent="ac_save()">Save</button>
                           </div>
                       </div>
                    </div>
                </div>
        </div>
    </div>
@stop

@section('javascript')
<script type="text/javascript" src="{{asset('js/blockui.js')}}"></script>
    <script>
        Vue.directive('select2', {
            inserted(el) {
                $(el).on('select2:select', () => {
                    const event = new Event('change', { bubbles: true, cancelable: true });
                    el.dispatchEvent(event);
                });

                $(el).on('select2:unselect', () => {
                    const event = new Event('change', {bubbles: true, cancelable: true})
                    el.dispatchEvent(event)
                })
            },
        });
        var app = new Vue({
            el : '.page-content',
            data() {
                return {
                    account_name : '',
                    parent_id : '',
                    hasError : {
                        parent_id : false,
                        account_name : false
                    },
                    errorMessage : {
                        parent_id : '',
                        account_name : ''
                    }
                }
            },

            methods : {
              ac_save(){

                // Reset State 
                this.hasError = {
                        parent_id : false,
                        account_name : false
                    };
                this.errorMessage = {
                        parent_id : '',
                        account_name : ''
                    };

                $('body').block({ css: { 
                    border: 'none', 
                    padding: '15px', 
                    backgroundColor: '#000', 
                    '-webkit-border-radius': '10px', 
                    '-moz-border-radius': '10px', 
                    opacity: .5, 
                    color: '#fff' 
                } });
                let data = {
                    'account_name' : this.account_name,
                    'parent_id' : this.parent_id,
                    'type' : '{{$type}}'
                };
                axios({
                    url : "{{route('coa-save')}}",
                    method : 'POST',
                    data
                
                })
                .then((resp)=>{
                    $('body').unblock();
                    if(resp.data.status){
                        toastr.success(resp.data.message);
                        setTimeout(function(){
                            let loc = window.location;
                            // window.location = loc.protocol+"//"+loc.hostname+"/login";
                            window.location = '/admin/accounts';
                        }, 1000);
                    }
                })
                .catch((err)=>{
                    $('body').unblock();

                    let hasErr = this.hasError;
                    let errMsg = this.errorMessage;

                    var errors = err.response;
                    console.log(errors);
                    if (errors.statusText === 'Unprocessable Entity') {
                        if (errors.data) {
                            if (errors.data.errors.parent_id) {
                                let e = errors.data.errors;

                                hasErr.parent_id = true;
                                errMsg.parent_id = Array.isArray(e.parent_id) ? e.parent_id[0] : e.parent_id;
                            }
                            if (errors.data.errors.account_name) {
                                let e = errors.data.errors;

                                hasErr.account_name = true;
                                errMsg.account_name = Array.isArray(e.account_name) ? e.account_name[0] : e.account_name;
                            }
                        }
                    }
                });
              }
            }
        });
    </script>
@stop