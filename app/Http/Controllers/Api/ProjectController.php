<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('is_published', true)->orderBy('created_at','desc')->get();
        return response()->json($projects);
    }
    
    public function showBySlug($slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();
        return response()->json($project);
    }


    public function adminIndex()
    {
        return response()->json(Project::orderBy('created_at','desc')->get());
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title'=>'required|string',
            'short_description'=>'nullable|string',
            'long_description'=>'nullable|string',
            'stack'=>'nullable|array',
            'images.*'=>'nullable|image|max:2048',
            'github_link'=>'nullable|url',
            'demo_link'=>'nullable|url',
            'video_link'=>'nullable|url',
            'is_published'=>'boolean'
        ]);
        if ($v->fails()) return response()->json($v->errors(),422);

        $data = $request->only(['title','short_description','long_description','stack','github_link','demo_link','video_link','is_published']);
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);

        // images
        $images = $request->input('existing_images') ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('projects','public');
                $images[] = $path;
            }
            $data['images'] = $images;
        } else {
            $data['images'] = $images;
        }

        if(isset($data['stack']) && !is_array($data['stack'])) {
            // attempt to decode json
            $decoded = json_decode($data['stack'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['stack'] = $decoded;
            } else {
                $data['stack'] = [];
            }
        }

        $project = Project::create($data);
        return response()->json($project,201);
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $data = $request->only(['title','short_description','long_description','stack','github_link','demo_link','video_link','is_published']);
        if ($request->has('title')) {
            $project->slug = Str::slug($request->title).'-'.Str::random(4);
        }
        // handle new images appended
        $imgs = $project->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('projects','public');
                $imgs[] = $path;
            }
            $data['images'] = $imgs;
        } else if ($request->has('existing_images')) {
            $data['images'] = $request->input('existing_images');
        }

        if(isset($data['stack']) && !is_array($data['stack'])) {
            $decoded = json_decode($data['stack'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['stack'] = $decoded;
            }
        }

        $project->update($data);
        return response()->json($project);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        // delete images from storage
        if ($project->images) {
            foreach ($project->images as $img) {
                Storage::disk('public')->delete($img);
            }
        }
        $project->delete();
        return response()->json(['message'=>'Deleted']);
    }

    public function deleteImage($id, Request $request)
    {
        $project = Project::findOrFail($id);
        $image = $request->input('image'); // path
        if (!$image) return response()->json(['message'=>'Image required'],422);

        $imgs = $project->images ?: [];
        $imgs = array_values(array_filter($imgs, fn($i) => $i !== $image));
        $project->images = $imgs;
        $project->save();
        Storage::disk('public')->delete($image);
        return response()->json($project);
    }
}
