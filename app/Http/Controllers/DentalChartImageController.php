<?php

namespace App\Http\Controllers;

use App\Models\DentalChart;
use App\Models\DentalChartImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DentalChartImageController extends Controller
{
    /**
     * Store a newly created image in storage.
     */
    public function store(Request $request, DentalChart $dentalChart)
    {
        $validatedData = $request->validate([
            'image' => 'required|file|mimes:jpeg,jpg,png|max:10240',
            'image_type' => 'required|in:xray,photo,diagram',
            'description' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dental_chart_images', $filename, 'public');
            
            $validatedData['dental_chart_id'] = $dentalChart->id;
            $validatedData['image_path'] = $path;
            $validatedData['uploaded_at'] = now();

            $image = DentalChartImage::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => $image->load('creator')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided'
        ], 400);
    }

    /**
     * Display the specified image.
     */
    public function show(DentalChartImage $dentalChartImage)
    {
        if ($dentalChartImage->image_path && Storage::disk('public')->exists($dentalChartImage->image_path)) {
            return Storage::disk('public')->response($dentalChartImage->image_path);
        }

        abort(404, 'Image not found');
    }

    /**
     * Remove the specified image from storage.
     */
    public function destroy(DentalChartImage $dentalChartImage)
    {
        $dentalChartImage->deleteImage();
        $dentalChartImage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}
