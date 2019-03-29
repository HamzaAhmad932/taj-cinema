@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="icon voyager-new"></i> Ledger
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-right">
                              <button class="btn btn-primary" @click.prevent="addNewRow()">ADD New Row</button>
                              <button class="btn btn-success btn-add-new">Save</button>  
                            </div>
                        </div>
                       <table class="table table-striped table-bordered">
                           <thead>
                               <th>Sr#</th>
                               <th>Account</th>
                               <th>Date</th>
                               <th>Description</th>
                               <th>Debit</th>
                               <th>Credit</th>
                               <th>Action</th>
                           </thead>
                           <tbody>
                               <tr v-for="(j, i) in jv">
                                   <td>@{{i+1}}</td>
                                   <td style="width: 200px;">
                                      <select name="account_id" v-model="j.account_id" id="account_id" class="form-control select2" v-select2>
                                       
                                      </select>
                                   </td>
                                   <td>
                                     <input v-model="j.date" type="date"  class="form-control">
                                   </td>
                                   <td>
                                     <input v-model="j.description" type="text" class="form-control">
                                   </td>
                                   <td>
                                     <input v-model="j.debit" type="number" class="form-control">
                                   </td>
                                   <td>
                                     <input v-model="j.credit" type="number" class="form-control">
                                   </td>
                                   <td><button class="btn btn-default" @click.prevent="removeRow(i)">Remove</button></td>
                               </tr>
                           </tbody>
                       </table> 
                    </div>
                </div>
        </div>
    </div>
@stop

@section('javascript')
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
                    jv : [{
                        date : '',
                        account_id : '',
                        description : '',
                        debit : '',
                        credit : '',
                    },
                    {
                        date : '',
                        account_id : '',
                        description : '',
                        debit : '',
                        credit : '',
                    }
                    ]
                }
            },

            methods : {
              addNewRow(){
                this.jv.push({
                    date : '',
                    account_id : '',
                    description : '',
                    debit : '',
                    credit : '',
                });
              },
              removeRow(i){
                if(this.jv.length > 1){
                    this.jv.splice(i, 1);
                }
              },
            }
        });
    </script>
@stop