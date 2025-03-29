<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourcesRequest;
use App\Http\Resources\Resources;
use App\Models\Resource;

class ResourceController extends Controller
{

    public function index()
    {
        return Resources::collection(Resource::all());
    }

    public function store(ResourcesRequest $request)
    {
        Resource::query()->create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Resource created'], 201);
    }

}
