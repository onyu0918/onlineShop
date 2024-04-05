<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index(Request $request) {
        $perPage = 3;
        // $sql = 'SELECT * FROM sub_categories order by `sub_categories`.`created_at` desc';
        $sql = 'SELECT categories.id as cid,categories.name as cname,categories.slug as cslug,categories.status as cstatus,category_id,categories.updated_at as cupdated_at,sub_categories.id,sub_categories.name,sub_categories.slug,sub_categories.status,sub_categories.created_at,sub_categories.updated_at,categories.image FROM sub_categories LEFT JOIN `categories` ON `categories`.`id` = `sub_categories`.`category_id` order by `sub_categories`.`created_at` DESC';

        $subCategories = DB::table(DB::raw("({$sql}) as aaa"))
            ->select('*')
            // ->leftJoin('categories','categories.id','sub_categories.categori_id')
            ->where('name', 'LIKE', "%{$request->keyword}%")
            ->paginate($perPage);
        return view('admin.sub_category.list',compact('subCategories'));
    }

    public function create() {
        $categories = DB::select('SELECT * from categories ORDER BY name ASC');
        $data['categories'] = $categories;
        return view('admin.sub_category.create', compact('categories'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            "name"=> "required",
            "slug"=> "required|unique:sub_categories",
            'category'=> "required",
            'status'=> 'required'
        ]);

        if($validator->passes()) {
            DB::insert('INSERT INTO sub_categories (name, slug, status, category_id) values (?, ?, ?, ?)',
            [
                $request->name,
                $request->slug,
                $request->status,
                $request->category
            ]);

            // $request->session()->flash('success','successed');
            session()->flash('success','successed');
            return response()->json([
                'status' => true,
                'message' => 'success',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }

    public function edit($id, Request $request) {
        $sub_categories = DB::select('SELECT * FROM sub_categories where id = ?',[
            $id
        ]);
        if(empty($sub_categories)) {
            return redirect()->route('sub-categories.index');
        }

        $categories = DB::select('SELECT * from categories ORDER BY name ASC');
        $data['categories'] = $categories;
        return view('admin.sub_category.edit',compact('categories','sub_categories'));
    }





    public function update($categoryId, Request $request) {
        $categories = DB::select('SELECT * FROM sub_categories where id = ?',[
            $categoryId
        ]);
        if(empty($categories)) {
            session()->flash('error','Empty');
            return response()->json([
                'status'=> false,
                'notFound'=> true,
                'message'=> 'not found',
            ]);
        }
        $validator = Validator::make($request->all(),[
            "name"=> "required",
            "slug"=> "required|unique:categories,slug,".$categories[0]->id.",id",
            "category_id"=> "required",
        ]);
        if($validator->passes()) {
            DB::update('UPDATE sub_categories set name = ?, slug = ?, status = ?, category_id = ? where id = ?',
            [
                $request->name,
                $request->slug,
                $request->status,
                $request->category_id,
                $categories[0]->id
            ]);
            // $request->session()->flash('success','successed');
            session()->flash('success','successed');
            return response()->json([
                'status' => true,
                'message' => 'success',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }
    public function destroy($categoryId, Request $request) {
        $categories = DB::select('SELECT * FROM sub_categories where id = ?',[
            $categoryId
        ]);
        if(empty($categories)) {
            session()->flash('error','fail!');
            return response()->json([
                'status' => false,
                'message' => 'fail',
            ]);
        }
        DB::delete('DELETE from sub_categories where id = ?',[
            $categoryId
        ]);
        session()->flash('success','success');
        return response()->json([
            'status' => true,
            'message' => 'success',
        ]);
    }
}
