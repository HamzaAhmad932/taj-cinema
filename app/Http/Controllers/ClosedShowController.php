<?php

namespace App\Http\Controllers;

use App\Show;
use App\Share;
use Exception;
use App\ShowClosed;
use App\AccountDetail;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

class ClosedShowController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{
    use BreadRelationshipParser;

    private $base_url = 'vendor.voyager.shows.';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);
        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        // Check permission
        $this->authorize('browse', app($dataType->model_name));
        $getter = $dataType->server_side ? 'paginate' : 'get';
        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];
        $searchable = $dataType->server_side ? array_keys(SchemaManager::describeTable(app($dataType->model_name)->getTable())->toArray()) : '';
        $orderBy = $request->get('order_by', $dataType->order_column);
        $sortOrder = $request->get('sort_order', null);
        $orderColumn = [];
        if ($orderBy) {
            $index = $dataType->browseRows->where('field', $orderBy)->keys()->first() + 1;
            $orderColumn = [[$index, 'desc']];
            if (!$sortOrder && isset($dataType->order_direction)) {
                $sortOrder = $dataType->order_direction;
                $orderColumn = [[$index, $dataType->order_direction]];
            } else {
                $orderColumn = [[$index, 'desc']];
            }
        }
        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model::select('*');
            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');
            if ($search->value && $search->key && $search->filter) {
                $search_filter = ($search->filter == 'equals') ? '=' : 'LIKE';
                $search_value = ($search->filter == 'equals') ? $search->value : '%'.$search->value.'%';
                $query->where($search->key, $search_filter, $search_value);
            }
            if ($orderBy && in_array($orderBy, $dataType->fields())) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'desc';
                $dataTypeContent = call_user_func([
                    $query->orderBy($orderBy, $querySortOrder),
                    $getter,
                ]);
            } elseif ($model->timestamps) {
                $dataTypeContent = call_user_func([$query->latest($model::CREATED_AT), $getter]);
            } else {
                $dataTypeContent = call_user_func([$query->orderBy($model->getKeyName(), 'DESC'), $getter]);
            }
            // Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }
        // Check if BREAD is Translatable
        if (($isModelTranslatable = is_bread_translatable($model))) {
            $dataTypeContent->load('translations');
        }
        // Check if server side pagination is enabled
        $isServerSide = isset($dataType->server_side) && $dataType->server_side;
        // Check if a default search key is set
        $defaultSearchKey = isset($dataType->default_search_key) ? $dataType->default_search_key : null;
        $view = 'voyager::bread.browse';
        if (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }

        $shows = ShowClosed::all();

        return Voyager::view($view, compact(
            'dataType',
            'dataTypeContent',
            'isModelTranslatable',
            'search',
            'orderBy',
            'orderColumn',
            'sortOrder',
            'searchable',
            'isServerSide',
            'defaultSearchKey',
            'shows'
        ));
    }

    // public function index()
    // {
    //     $allClosedShows = ShowClosed::all();

    //     return view($this->base_url.'closed-show', [
    //         'shows'=> $allClosedShows
    //     ]);
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeShare(Request $request)
    {

        try {

            $update = Share::find($request->show_closed_id);
            if(isset($update)){
                $update->staff = $request->staff;
                $update->representative = $request->representative;
                $update->distributor = $request->distributor;
                $update->others = $request->others;
                $update->collection = $request->collection;
                $update->save();

                /***** Here Update Sales Account****/


                return response()->json([
                    'status'=> 'success',
                    'status_code'=> 200,
                    'message'=> 'Record Updated Successfully.'
                ]);
            }
            /**********************
            * Account Details Entry
            ***********************
            */

            $show = Show::findOrFail($request->show_id);
            $show_closed = ShowClosed::findOrFail($request->show_closed_id);

            /*******************************
            *  Sale Revenue Account Credited
            ********************************
            ***/

            $acc_detail = new AccountDetail();
            $acc_detail->account_id = 23; //Shows Sale account ID
            $acc_detail->description = "Show Sale account entry for show id : ". $request->show_id.". Amount Credited:".$show_closed->total_amount.".";
            // $accDetail->debit = 
            $acc_detail->credit = $show_closed->total_amount;
            $acc_detail->show_id = $request->show_id;
            $acc_detail->date = $show->show_start_date;
            $acc_detail->save(); 

            
            if($show){

                /****************************
                *  Staff Account Calculations
                *****************************
                ***/ 
                $staff_credit = 0;
                foreach($request->staff as $k => $val){
                    if($k == 'hall'){
                        $staff_credit += $val * $show->hall;
                    }
                    if($k == 'gallary'){
                        $staff_credit += $val * $show->gallery;
                    }
                    if($k == 'box'){
                        $staff_credit += $val * $show->box;
                    }
                }

                /*****************
                * Sale Debit Entry
                ******************
                */
                $acc_detail_d_staff = new AccountDetail();
                $acc_detail_d_staff->account_id = 23; //Shows Sale account ID
                $acc_detail_d_staff->description = "Debit entry for show id : ". $request->show_id.". Amount Debit  to staff account: ".$staff_credit.".";
                $acc_detail_d_staff->debit = $staff_credit;
                // $acc_detail->credit = $show_closed->total_amount;
                $acc_detail_d_staff->show_id = $request->show_id;
                $acc_detail_d_staff->date = $show->show_start_date;
                $acc_detail_d_staff->save(); 


                $acc_detail_c_staff = new AccountDetail();
                $acc_detail_c_staff->account_id = 17; //Staff account ID
                $acc_detail_c_staff->description = "staff account entry for show id : ". $request->show_id.". Amount Credited:".$staff_credit.".";
                // $accDetail->debit = 
                $acc_detail_c_staff->credit = $staff_credit;
                $acc_detail_c_staff->show_id = $request->show_id;
                $acc_detail_c_staff->date = $show->show_start_date;
                $acc_detail_c_staff->save(); 

                /****************************
                *  Representative Account Calculations
                *****************************
                ***/ 

                $rep_credit = 0;
                foreach ($request->representative as $k => $val) {
                    if($k == 'hall'){
                        $rep_credit += $val * $show->hall;
                    }
                    if($k == 'gallary'){
                        $rep_credit += $val * $show->gallery;
                    }
                    if($k == 'box'){
                        $rep_credit += $val * $show->box;
                    }
                }

                /*****************
                * Sale Debit Entry
                ******************
                */
                $acc_detail_d_rep = new AccountDetail();
                $acc_detail_d_rep->account_id = 23; //Shows Sale account ID
                $acc_detail_d_rep->description = "Debit entry for show id : ". $request->show_id.". Amount Debit  to Representative account: ".$rep_credit.".";
                $acc_detail_d_rep->debit = $rep_credit;
                // $acc_detail->credit = $show_closed->total_amount;
                $acc_detail_d_rep->show_id = $request->show_id;
                $acc_detail_d_rep->date = $show->show_start_date;
                $acc_detail_d_rep->save();


                $acc_detail_c_rep = new AccountDetail();
                $acc_detail_c_rep->account_id = 18; //Representative account ID
                $acc_detail_c_rep->description = "representative account entry for show id : ". $request->show_id.". Amount Credited:".$rep_credit.".";
                // $accDetail->debit = 
                $acc_detail_c_rep->credit = $rep_credit;
                $acc_detail_c_rep->show_id = $request->show_id;
                $acc_detail_c_rep->date = $show->show_start_date;
                $acc_detail_c_rep->save(); 

                /****************************
                *  Distributor Account Calculations
                *****************************
                ***/ 
                
                $first_remaning = $show_closed->total_amount - ($rep_credit + $staff_credit);
                $distributor_credit = ($request->distributor['distributor_percentage']/100) * $first_remaning; 

                /*****************
                * Sale Debit Entry
                ******************
                */
                $acc_detail_d_dist = new AccountDetail();
                $acc_detail_d_dist->account_id = 23; //Shows Sale account ID
                $acc_detail_d_dist->description = "Debit entry for show id : ". $request->show_id.". Amount Debit  to Distributor account: ".$distributor_credit.".";
                $acc_detail_d_dist->debit = $distributor_credit;
                // $acc_detail->credit = $show_closed->total_amount;
                $acc_detail_d_dist->show_id = $request->show_id;
                $acc_detail_d_dist->date = $show->show_start_date;
                $acc_detail_d_dist->save();


                $acc_detail_c_dist = new AccountDetail();
                $acc_detail_c_dist->account_id = 19; //Distributor account ID
                $acc_detail_c_dist->description = "Distributor account entry for show id : ". $request->show_id.". Amount Credited:".$distributor_credit.".";
                // $accDetail->debit = 
                $acc_detail_c_dist->credit = $distributor_credit;
                $acc_detail_c_dist->show_id = $request->show_id;
                $acc_detail_c_dist->date = $show->show_start_date;
                $acc_detail_c_dist->save(); 
                
                $second_remaining = $first_remaning - $distributor_credit;

                /****************************
                *  Salary Account Calculations
                *****************************
                ***/ 

                $salary_debit = ($request->others['salary_percentage']/100) * $second_remaining;

                $acc_detail_d_salary = new AccountDetail();
                $acc_detail_d_salary->account_id = 23; //Shows Sale account ID
                $acc_detail_d_salary->description = "Debit entry for show id : ". $request->show_id.". Amount Debit  to Salary account: ".$salary_debit.".";
                $acc_detail_d_salary->debit = $salary_debit;
                // $acc_detail->credit = $show_closed->total_amount;
                $acc_detail_d_salary->show_id = $request->show_id;
                $acc_detail_d_salary->date = $show->show_start_date;
                $acc_detail_d_salary->save();

                $acc_detail_c_salary = new AccountDetail();
                $acc_detail_c_salary->account_id = 20; //salary account ID
                $acc_detail_c_salary->description = "Salary account entry for show id: ". $request->show_id.". Amount Credited:".$salary_debit.".";
                // $accDetail->debit = 
                $acc_detail_c_salary->credit = $salary_debit;
                $acc_detail_c_salary->show_id = $request->show_id;
                $acc_detail_c_salary->date = $show->show_start_date;
                $acc_detail_c_salary->save(); 

                /****************************
                *  AC Account Calculations
                *****************************
                ***/ 

                $ac_credit = ($request->others['ac_percentage']/100) * ($second_remaining);

                $acc_detail_d_ac = new AccountDetail();
                $acc_detail_d_ac->account_id = 23; //Shows Sale account ID
                $acc_detail_d_ac->description = "Debit entry for show id : ". $request->show_id.". Amount Debit  to AC account: ".$ac_credit.".";
                $acc_detail_d_ac->debit = $ac_credit;
                // $acc_detail->credit = $show_closed->total_amount;
                $acc_detail_d_ac->show_id = $request->show_id;
                $acc_detail_d_ac->date = $show->show_start_date;
                $acc_detail_d_ac->save();

                $acc_detail_c_ac = new AccountDetail();
                $acc_detail_c_ac->account_id = 21; //ac account ID
                $acc_detail_c_ac->description = "AC account entry for show id: ". $request->show_id.". Amount Credited:".$ac_credit.".";
                // $accDetail->debit = 
                $acc_detail_c_ac->credit = $ac_credit;
                $acc_detail_c_ac->show_id = $request->show_id;
                $acc_detail_c_ac->date = $show->show_start_date;
                $acc_detail_c_ac->save();


                $collection = $salary_debit + $ac_credit;

                $third_remaining = $second_remaining - $collection;

                /*****************
                * One Rupee Entry
                ******************
                */
                $acc_detail_d_one = new AccountDetail();
                $acc_detail_d_one->account_id = 23; //Shows Sale account ID
                $acc_detail_d_one->description = "Debit entry for show id : ". $request->show_id.". Amount Debit  to One Rupee account: ".$request->others['rupee_percentage'].".";
                $acc_detail_d_one->debit = $request->others['rupee_percentage'];
                // $acc_detail->credit = $show_closed->total_amount;
                $acc_detail_d_one->show_id = $request->show_id;
                $acc_detail_d_one->date = $show->show_start_date;
                $acc_detail_d_one->save();

                $acc_detail_c_one = new AccountDetail();
                $acc_detail_c_one->account_id = 22; //Staff account ID
                $acc_detail_c_one->description = "AC account entry for show id: ". $request->show_id.". Amount Credited:".$ac_credit.".";
                // $accDetail->debit = 
                $acc_detail_c_one->credit = $request->others['rupee_percentage'];
                $acc_detail_c_one->show_id = $request->show_id;
                $acc_detail_c_one->date = $show->show_start_date;
                $acc_detail_c_one->save();

                $profit = $third_remaining - $request->others['rupee_percentage'];
                

            }


            $share = new Share();
            $share->staff = $request->staff;
            $share->show_closed_id = $request->show_closed_id;
            $share->representative = $request->representative;
            $share->distributor = $request->distributor;
            $share->others = $request->others;
            $share->collection = $request->collection;
            $share->save();

            // $accoutDetail = new AccountDetail();

            return response()->json([
                'status'=> 'success',
                'status_code'=> 200,
                'message'=> 'Record Saved Successfully.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'=> 'fail',
                'status_code'=> 422,
                'message'=> $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function show(Request $request, $id)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $dataTypeContent = call_user_func([$model, 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }
        // Replace relationships' keys for labels and create READ links if a slug is provided.
        $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType, true);
        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'read');
        // Check permission
        $this->authorize('read', $dataTypeContent);
        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);
        $view = 'voyager::bread.read';
        if (view()->exists("voyager::$slug.read")) {
            $view = "voyager::$slug.read";
        }
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fetchShow($id)
    {
        $show = Share::where('show_closed_id', $id)->first();
        if(isset($show)){
            return response()->json([
                'status'=> 'success',
                'status_code'=> 200,
                'data'=> $show
            ]);

        } else {
            return response()->json([
                'status'=> 'success',
                'status_code'=> 404,
                'data'=> false
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     //
    // }

    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name
        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }
        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');
        // Check permission
        $this->authorize('edit', $dataTypeContent);
        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);
        $view = 'voyager::bread.edit-add';
        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }
    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        // Compatibility with Model binding.
        $id = $id instanceof Model ? $id->{$id->getKeyName()} : $id;
        $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
        // Check permission
        $this->authorize('edit', $data);
        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id);
        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }
        if (!$request->ajax()) {
            $this->insertUpdateData($request, $slug, $dataType->editRows, $data);
            event(new BreadDataUpdated($dataType, $data));
            return redirect()
                ->route("voyager.{$dataType->slug}.index")
                ->with([
                    'message'    => __('voyager::generic.successfully_updated')." {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }
    //***************************************
    //
    //                   /\
    //                  /  \
    //                 / /\ \
    //                / ____ \
    //               /_/    \_\
    //
    //
    // Add a new item of our Data Type BRE(A)D
    //
    //****************************************
    public function create(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        // Check permission
        $this->authorize('add', app($dataType->model_name));
        $dataTypeContent = (strlen($dataType->model_name) != 0)
                            ? new $dataType->model_name()
                            : false;
        foreach ($dataType->addRows as $key => $row) {
            $dataType->addRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }
        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'add');
        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);
        $view = 'voyager::bread.edit-add';
        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }
    /**
     * POST BRE(A)D - Store data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        // Check permission
        $this->authorize('add', app($dataType->model_name));
        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);
        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }
        if (!$request->has('_validate')) {
            $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());
            event(new BreadDataAdded($dataType, $data));
            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $data]);
            }
            return redirect()
                ->route("voyager.{$dataType->slug}.index")
                ->with([
                        'message'    => __('voyager::generic.successfully_added_new')." {$dataType->display_name_singular}",
                        'alert-type' => 'success',
                    ]);
        }
    }
    //***************************************
    //                _____
    //               |  __ \
    //               | |  | |
    //               | |  | |
    //               | |__| |
    //               |_____/
    //
    //         Delete an item BREA(D)
    //
    //****************************************
    public function destroy(Request $request, $id)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        // Check permission
        $this->authorize('delete', app($dataType->model_name));
        // Init array of IDs
        $ids = [];
        if (empty($id)) {
            // Bulk delete, get IDs from POST
            $ids = explode(',', $request->ids);
        } else {
            // Single item delete, get ID from URL
            $ids[] = $id;
        }
        foreach ($ids as $id) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
            $this->cleanup($dataType, $data);
        }
        $displayName = count($ids) > 1 ? $dataType->display_name_plural : $dataType->display_name_singular;
        $res = $data->destroy($ids);
        $data = $res
            ? [
                'message'    => __('voyager::generic.successfully_deleted')." {$displayName}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => __('voyager::generic.error_deleting')." {$displayName}",
                'alert-type' => 'error',
            ];
        if ($res) {
            event(new BreadDataDeleted($dataType, $data));
        }
        return redirect()->route("voyager.{$dataType->slug}.index")->with($data);
    }
    /**
     * Remove translations, images and files related to a BREAD item.
     *
     * @param \Illuminate\Database\Eloquent\Model $dataType
     * @param \Illuminate\Database\Eloquent\Model $data
     *
     * @return void
     */
    protected function cleanup($dataType, $data)
    {
        // Delete Translations, if present
        if (is_bread_translatable($data)) {
            $data->deleteAttributeTranslations($data->getTranslatableAttributes());
        }
        // Delete Images
        $this->deleteBreadImages($data, $dataType->deleteRows->where('type', 'image'));
        // Delete Files
        foreach ($dataType->deleteRows->where('type', 'file') as $row) {
            if (isset($data->{$row->field})) {
                foreach (json_decode($data->{$row->field}) as $file) {
                    $this->deleteFileIfExists($file->download_link);
                }
            }
        }
    }
    /**
     * Delete all images related to a BREAD item.
     *
     * @param \Illuminate\Database\Eloquent\Model $data
     * @param \Illuminate\Database\Eloquent\Model $rows
     *
     * @return void
     */
    public function deleteBreadImages($data, $rows)
    {
        foreach ($rows as $row) {
            if ($data->{$row->field} != config('voyager.user.default_avatar')) {
                $this->deleteFileIfExists($data->{$row->field});
            }
            if (isset($row->details->thumbnails)) {
                foreach ($row->details->thumbnails as $thumbnail) {
                    $ext = explode('.', $data->{$row->field});
                    $extension = '.'.$ext[count($ext) - 1];
                    $path = str_replace($extension, '', $data->{$row->field});
                    $thumb_name = $thumbnail->name;
                    $this->deleteFileIfExists($path.'-'.$thumb_name.$extension);
                }
            }
        }
        if ($rows->count() > 0) {
            event(new BreadImagesDeleted($data, $rows));
        }
    }
    /**
     * Order BREAD items.
     *
     * @param string $table
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function order(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        // Check permission
        $this->authorize('edit', app($dataType->model_name));
        if (!isset($dataType->order_column) || !isset($dataType->order_display_column)) {
            return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                'message'    => __('voyager::bread.ordering_not_set'),
                'alert-type' => 'error',
            ]);
        }
        $model = app($dataType->model_name);
        $results = $model->orderBy($dataType->order_column, $dataType->order_direction)->get();
        $display_column = $dataType->order_display_column;
        $dataRow = Voyager::model('DataRow')->whereDataTypeId($dataType->id)->whereField($display_column)->first();
        $view = 'voyager::bread.order';
        if (view()->exists("voyager::$slug.order")) {
            $view = "voyager::$slug.order";
        }
        return Voyager::view($view, compact(
            'dataType',
            'display_column',
            'dataRow',
            'results'
        ));
    }
    public function update_order(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        // Check permission
        $this->authorize('edit', app($dataType->model_name));
        $model = app($dataType->model_name);
        $order = json_decode($request->input('order'));
        $column = $dataType->order_column;
        foreach ($order as $key => $item) {
            $i = $model->findOrFail($item->id);
            $i->$column = ($key + 1);
            $i->save();
        }
    }
}
