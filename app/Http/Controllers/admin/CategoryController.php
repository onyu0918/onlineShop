<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request) {

        $perPage = 3;
        $sql = 'SELECT * FROM categories order by created_at desc';

        $categories = DB::table(DB::raw("({$sql}) as aaa"))
            ->select('*')
            ->where('name', 'LIKE', "%{$request->keyword}%")
            ->paginate($perPage);
        return view('admin.category.list',compact('categories'));
    }
    public function create() {
        return view("admin.category.create");
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            "name"=> "required",
            "slug"=> "required|unique:categories",
        ]);

        if($validator->passes()) {
            DB::insert('INSERT INTO categories (name, slug, status) values (?, ?, ?)',
            [
                $request->name,
                $request->slug,
                $request->status
            ]);
            $category = DB::select("SELECT * FROM categories WHERE slug = ?",[
                $request->slug
            ]);
            if(!empty($request->image_id)) {
                $tempImage = DB::select("SELECT * from temp_images where id = ?",[
                    $request->image_id
                ]);
                $extArray = explode('.',$tempImage[0]->name);
                $ext = last($extArray);
                $newImageName = $category[0]->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage[0]->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath, $dPath);
                DB::update("UPDATE categories set image = ? WHERE id = ?",[
                    $newImageName,
                    $category[0]->id
                ]);
            }
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
    public function edit(Request $request) {
        $categories = DB::select('SELECT * FROM categories where id = ?',[
            $request->category
        ]);
        if(empty($categories)) {
            return redirect()->route('categories.index');
        }
        return view('admin.category.edit',compact('categories'));
    }

    public function update($categoryId, Request $request) {
        $categories = DB::select('SELECT * FROM categories where id = ?',[
            $request->category
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
        ]);
        if($validator->passes()) {
            DB::update('UPDATE categories set name = ?, slug = ?, status = ? where id = ?',
            [
                $request->name,
                $request->slug,
                $request->status,
                $categories[0]->id
            ]);
            $category = DB::select("SELECT * FROM categories WHERE slug = ?",[
                $request->slug
            ]);

            $oldImage = $category[0]->image;

            if(!empty($request->image_id)) {
                $tempImage = DB::select("SELECT * from temp_images where id = ?",[
                    $request->image_id
                ]);
                $extArray = explode('.',$tempImage[0]->name);
                $ext = last($extArray);
                $newImageName = $category[0]->id.'-'.time().'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage[0]->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath, $dPath);
                DB::update("UPDATE categories set image = ? WHERE id = ?",[
                    $newImageName,
                    $category[0]->id
                ]);
                File::delete(public_path().'/uploads/category/'.$oldImage);

            }
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
        $categories = DB::select('SELECT * FROM categories where id = ?',[
            $categoryId
        ]);
        if(empty($categories)) {
            session()->flash('error','fail!');
            return response()->json([
                'status' => false,
                'message' => 'fail',
            ]);
        }
        File::delete(public_path().'/uploads/category/'.$categories[0]->image);
        DB::delete('DELETE from categories where id = ?',[
            $categoryId
        ]);
        session()->flash('success','success');
        return response()->json([
            'status' => true,
            'message' => 'success',
        ]);
    }
}
