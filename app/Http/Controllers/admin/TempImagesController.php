<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TempImagesController extends Controller
{
    public function create(Request $request)  {
        $image = $request->image;

        if(!empty($image)) {
            $ext = $image->getClientOriginalExtension();
            $newName = time() .".". $ext;

            DB::insert('INSERT INTO temp_images (name) values (?)',
            [
                $newName
            ]);
            $tempImage = DB::select("SELECT * FROM temp_images where name = ?",[
                $newName
            ]);

            $image->move(public_path() .'/temp', $newName);
            return response()->json([
                'status'=> true,
                'image_id'=> $tempImage[0]->id,
                'message'=> "success"
            ]);
        }

    }
}
