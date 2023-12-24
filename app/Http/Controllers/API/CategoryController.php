<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/categories",
     *     @OA\Response(response="200", description="List Categories.")
     * )
     */
    function getAll()
    {
        $list = Categories::all();
        return response()->json($list, 200, ['Charset' => 'utf-8']);
    }

    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/categories",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    function create(Request $request)
    {
        $input = $request->all();
        $image = $request->file('image');

        $manager = new ImageManager(new Driver());

        $imageName = uniqid() . ".webp";

        $folderName = "upload";
        $folderPath = public_path($folderName);
        if (!file_exists($folderPath) && !is_dir($folderPath)) {
            mkdir($folderPath, 0777);
        }

        $sizes = [50, 150, 300, 600, 1200];
        foreach ($sizes as $size) {
            $imageSave = $manager->read($image);
            $imageSave->scale(width: $size);
            $imageSave->toWebp()->save($folderPath . "/" . $size . "_" . $imageName);
        }

        $input["image"] = $imageName;
        $category = Categories::create($input);

        return response()->json($category, 200, ["Charset" => "utf-8"]);
    }

    /**
     * @OA\Delete(
     *     tags={"Category"},
     *     path="/api/categories/{id}",
     *     @OA\Response(response="200", description="Delete Category."),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to delete",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     )
     * )
     */
    function delete($id)
    {
        Categories::destroy($id);

        return response()->json('Successfully deleted!', 200, ["Charset" => "utf-8"]);
    }
}
