<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetManufacturer;
use Modules\Asset\Entities\AssetType;
use Modules\Asset\Enums\AssetStatus;

class Select2Controller extends Controller
{

    /**
     * return asset list
     */
    public function getAssets(Request $request): JsonResponse
    {
        $response = [];
        $query = Asset::query()
            ->when($request->search, function ($query) use ($request) {
                return   $query->where('unique_id', 'Like', "%$request->search%")
                    ->where('model', 'Like', "%$request->search%")
                    ->whereHas('type', function ($query) use ($request) {
                        return  $query->where('name', 'Like', "%$request->search%");
                    })
                    ->whereHas('manufacturer', function ($query) use ($request) {
                        $query->where('name', 'Like', "%$request->search%");
                    });
            });

        $list = $query->get();

        foreach ($list as  $data) {
            $response[] = array(
                "id" => $data->id,
                "text" => ucwords($data->name)
            );
        }
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    /**
     * return asset available for assignment list
     */
    public function getOpenAssets(Request $request): JsonResponse
    {
        $response = [];
        $query = Asset::where('status', AssetStatus::Available)
            ->when($request->search, function ($query) use ($request) {
                return   $query->where('unique_id', 'Like', "%$request->search%")
                    ->orWhere('model', 'Like', "%$request->search%")
                    ->orWhereHas('type', function ($query) use ($request) {
                        return  $query->where('name', 'Like', "%$request->search%");
                    })
                    ->orWhereHas('manufacturer', function ($query) use ($request) {
                        $query->where('name', 'Like', "%$request->search%");
                    });
            })
            ->orderBy('model');
        $list = $query->get();
        foreach ($list as  $data) {
            $response[] = array(
                "id" => $data->id,
                "text" => ucwords($data->name)
            );
        }
        return response()->json([
            'success' => true,
            'data' => $response,
            'query' => $query->toSql()
        ]);
    }

    /**
     * return asset type list
     */
    public function getAssetTypes(Request $request): JsonResponse
    {
        $response = [];
        $query = AssetType::query()
            ->select('id', 'name')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            });
        $list = $query->get();
        foreach ($list as  $data) {
            $response[] = array(
                "id" => $data->id,
                "text" => ucwords($data->name)
            );
        }
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    /**
     * return asset manufacturer list
     */
    public function getAssetManufacturers(Request $request): JsonResponse
    {
        $response = [];
        $query = AssetManufacturer::query()
            ->select('id', 'name')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            });
        $list = $query->get();
        foreach ($list as  $data) {
            $response[] = array(
                "id" => $data->id,
                "text" => ucwords($data->name)
            );
        }
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }
}
