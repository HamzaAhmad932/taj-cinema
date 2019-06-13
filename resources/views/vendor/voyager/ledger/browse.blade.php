@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css" />
@stop


@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Account</label>
                                    <select v-model="data.account_id" name="" id="" class="form-control select2" v-select2>
                                        <option value="">None</option>
                                        @foreach($accounts as $account)
                                            <option value="{{$account->id}}">{{$account->account_name}}</option>
                                        @endforeach()
                                    </select>
                                    <span v-if="hasError.account_id" style="color: red;">@{{errMessage.account_id}}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Show Wise Ledger</label>
                                            <input type="checkbox" data-on="Yes" data-off="No" v-model="data.show_wise" @change="toggleShow()"/>
                                        </div> 
                                    </div>
                                    <div class="col-md-9">
                                        <div v-if="data.show_wise">
                                           <div class="form-group">
                                                <label for="">Show</label>
                                                <select v-model="data.show_id" name="" id="" class="form-control select2" v-select2>
                                                    <option value="">None</option>
                                                    @foreach($shows as $show)
                                                        <option value="{{$show->show_id}}">{{$show->show_id}}</option>
                                                    @endforeach()
                                                </select>
                                                <span v-if="hasError.to_date" style="color: red;">@{{errMessage.to_date}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">From Date</label>
                                    <input v-model="data.from_date" type="date" class="form-control" />
                                    <span v-if="hasError.from_date" style="color: red;">@{{errMessage.from_date}}</span>
                                </div>
                            </div>
                            <div class="col-md-6">  
                                <div class="form-group">
                                    <label for="">To Date</label>
                                    <input v-model="data.to_date" type="date" class="form-control" />
                                    <span v-if="hasError.to_date" style="color: red;">@{{errMessage.to_date}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <button @click.prevent="generateLedger()" class="btn btn-primary" style="margin-top: 24px;"><i class="voyager-edit"></i> Generate</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button @click="checkbutton()" class="btn btn-secondary" style="margin-top: 24px;">Print</button>
                                        <!-- <button class="btn btn-secondary" style="margin-top: 24px;">Excel</button> -->
                                        <button class="btn btn-secondary" style="margin-top: 24px;">PDF</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <th>Sr#</th>
                                <th>Date</th>
                                <th>Acc#</th>
                                <th>Account name</th>
                                <th>Particulars</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                            </thead>
                            <tbody>
                                <tr v-for="(en, i) in entry">
                                    <td>@{{i+1}}</td>
                                    <td>@{{en.date}}</td>
                                    <td>@{{en.account_id}}</td>
                                    <td>@{{en.account_name}}</td>
                                    <td>@{{en.description}}</td>
                                    <td>@{{en.debit}}</td>
                                    <td>@{{en.credit}}</td>
                                    <td>@{{en.balance}}</td>
                                </tr>
                                <tr v-if="show_total_balance">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="font-weight: bold;">Total Balance : </td>
                                    <td>@{{total_balance}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script type="text/javascript" src="{{asset('js/moment.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/daterangepicker.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{asset('js/daterangepicker.css')}}" />
<script type="text/javascript" src="{{asset('js/blockui.js')}}"></script>
    <script>
        $(document).ready(function(){
            // $('.toggleswitch').bootstrapToggle();
            // $('input[name="dates"]').daterangepicker();
        });

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
                    data : {
                        show_wise : false,
                        show_id : '',
                        account_id : '',
                        from_date : '',
                        to_date : '',
                    },
                    entry : [{
                        account_id: '',
                        account_name: '',
                        balance: '',
                        created_at: '',
                        credit: '',
                        date: '',
                        debit: '',
                        description: '',
                        id: '',
                        reference:'',
                        show_id: '',
                        updated_at: ''
                    }],
                    show_total_balance : false,
                    total_balance : '',
                    hasError : {
                        show_id : false,
                        account_id : false,
                        from_date : false,
                        to_date : false,
                    },
                    errMessage : {
                        show_id : 'Show reference is required.',
                        account_id : 'Account reference is required.',
                        from_date : 'From date is required.',
                        to_date : 'To date is required.',
                    }
                }
            },

            methods : {
              toggleShow(){
                
              },
              checkbutton(){
                alert('working..');
              },
            generateLedger(){
                let valid = this.validate();
                if(valid){
                    
                    $('body').block({ css: { 
                        border: 'none', 
                        padding: '15px', 
                        backgroundColor: '#000', 
                        '-webkit-border-radius': '10px', 
                        '-moz-border-radius': '10px', 
                        opacity: .5, 
                        color: '#fff' 
                    } });

                    this.show_total_balance = false;

                    axios({
                        url : "{{route('ledger-fetch')}}",
                        method : 'POST',
                        data : this.data
                    }).then((resp)=>{
                        $('body').unblock();
                        this.entry = resp.data.enteries;
                        this.total_balance = resp.data.total_balance;
                        this.show_total_balance = true;
                    })
                    .catch((err)=>{
                        $('body').unblock();
                        console.log(err);
                    });
                }
            },
            validate(){
                
                let self = this;

                self.hasError = {
                    show_id : false,
                    account_id : false,
                    from_date : false,
                    to_date : false,
                };
                let errFlag = false;
                if(self.data.account_id == ''){
                    self.hasError.account_id = true;
                    errFlag = true;
                }
                if(self.data.to_date == ''){
                    self.hasError.to_date = true;
                    errFlag = true;
                }
                if(self.data.from_date == ''){
                    self.hasError.from_date = true;
                    errFlag = true;
                }
                if(self.data.show_wise == true){
                    if(self.data.show_id == ''){
                        self.hasError.show_id = true;
                        errFlag = true;
                    }
                }

                if(errFlag){
                    return false;
                }else{
                    return true;
                }
            }
            },
        });
    </script>
@stop