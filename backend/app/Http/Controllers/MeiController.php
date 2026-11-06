<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Mei\StoreMeiRequest;
use App\Http\Requests\Mei\UpdateMeiRequest;
use App\Http\Resources\MeiResource;
use App\Models\Mei;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MeiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $meis = $request->user()
            ->meis()
            ->latest('id')
            ->paginate(15);

        return MeiResource::collection($meis);
    }

    public function store(StoreMeiRequest $request): JsonResponse
    {
        $mei = $request->user()->meis()->create($request->validated());

        return (new MeiResource($mei))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Mei $mei): MeiResource
    {
        return new MeiResource($mei);
    }

    public function update(UpdateMeiRequest $request, Mei $mei): MeiResource
    {
        $mei->update($request->validated());

        return new MeiResource($mei);
    }

    public function destroy(Mei $mei): Response
    {
        $mei->delete();

        return response()->noContent();
    }

    public function restore(Mei $mei): MeiResource|JsonResponse
    {
        if (! $mei->trashed()) {
            return response()->json([
                'message' => 'O MEI já está ativo.',
            ], Response::HTTP_CONFLICT);
        }

        $mei->restore();

        return new MeiResource($mei->refresh());
    }
}
